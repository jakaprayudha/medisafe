<?php
/**
 * GET fromDate, toDate[, doctor, poli, provider, gender, status]
 *   -> { status, data: [baris], ringkasan: {...}, opsi: {dokter, poli, provider}, filter: {...} }
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/appointmentService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan', 'data' => []]);
   exit;
}

try {
   $p = apParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
   exit;
}

$rows = apRows($koneksi, $idc, $p);
$ringkasan = apRingkasan($rows, $p);

// baris dikirim sebagai array (tanpa nama kolom berulang) + gzip: respons setahun ±18 MB -> ±1–2 MB
$kolom = ['tanggal', 'jam', 'status', 'grup', 'status_label', 'noKartu', 'nik', 'nama', 'gender', 'dokter', 'poli', 'bayar'];
$baris = array_map(fn($r) => array_map(fn($c) => $r[$c], $kolom), $rows);
unset($rows);
if (!ini_get('zlib.output_compression') && extension_loaded('zlib')) {
   ob_start('ob_gzhandler');
}
echo json_encode([
   'status' => 'success',
   'kolom' => $kolom,
   'data' => $baris,
   'ringkasan' => $ringkasan,
   'opsi' => empty($_GET['tanpa_opsi']) ? apOpsi($koneksi, $idc, $p) : null,
   'filter' => $p + ['label' => apLabelFilter($p)],
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
