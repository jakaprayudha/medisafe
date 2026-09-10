<?php

/**
 * ============================================================
 * DASHBOARD ADMINISTRATOR UTAMA
 * ============================================================
 *
 * Endpoint:
 * controller/dashboard/administratorDashboardController.php?action=dashboard
 *
 * Scope:
 * - GLOBAL / SELURUH FASKES
 * - TIDAK menggunakan id_customer sebagai filter utama
 *
 * Data:
 * - Total Faskes
 * - Faskes Aktif
 * - Faskes Pending
 * - Total Pasien
 * - IDSH Pasien
 * - IDSH Dokter
 * - Faskes Terbaru
 * - Monitoring Database
 * - Monitoring RME
 * - Alert
 * - Statistik periode
 *
 * ============================================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../database/connect.php';


/* ============================================================
   ERROR REPORTING
============================================================ */

mysqli_report(MYSQLI_REPORT_OFF);


/* ============================================================
   RESPONSE
============================================================ */

function responseJson(
   $status,
   $message,
   $data = []
) {

   echo json_encode(
      array_merge(
         [
            'status'  => $status,
            'message' => $message
         ],
         $data
      ),
      JSON_UNESCAPED_UNICODE
   );

   exit;
}


/* ============================================================
   PERIOD
============================================================ */

function getPeriod()
{
   $periode = $_GET['periode'] ?? 'today';

   $today = date('Y-m-d');

   switch ($periode) {

      case 'week':

         $start = date(
            'Y-m-d',
            strtotime('monday this week')
         );

         $end = $today;

         break;


      case 'month':

         $start = date(
            'Y-m-01'
         );

         $end = $today;

         break;


      case 'custom':

         $start =
            $_GET['tanggal_mulai']
            ?? $today;

         $end =
            $_GET['tanggal_selesai']
            ?? $today;


         $startValid =
            DateTime::createFromFormat(
               'Y-m-d',
               $start
            );

         $endValid =
            DateTime::createFromFormat(
               'Y-m-d',
               $end
            );


         if (
            !$startValid ||
            !$endValid
         ) {

            $start = $today;
            $end   = $today;
         }

         break;


      case 'today':

      default:

         $periode = 'today';

         $start = $today;
         $end   = $today;

         break;
   }


   if ($start > $end) {

      $tmp   = $start;
      $start = $end;
      $end   = $tmp;
   }


   return [

      'type'  => $periode,

      'start' => $start,

      'end'   => $end

   ];
}


/* ============================================================
   SAFE QUERY
============================================================ */

function fetchOne($sql)
{
   global $koneksi;

   $result = mysqli_query(
      $koneksi,
      $sql
   );

   if (!$result) {

      return null;
   }

   $row =
      mysqli_fetch_assoc($result);

   mysqli_free_result($result);

   return $row;
}


/* ============================================================
   SAFE COUNT
============================================================ */

function getCount($sql)
{
   $row = fetchOne($sql);

   if (!$row) {

      return 0;
   }

   return (int)(
      $row['total']
      ?? 0
   );
}


/* ============================================================
   FORMAT NUMBER
============================================================ */

function formatNumber($number)
{
   return number_format(
      (float)$number,
      0,
      ',',
      '.'
   );
}


/* ============================================================
   FORMAT PERSENTASE
============================================================ */

function percentage(
   $value,
   $total
) {

   if ((float)$total <= 0) {

      return 0;
   }

   return round(
      (
         (float)$value /
         (float)$total
      ) * 100,
      1
   );
}


/* ============================================================
   TOTAL FASKES
============================================================ */

function getFaskesKPI()
{
   global $koneksi;


   $total = getCount("
        SELECT COUNT(*) AS total
        FROM ms_faskes
    ");


   $aktif = getCount("
        SELECT COUNT(*) AS total
        FROM ms_faskes
        WHERE faskes_status = 1
    ");


   $pending = getCount("
        SELECT COUNT(*) AS total
        FROM ms_faskes
        WHERE faskes_status = 0
           OR faskes_status IS NULL
    ");


   return [

      'total'   => $total,

      'aktif'   => $aktif,

      'pending' => $pending

   ];
}


/* ============================================================
   TOTAL PASIEN
============================================================ */

function getPatientKPI()
{
   /*
     * Menggunakan DISTINCT id_patient
     * agar satu pasien tidak dihitung
     * berulang kali.
     */

   $total = getCount("
        SELECT COUNT(DISTINCT id_patient) AS total
        FROM pasien_visit
        WHERE id_patient IS NOT NULL
          AND TRIM(id_patient) <> ''
    ");


   /*
     * Fallback jika pasien_visit kosong.
     */

   if ($total <= 0) {

      $total = getCount("
            SELECT COUNT(*) AS total
            FROM ms_patient
        ");
   }


   return $total;
}


/* ============================================================
   IDSH PASIEN
============================================================ */

function getPatientIDSH()
{
   $total = getCount("
        SELECT COUNT(*) AS total
        FROM ms_patient
    ");


   $terverifikasi = getCount("
        SELECT COUNT(*) AS total
        FROM ms_patient
        WHERE idsh IS NOT NULL
          AND TRIM(idsh) <> ''
    ");


   return [

      'total' =>
      $total,

      'terverifikasi' =>
      $terverifikasi,

      'persentase' =>
      percentage(
         $terverifikasi,
         $total
      )

   ];
}


/* ============================================================
   IDSH DOKTER
============================================================ */

function getDoctorIDSH()
{
   /*
     * Untuk ms_doctor:
     * - total dokter menggunakan id_doctor
     *
     * Karena schema yang tersedia sebelumnya
     * belum memastikan nama field IDSH dokter,
     * kita hitung total dokter terlebih dahulu.
     */

   $total = getCount("
        SELECT COUNT(*) AS total
        FROM ms_doctor
    ");


   /*
     * Coba beberapa kemungkinan field IDSH.
     *
     * Jika field tidak tersedia,
     * otomatis dianggap 0.
     */

   $terverifikasi = 0;


   $candidateFields = [

      'idsh',
      'doctor_idsh',
      'id_sh',
      'doctor_id_sh'

   ];


   foreach (
      $candidateFields
      as $field
   ) {

      $check = mysqli_query(
         $GLOBALS['koneksi'],
         "
            SHOW COLUMNS
            FROM ms_doctor
            LIKE '" .
            mysqli_real_escape_string(
               $GLOBALS['koneksi'],
               $field
            ) .
            "'
            "
      );


      if (
         $check &&
         mysqli_num_rows($check) > 0
      ) {

         mysqli_free_result(
            $check
         );


         $terverifikasi =
            getCount("
                    SELECT COUNT(*) AS total
                    FROM ms_doctor
                    WHERE `$field` IS NOT NULL
                      AND TRIM(`$field`) <> ''
                ");

         break;
      }


      if ($check) {

         mysqli_free_result(
            $check
         );
      }
   }


   return [

      'total' =>
      $total,

      'terverifikasi' =>
      $terverifikasi,

      'persentase' =>
      percentage(
         $terverifikasi,
         $total
      )

   ];
}


/* ============================================================
   FASKES TERBARU
============================================================ */

function getLatestFaskes()
{
   global $koneksi;


   $sql = "
        SELECT

            mf.id_faskes,

          sc.clinic_name as faskes_code,

            mf.faskes_status,

            mf.faskes_city,

            mf.faskes_district,

            mf.faskes_address,

            mf.faskes_phone,

            mf.created_at,

            sc.clinic_name

        FROM ms_faskes mf
        LEFT JOIN setting_clinic sc ON mf.id_clinic = sc.id

        ORDER BY
            id_faskes DESC

        LIMIT 10
    ";


   $result =
      mysqli_query(
         $koneksi,
         $sql
      );


   if (!$result) {

      return [];
   }


   $data = [];


   while (
      $row =
      mysqli_fetch_assoc($result)
   ) {

      /*
         * Karena schema ms_faskes
         * belum menunjukkan field tipe faskes,
         * jangan mengarang "Rumah Sakit/Klinik/Puskesmas".
         */

      $status =
         (int)(
            $row['faskes_status']
            ?? 0
         );


      if ($status === 1) {

         $statusLabel = 'AKTIF';

         $statusClass =
            'adm-online';
      } else {

         $statusLabel = 'PENDING';

         $statusClass =
            'adm-warning';
      }


      $data[] = [

         'id_faskes' =>
         (int)$row['id_faskes'],

         'faskes_code' =>
         $row['faskes_code']
            ?: '-',

         'faskes_city' =>
         $row['faskes_city']
            ?: '-',

         'faskes_district' =>
         $row['faskes_district']
            ?: '-',

         'faskes_address' =>
         $row['faskes_address']
            ?: '-',

         'faskes_phone' =>
         $row['faskes_phone']
            ?: '-',

         'status' =>
         $status,

         'status_label' =>
         $statusLabel,

         'status_class' =>
         $statusClass,

         'created_at' =>
         $row['created_at']
            ?: null

      ];
   }


   mysqli_free_result(
      $result
   );


   return $data;
}


/* ============================================================
   MONITORING DATABASE
============================================================ */

function getDatabaseStatus()
{
   global $koneksi;


   if (
      $koneksi &&
      mysqli_ping($koneksi)
   ) {

      return [

         'name' =>
         'Database Platform',

         'status' =>
         'online',

         'status_label' =>
         'NORMAL',

         'status_class' =>
         'adm-online',

         'description' =>
         'Database server dan koneksi aplikasi'

      ];
   }


   return [

      'name' =>
      'Database Platform',

      'status' =>
      'error',

      'status_label' =>
      'ERROR',

      'status_class' =>
      'adm-error',

      'description' =>
      'Koneksi database bermasalah'

   ];
}


/* ============================================================
   MONITORING RME
============================================================ */

function getRMEMonitoring(
   $start,
   $end
) {

   global $koneksi;


   /*
     * Kita gunakan field RME yang memang tersedia
     * di pasien_visit.
     */

   $total = getCount("
        SELECT COUNT(*) AS total

        FROM pasien_visit

        WHERE visit_date
              BETWEEN '" .
      mysqli_real_escape_string(
         $koneksi,
         $start
      ) .
      "' AND '" .
      mysqli_real_escape_string(
         $koneksi,
         $end
      ) .
      "'
    ");


   /*
     * RME dianggap lengkap jika
     * field-field utama terisi.
     */

   $lengkap = getCount("
        SELECT COUNT(*) AS total

        FROM pasien_visit

        WHERE visit_date
              BETWEEN '" .
      mysqli_real_escape_string(
         $koneksi,
         $start
      ) .
      "' AND '" .
      mysqli_real_escape_string(
         $koneksi,
         $end
      ) .
      "'

        AND COALESCE(
                NULLIF(TRIM(anamnesa), ''),
                NULL
            ) IS NOT NULL

        AND COALESCE(
                NULLIF(TRIM(diagnosa), ''),
                NULL
            ) IS NOT NULL

        AND COALESCE(
                NULLIF(TRIM(tindakan), ''),
                NULL
            ) IS NOT NULL
    ");


   $tidakLengkap =
      max(
         0,
         $total - $lengkap
      );


   return [

      'total' =>
      $total,

      'lengkap' =>
      $lengkap,

      'tidak_lengkap' =>
      $tidakLengkap,

      'persentase' =>
      percentage(
         $lengkap,
         $total
      )

   ];
}


/* ============================================================
   DATA IMPORT
============================================================ */

function getImportMonitoring()
{
   /*
     * BELUM ADA TABLE IMPORT
     * yang diberikan dalam schema.
     *
     * Jangan membuat angka dummy.
     */

   return [

      'available' =>
      false,

      'message' =>
      'Data import belum memiliki sumber tabel/log yang teridentifikasi.',

      'items' => []

   ];
}


/* ============================================================
   INTEGRATION
============================================================ */

function getIntegrationMonitoring()
{
   /*
     * Belum ada tabel monitoring integration
     * pada schema yang diberikan.
     *
     * Jangan mengklaim SATUSEHAT ONLINE
     * hanya berdasarkan keberadaan konfigurasi.
     */

   return [

      [

         'name' =>
         'SATUSEHAT',

         'description' =>
         'Healthcare interoperability',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning'

      ],


      [

         'name' =>
         'Laboratory',

         'description' =>
         'Laboratory data integration',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning'

      ],


      [

         'name' =>
         'IDSH',

         'description' =>
         'Identitas dokter & pasien',

         'status' =>
         'available',

         'status_label' =>
         'ACTIVE',

         'status_class' =>
         'adm-online'

      ],


      [

         'name' =>
         'API Gateway',

         'description' =>
         'Platform API service',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning'

      ]

   ];
}


/* ============================================================
   ALERT
============================================================ */

function getAlerts(
   $faskes,
   $import,
   $rme
) {

   $alerts = [];


   /*
     * FASKES PENDING
     */

   if (
      $faskes['pending'] > 0
   ) {

      $alerts[] = [

         'type' =>
         'danger',

         'icon' =>
         'solar:danger-triangle-bold',

         'title' =>
         $faskes['pending'] .
            ' Faskes Belum Terverifikasi',

         'text' =>
         'Data fasilitas kesehatan membutuhkan proses verifikasi administrator.'

      ];
   }


   /*
     * IMPORT
     */

   if (
      !empty($import['available'])
   ) {

      /*
         * Reserved untuk implementasi
         * tabel import.
         */
   }


   /*
     * RME
     */

   if (
      $rme['tidak_lengkap'] > 0
   ) {

      $alerts[] = [

         'type' =>
         'info',

         'icon' =>
         'solar:document-text-bold',

         'title' =>
         'Monitoring RME Menemukan Data Tidak Lengkap',

         'text' =>
         formatNumber(
            $rme['tidak_lengkap']
         ) .
            ' data RME pada periode terpilih belum memenuhi kelengkapan.'

      ];
   }


   /*
     * Jika tidak ada alert
     */

   if (
      count($alerts) === 0
   ) {

      $alerts[] = [

         'type' =>
         'info',

         'icon' =>
         'solar:check-circle-bold',

         'title' =>
         'Tidak Ada Alert Kritis',

         'text' =>
         'Tidak ditemukan aktivitas yang membutuhkan tindak lanjut segera.'

      ];
   }


   return $alerts;
}


/* ============================================================
   SYSTEM MONITOR
============================================================ */

function getSystemMonitoring()
{
   $database =
      getDatabaseStatus();


   return [

      [

         'key' =>
         'satusehat',

         'name' =>
         'Integrasi SATUSEHAT',

         'description' =>
         'API connectivity dan pertukaran data',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning',

         'icon' =>
         'solar:link-bold'

      ],


      [

         'key' =>
         'database',

         'name' =>
         $database['name'],

         'description' =>
         $database['description'],

         'status' =>
         $database['status'],

         'status_label' =>
         $database['status_label'],

         'status_class' =>
         $database['status_class'],

         'icon' =>
         'solar:database-bold'

      ],


      [

         'key' =>
         'laboratory',

         'name' =>
         'Laboratory Service',

         'description' =>
         'Integrasi dan sinkronisasi data laboratorium',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning',

         'icon' =>
         'solar:test-tube-bold'

      ],


      [

         'key' =>
         'import',

         'name' =>
         'Data Import Service',

         'description' =>
         'Monitoring proses import data',

         'status' =>
         'unknown',

         'status_label' =>
         'BELUM DIMONITOR',

         'status_class' =>
         'adm-warning',

         'icon' =>
         'solar:cloud-upload-bold'

      ],


      [

         'key' =>
         'rme',

         'name' =>
         'Monitoring RME',

         'description' =>
         'Monitoring kelengkapan data rekam medis elektronik',

         'status' =>
         'normal',

         'status_label' =>
         'NORMAL',

         'status_class' =>
         'adm-online',

         'icon' =>
         'solar:monitor-bold'

      ]

   ];
}


/* ============================================================
   MAIN DASHBOARD
============================================================ */

function getDashboard()
{
   $period =
      getPeriod();


   /*
     * --------------------------------------------------------
     * KPI FASKES
     * --------------------------------------------------------
     */

   $faskes =
      getFaskesKPI();


   /*
     * --------------------------------------------------------
     * KPI PASIEN
     * --------------------------------------------------------
     */

   $totalPasien =
      getPatientKPI();


   /*
     * --------------------------------------------------------
     * IDSH
     * --------------------------------------------------------
     */

   $idshPasien =
      getPatientIDSH();

   $idshDokter =
      getDoctorIDSH();


   /*
     * --------------------------------------------------------
     * RME
     * --------------------------------------------------------
     */

   $rme =
      getRMEMonitoring(
         $period['start'],
         $period['end']
      );


   /*
     * --------------------------------------------------------
     * FASKES TERBARU
     * --------------------------------------------------------
     */

   $latestFaskes =
      getLatestFaskes();


   /*
     * --------------------------------------------------------
     * IMPORT
     * --------------------------------------------------------
     */

   $import =
      getImportMonitoring();


   /*
     * --------------------------------------------------------
     * SYSTEM
     * --------------------------------------------------------
     */

   $system =
      getSystemMonitoring();


   /*
     * --------------------------------------------------------
     * INTEGRATION
     * --------------------------------------------------------
     */

   $integration =
      getIntegrationMonitoring();


   /*
     * --------------------------------------------------------
     * ALERT
     * --------------------------------------------------------
     */

   $alerts =
      getAlerts(
         $faskes,
         $import,
         $rme
      );


   /*
     * --------------------------------------------------------
     * RESPONSE
     * --------------------------------------------------------
     */

   responseJson(

      true,

      'Dashboard administrator berhasil diambil.',

      [

         'server_time' =>
         date('Y-m-d H:i:s'),


         'scope' =>
         'global',


         'period' =>
         $period,


         'kpi' => [

            'faskes' => [

               'total' =>
               $faskes['total'],

               'aktif' =>
               $faskes['aktif'],

               'pending' =>
               $faskes['pending']

            ],


            'pasien' => [

               'total' =>
               $totalPasien

            ],


            'dokter' => [

               'total' =>
               $idshDokter['total'],

               'terverifikasi' =>
               $idshDokter['terverifikasi'],

               'persentase' =>
               $idshDokter['persentase']

            ],


            'import' => [

               'persentase' =>
               null,

               'available' =>
               $import['available']

            ]

         ],


         'idsh' => [

            'dokter' =>
            $idshDokter,

            'pasien' =>
            $idshPasien

         ],


         'faskes' =>
         $latestFaskes,


         'system' =>
         $system,


         'import' =>
         $import,


         'integration' =>
         $integration,


         'rme' =>
         $rme,


         'alerts' =>
         $alerts

      ]
   );
}


/* ============================================================
   ROUTER
============================================================ */

$action =
   $_GET['action']
   ?? 'dashboard';


switch ($action) {

   case 'dashboard':

      getDashboard();

      break;


   default:

      responseJson(
         false,
         'Action dashboard tidak ditemukan.'
      );

      break;
}
