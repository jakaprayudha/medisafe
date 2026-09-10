<?php

/**
 * ============================================================
 * DASHBOARD PERAWAT
 * ============================================================
 * Endpoint:
 * controller/dashboard/perawatDashboardController.php?action=dashboard
 *
 * Support:
 * - Filter today / week / month / custom
 * - KPI pasien dirawat
 * - Pasien masuk
 * - Tindakan menunggu
 * - Pemberian obat
 * - Daftar pasien dalam perawatan
 * - Tugas keperawatan
 * - Monitoring tanda vital
 * - Progress pelayanan
 * - Alert
 * - Informasi shift
 * ============================================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../database/connect.php';


/* ============================================================
   HELPER
============================================================ */

function responseJson($status, $message, $data = [])
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
 * Escape value untuk LIKE / query sederhana
 */
function cleanValue($value)
{
   global $koneksi;

   return mysqli_real_escape_string(
      $koneksi,
      trim((string)$value)
   );
}


/**
 * Tentukan periode dashboard
 */
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

         $start = $_GET['tanggal_mulai'] ?? $today;

         $end = $_GET['tanggal_selesai'] ?? $today;

         /*
             * Validasi format tanggal
             */
         $startValid = DateTime::createFromFormat(
            'Y-m-d',
            $start
         );

         $endValid = DateTime::createFromFormat(
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


   /*
     * Jika terbalik
     */
   if ($start > $end) {

      $temp  = $start;
      $start = $end;
      $end   = $temp;
   }


   return [
      'type'  => $periode,
      'start' => $start,
      'end'   => $end
   ];
}


/**
 * Ambil nama pasien
 */
function getPatientName($idPatient)
{
   global $koneksi;

   if (
      $idPatient === null ||
      $idPatient === ''
   ) {
      return '-';
   }

   $idPatient = (int)$idPatient;

   $sql = "
        SELECT
            patient_name,
            nomor_rm
        FROM ms_patient
        WHERE id_patient = ?
        LIMIT 1
    ";

   $stmt = mysqli_prepare($koneksi, $sql);

   if (!$stmt) {
      return [
         'nama'      => '-',
         'nomor_rm'  => '-'
      ];
   }

   mysqli_stmt_bind_param(
      $stmt,
      "i",
      $idPatient
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   mysqli_stmt_close($stmt);


   return [
      'nama'     => $row['patient_name'] ?? '-',
      'nomor_rm' => $row['nomor_rm'] ?? '-'
   ];
}


/* ============================================================
   SESSION
============================================================ */

// $idCustomer = $_SESSION['id_customer'] ?? null;
$idCustomer = 1;

/*
 * Tidak ada filter id_perawat.
 * pasien_visit tidak memiliki field relasi perawat.
 * Seluruh pasien diambil global berdasarkan id_customer.
 */
$idPerawat = null;


if (
   $idCustomer === null ||
   $idCustomer === ''
) {

   responseJson(
      false,
      'Session id_customer tidak ditemukan.'
   );
}


/* ============================================================
   ACTION
============================================================ */

$action = $_GET['action'] ?? 'dashboard';


if ($action !== 'dashboard') {

   responseJson(
      false,
      'Action tidak dikenali.'
   );
}


/* ============================================================
   PERIODE
============================================================ */

$period = getPeriod();

$startDate = $period['start'];
$endDate   = $period['end'];


/* ============================================================
   KPI
============================================================ */

$kpi = [
   'total_pasien_dirawat' => 0,
   'pasien_stabil'        => 0,
   'pasien_masuk'         => 0,
   'tindakan_menunggu'    => 0,
   'pemberian_obat'       => 0,
   'obat_sudah_diberikan' => 0
];


/* ============================================================
   TOTAL PASIEN DIRAWAT
============================================================ */

/*
 * status_rawatinap / status_perawatan_inap digunakan
 * untuk mendeteksi pasien rawat inap.
 */

$sql = "
    SELECT COUNT(DISTINCT pv.id_patient) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $kpi['total_pasien_dirawat'] =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


/* ============================================================
   PASIEN STABIL
============================================================ */

/*
 * Untuk sementara status stabil dihitung dari pasien
 * yang memiliki kondisi keluar / status vital normal.
 *
 * Jika sistem sudah memiliki field status klinis khusus,
 * bagian ini bisa diarahkan ke field tersebut.
 */

$sql = "
    SELECT COUNT(DISTINCT pv.id_patient) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
      AND (
            pv.kondisi_masuk IS NULL
            OR pv.kondisi_masuk = ''
            OR LOWER(pv.kondisi_masuk) LIKE '%stabil%'
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $kpi['pasien_stabil'] =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


/* ============================================================
   PASIEN MASUK
============================================================ */

$sql = "
    SELECT COUNT(DISTINCT pv.id_patient) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $kpi['pasien_masuk'] =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


/* ============================================================
   TINDAKAN MENUNGGU
============================================================ */

/*
 * Karena schema tindakan keperawatan khusus belum diberikan,
 * kita menggunakan field tindakan pada pasien_visit.
 *
 * Pasien rawat inap yang belum mempunyai tindakan
 * dianggap membutuhkan tindakan.
 */

$sql = "
    SELECT COUNT(*) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
      AND (
            pv.tindakan IS NULL
            OR TRIM(pv.tindakan) = ''
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $kpi['tindakan_menunggu'] =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


/* ============================================================
   PEMBERIAN OBAT
============================================================ */

/*
 * Belum ada schema tabel pemberian obat keperawatan
 * pada data yang tersedia.
 *
 * Nilai default 0 terlebih dahulu.
 *
 * Jika tersedia tabel medication / pemberian_obat,
 * bagian ini tinggal diarahkan ke tabel tersebut.
 */

$kpi['pemberian_obat']       = 0;
$kpi['obat_sudah_diberikan'] = 0;


/* ============================================================
   DAFTAR PASIEN DALAM PERAWATAN
============================================================ */

$patients = [];


/*
 * Ambil visit terakhir per pasien
 */
$sql = "
    SELECT
        pv.id_visit,
        pv.visit_ID,
        pv.id_patient,
        pv.visit_date,
        pv.visit_time,
        pv.kondisi_masuk,
        pv.kondisi_keluar,
        pv.status_rawatinap,
        pv.status_perawatan_inap,

        mp.patient_name,
        mp.nomor_rm

    FROM pasien_visit pv

    INNER JOIN ms_patient mp
        ON CAST(mp.id_patient AS CHAR) =
           CAST(pv.id_patient AS CHAR)

    INNER JOIN (
        SELECT
            id_patient,
            MAX(id_visit) AS max_id_visit
        FROM pasien_visit
        WHERE id_customer = ?
          AND visit_date BETWEEN ? AND ?
          AND (
                status_rawatinap = 1
                OR status_perawatan_inap = 1
              )
        GROUP BY id_patient
    ) latest

        ON latest.id_patient = pv.id_patient
       AND latest.max_id_visit = pv.id_visit

    WHERE pv.id_customer = ?

    ORDER BY pv.id_visit DESC

    LIMIT 20
";


$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "issi",
      $idCustomer,
      $startDate,
      $endDate,
      $idCustomer
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   while ($row = mysqli_fetch_assoc($result)) {

      /*
         * Status pasien
         */
      $status = 'Stabil';
      $statusClass = 'stable';


      $kondisi = strtolower(
         trim(
            $row['kondisi_masuk'] ?? ''
         )
      );


      if (
         strpos($kondisi, 'kritis') !== false ||
         strpos($kondisi, 'gawat') !== false
      ) {

         $status = 'Perhatian';
         $statusClass = 'critical';
      } elseif (
         strpos($kondisi, 'monitor') !== false ||
         strpos($kondisi, 'observasi') !== false
      ) {

         $status = 'Monitor';
         $statusClass = 'monitor';
      }


      $patients[] = [
         'id_visit'    => (int)$row['id_visit'],
         'visit_ID'    => $row['visit_ID'],
         'id_patient'  => $row['id_patient'],
         'nama'        => $row['patient_name'] ?? '-',
         'nomor_rm'    => $row['nomor_rm'] ?? '-',

         /*
             * Belum ada relasi bed yang pasti pada
             * schema pasien_visit.
             */
         'kamar'       => '-',
         'bed'         => '-',

         'status'      => $status,
         'status_class' => $statusClass,

         'tanggal'     => $row['visit_date'],
         'jam'         => $row['visit_time'] ?? ''
      ];
   }

   mysqli_stmt_close($stmt);
}


/* ============================================================
   TUGAS KEPERAWATAN
============================================================ */

$tasks = [];


/*
 * Tugas 1:
 * Monitoring tanda vital
 *
 * Menggunakan field vital pada pasien_visit.
 */

$sql = "
    SELECT COUNT(*) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
      AND (
            pv.tekanan_darah IS NULL
            OR TRIM(pv.tekanan_darah) = ''
            OR pv.suhu IS NULL
            OR TRIM(pv.suhu) = ''
            OR pv.nadi IS NULL
            OR TRIM(pv.nadi) = ''
            OR pv.respirasi IS NULL
            OR TRIM(pv.respirasi) = ''
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

$vitalWaiting = 0;

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $vitalWaiting =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


$tasks[] = [
   'type'  => 'vital',
   'title' => 'Monitoring Tanda Vital',
   'total' => $vitalWaiting,
   'time'  => '09:00',
   'icon'  => 'solar:temperature-bold',
   'class' => 'orange'
];


/*
 * Tugas pemberian obat
 */
$tasks[] = [
   'type'  => 'medication',
   'title' => 'Pemberian Obat',
   'total' => 0,
   'time'  => '10:00',
   'icon'  => 'solar:pills-3-bold',
   'class' => ''
];


/*
 * Dokumentasi tindakan pelayanan
 */
$tasks[] = [
   'type'  => 'nursing_action',
   'title' => 'Tindakan / Dokumentasi Pelayanan',
   'total' => $kpi['tindakan_menunggu'],
   'time'  => '11:00',
   'icon'  => 'solar:medical-kit-bold',
   'class' => 'green'
];


/*
 * Dokumentasi
 */
$rmeIncomplete = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
      AND (
            pv.anamnesa IS NULL
            OR TRIM(pv.anamnesa) = ''
            OR pv.diagnosa IS NULL
            OR TRIM(pv.diagnosa) = ''
            OR pv.tindakan IS NULL
            OR TRIM(pv.tindakan) = ''
          )
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $rmeIncomplete =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


$tasks[] = [
   'type'  => 'documentation',
   'title' => 'Dokumentasi RME',
   'total' => $rmeIncomplete,
   'time'  => 'Prioritas',
   'icon'  => 'solar:document-text-bold',
   'class' => 'red'
];


/* ============================================================
   VITAL TERAKHIR
============================================================ */

$vital = [
   'tekanan_darah' => '-',
   'nadi'          => '-',
   'suhu'          => '-',
   'spo2'          => '-',
   'updated_at'    => null
];


$sql = "
    SELECT
        tekanan_darah,
        nadi,
        suhu,
        saturasi,
        visit_date,
        visit_time
    FROM pasien_visit
    WHERE id_customer = ?
      AND visit_date BETWEEN ? AND ?
      AND (
            status_rawatinap = 1
            OR status_perawatan_inap = 1
          )
      AND (
            tekanan_darah IS NOT NULL
            OR nadi IS NOT NULL
            OR suhu IS NOT NULL
            OR saturasi IS NOT NULL
          )
    ORDER BY
        visit_date DESC,
        id_visit DESC
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   if ($row) {

      $vital = [
         'tekanan_darah' => $row['tekanan_darah'] ?? '-',
         'nadi'          => $row['nadi'] ?? '-',
         'suhu'          => $row['suhu'] ?? '-',
         'spo2'          => $row['saturasi'] ?? '-',
         'updated_at'    =>
         trim(
            ($row['visit_date'] ?? '') .
               ' ' .
               ($row['visit_time'] ?? '')
         )
      ];
   }

   mysqli_stmt_close($stmt);
}


/* ============================================================
   PROGRESS
============================================================ */

/*
 * Progress tanda vital
 */
$totalDirawat = $kpi['total_pasien_dirawat'];

$vitalCompleted = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM pasien_visit pv
    WHERE pv.id_customer = ?
      AND pv.visit_date BETWEEN ? AND ?
      AND (
            pv.status_rawatinap = 1
            OR pv.status_perawatan_inap = 1
          )
      AND pv.tekanan_darah IS NOT NULL
      AND TRIM(pv.tekanan_darah) <> ''
      AND pv.nadi IS NOT NULL
      AND TRIM(pv.nadi) <> ''
      AND pv.suhu IS NOT NULL
      AND TRIM(pv.suhu) <> ''
";

$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {

   mysqli_stmt_bind_param(
      $stmt,
      "iss",
      $idCustomer,
      $startDate,
      $endDate
   );

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);

   $row = mysqli_fetch_assoc($result);

   $vitalCompleted =
      (int)($row['total'] ?? 0);

   mysqli_stmt_close($stmt);
}


$progressVital = $totalDirawat > 0
   ? round(
      ($vitalCompleted / $totalDirawat) * 100
   )
   : 0;


/*
 * Progress dokumentasi
 */
$progressDocumentation =
   $totalDirawat > 0
   ? round(
      (
         ($totalDirawat - $rmeIncomplete)
         / $totalDirawat
      ) * 100
   )
   : 0;


/*
 * Progress tindakan
 */
$progressAction =
   $totalDirawat > 0
   ? round(
      (
         ($totalDirawat - $kpi['tindakan_menunggu'])
         / $totalDirawat
      ) * 100
   )
   : 0;


/*
 * Batasi 0 - 100
 */
$progressVital =
   max(0, min(100, $progressVital));

$progressDocumentation =
   max(0, min(100, $progressDocumentation));

$progressAction =
   max(0, min(100, $progressAction));


$progress = [

   [
      'name'    => 'Tanda Vital',
      'value'   => $progressVital,
      'class'   => ''
   ],

   [
      'name'    => 'Pemberian Obat',
      'value'   => 0,
      'class'   => 'green'
   ],

   [
      'name'    => 'Tindakan Keperawatan',
      'value'   => $progressAction,
      'class'   => 'orange'
   ],

   [
      'name'    => 'Dokumentasi RME',
      'value'   => $progressDocumentation,
      'class'   => 'blue'
   ]
];


/* ============================================================
   ALERT
============================================================ */

$alerts = [];


/*
 * Pasien dengan kondisi yang perlu perhatian
 */
$criticalCount = 0;

foreach ($patients as $patient) {

   if (
      $patient['status_class'] === 'critical'
   ) {

      $criticalCount++;
   }
}


if ($criticalCount > 0) {

   $alerts[] = [
      'type'  => 'danger',
      'title' => 'Pasien Memerlukan Monitoring Ketat',
      'text'  =>
      $criticalCount .
         ' pasien memiliki status yang membutuhkan pemantauan lebih lanjut.',
      'icon'  => 'solar:danger-triangle-bold'
   ];
}


/*
 * Tindakan belum terdokumentasi
 */
if ($kpi['tindakan_menunggu'] > 0) {

   $alerts[] = [
      'type'  => 'warning',
      'title' =>
      $kpi['tindakan_menunggu'] .
         ' Tindakan Belum Didokumentasikan',
      'text' =>
      'Pastikan seluruh tindakan pelayanan dicatat pada RME pasien.',
      'icon' => 'solar:clock-circle-bold'
   ];
}


/*
 * Dokumentasi RME
 */
if ($rmeIncomplete > 0) {

   $alerts[] = [
      'type'  => 'info',
      'title' =>
      'Dokumentasi Asuhan Belum Lengkap',
      'text' =>
      'Terdapat ' .
         $rmeIncomplete .
         ' catatan yang masih membutuhkan kelengkapan data.',
      'icon' => 'solar:document-text-bold'
   ];
}


/* ============================================================
   SHIFT
============================================================ */

/*
 * Belum ada tabel shift perawat yang diberikan.
 *
 * Kita gunakan waktu server untuk menentukan shift
 * sebagai fallback.
 */

$currentHour = (int)date('H');

if (
   $currentHour >= 7 &&
   $currentHour < 14
) {

   $shiftName = 'Pagi';
   $shiftStart = '07:00';
   $shiftEnd = '14:00';
} elseif (
   $currentHour >= 14 &&
   $currentHour < 21
) {

   $shiftName = 'Siang';
   $shiftStart = '14:00';
   $shiftEnd = '21:00';
} else {

   $shiftName = 'Malam';
   $shiftStart = '21:00';
   $shiftEnd = '07:00';
}


/*
 * Jumlah pasien
 */
$shiftPatients = $kpi['total_pasien_dirawat'];


/*
 * Jumlah perawat aktif belum bisa dihitung
 * tanpa tabel shift / jadwal perawat.
 */
$perawatBertugas = 0;


/*
 * Rasio
 */
$ratio = $perawatBertugas > 0
   ? '1 : ' . ceil(
      $shiftPatients / $perawatBertugas
   )
   : '-';


$shift = [

   'name'             => $shiftName,
   'start'            => $shiftStart,
   'end'              => $shiftEnd,
   'perawat_bertugas' => $perawatBertugas,
   'pasien'           => $shiftPatients,
   'rasio'            => $ratio
];


/* ============================================================
   RESPONSE
============================================================ */

responseJson(
   true,
   'Data dashboard pasien global berhasil diambil.',
   [

      /*
         * Periode
         */
      'period' => $period,


      /*
         * Session perawat
         */
      'perawat' => [
         'id' => $idPerawat
      ],


      /*
         * KPI
         */
      'kpi' => $kpi,


      /*
         * Pasien
         */
      'patients' => [
         'total' => count($patients),
         'items' => $patients
      ],


      /*
         * Task
         */
      'tasks' => [
         'total' => count($tasks),
         'items' => $tasks
      ],


      /*
         * Vital
         */
      'vital' => $vital,


      /*
         * Progress
         */
      'progress' => $progress,


      /*
         * Alert
         */
      'alerts' => [
         'total' => count($alerts),
         'items' => $alerts
      ],


      /*
         * Shift
         */
      'shift' => $shift
   ]
);
