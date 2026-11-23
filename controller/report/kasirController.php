<?php
/**
 * Data halaman module/admin/rpt_kasir.
 * GET ?from=Y-m-d&to=Y-m-d[&dasar=bayar|kunjungan][&metode=..][&penjamin=id][&poli=..]
 *  -> { status, periode, ringkasan, harian, metode, penjamin, poli, belum_bayar, data:[transaksi], opsi }
 */
require '../../database/connect.php';
require __DIR__ . '/kasirService.php';
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
   $p = ksParams($_GET);
} catch (InvalidArgumentException $e) {
   balas(422, ['status' => 'error', 'message' => $e->getMessage()]);
}

try {
   $rows = ksRows($koneksi, $idc, $p);
   $agr = ksRingkasan($rows, $p);
   balas(200, array_merge(['status' => 'success', 'periode' => $p], $agr, [
      'belum_bayar' => ksBelumBayar($koneksi, $idc, $p),
      'data' => $rows,
      'opsi' => ksOpsi($koneksi, $idc),
   ]));
} catch (Throwable $e) {
   error_log('[report/kasirController] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat laporan kasir.']);
}
