<?php
/**
 * GET fromDate, toDate[, doctor, provider, room, status(belum|dirawat|pulang)]
 *  -> { status, kolom, data: [baris], ringkasan, opsi, filter }
 * Tanggal = tanggal masuk rawat inap.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/ranapService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan', 'data' => []]);
   exit;
}

try {
   $p = rnParams($_GET);
   $rows = rnRows($koneksi, $idc, $p);
   $km = rnKamar($koneksi, $idc);
   $r = rnRingkasan($rows, $p, $km['bed'], $koneksi);
   $r['sedang_dirawat'] = rnSedangDirawat($koneksi, $idc);

   $kolom = ['masuk', 'jam', 'pulang', 'jam_pulang', 'los', 'los_valid', 'status', 'cppt', 'nama', 'rm', 'nik', 'noKartu', 'gender', 'umur',
      'dokter', 'diagnosa', 'kamar', 'bed', 'kelas', 'bayar', 'kondisi', 'cara_keluar'];
   $baris = array_map(fn($x) => array_map(fn($c) => $x[$c], $kolom), $rows);
   $opsi = null;
   if (empty($_GET['tanpa_opsi'])) {
      $o = apOpsi($koneksi, $idc, ['from' => $p['from'], 'to' => $p['to']]);
      $opsi = ['dokter' => $o['dokter'], 'provider' => $o['provider'], 'kamar' => $km['rooms']];
   }
   echo json_encode([
      'status' => 'success',
      'kolom' => $kolom,
      'data' => $baris,
      'ringkasan' => $r,
      'opsi' => $opsi,
      'filter' => $p + ['label' => rnLabelFilter($p, $km['rooms'])],
   ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
} catch (Throwable $e) {
   error_log('[ranapController] ' . $e->getMessage());
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Gagal memuat laporan rawat inap.', 'data' => []]);
}
