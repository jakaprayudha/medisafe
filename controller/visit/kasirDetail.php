<?php
/**
 * Rincian pembayaran satu kunjungan (module/admin/kasir_detail).
 * GET ?no=<visit_ID> -> { status, kunjungan:{...}, tagihan:{obat[], jasa[], total_obat, total_jasa, total, tiket_belum_selesai} }
 */
require '../../database/connect.php';
require __DIR__ . '/_tagihan.php';
header('Content-Type: application/json; charset=utf-8');

function balas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
$no = trim((string) ($_GET['no'] ?? ''));
if ($no === '' || strlen($no) > 100) balas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak valid.']);

try {
   $kv = ambilKunjungan($koneksi, $no, $idc);
   if (!$kv) balas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   $tagihan = ambilTagihan($koneksi, $no, $idc);
} catch (Throwable $e) {
   error_log('[visit/kasirDetail] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat rincian pembayaran.']);
}

$kv['lunas'] = (int) $kv['status_bayar'] === 1;
foreach (['amount_results', 'amount_payment', 'amount_changes', 'id_provider', 'visit_status'] as $c) $kv[$c] = (int) $kv[$c];
balas(200, ['status' => 'success', 'kunjungan' => $kv, 'tagihan' => $tagihan]);
