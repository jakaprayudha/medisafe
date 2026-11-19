<?php
/** Export Excel laporan tindakan: Ringkasan, Per Tindakan, Per Dokter, Detail. */
require_once '../../database/connect.php';
require_once '../../controller/report/prosedurService.php';
require_once '../../controller/report/xlsxRingan.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
if (!in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin', 'administrator'], true)) {
   http_response_code(403);
   exit('Akses laporan tidak diizinkan.');
}
try {
   $p = prParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
@set_time_limit(300);

$rows = prRows($koneksi, $idc, $p);
$r = prRingkasan($rows, $p, prKunjungan($koneksi, $idc, $p));
$klinik = apKlinik($koneksi, $idc);
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$rp = fn($v) => ['v' => (float) $v, 's' => 6];

$x = new XlsxRingan();
$x->sheet('Ringkasan', [36, 18, 10]);
$x->row([['v' => 'LAPORAN TINDAKAN & PROSEDUR — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
$x->row([['v' => 'Periode: ' . $periode, 's' => 4]]);
$x->row([['v' => 'Filter: ' . prLabelFilter($p) . ' · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
$x->kosong();
$x->row(['Indikator', 'Nilai'], 1);
foreach ([
   ['Pendapatan bruto (harga × jumlah)', $r['kotor']],
   ['Total diskon', $r['diskon']],
   ['Pendapatan bersih', $r['total']],
   ['Fee dokter', $r['fee_dokter']],
   ['Fee perawat', $r['fee_perawat']],
   ['Fee admin', $r['fee_admin']],
   ['Jumlah tindakan (qty)', $r['qty']],
   ['Jenis tindakan', $r['jenis']],
   ['Kunjungan dengan tindakan', $r['kunjungan_tindakan']],
   ['Total kunjungan pada periode', $r['kunjungan']],
   ['Rata-rata pendapatan / kunjungan bertindakan', $r['rata_per_kunjungan']],
] as [$k, $v]) $x->row([$k, $rp($v)]);
$x->row(['Kunjungan dengan tindakan (%)', ['v' => $r['pct_kunjungan'] / 100, 's' => 2]]);
if ($r['tanpa_tarif']) $x->row([['v' => $r['tanpa_tarif'] . ' jenis tindakan tidak ditemukan di master tarif (fee = 0).', 's' => 4]]);
foreach (['Kategori' => $r['kategori'], 'Jenis bayar' => $r['bayar'], 'Poli' => $r['poli']] as $judul => $data) {
   $x->kosong();
   $x->row([$judul, 'Pendapatan', '%'], 1);
   foreach ($data as $d) $x->row([$d['nama'], $rp($d['jumlah']), ['v' => $r['total'] ? $d['jumlah'] / $r['total'] : 0, 's' => 2]]);
}
$x->kosong();
$x->row([$r['periode'] === 'hari' ? 'Tanggal' : 'Bulan', 'Pendapatan', 'Jumlah'], 1);
foreach ($r['tren'] as $t) $x->row([$t['t'], $rp($t['total']), $t['qty']]);

$x->sheet('Per Tindakan', [7, 40, 16, 9, 10, 14, 16, 14, 14, 14, 26, 12]);
$x->freezeHeader();
$x->autoFilter(12);
$x->row(['Peringkat', 'Tindakan', 'Kategori', 'Jumlah', 'Kunjungan', 'Rata-rata harga', 'Pendapatan', 'Fee dokter', 'Fee perawat', 'Fee admin', 'Dokter terbanyak', 'Master tarif'], 1);
foreach ($r['items'] as $it) {
   $x->row([$it['rank'], $it['item'], $it['kategori'], $it['qty'], $it['kunjungan'], $rp($it['rata']), $rp($it['total']), $rp($it['fee_dokter']), $rp($it['fee_perawat']), $rp($it['fee_admin']),
      $it['dokter_utama'], $it['tarif'] ? 'Ada' : 'Tidak ada'], 5);
}

$x->sheet('Per Dokter', [32, 10, 12, 8, 16, 14, 14, 14]);
$x->freezeHeader();
$x->row(['Dokter', 'Kunjungan', 'Jumlah tindakan', 'Jenis', 'Pendapatan', 'Fee dokter', 'Fee perawat', 'Fee admin'], 1);
foreach ($r['dokter'] as $d) $x->row([$d['dokter'], $d['kunjungan'], $d['qty'], $d['jenis'], $rp($d['total']), $rp($d['fee_dokter']), $rp($d['fee_perawat']), $rp($d['fee_admin'])], 5);
$x->row(['TOTAL', $r['kunjungan_tindakan'], $r['qty'], $r['jenis'], $rp($r['total']), $rp($r['fee_dokter']), $rp($r['fee_perawat']), $rp($r['fee_admin'])], 7);

$x->sheet('Detail', [11, 7, 28, 12, 26, 18, 18, 34, 16, 6, 12, 11, 13, 12, 12, 12]);
$x->freezeHeader();
$x->autoFilter(16);
$x->row(['Tanggal', 'Jam', 'Pasien', 'No. RM', 'Dokter', 'Poli', 'Jenis Bayar', 'Tindakan', 'Kategori', 'Qty', 'Harga', 'Diskon', 'Total', 'Fee dokter', 'Fee perawat', 'Fee admin'], 1);
foreach ($rows as $d) {
   $x->row([$d['tanggal'], $d['jam'], $d['nama'], ['v' => $d['rm'], 't' => 'str', 's' => 5], $d['dokter'], $d['poli'], $d['bayar'], $d['item'], $d['kategori'], $d['qty'],
      $rp($d['harga']), $rp($d['diskon']), $rp($d['total']), $rp($d['fee_dokter']), $rp($d['fee_perawat']), $rp($d['fee_admin'])], 5);
}

setcookie('pr_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
$x->kirim('laporan_tindakan_' . $p['from'] . '_' . $p['to'] . '.xlsx');
exit;
