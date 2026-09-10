<?php

/**
 * Dashboard Admisi / Receptionist
 * Endpoint:
 * controller/dashboard/admisiDashboardController.php?action=dashboard
 *
 * Sumber utama:
 * - pasien_visit
 * - ms_patient
 * - ms_doctor
 * - ms_poli
 * - antrian_poli
 *
 * Scope data:
 * - id_customer
 * - periode tanggal pelayanan
 */

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$action = $_GET['action'] ?? 'dashboard';

if ($action !== 'dashboard') {
   response(false, 'Action tidak ditemukan.');
}

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;

if ($id_customer === null || $id_customer === '') {
   response(false, 'Session id_customer tidak ditemukan.');
}

$period = $_GET['period'] ?? 'today';
$start  = $_GET['start'] ?? date('Y-m-d');
$end    = $_GET['end'] ?? date('Y-m-d');

[$start, $end] = normalizePeriod($period, $start, $end);

try {
   $data = [
      'status'  => true,
      'message' => 'Data dashboard admisi berhasil diambil.',
      'period'  => [
         'type'  => $period,
         'start' => $start,
         'end'   => $end
      ],
      'kpi'             => getKpi($koneksi, $id_customer, $start, $end),
      'doctors'         => getDoctorsToday($koneksi, $id_customer, $start, $end),
      'queue'           => getQueue($koneksi, $id_customer, $start, $end),
      'doctor_summary'  => getDoctorSummary($koneksi, $id_customer, $start, $end),
      'poli'            => getPoliSummary($koneksi, $id_customer, $start, $end),
      'patient_types'   => getPatientTypes($koneksi, $id_customer, $start, $end),
      'visit_chart'     => getVisitChart($koneksi, $id_customer, $start, $end)
   ];

   echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
   response(false, 'Gagal mengambil data dashboard admisi.', [
      'error' => $e->getMessage()
   ]);
}


/* =========================================================
   RESPONSE
========================================================= */

function response(bool $status, string $message, array $extra = []): void
{
   echo json_encode(
      array_merge([
         'status'  => $status,
         'message' => $message
      ], $extra),
      JSON_UNESCAPED_UNICODE
   );
   exit;
}


/* =========================================================
   PERIOD
========================================================= */

function normalizePeriod(string $period, string $start, string $end): array
{
   $today = new DateTimeImmutable(date('Y-m-d'));

   switch ($period) {
      case 'yesterday':
         $date = $today->modify('-1 day')->format('Y-m-d');
         return [$date, $date];

      case '7days':
         return [
            $today->modify('-6 days')->format('Y-m-d'),
            $today->format('Y-m-d')
         ];

      case '30days':
         return [
            $today->modify('-29 days')->format('Y-m-d'),
            $today->format('Y-m-d')
         ];

      case 'thismonth':
         return [
            $today->modify('first day of this month')->format('Y-m-d'),
            $today->format('Y-m-d')
         ];

      case 'lastmonth':
         $first = $today->modify('first day of last month');
         $last  = $today->modify('last day of last month');

         return [
            $first->format('Y-m-d'),
            $last->format('Y-m-d')
         ];

      case 'custom':
         if (!validDate($start) || !validDate($end)) {
            throw new Exception('Tanggal custom tidak valid.');
         }

         if ($start > $end) {
            [$start, $end] = [$end, $start];
         }

         return [$start, $end];

      case 'today':
      default:
         return [
            $today->format('Y-m-d'),
            $today->format('Y-m-d')
         ];
   }
}

function validDate(string $date): bool
{
   $d = DateTime::createFromFormat('Y-m-d', $date);
   return $d && $d->format('Y-m-d') === $date;
}


/* =========================================================
   DB HELPER
========================================================= */

function fetchAllRows(mysqli $koneksi, string $sql, string $types = '', array $params = []): array
{
   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      throw new Exception('Prepare gagal: ' . $koneksi->error);
   }

   if ($types !== '' && !empty($params)) {
      $stmt->bind_param($types, ...$params);
   }

   if (!$stmt->execute()) {
      $error = $stmt->error;
      $stmt->close();
      throw new Exception('Execute gagal: ' . $error);
   }

   $result = $stmt->get_result();
   $rows = [];

   if ($result) {
      while ($row = $result->fetch_assoc()) {
         $rows[] = $row;
      }
   }

   $stmt->close();

   return $rows;
}

function fetchOneRow(mysqli $koneksi, string $sql, string $types = '', array $params = []): array
{
   $rows = fetchAllRows($koneksi, $sql, $types, $params);
   return $rows[0] ?? [];
}


/* =========================================================
   KPI
========================================================= */

function getKpi(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            COUNT(DISTINCT pv.id_patient) AS total_pasien,

            COUNT(DISTINCT CASE
                WHEN pv.status_antrian IS NULL
                  OR pv.status_antrian = ''
                  OR pv.status_antrian IN ('0', '1')
                THEN pv.id_patient
            END) AS pasien_menunggu,

            COUNT(DISTINCT CASE
                WHEN pv.status_antrian IN ('2', 'dipanggil', 'called', '3', 'diperiksa', 'examination')
                THEN pv.id_patient
            END) AS sedang_diperiksa,

            COUNT(DISTINCT CASE
                WHEN pv.status_bayar IS NOT NULL
                 AND pv.status_bayar <> 0
                THEN pv.id_patient
            END) AS pelayanan_selesai

        FROM pasien_visit pv
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
    ";

   $row = fetchOneRow(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   $total   = (int)($row['total_pasien'] ?? 0);
   $waiting = (int)($row['pasien_menunggu'] ?? 0);
   $process = (int)($row['sedang_diperiksa'] ?? 0);
   $done    = (int)($row['pelayanan_selesai'] ?? 0);

   /*
     * Fallback status selesai:
     * Jika status_antrian tersedia dan sudah menunjukkan selesai,
     * tetap bisa dihitung sebagai selesai.
     */
   $doneRow = fetchOneRow(
      $koneksi,
      "
        SELECT COUNT(DISTINCT pv.id_patient) AS total
        FROM pasien_visit pv
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
          AND (
                pv.status_antrian IN ('4', 'selesai', 'completed')
                OR pv.visit_out IS NOT NULL
                OR pv.visit_date_out IS NOT NULL
              )
        ",
      'iss',
      [$id_customer, $start, $end]
   );

   $done = max($done, (int)($doneRow['total'] ?? 0));

   $percentage = $total > 0
      ? round(($done / $total) * 100)
      : 0;

   /*
     * Perubahan terhadap periode sebelumnya hanya dihitung
     * jika tersedia periode sebelumnya.
     */
   $previous = getPreviousPeriod($start, $end);

   $prevRow = fetchOneRow(
      $koneksi,
      "
        SELECT COUNT(DISTINCT id_patient) AS total
        FROM pasien_visit
        WHERE id_customer = ?
          AND visit_date BETWEEN ? AND ?
        ",
      'iss',
      [$id_customer, $previous[0], $previous[1]]
   );

   $prevTotal = (int)($prevRow['total'] ?? 0);

   $growth = null;
   if ($prevTotal > 0) {
      $growth = round((($total - $prevTotal) / $prevTotal) * 100, 1);
   }

   return [
      'total_pasien'       => $total,
      'pasien_menunggu'    => $waiting,
      'sedang_diperiksa'   => $process,
      'pelayanan_selesai'  => $done,
      'persentase_selesai' => $percentage,
      'pertumbuhan'        => $growth
   ];
}


/* =========================================================
   DOKTER HARI INI
   Asumsi kehadiran:
   - dokter aktif + memiliki kunjungan pada periode = Hadir
   - dokter aktif tanpa kunjungan = Belum Hadir
========================================================= */


function getDoctorsToday(
   mysqli $koneksi,
   $id_customer,
   string $start,
   string $end
): array {

   $sql = "
        SELECT
            d.id_doctor,
            d.doctor_name,

            COALESCE(
                p_visit.poli_name,
                p_doctor.poli_name,
                '-'
            ) AS poli_name,

            COUNT(pv.id_visit) AS total_visit,

            MIN(
                NULLIF(
                    pv.visit_time,
                    ''
                )
            ) AS jam_masuk

        FROM ms_doctor d

        /* =====================================================
           POLI DARI ms_doctor.id_poli
        ===================================================== */
        LEFT JOIN ms_poli p_doctor
            ON CAST(p_doctor.id_poli AS CHAR)
             = CAST(d.id_poli AS CHAR)
            AND p_doctor.id_customer = d.id_customer

        /* =====================================================
           KUNJUNGAN
        ===================================================== */
        LEFT JOIN pasien_visit pv
            ON pv.id_doctor = d.id_doctor
            AND pv.id_customer = ?
            AND pv.visit_date BETWEEN ? AND ?

        /* =====================================================
           POLI DARI pasien_visit.id_poli
        ===================================================== */
        LEFT JOIN ms_poli p_visit
            ON CAST(p_visit.id_poli AS CHAR)
             = CAST(pv.id_poli AS CHAR)
            AND p_visit.id_customer = pv.id_customer

        WHERE d.doctor_status = 1
          AND d.id_customer = ?

        GROUP BY
            d.id_doctor,
            d.doctor_name,
            d.id_poli,
            p_doctor.poli_name,
            p_visit.poli_name

        ORDER BY
            total_visit DESC,
            d.doctor_name ASC

        LIMIT 50
    ";


   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'issi',
      [
         $id_customer,
         $start,
         $end,
         $id_customer
      ]
   );


   $items = [];

   $hadir = 0;


   foreach ($rows as $row) {

      $name = trim(
         (string) (
            $row['doctor_name']
            ?? 'Dokter'
         )
      );


      $initial = getInitials(
         $name
      );


      $totalVisit = (int) (
         $row['total_visit']
         ?? 0
      );


      $isPresent =
         $totalVisit > 0;


      if ($isPresent) {
         $hadir++;
      }


      $items[] = [

         'id_doctor' =>
         $row['id_doctor'],

         'doctor_name' =>
         $name,

         'initial' =>
         $initial,

         'poli_name' =>
         !empty($row['poli_name'])
            ? $row['poli_name']
            : '-',

         'status' =>
         $isPresent
            ? 'present'
            : 'absent',

         'status_label' =>
         $isPresent
            ? 'Hadir'
            : 'Belum Hadir',

         'jam_masuk' =>
         formatTime(
            $row['jam_masuk']
         ),

         'total_visit' =>
         $totalVisit
      ];
   }


   return [

      'total' =>
      count($items),

      'hadir' =>
      $hadir,

      'belum_hadir' =>
      max(
         0,
         count($items) - $hadir
      ),

      'items' =>
      $items
   ];
}

/* =========================================================
   ANTRIAN PASIEN
   Prioritas antrian_poli, fallback pasien_visit.
========================================================= */

function getQueue(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            ap.id,
            ap.nomor,
            ap.kode_antri,
            ap.tanggal,
            ap.jampraktek,
            ap.status AS queue_status,
            ap.nomor_visit,
            pv.visit_ID,
            pv.id_patient,
            pv.id_doctor,
            pv.id_poli,
            COALESCE(
                NULLIF(TRIM(pv.patient_name_pcare), ''),
                NULLIF(TRIM(mp.patient_name), ''),
                'Pasien'
            ) AS patient_name,
            COALESCE(p.poli_name, '-') AS poli_name
        FROM antrian_poli ap
        LEFT JOIN pasien_visit pv
               ON pv.visit_ID = ap.nomor_visit
              AND pv.id_customer = ?
        LEFT JOIN ms_patient mp
               ON CAST(mp.id_patient AS CHAR) = CAST(pv.id_patient AS CHAR)
        LEFT JOIN ms_poli p
               ON CAST(p.id_poli AS CHAR) = CAST(pv.id_poli AS CHAR)
        WHERE ap.id_customer = CAST(? AS CHAR)
          AND ap.tanggal BETWEEN ? AND ?
        ORDER BY ap.tanggal DESC, ap.id ASC
        LIMIT 100
    ";

   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'isss',
      [$id_customer, $id_customer, $start, $end]
   );

   $items = [];
   $waiting = 0;
   $processing = 0;
   $completed = 0;

   foreach ($rows as $row) {
      [$status, $label, $class] = mapQueueStatus($row['queue_status']);

      if ($status === 'waiting') {
         $waiting++;
      } elseif ($status === 'examination') {
         $processing++;
      } elseif ($status === 'completed') {
         $completed++;
      }

      $number = trim((string)($row['kode_antri'] ?? ''));
      if ($number === '') {
         $number = (string)($row['nomor'] ?? '-');
      }

      $items[] = [
         'id'           => (int)$row['id'],
         'number'       => $number,
         'nomor'        => $row['nomor'],
         'patient_name' => $row['patient_name'],
         'poli_name'    => $row['id_poli'],
         'doctor_name'  => $row['id_doctor'],
         'status'       => $status,
         'status_label'  => $label,
         'status_class'  => $class,
         'tanggal'      => $row['tanggal'],
         'jampraktek'   => $row['jampraktek']
      ];
   }

   /*
     * Jika antrian_poli kosong, ambil antrean dari pasien_visit.
     * Ini menjaga dashboard tetap berguna pada instalasi yang belum
     * menggunakan tabel antrian_poli.
     */
   if (empty($items)) {
      $fallback = getQueueFromVisit($koneksi, $id_customer, $start, $end);

      foreach ($fallback['items'] as $item) {
         $items[] = $item;

         if ($item['status'] === 'waiting') {
            $waiting++;
         } elseif ($item['status'] === 'examination') {
            $processing++;
         } elseif ($item['status'] === 'completed') {
            $completed++;
         }
      }
   }

   return [
      'waiting'   => $waiting,
      'processing' => $processing,
      'completed' => $completed,
      'total'     => count($items),
      'items'     => $items
   ];
}

function getQueueFromVisit(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            pv.id_visit,
            pv.visit_ID,
            pv.visit_antrian,
            pv.status_antrian,
            pv.patient_name_pcare,
            pv.id_patient,
            pv.id_poli,
            pv.id_doctor,
            COALESCE(p.poli_name, '-') AS poli_name,
            COALESCE(d.doctor_name, '-') AS doctor_name,
            pv.visit_date,
            pv.visit_time
        FROM pasien_visit pv
        LEFT JOIN ms_poli p
               ON CAST(p.id_poli AS CHAR) = CAST(pv.id_poli AS CHAR)
        LEFT JOIN ms_doctor d
               ON CAST(d.id_doctor AS CHAR) = CAST(pv.id_doctor AS CHAR)
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
        ORDER BY pv.visit_date DESC, pv.visit_time ASC, pv.id_visit ASC
        LIMIT 100
    ";

   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   $items = [];

   foreach ($rows as $row) {
      [$status, $label, $class] = mapVisitStatus(
         $row['status_antrian'],
         $row['visit_time']
      );

      $items[] = [
         'id'           => (int)$row['id_visit'],
         'number'       => $row['visit_antrian'] ?: '-',
         'nomor'        => $row['visit_antrian'] ?: '-',
         'patient_name' => $row['patient_name_pcare'] ?: 'Pasien',
         'poli_name'    => $row['poli_name'],
         'doctor_name'  => $row['doctor_name'],
         'status'       => $status,
         'status_label' => $label,
         'status_class' => $class,
         'tanggal'      => $row['visit_date'],
         'jampraktek'   => $row['visit_time']
      ];
   }

   return ['items' => $items];
}


/* =========================================================
   PASIEN PER DOKTER
========================================================= */

function getDoctorSummary(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            pv.id_doctor,
            pv.id_poli,
            COALESCE(d.doctor_name, CONCAT('Dokter #', pv.id_doctor)) AS doctor_name,
            COUNT(DISTINCT pv.id_patient) AS total_pasien
        FROM pasien_visit pv
        LEFT JOIN ms_doctor d
               ON CAST(d.id_doctor AS CHAR) = CAST(pv.id_doctor AS CHAR)
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
          AND pv.id_doctor IS NOT NULL
          AND TRIM(pv.id_doctor) <> ''
        GROUP BY pv.id_doctor, d.doctor_name, pv.id_poli
        ORDER BY total_pasien DESC
        LIMIT 20
    ";

   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   $items = [];

   foreach ($rows as $row) {
      $items[] = [
         'id_doctor'   => $row['id_doctor'],
         'doctor_name' => $row['doctor_name'],
         'poli_name'   => $row['id_poli'],
         'total_pasien' => (int)$row['total_pasien']
      ];
   }

   return [
      'total' => count($items),
      'items' => $items
   ];
}


/* =========================================================
   STATUS POLI
   Aktif = ada kunjungan pada periode.
   Belum Mulai = belum ada kunjungan.
========================================================= */

function getPoliSummary(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            p.id_poli,
            p.poli_name,
            COALESCE(d.doctor_name, '-') AS doctor_name,
            COUNT(DISTINCT pv.id_patient) AS total_pasien
        FROM ms_poli p
        LEFT JOIN pasien_visit pv
               ON CAST(pv.id_poli AS CHAR) = CAST(p.id_poli AS CHAR)
              AND pv.id_customer = ?
              AND pv.visit_date BETWEEN ? AND ?
        LEFT JOIN ms_doctor d
               ON CAST(d.id_doctor AS CHAR) = CAST(pv.id_doctor AS CHAR)
        WHERE p.poli_status = '1'
        GROUP BY p.id_poli, p.poli_name, d.doctor_name
        ORDER BY total_pasien DESC, p.poli_name ASC
        LIMIT 30
    ";

   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   /*
     * Cari total maksimum untuk progress bar.
     * Progress di sini menunjukkan proporsi terhadap poli dengan
     * jumlah pasien terbesar pada periode yang dipilih.
     */
   $max = 0;
   foreach ($rows as $row) {
      $max = max($max, (int)$row['total_pasien']);
   }

   $items = [];

   foreach ($rows as $row) {
      $total = (int)$row['total_pasien'];
      $progress = $max > 0 ? round(($total / $max) * 100) : 0;

      $items[] = [
         'id_poli'      => $row['id_poli'],
         'poli_name'    => $row['poli_name'],
         'doctor_name'  => $row['doctor_name'],
         'total_pasien' => $total,
         'persentase'   => $progress,
         'status'       => $total > 0 ? 'active' : 'not_started',
         'status_label' => $total > 0 ? 'Aktif' : 'Belum Mulai'
      ];
   }

   return [
      'total' => count($items),
      'items' => $items
   ];
}


/* =========================================================
   JENIS PASIEN
   Prioritas klasifikasi:
   1. metode_bayar
   2. noKartu -> BPJS
   3. lainnya -> Umum
========================================================= */

function getPatientTypes(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            COUNT(DISTINCT pv.id_patient) AS total,
            SUM(
                CASE
                    WHEN LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%bpjs%'
                      OR LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%jkn%'
                      OR COALESCE(TRIM(pv.noKartu), '') <> ''
                    THEN 1 ELSE 0
                END
            ) AS bpjs,
            SUM(
                CASE
                    WHEN LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%asuransi%'
                      OR LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%insurance%'
                      OR LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%prudential%'
                      OR LOWER(COALESCE(pv.metode_bayar, '')) LIKE '%axa%'
                    THEN 1 ELSE 0
                END
            ) AS asuransi
        FROM pasien_visit pv
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
          AND pv.id_patient IS NOT NULL
          AND TRIM(pv.id_patient) <> ''
    ";

   $row = fetchOneRow(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   $total = (int)($row['total'] ?? 0);
   $bpjs = (int)($row['bpjs'] ?? 0);
   $asuransi = (int)($row['asuransi'] ?? 0);

   /*
     * Karena SUM di atas bekerja pada baris visit sementara KPI
     * menggunakan DISTINCT patient, batasi agar total kategori
     * tidak melebihi total pasien. Untuk data yang membutuhkan
     * klasifikasi pasien unik secara ketat, ambil visit terakhir.
     */
   $unique = getUniquePatientTypes($koneksi, $id_customer, $start, $end);

   $bpjs = $unique['bpjs'];
   $asuransi = $unique['asuransi'];
   $umum = $unique['umum'];

   $items = [
      [
         'label'      => 'BPJS',
         'key'        => 'bpjs',
         'total'      => $bpjs,
         'persentase' => $total > 0 ? round(($bpjs / $total) * 100) : 0
      ],
      [
         'label'      => 'Umum',
         'key'        => 'umum',
         'total'      => $umum,
         'persentase' => $total > 0 ? round(($umum / $total) * 100) : 0
      ],
      [
         'label'      => 'Asuransi',
         'key'        => 'asuransi',
         'total'      => $asuransi,
         'persentase' => $total > 0 ? round(($asuransi / $total) * 100) : 0
      ]
   ];

   return [
      'total' => $total,
      'items' => $items
   ];
}

function getUniquePatientTypes(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            x.id_patient,
            x.metode_bayar,
            x.noKartu
        FROM pasien_visit x
        INNER JOIN (
            SELECT id_patient, MAX(id_visit) AS max_id
            FROM pasien_visit
            WHERE id_customer = ?
              AND visit_date BETWEEN ? AND ?
              AND id_patient IS NOT NULL
              AND TRIM(id_patient) <> ''
            GROUP BY id_patient
        ) latest
          ON latest.id_patient = x.id_patient
         AND latest.max_id = x.id_visit
        WHERE x.id_customer = ?
    ";

   /*
     * id_visit adalah integer pada schema saat ini.
     */
   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'issi',
      [$id_customer, $start, $end, $id_customer]
   );

   $result = [
      'bpjs' => 0,
      'umum' => 0,
      'asuransi' => 0
   ];

   foreach ($rows as $row) {
      $method = strtolower(trim((string)($row['metode_bayar'] ?? '')));
      $noKartu = trim((string)($row['noKartu'] ?? ''));

      if (
         strpos($method, 'bpjs') !== false ||
         strpos($method, 'jkn') !== false ||
         $noKartu !== ''
      ) {
         $result['bpjs']++;
      } elseif (
         strpos($method, 'asuransi') !== false ||
         strpos($method, 'insurance') !== false ||
         strpos($method, 'prudential') !== false ||
         strpos($method, 'axa') !== false
      ) {
         $result['asuransi']++;
      } else {
         $result['umum']++;
      }
   }

   return $result;
}


/* =========================================================
   GRAFIK KUNJUNGAN
========================================================= */

function getVisitChart(mysqli $koneksi, $id_customer, string $start, string $end): array
{
   $sql = "
        SELECT
            pv.visit_date AS tanggal,
            COUNT(DISTINCT pv.id_patient) AS total
        FROM pasien_visit pv
        WHERE pv.id_customer = ?
          AND pv.visit_date BETWEEN ? AND ?
        GROUP BY pv.visit_date
        ORDER BY pv.visit_date ASC
    ";

   $rows = fetchAllRows(
      $koneksi,
      $sql,
      'iss',
      [$id_customer, $start, $end]
   );

   $map = [];

   foreach ($rows as $row) {
      $map[$row['tanggal']] = (int)$row['total'];
   }

   $items = [];
   $cursor = new DateTimeImmutable($start);
   $last = new DateTimeImmutable($end);

   while ($cursor <= $last) {
      $date = $cursor->format('Y-m-d');

      $items[] = [
         'date'  => $date,
         'label' => $cursor->format('d/m'),
         'day'   => getDayLabel($cursor),
         'total' => $map[$date] ?? 0
      ];

      $cursor = $cursor->modify('+1 day');
   }

   return $items;
}


/* =========================================================
   STATUS MAPPING
========================================================= */

function mapQueueStatus($value): array
{
   $v = strtolower(trim((string)$value));

   if (in_array($v, ['1', 'called', 'dipanggil'], true)) {
      return ['called', 'Dipanggil', 'called'];
   }

   if (in_array($v, ['2', '3', 'examination', 'diperiksa', 'sedang_diperiksa', 'process'], true)) {
      return ['examination', 'Diperiksa', 'examination'];
   }

   if (in_array($v, ['4', 'selesai', 'completed', 'done'], true)) {
      return ['completed', 'Selesai', 'completed'];
   }

   return ['waiting', 'Menunggu', 'waiting'];
}

function mapVisitStatus($status, $visitTime = null): array
{
   $v = strtolower(trim((string)$status));

   if (in_array($v, ['1', 'called', 'dipanggil'], true)) {
      return ['called', 'Dipanggil', 'called'];
   }

   if (in_array($v, ['2', '3', 'examination', 'diperiksa', 'process'], true)) {
      return ['examination', 'Diperiksa', 'examination'];
   }

   if (in_array($v, ['4', 'selesai', 'completed', 'done'], true)) {
      return ['completed', 'Selesai', 'completed'];
   }

   return ['waiting', 'Menunggu', 'waiting'];
}


/* =========================================================
   UTILITY
========================================================= */

function getInitials(string $name): string
{
   $name = trim($name);

   if ($name === '') {
      return 'DR';
   }

   $parts = preg_split('/\s+/', $name);

   if (count($parts) === 1) {
      return strtoupper(substr($parts[0], 0, 2));
   }

   return strtoupper(
      substr($parts[0], 0, 1) .
         substr($parts[1], 0, 1)
   );
}

function formatTime($time): ?string
{
   if (!$time) {
      return null;
   }

   $time = substr((string)$time, 0, 5);

   return preg_match('/^\d{2}:\d{2}$/', $time)
      ? $time
      : null;
}

function getDayLabel(DateTimeImmutable $date): string
{
   $days = [
      1 => 'Sen',
      2 => 'Sel',
      3 => 'Rab',
      4 => 'Kam',
      5 => 'Jum',
      6 => 'Sab',
      7 => 'Min'
   ];

   return $days[(int)$date->format('N')] ?? '';
}

function getPreviousPeriod(string $start, string $end): array
{
   $s = new DateTimeImmutable($start);
   $e = new DateTimeImmutable($end);

   $days = $s->diff($e)->days + 1;

   $previousEnd = $s->modify('-1 day');
   $previousStart = $previousEnd->modify('-' . ($days - 1) . ' days');

   return [
      $previousStart->format('Y-m-d'),
      $previousEnd->format('Y-m-d')
   ];
}
