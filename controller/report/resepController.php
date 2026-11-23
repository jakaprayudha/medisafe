<?php
/**
 * Data halaman module/admin/rpt_resep (laporan pelayanan resep).
 * GET ?from=Y-m-d&to=Y-m-d[&status=0|1|2|3|proses][&jenis=..][&penjamin=id][&dokter=..]
 */
require '../../database/connect.php';
require __DIR__ . '/resepService.php';
header('Content-Type: application/json; charset=utf-8');
if (!ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

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

try {
   $p = rsParams($_GET);
} catch (InvalidArgumentException $e) {
   balas(422, ['status' => 'error', 'message' => $e->getMessage()]);
}
try {
   [$tiket, $item] = rsData($koneksi, $idc, $p);
   $agr = rsRingkasan($tiket, $item, $p);
   unset($agr['obat_semua']);
   // ringkasan & grafik memakai semua resep; tabel rincian di halaman cukup 3.000 terbaru (Excel tetap lengkap)
   $totalBaris = count($tiket);
   $opsi = rsOpsi($tiket);
   if ($totalBaris > 3000) $tiket = array_slice($tiket, -3000);
   balas(200, array_merge(['status' => 'success', 'periode' => $p], $agr, [
      'data' => $tiket,
      'data_total' => $totalBaris,
      'opsi' => array_merge($opsi, ['penjamin' => rsPenjamin($koneksi)]),
   ]));
} catch (Throwable $e) {
   error_log('[report/resepController] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat laporan resep.']);
}
