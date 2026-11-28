<?php
/**
 * Simpan pembayaran kunjungan (module/admin/kasir_detail).
 *
 * POST JSON { id_visit, metode_bayar: Tunai|Transfer|QRIS|Lainnya, bayar, total }
 *   total = total yang dilihat kasir; ditolak (409) bila tagihan sudah berubah sejak halaman dimuat.
 * Total dihitung ulang di server (controller/visit/_tagihan.php), bukan dipercaya dari browser.
 *
 * Perbaikan dari versi lama: SQL injection (semua nilai ditempel ke query), total & uang dari browser
 * disimpan apa adanya (bisa kosong -> SQL error / bisa diubah), kunjungan yang sudah lunas bisa dibayar
 * ulang (nomor transaksi tertimpa), tidak ada cek uang >= total di server.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) balas(422, ['status' => 'error', 'message' => 'Data tidak valid.']);
$no = trim((string) ($in['id_visit'] ?? ''));
if ($no === '') balas(422, ['status' => 'error', 'message' => 'ID kunjungan tidak ditemukan.']);
$metode = (string) ($in['metode_bayar'] ?? '');
$bulat = fn($v) => (int) preg_replace('/\D/', '', (string) ($v ?? ''));

try {
   $kv = ambilKunjungan($koneksi, $no, $idc);
   if (!$kv) balas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $kv['status_bayar'] === 1) balas(409, ['status' => 'error', 'message' => 'Kunjungan ini sudah lunas (No. ' . $kv['nomor_transaksi'] . ').']);
   $total = ambilTagihan($koneksi, $no, $idc)['total'];
   if (isset($in['total']) && $bulat($in['total']) !== $total) {
      balas(409, ['status' => 'error', 'message' => 'Tagihan sudah berubah menjadi Rp' . number_format($total, 0, ',', '.') . '. Muat ulang halaman lalu periksa lagi.', 'total' => $total]);
   }

   if ($total === 0) {
      $metode = 'AUTO';   // tanpa tagihan: langsung lunas (sama seperti sebelumnya)
      $bayar = 0;
   } else {
      if (!in_array($metode, ['Tunai', 'Transfer', 'QRIS', 'Lainnya'], true)) balas(422, ['status' => 'error', 'message' => 'Metode bayar tidak valid.']);
      $bayar = $metode === 'Tunai' ? $bulat($in['bayar'] ?? '') : $total;   // non-tunai: dibayar pas
      if ($bayar < $total) balas(422, ['status' => 'error', 'message' => 'Uang diterima kurang dari total tagihan.']);
      if ($bayar > $total + 100000000) balas(422, ['status' => 'error', 'message' => 'Jumlah uang diterima tidak wajar.']);
   }
   $kembalian = $bayar - $total;
   $nomor = date('Ymd') . random_int(1000, 9999);

   // syarat status_bayar <> 1 di WHERE: dua klik / dua kasir bersamaan tidak bisa membayar dua kali
   $st = $koneksi->prepare("UPDATE pasien_visit SET status_bayar = 1, metode_bayar = ?, nomor_transaksi = ?, amount_results = ?, amount_payment = ?, amount_changes = ?
      WHERE visit_ID = ? AND id_customer = ? AND COALESCE(status_bayar, 0) <> 1");
   $st->bind_param('ssiiiss', $metode, $nomor, $total, $bayar, $kembalian, $no, $idc);
   $st->execute();
   $kena = $st->affected_rows;
   $st->close();
   if ($kena < 1) balas(409, ['status' => 'error', 'message' => 'Kunjungan ini sudah lunas.']);
   require_once __DIR__ . '/../../utility/visitLog.php';
   logKunjungan($koneksi, 'bayar', $no, 'No. transaksi ' . $nomor . ' · ' . $metode . ' · Rp' . number_format($total, 0, ',', '.'),
      ['status_bayar' => (int) $kv['status_bayar']], ['status_bayar' => 1, 'metode_bayar' => $metode, 'nomor_transaksi' => $nomor, 'total' => $total, 'bayar' => $bayar, 'kembalian' => $kembalian], 'Kasir');
} catch (Throwable $e) {
   error_log('[visit/bayar] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal menyimpan pembayaran.']);
}

balas(200, [
   'status' => 'success',
   'message' => 'Pembayaran berhasil',
   'nomor_transaksi' => $nomor,
   'total' => $total,
   'bayar' => $bayar,
   'kembalian' => $kembalian,
]);
