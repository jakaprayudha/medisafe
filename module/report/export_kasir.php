<?php
/**
 * Export Excel laporan kasir: sheet "Ringkasan", "Harian", "Transaksi".
 * Filter sama dengan module/admin/rpt_kasir (controller/report/kasirService.php).
 */
require_once '../../database/connect.php';
require_once '../../controller/report/kasirService.php';
require_once '../../controller/report/xlsxRingan.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = ksParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
if (!class_exists('ZipArchive')) {
   http_response_code(500);
   exit('Ekstensi PHP zip belum aktif di server.');
}
@set_time_limit(300);

$rows = ksRows($koneksi, $idc, $p);
$a = ksRingkasan($rows, $p);
$r = $a['ringkasan'];
$belum = ksBelumBayar($koneksi, $idc, $p);
$opsi = ksOpsi($koneksi, $idc);
$klinik = ksKlinik($koneksi, $idc);
$periode = ksTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . ksTglIndo($p['to']) : '');
$num = fn($v) => ['v' => (int) $v, 's' => 6];

$x = new XlsxRingan();

/* ---------------- Ringkasan ---------------- */
$x->sheet('Ringkasan', [34, 18, 14, 10]);
$x->row([['v' => 'LAPORAN KASIR — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
$x->row([['v' => 'Periode: ' . $periode, 's' => 4]]);
$x->row([['v' => 'Filter: ' . ksLabelFilter($p, $opsi) . ' · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
$x->kosong();
$x->row(['Ringkasan', 'Nilai'], 1);
foreach ([
   ['Total diterima (Rp)', $r['total']], ['Jumlah transaksi', $r['transaksi']], ['Transaksi berbayar', $r['berbayar']],
   ['Transaksi tanpa tagihan', $r['tanpa_tagihan']], ['Rata-rata per transaksi berbayar (Rp)', $r['rata']], ['Transaksi terbesar (Rp)', $r['terbesar']],
   ['Tunai (Rp)', $r['tunai']], ['Non-tunai (Rp)', $r['non_tunai']], ['Uang masuk (Rp)', $r['dibayar']], ['Kembalian (Rp)', $r['kembalian']],
   ['Komposisi obat & BMHP (Rp)', $r['obat']], ['Komposisi tindakan & administrasi (Rp)', $r['tindakan']],
   ['Belum dibayar — kunjungan', $belum['kunjungan']], ['Belum dibayar — total tagihan (Rp)', $belum['total']],
] as [$l, $v]) $x->row([['v' => $l, 's' => 5], $num($v)]);

foreach (['Per metode bayar' => $a['metode'], 'Per penjamin' => $a['penjamin'], 'Per poli' => $a['poli']] as $judul => $grup) {
   $x->kosong();
   $x->row([$judul, 'Total (Rp)', 'Transaksi', '%'], 1);
   foreach ($grup as $g) $x->row([['v' => $g['nama'], 's' => 5], $num($g['total']), $num($g['transaksi']), ['v' => $r['total'] ? $g['total'] / $r['total'] : 0, 's' => 2]]);
}

/* ---------------- Harian ---------------- */
$x->sheet('Harian', [14, 12, 18, 18, 18]);
$x->row(['Tanggal', 'Transaksi', 'Tunai (Rp)', 'Non-tunai (Rp)', 'Total (Rp)'], 1);
$x->freezeHeader();
foreach ($a['harian'] as $h) $x->row([$h['tanggal'], $num($h['transaksi']), $num($h['tunai']), $num($h['non_tunai']), $num($h['total'])], 5);
$x->row([['v' => 'TOTAL', 's' => 7], $num($r['transaksi']), $num($r['tunai']), $num($r['non_tunai']), $num($r['total'])]);

/* ---------------- Transaksi ---------------- */
$x->sheet('Transaksi', [6, 12, 16, 22, 12, 28, 12, 18, 16, 26, 14, 14, 14, 14, 14, 14]);
$x->row(['No', 'Tgl bayar', 'No. transaksi', 'No. kunjungan', 'Tgl kunjungan', 'Pasien', 'No. RM', 'Penjamin', 'Poli', 'Dokter', 'Metode',
   'Obat (Rp)', 'Tindakan (Rp)', 'Total (Rp)', 'Dibayar (Rp)', 'Kembalian (Rp)'], 1);
$x->freezeHeader();
$x->autoFilter(16);
foreach ($rows as $i => $d) {
   $x->row([
      $i + 1, $d['tgl_bayar'], ['v' => $d['no_transaksi'], 't' => 'str', 's' => 5], $d['visit_ID'], $d['tgl_kunjungan'], $d['pasien'],
      ['v' => $d['nomor_rm'], 't' => 'str', 's' => 5], $d['penjamin'], $d['poli'], $d['dokter'], $d['metode'],
      $num($d['obat']), $num($d['tindakan']), $num($d['total']), $num($d['dibayar']), $num($d['kembalian']),
   ], 5);
}
unset($rows);

// cookie dibaca halaman laporan untuk menutup indikator "menyiapkan file"
setcookie('rk_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
$x->kirim('laporan_kasir_' . $p['from'] . ($p['from'] !== $p['to'] ? '_' . $p['to'] : '') . '.xlsx');
exit;
