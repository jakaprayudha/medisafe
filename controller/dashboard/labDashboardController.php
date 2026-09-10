<?php

/**
 * Dashboard Laboratorium
 * Endpoint:
 * controller/dashboard/labDashboardController.php?action=dashboard
 *
 * Sumber data utama:
 * - visit_inspection
 * - laboratorium_result
 * - pasien_visit
 *
 * Catatan:
 * Schema yang tersedia tidak memiliki field status pemeriksaan/validasi.
 * Karena itu:
 * - Menunggu = inspection belum memiliki result
 * - Selesai = inspection sudah memiliki minimal 1 result
 * - Sedang diproses / validasi tidak diklaim dari data yang tidak tersedia
 */

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

if ($action !== 'dashboard') {
   echo json_encode([
      'status' => false,
      'message' => 'Action tidak valid.'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;
if ($id_customer === null || $id_customer === '') {
   echo json_encode([
      'status' => false,
      'message' => 'Session id_customer tidak ditemukan.'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

function labBind(mysqli_stmt $stmt, string $types, array $params): bool
{
   if ($types === '' || empty($params)) {
      return true;
   }

   return $stmt->bind_param($types, ...$params);
}

function labFetchAll(mysqli $koneksi, string $sql, string $types = '', array $params = []): array
{
   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      return [];
   }

   if ($types !== '' && !labBind($stmt, $types, $params)) {
      $stmt->close();
      return [];
   }

   if (!$stmt->execute()) {
      $stmt->close();
      return [];
   }

   $result = $stmt->get_result();

   if (!$result) {
      $stmt->close();
      return [];
   }

   $rows = $result->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   return $rows;
}

function labFetchOne(mysqli $koneksi, string $sql, string $types = '', array $params = []): array
{
   $rows = labFetchAll($koneksi, $sql, $types, $params);
   return $rows[0] ?? [];
}

function labScalar(mysqli $koneksi, string $sql, string $types = '', array $params = [])
{
   $row = labFetchOne($koneksi, $sql, $types, $params);
   return array_values($row)[0] ?? 0;
}

function labDate(string $date): bool
{
   $d = DateTime::createFromFormat('Y-m-d', $date);
   return $d && $d->format('Y-m-d') === $date;
}

function labDateRange(): array
{
   $periode = $_GET['periode'] ?? 'today';
   $today = date('Y-m-d');

   switch ($periode) {
      case 'week':
      case '7days':
         $start = date('Y-m-d', strtotime('monday this week'));
         $end = $today;
         break;

      case 'month':
      case 'thismonth':
         $start = date('Y-m-01');
         $end = $today;
         break;

      case 'custom':
         $start = $_GET['tanggal_mulai'] ?? $today;
         $end = $_GET['tanggal_selesai'] ?? $today;

         if (!labDate($start)) {
            $start = $today;
         }

         if (!labDate($end)) {
            $end = $today;
         }

         break;

      case 'yesterday':
         $start = date('Y-m-d', strtotime('-1 day'));
         $end = $start;
         break;

      case 'today':
      default:
         $periode = 'today';
         $start = $today;
         $end = $today;
         break;
   }

   if ($start > $end) {
      [$start, $end] = [$end, $start];
   }

   return [$periode, $start, $end];
}

function labPatientName(array $row): string
{
   $name = trim((string)($row['patient_name_pcare'] ?? ''));

   if ($name !== '') {
      return $name;
   }

   return '-';
}

[$periode, $tanggal_mulai, $tanggal_selesai] = labDateRange();

/*
 * ==========================================================
 * KPI
 * ==========================================================
 *
 * Semua inspection dihitung dari inspection_date.
 * Result dihitung berdasarkan adanya minimal satu row
 * laboratorium_result untuk inspection tersebut.
 */

$total_pemeriksaan = (int) labScalar(
   $koneksi,
   "SELECT COUNT(*)
     FROM visit_inspection
     WHERE id_customer = ?
       AND inspection_date BETWEEN ? AND ?",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$menunggu_pemeriksaan = (int) labScalar(
   $koneksi,
   "SELECT COUNT(*)
     FROM visit_inspection vi
     WHERE vi.id_customer = ?
       AND vi.inspection_date BETWEEN ? AND ?
       AND NOT EXISTS (
           SELECT 1
           FROM laboratorium_result lr
           WHERE lr.id_inspection = vi.id_inspection
       )",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$hasil_selesai = (int) labScalar(
   $koneksi,
   "SELECT COUNT(*)
     FROM visit_inspection vi
     WHERE vi.id_customer = ?
       AND vi.inspection_date BETWEEN ? AND ?
       AND EXISTS (
           SELECT 1
           FROM laboratorium_result lr
           WHERE lr.id_inspection = vi.id_inspection
       )",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

/*
 * Schema tidak mempunyai status proses.
 * Sedang diproses tidak dapat dibedakan secara valid
 * antara proses pemeriksaan dan status lainnya.
 */
$sedang_diproses = 0;

/*
 * Tidak ada field validasi pada laboratorium_result.
 * Nilai ini tidak difabrikasi.
 */
$hasil_validasi = 0;
$persentase_selesai = $total_pemeriksaan > 0
   ? round(($hasil_selesai / $total_pemeriksaan) * 100)
   : 0;

/*
 * ==========================================================
 * ANTREAN PEMERIKSAAN
 * ==========================================================
 *
 * Maksimal 50 dikirim ke frontend.
 * View dapat menampilkan 10 + scroll seperti dashboard kasir.
 */
$queueRows = labFetchAll(
   $koneksi,
   "SELECT
        vi.id_inspection,
        vi.id_visit,
        vi.inspection_name,
        vi.inspection_date,
        vi.inspection_source,
        vi.inspection_number,
        vi.created_at,
        vi.inspection_note,
        vi.id_customer,
        pv.patient_name_pcare,
        pv.visit_ID,
        pv.id_patient,
        pv.id_poli,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM laboratorium_result lr
                WHERE lr.id_inspection = vi.id_inspection
            ) THEN 'completed'
            ELSE 'waiting'
        END AS status_data
     FROM visit_inspection vi
     LEFT JOIN pasien_visit pv
       ON pv.visit_ID = vi.id_visit
      AND pv.id_customer = vi.id_customer
     WHERE vi.id_customer = ?
       AND vi.inspection_date BETWEEN ? AND ?
     ORDER BY
       CASE
           WHEN EXISTS (
               SELECT 1
               FROM laboratorium_result lr
               WHERE lr.id_inspection = vi.id_inspection
           ) THEN 1
           ELSE 0
       END ASC,
       vi.created_at ASC
     LIMIT 50",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$queueItems = [];

foreach ($queueRows as $row) {
   $status = $row['status_data'] === 'completed'
      ? 'completed'
      : 'waiting';

   $statusLabel = $status === 'completed'
      ? 'SELESAI'
      : 'MENUNGGU';

   $statusClass = $status === 'completed'
      ? 'lab-ready'
      : 'lab-waiting';

   $queueItems[] = [
      'id' => (int)$row['id_inspection'],
      'queue_number' => trim((string)($row['inspection_number'] ?? '')) ?: ('LAB-' . $row['id_inspection']),
      'patient_name' => labPatientName($row),
      'rm' => trim((string)($row['id_patient'] ?? '')) ?: '-',
      'visit_ID' => trim((string)($row['visit_ID'] ?? $row['id_visit'] ?? '')) ?: '-',
      'inspection_name' => trim((string)($row['inspection_name'] ?? '')) ?: '-',
      'inspection_source' => trim((string)($row['inspection_source'] ?? '')) ?: '-',
      'status' => $status,
      'status_label' => $statusLabel,
      'status_class' => $statusClass,
      'tanggal' => $row['inspection_date'] ?? null,
      'created_at' => $row['created_at'] ?? null
   ];
}

/*
 * ==========================================================
 * HASIL LAB TERBARU
 * ==========================================================
 *
 * laboratorium_result tidak memiliki nama item/reference range/
 * status validasi. Maka dashboard hanya menampilkan:
 * hasil, keterangan, inspection name, nomor pemeriksaan.
 */
$resultRows = labFetchAll(
   $koneksi,
   "SELECT
        lr.id,
        lr.id_inspection,
        lr.id_item,
        lr.hasil,
        lr.keterangan,
        lr.created_at,
        vi.inspection_name,
        vi.inspection_number,
        vi.inspection_date,
        vi.id_visit,
        pv.patient_name_pcare,
        pv.id_patient,
        pv.visit_ID
     FROM laboratorium_result lr
     INNER JOIN visit_inspection vi
       ON vi.id_inspection = lr.id_inspection
      AND vi.id_customer = ?
     LEFT JOIN pasien_visit pv
       ON pv.visit_ID = vi.id_visit
      AND pv.id_customer = vi.id_customer
     WHERE vi.inspection_date BETWEEN ? AND ?
     ORDER BY lr.created_at DESC
     LIMIT 50",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$resultItems = [];

foreach ($resultRows as $row) {
   $hasil = trim((string)($row['hasil'] ?? ''));
   $keterangan = trim((string)($row['keterangan'] ?? ''));

   $resultItems[] = [
      'id' => (int)$row['id'],
      'id_inspection' => (int)$row['id_inspection'],
      'id_item' => $row['id_item'] !== null ? (int)$row['id_item'] : null,
      'patient_name' => labPatientName($row),
      'rm' => trim((string)($row['id_patient'] ?? '')) ?: '-',
      'visit_ID' => trim((string)($row['visit_ID'] ?? $row['id_visit'] ?? '')) ?: '-',
      'inspection_number' => trim((string)($row['inspection_number'] ?? '')) ?: '-',
      'inspection_name' => trim((string)($row['inspection_name'] ?? '')) ?: '-',
      'hasil' => $hasil !== '' ? $hasil : '-',
      'keterangan' => $keterangan !== '' ? $keterangan : '-',
      'created_at' => $row['created_at'] ?? null,
      'status' => 'completed',
      'status_label' => 'HASIL'
   ];
}

/*
 * ==========================================================
 * JENIS PEMERIKSAAN
 * ==========================================================
 *
 * Karena tidak ada tabel kategori pemeriksaan yang diberikan,
 * distribusi memakai inspection_name dari visit_inspection.
 */
$typeRows = labFetchAll(
   $koneksi,
   "SELECT
        inspection_name,
        COUNT(*) AS total
     FROM visit_inspection
     WHERE id_customer = ?
       AND inspection_date BETWEEN ? AND ?
       AND inspection_name IS NOT NULL
       AND TRIM(inspection_name) <> ''
     GROUP BY inspection_name
     ORDER BY total DESC
     LIMIT 10",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$typeItems = [];
foreach ($typeRows as $row) {
   $typeItems[] = [
      'label' => trim((string)$row['inspection_name']),
      'total' => (int)$row['total']
   ];
}

$typeMax = 0;
foreach ($typeItems as $item) {
   $typeMax = max($typeMax, $item['total']);
}

foreach ($typeItems as &$item) {
   $item['persentase'] = $typeMax > 0
      ? round(($item['total'] / $typeMax) * 100)
      : 0;
}
unset($item);

/*
 * ==========================================================
 * TREND PEMERIKSAAN
 * ==========================================================
 */
$trendRows = labFetchAll(
   $koneksi,
   "SELECT
        vi.inspection_date AS tanggal,
        COUNT(*) AS masuk,
        SUM(
            CASE WHEN EXISTS (
                SELECT 1
                FROM laboratorium_result lr
                WHERE lr.id_inspection = vi.id_inspection
            ) THEN 1 ELSE 0 END
        ) AS selesai
     FROM visit_inspection vi
     WHERE vi.id_customer = ?
       AND vi.inspection_date BETWEEN ? AND ?
     GROUP BY vi.inspection_date
     ORDER BY vi.inspection_date ASC",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

$trendItems = [];
foreach ($trendRows as $row) {
   $trendItems[] = [
      'date' => $row['tanggal'],
      'label' => date('d/m', strtotime($row['tanggal'])),
      'masuk' => (int)$row['masuk'],
      'selesai' => (int)$row['selesai']
   ];
}

/*
 * ==========================================================
 * ALERT
 * ==========================================================
 */
$alertItems = [];

if ($menunggu_pemeriksaan > 0) {
   $alertItems[] = [
      'type' => 'warning',
      'icon' => 'solar:clock-circle-bold',
      'title' => 'Pemeriksaan Menunggu',
      'text' => $menunggu_pemeriksaan . ' pemeriksaan belum memiliki hasil laboratorium.'
   ];
}

if ($hasil_selesai > 0) {
   $alertItems[] = [
      'type' => 'info',
      'icon' => 'solar:document-text-bold',
      'title' => 'Hasil Tersedia',
      'text' => $hasil_selesai . ' pemeriksaan sudah memiliki hasil.'
   ];
}

if ($total_pemeriksaan === 0) {
   $alertItems[] = [
      'type' => 'info',
      'icon' => 'solar:info-circle-bold',
      'title' => 'Tidak Ada Pemeriksaan',
      'text' => 'Tidak ada permintaan pemeriksaan pada periode yang dipilih.'
   ];
}

/*
 * ==========================================================
 * STATISTIK
 * ==========================================================
 *
 * TAT tidak dapat dihitung dengan benar karena schema yang
 * tersedia belum mempunyai timestamp penerimaan/proses/selesai.
 */
$tatMenit = null;

/*
 * Jumlah result rows pada periode.
 */
$total_result_rows = (int) labScalar(
   $koneksi,
   "SELECT COUNT(*)
     FROM laboratorium_result lr
     INNER JOIN visit_inspection vi
       ON vi.id_inspection = lr.id_inspection
      AND vi.id_customer = ?
     WHERE vi.inspection_date BETWEEN ? AND ?",
   "iss",
   [$id_customer, $tanggal_mulai, $tanggal_selesai]
);

/*
 * ==========================================================
 * RESPONSE
 * ==========================================================
 */
echo json_encode([
   'status' => true,
   'message' => 'Data dashboard laboratorium berhasil diambil.',
   'period' => [
      'type' => $periode,
      'start' => $tanggal_mulai,
      'end' => $tanggal_selesai
   ],

   'kpi' => [
      'permintaan_pemeriksaan' => $total_pemeriksaan,
      'menunggu_pemeriksaan' => $menunggu_pemeriksaan,
      'sedang_diproses' => $sedang_diproses,
      'hasil_selesai' => $hasil_selesai,
      'hasil_validasi' => $hasil_validasi,
      'persentase_selesai' => $persentase_selesai
   ],

   'queue' => [
      'waiting' => $menunggu_pemeriksaan,
      'total' => count($queueItems),
      'items' => $queueItems
   ],

   'results' => [
      'total' => count($resultItems),
      'total_result_rows' => $total_result_rows,
      'items' => $resultItems
   ],

   'process' => [
      'penerimaan_spesimen' => null,
      'pemeriksaan' => null,
      'validasi_hasil' => null,
      'hasil_terkirim' => null,
      'message' => 'Status proses belum dapat dihitung dari schema yang tersedia.'
   ],

   'types' => [
      'total' => count($typeItems),
      'items' => $typeItems
   ],

   'trend' => $trendItems,

   'alerts' => [
      'total' => count($alertItems),
      'items' => $alertItems
   ],

   'summary' => [
      'turn_around_time_menit' => $tatMenit,
      'validasi_hasil' => null,
      'hasil_abnormal' => null,
      'pemeriksaan_selesai' => $hasil_selesai,
      'total_result_rows' => $total_result_rows
   ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
