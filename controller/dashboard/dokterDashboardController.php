<?php

/**
 * ============================================================
 * DOKTER DASHBOARD CONTROLLER
 * ============================================================
 *
 * Endpoint:
 * controller/dashboard/dokterDashboardController.php?action=dashboard
 *
 * Parameter:
 * ?action=dashboard
 * &period=today
 * &start_date=YYYY-MM-DD
 * &end_date=YYYY-MM-DD
 *
 * Session:
 * $_SESSION['id_customer']
 * $_SESSION['id_doctor']
 *
 * ============================================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../database/connect.php';


/**
 * ============================================================
 * ERROR HANDLER
 * ============================================================
 */

mysqli_report(MYSQLI_REPORT_OFF);


/**
 * ============================================================
 * SESSION CUSTOMER
 * ============================================================
 */

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;


/**
 * ============================================================
 * SESSION DOKTER
 * ============================================================
 *
 * Coba beberapa kemungkinan nama session.
 * Yang paling utama: id_doctor.
 */

$id_doctor = $_SESSION['fullname']
   ?? $_SESSION['id_dokter']
   ?? $_SESSION['doctor_id']
   ?? null;

// $id_doctor = "dr. Devi Eka Pertiwi";


/**
 * ============================================================
 * HELPER RESPONSE
 * ============================================================
 */

function responseJson($status, $message = '', $data = [])
{
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


/**
 * ============================================================
 * VALIDASI DATABASE
 * ============================================================
 */

if (!isset($koneksi)) {

   responseJson(
      false,
      'Koneksi database tidak tersedia.'
   );
}


/**
 * ============================================================
 * VALIDASI CUSTOMER
 * ============================================================
 */

if (!$id_customer) {

   responseJson(
      false,
      'Session id_customer tidak ditemukan.'
   );
}


/**
 * ============================================================
 * VALIDASI DOKTER
 * ============================================================
 */

if (!$id_doctor) {

   responseJson(
      false,
      'Session dokter tidak ditemukan. Pastikan session id_doctor tersedia.'
   );
}


/**
 * ============================================================
 * ACTION
 * ============================================================
 */

$action = $_GET['action'] ?? 'dashboard';


/**
 * ============================================================
 * GET DATE RANGE
 * ============================================================
 */

function getDateRange()
{
   $period = $_GET['period'] ?? 'today';

   $today = new DateTime(
      date('Y-m-d')
   );

   $start = clone $today;
   $end   = clone $today;


   switch ($period) {

      case 'today':

         $start = clone $today;
         $end   = clone $today;

         break;


      case 'yesterday':

         $start = clone $today;
         $start->modify('-1 day');

         $end = clone $start;

         break;


      case '7days':

         $start = clone $today;
         $start->modify('-6 days');

         $end = clone $today;

         break;


      case '30days':

         $start = clone $today;
         $start->modify('-29 days');

         $end = clone $today;

         break;


      case 'thismonth':

         $start = new DateTime(
            date('Y-m-01')
         );

         $end = clone $today;

         break;


      case 'lastmonth':

         $start = new DateTime(
            date('Y-m-01', strtotime('-1 month'))
         );

         $end = new DateTime(
            date('Y-m-t', strtotime('-1 month'))
         );

         break;


      case 'custom':

         $startDate =
            $_GET['start_date'] ?? '';

         $endDate =
            $_GET['end_date'] ?? '';


         if (
            !$startDate ||
            !$endDate
         ) {

            responseJson(
               false,
               'Tanggal custom belum lengkap.'
            );
         }


         $start = DateTime::createFromFormat(
            'Y-m-d',
            $startDate
         );

         $end = DateTime::createFromFormat(
            'Y-m-d',
            $endDate
         );


         if (
            !$start ||
            !$end
         ) {

            responseJson(
               false,
               'Format tanggal custom tidak valid.'
            );
         }


         break;


      default:

         $period = 'today';

         $start = clone $today;
         $end   = clone $today;

         break;
   }


   if ($start > $end) {

      responseJson(
         false,
         'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
      );
   }


   return [
      'type'  => $period,
      'start' => $start->format('Y-m-d'),
      'end'   => $end->format('Y-m-d')
   ];
}


/**
 * ============================================================
 * DATE RANGE
 * ============================================================
 */

$dateRange = getDateRange();

$start_date = $dateRange['start'];
$end_date   = $dateRange['end'];


/**
 * ============================================================
 * DASHBOARD
 * ============================================================
 */

if ($action === 'dashboard') {


   /**
    * ========================================================
    * 1. DOKTER INFO
    * ========================================================
    *
    * Kita ambil id dokter dari pasien_visit terlebih dahulu.
    *
    * Nama dokter akan dicoba dari ms_doctor jika tabel tersedia.
    */

   $doctorName = 'Dokter';

   $doctorCode = (string) $id_doctor;


   /**
    * Coba mengambil nama dokter dari ms_doctor.
    *
    * Karena schema ms_doctor belum diberikan,
    * query dibuat dengan pengecekan tabel terlebih dahulu.
    */

   $checkDoctorTable = mysqli_query(
      $koneksi,
      "SHOW TABLES LIKE 'ms_doctor'"
   );


   if (
      $checkDoctorTable &&
      mysqli_num_rows($checkDoctorTable) > 0
   ) {

      /**
       * Coba struktur yang umum digunakan:
       * id_doctor
       * doctor_name
       */

      $stmt = mysqli_prepare(
         $koneksi,
         "
            SELECT
                id_doctor,
                doctor_name
            FROM ms_doctor
            WHERE id_doctor = ?
            LIMIT 1
            "
      );


      if ($stmt) {

         $doctorParam = (string) $id_doctor;

         mysqli_stmt_bind_param(
            $stmt,
            "s",
            $doctorParam
         );

         mysqli_stmt_execute($stmt);

         $result =
            mysqli_stmt_get_result($stmt);


         if (
            $result &&
            $row = mysqli_fetch_assoc($result)
         ) {

            if (
               !empty($row['doctor_name'])
            ) {

               $doctorName =
                  $row['doctor_name'];
            }
         }

         mysqli_stmt_close($stmt);
      }
   }


   /**
    * ========================================================
    * 2. TOTAL PASIEN
    * ========================================================
    *
    * DISTINCT id_patient
    *
    * Jadi satu pasien dengan beberapa visit
    * tetap dihitung sebagai satu pasien.
    */

   $totalPasien = 0;


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            COUNT(DISTINCT id_patient) AS total
        FROM pasien_visit
        WHERE id_customer = ?
          AND id_doctor = ?
          AND visit_date BETWEEN ? AND ?
          AND id_patient IS NOT NULL
          AND TRIM(id_patient) <> ''
        "
   );


   if ($stmt) {

      mysqli_stmt_bind_param(
         $stmt,
         "iiss",
         $id_customer,
         $id_doctor,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);

      if ($result) {

         $row =
            mysqli_fetch_assoc($result);

         $totalPasien =
            (int) ($row['total'] ?? 0);
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 3. PASIEN MENUNGGU
    * ========================================================
    *
    * Sumber:
    * antrian_poli
    *
    * status:
    * 0 = menunggu
    */

   $pasienMenunggu = 0;


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            COUNT(*) AS total
        FROM antrian_poli
        WHERE id_customer = ?
          AND id_dokter = ?
          AND tanggal BETWEEN ? AND ?
          AND status = 0
        "
   );


   if ($stmt) {

      $doctorParam = (string) $id_doctor;

      mysqli_stmt_bind_param(
         $stmt,
         "ssss",
         $id_customer,
         $doctorParam,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);

      if ($result) {

         $row =
            mysqli_fetch_assoc($result);

         $pasienMenunggu =
            (int) ($row['total'] ?? 0);
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 4. SEDANG DIPERIKSA
    * ========================================================
    *
    * Berdasarkan antrian:
    * status = 1
    *
    * Karena schema antrian_poli:
    * status int
    *
    * Dari data sebelumnya:
    * 0 = Menunggu
    * status lain digunakan untuk proses.
    *
    * Kita hitung pasien dengan visit aktif
    * berdasarkan status antrian.
    */

   $sedangDiperiksa = 0;


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            COUNT(*) AS total
        FROM antrian_poli ap
        INNER JOIN pasien_visit pv
            ON pv.visit_ID = ap.nomor_visit
        WHERE ap.id_customer = ?
          AND ap.id_dokter = ?
          AND ap.tanggal BETWEEN ? AND ?
          AND ap.status = 1
          AND pv.id_customer = ?
          AND pv.id_doctor = ?
        "
   );


   if ($stmt) {

      $doctorParam = (string) $id_doctor;

      mysqli_stmt_bind_param(
         $stmt,
         "sssssi",
         $id_customer,
         $doctorParam,
         $start_date,
         $end_date,
         $id_customer,
         $id_doctor
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);

      if ($result) {

         $row =
            mysqli_fetch_assoc($result);

         $sedangDiperiksa =
            (int) ($row['total'] ?? 0);
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 5. PASIEN SELESAI
    * ========================================================
    *
    * visit_status:
    * status penyelesaian pelayanan.
    *
    * Karena implementasi SIMRS bisa memiliki variasi status,
    * kita gunakan pasien_visit.visit_status_rawatan
    * dan visit_out sebagai indikator selesai.
    *
    * Prioritas:
    * visit_out tidak kosong.
    */

   $pasienSelesai = 0;


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            COUNT(DISTINCT id_patient) AS total
        FROM pasien_visit
        WHERE id_customer = ?
          AND id_doctor = ?
          AND visit_date BETWEEN ? AND ?
          AND id_patient IS NOT NULL
          AND TRIM(id_patient) <> ''
          AND (
                (
                    visit_out IS NOT NULL
                    AND TRIM(visit_out) <> ''
                )
                OR visit_status_rawatan = 1
                OR status_pulang = '1'
              )
        "
   );


   if ($stmt) {

      mysqli_stmt_bind_param(
         $stmt,
         "iiss",
         $id_customer,
         $id_doctor,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);

      if ($result) {

         $row =
            mysqli_fetch_assoc($result);

         $pasienSelesai =
            (int) ($row['total'] ?? 0);
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 6. ANTRIAN DOKTER
    * ========================================================
    */

   $queueItems = [];


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            ap.id,
            ap.nomor,
            ap.poli,
            ap.tanggal,
            ap.nomor_visit,
            ap.status,
            ap.kode_antri,
            ap.jampraktek,

            pv.id_patient,
            pv.visit_ID,
            pv.visit_status,
            pv.visit_status_rawatan,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_datebirth,
            mp.patient_bpjs

        FROM antrian_poli ap

        LEFT JOIN pasien_visit pv
            ON pv.visit_ID = ap.nomor_visit
           AND pv.id_customer = ap.id_customer

        LEFT JOIN ms_patient mp
            ON CAST(mp.id_patient AS CHAR) =
               CAST(pv.id_patient AS CHAR)
           AND mp.id_customer = pv.id_customer

        WHERE ap.id_customer = ?
          AND ap.id_dokter = ?
          AND ap.tanggal BETWEEN ? AND ?

        ORDER BY
            ap.tanggal ASC,
            ap.nomor ASC

        LIMIT 10
        "
   );


   if ($stmt) {

      $doctorParam = (string) $id_doctor;

      mysqli_stmt_bind_param(
         $stmt,
         "ssss",
         $id_customer,
         $doctorParam,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if ($result) {

         while (
            $row = mysqli_fetch_assoc($result)
         ) {

            $status = (int) (
               $row['status'] ?? 0
            );


            if ($status === 0) {

               $statusText = 'Menunggu';
               $statusClass = 'waiting';
            } elseif ($status === 1) {

               $statusText = 'Dipanggil';
               $statusClass = 'called';
            } elseif ($status === 2) {

               $statusText = 'Selesai';
               $statusClass = 'done';
            } else {

               $statusText = 'Proses';
               $statusClass = 'called';
            }


            $patientName =
               $row['patient_name']
               ?? 'Pasien';


            $nomor =
               $row['kode_antri']
               ?: $row['nomor'];


            $queueItems[] = [

               'id' =>
               (int) $row['id'],

               'nomor' =>
               $nomor,

               'nama' =>
               $patientName,

               'nomor_rm' =>
               $row['nomor_rm']
                  ?? '-',

               'poli' =>
               $row['poli']
                  ?? '-',

               'tanggal' =>
               $row['tanggal']
                  ?? '',

               'nomor_visit' =>
               $row['nomor_visit']
                  ?? '',

               'status' =>
               $statusText,

               'status_class' =>
               $statusClass,

               'status_value' =>
               $status,

               'jampraktek' =>
               $row['jampraktek']
                  ?? ''

            ];
         }
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 7. PASIEN BERIKUTNYA
    * ========================================================
    *
    * Prioritas:
    * status = 0
    *
    * Urut berdasarkan nomor antrian.
    */

   $nextPatient = null;


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT

            ap.id,
            ap.nomor,
            ap.kode_antri,
            ap.poli,
            ap.tanggal,
            ap.nomor_visit,
            ap.status,
            ap.jampraktek,

            pv.id_patient,
            pv.visit_ID,
            pv.metode_bayar,
            pv.noKartu,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_datebirth,
            mp.patient_bpjs

        FROM antrian_poli ap

        LEFT JOIN pasien_visit pv
            ON pv.visit_ID = ap.nomor_visit
           AND pv.id_customer = ap.id_customer

        LEFT JOIN ms_patient mp
            ON CAST(mp.id_patient AS CHAR) =
               CAST(pv.id_patient AS CHAR)
           AND mp.id_customer = pv.id_customer

        WHERE ap.id_customer = ?
          AND ap.id_dokter = ?
          AND ap.tanggal = ?
          AND ap.status = 0

        ORDER BY
            ap.nomor ASC

        LIMIT 1
        "
   );


   if ($stmt) {

      $doctorParam = (string) $id_doctor;

      /*
         * tanggal hari ini digunakan untuk pasien berikutnya.
         */

      $todayDate = date('Y-m-d');


      mysqli_stmt_bind_param(
         $stmt,
         "sss",
         $id_customer,
         $doctorParam,
         $todayDate
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if (
         $result &&
         $row = mysqli_fetch_assoc($result)
      ) {

         $nextPatient = [

            'id' =>
            (int) $row['id'],

            'nomor' =>
            $row['kode_antri']
               ?: $row['nomor'],

            'nama' =>
            $row['patient_name']
               ?? 'Pasien',

            'nomor_rm' =>
            $row['nomor_rm']
               ?? '-',

            'id_patient' =>
            $row['id_patient']
               ?? '',

            'nomor_visit' =>
            $row['nomor_visit']
               ?? '',

            'poli' =>
            $row['poli']
               ?? '-',

            'metode_bayar' =>
            $row['metode_bayar']
               ?? '',

            'noKartu' =>
            $row['noKartu']
               ?? '',

            'jampraktek' =>
            $row['jampraktek']
               ?? ''

         ];
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 8. STATISTIK PASIEN PER TANGGAL
    * ========================================================
    */

   $patientChart = [];


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT
            visit_date AS tanggal,
            COUNT(DISTINCT id_patient) AS total

        FROM pasien_visit

        WHERE id_customer = ?
          AND id_doctor = ?
          AND visit_date BETWEEN ? AND ?

        GROUP BY visit_date

        ORDER BY visit_date ASC
        "
   );


   if ($stmt) {

      mysqli_stmt_bind_param(
         $stmt,
         "iiss",
         $id_customer,
         $id_doctor,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if ($result) {

         while (
            $row = mysqli_fetch_assoc($result)
         ) {

            $patientChart[] = [

               'date' =>
               $row['tanggal'],

               'total' =>
               (int) $row['total']

            ];
         }
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 9. RME PERLU DILENGKAPI
    * ========================================================
    *
    * Berdasarkan kolom pasien_visit yang tersedia.
    *
    * Indikator:
    *
    * - anamnesa kosong
    * - diagnosa kosong
    * - tindakan kosong
    * - edukasi kosong
    *
    * Satu visit hanya ditampilkan satu kali.
    */

   $rmeIncomplete = [];


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT

            pv.id_visit,
            pv.visit_ID,
            pv.id_patient,
            pv.visit_date,
            pv.anamnesa,
            pv.diagnosa,
            pv.tindakan,
            pv.edukasi,
            pv.diagnosa_utama,

            mp.patient_name,
            mp.nomor_rm

        FROM pasien_visit pv

        LEFT JOIN ms_patient mp
            ON CAST(mp.id_patient AS CHAR) =
               CAST(pv.id_patient AS CHAR)
           AND mp.id_customer = pv.id_customer

        WHERE pv.id_customer = ?
          AND pv.id_doctor = ?
          AND pv.visit_date BETWEEN ? AND ?

          AND (

                pv.anamnesa IS NULL
                OR TRIM(pv.anamnesa) = ''

                OR pv.diagnosa IS NULL
                OR TRIM(pv.diagnosa) = ''

                OR pv.diagnosa_utama IS NULL
                OR TRIM(pv.diagnosa_utama) = ''

                OR pv.tindakan IS NULL
                OR TRIM(pv.tindakan) = ''

              )

        ORDER BY
            pv.visit_date DESC,
            pv.id_visit DESC

        LIMIT 10
        "
   );


   if ($stmt) {

      mysqli_stmt_bind_param(
         $stmt,
         "iiss",
         $id_customer,
         $id_doctor,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if ($result) {

         while (
            $row = mysqli_fetch_assoc($result)
         ) {

            $description = '';
            $icon = 'solar:document-text-bold';
            $action = 'Lengkapi';


            if (
               empty(trim(
                  (string) $row['anamnesa']
               ))
            ) {

               $description =
                  'Anamnesa belum lengkap';
            } elseif (
               empty(trim(
                  (string) $row['diagnosa']
               )) &&
               empty(trim(
                  (string) $row['diagnosa_utama']
               ))
            ) {

               $description =
                  'Diagnosis belum disimpan';

               $icon =
                  'solar:clipboard-text-bold';
            } elseif (
               empty(trim(
                  (string) $row['tindakan']
               ))
            ) {

               $description =
                  'Tindakan belum disimpan';

               $icon =
                  'solar:stethoscope-bold';
            } else {

               $description =
                  'Dokumentasi medis belum lengkap';
            }


            $rmeIncomplete[] = [

               'id_visit' =>
               (int) $row['id_visit'],

               'visit_ID' =>
               $row['visit_ID']
                  ?? '',

               'id_patient' =>
               $row['id_patient']
                  ?? '',

               'nama' =>
               $row['patient_name']
                  ?? 'Pasien',

               'nomor_rm' =>
               $row['nomor_rm']
                  ?? '-',

               'tanggal' =>
               $row['visit_date'],

               'description' =>
               $description,

               'icon' =>
               $icon,

               'action' =>
               $action

            ];
         }
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 10. DIAGNOSIS TERBANYAK
    * ========================================================
    *
    * Menggunakan:
    * kdDiag1
    * nmDiag1
    *
    * Untuk diagnosis utama.
    */

   $diagnosis = [];


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT

            kdDiag1 AS kode,
            nmDiag1 AS nama,
            COUNT(*) AS total

        FROM pasien_visit

        WHERE id_customer = ?
          AND id_doctor = ?
          AND visit_date BETWEEN ? AND ?

          AND kdDiag1 IS NOT NULL
          AND TRIM(kdDiag1) <> ''

        GROUP BY
            kdDiag1,
            nmDiag1

        ORDER BY
            total DESC

        LIMIT 5
        "
   );


   if ($stmt) {

      mysqli_stmt_bind_param(
         $stmt,
         "iiss",
         $id_customer,
         $id_doctor,
         $start_date,
         $end_date
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if ($result) {

         $rank = 1;

         while (
            $row = mysqli_fetch_assoc($result)
         ) {

            $diagnosis[] = [

               'rank' =>
               str_pad(
                  $rank,
                  2,
                  '0',
                  STR_PAD_LEFT
               ),

               'kode' =>
               $row['kode']
                  ?? '',

               'nama' =>
               $row['nama']
                  ?: 'Tidak diketahui',

               'total' =>
               (int) $row['total']

            ];

            $rank++;
         }
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 11. STATUS PELAYANAN
    * ========================================================
    */

   $percentageSelesai = 0;


   if ($totalPasien > 0) {

      $percentageSelesai =
         round(
            (
               $pasienSelesai /
               $totalPasien
            ) * 100
         );
   }


   if ($percentageSelesai > 100) {

      $percentageSelesai = 100;
   }


   /**
    * ========================================================
    * 12. JAM PRAKTEK
    * ========================================================
    *
    * Diambil dari antrian_poli.jampraktek.
    *
    * Karena belum ada schema jadwal dokter,
    * kita tidak membuat data jadwal palsu.
    */

   $practiceSchedules = [];


   $stmt = mysqli_prepare(
      $koneksi,
      "
        SELECT

            jampraktek,
            poli,
            COUNT(*) AS jumlah_pasien

        FROM antrian_poli

        WHERE id_customer = ?
          AND id_dokter = ?
          AND tanggal = ?

        GROUP BY
            jampraktek,
            poli

        ORDER BY
            jampraktek ASC
        "
   );


   if ($stmt) {

      $doctorParam = (string) $id_doctor;

      $todayDate = date('Y-m-d');


      mysqli_stmt_bind_param(
         $stmt,
         "sss",
         $id_customer,
         $doctorParam,
         $todayDate
      );

      mysqli_stmt_execute($stmt);

      $result =
         mysqli_stmt_get_result($stmt);


      if ($result) {

         while (
            $row = mysqli_fetch_assoc($result)
         ) {

            $practiceSchedules[] = [

               'jampraktek' =>
               $row['jampraktek']
                  ?? '',

               'poli' =>
               $row['poli']
                  ?? '',

               'jumlah_pasien' =>
               (int) $row['jumlah_pasien']

            ];
         }
      }

      mysqli_stmt_close($stmt);
   }


   /**
    * ========================================================
    * 13. RETURN RESPONSE
    * ========================================================
    */

   responseJson(

      true,

      'Data dashboard dokter berhasil diambil.',

      [

         'period' => [

            'type' =>
            $dateRange['type'],

            'start' =>
            $start_date,

            'end' =>
            $end_date

         ],


         /**
          * DOKTER
          */

         'doctor' => [

            'id' =>
            $id_doctor,

            'code' =>
            $doctorCode,

            'name' =>
            $doctorName

         ],


         /**
          * KPI
          */

         'kpi' => [

            'total_pasien' =>
            $totalPasien,

            'pasien_menunggu' =>
            $pasienMenunggu,

            'sedang_diperiksa' =>
            $sedangDiperiksa,

            'selesai' =>
            $pasienSelesai,

            'persentase_selesai' =>
            $percentageSelesai

         ],


         /**
          * PASIEN BERIKUTNYA
          */

         'next_patient' =>
         $nextPatient,


         /**
          * ANTRIAN
          */

         'queue' => [

            'waiting' =>
            $pasienMenunggu,

            'items' =>
            $queueItems

         ],


         /**
          * CHART
          */

         'patient_chart' =>
         $patientChart,


         /**
          * RME
          */

         'rme' => [

            'total' =>
            count($rmeIncomplete),

            'items' =>
            $rmeIncomplete

         ],


         /**
          * DIAGNOSIS
          */

         'diagnosis' =>
         $diagnosis,


         /**
          * JADWAL / PRAKTEK
          */

         'schedule' => [

            'today' =>
            $practiceSchedules

         ]

      ]

   );
}


/**
 * ============================================================
 * UNKNOWN ACTION
 * ============================================================
 */

responseJson(
   false,
   'Action tidak ditemukan.'
);
