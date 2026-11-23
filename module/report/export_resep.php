<?php
/**
 * Export Excel laporan pelayanan resep: sheet "Ringkasan", "Obat", "Harian", "Resep".
 * Filter sama dengan module/admin/rpt_resep (controller/report/resepService.php).
 */
require_once '../../database/connect.php';
require_once '../../controller/report/resepService.php';
require_once '../../controller/report/xlsxRingan.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = rsParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
if (!class_exists('ZipArchive')) {
   http_response_code(500);
   exit('Ekstensi PHP zip belum aktif di server.');
}
@set_time_limit(300);

[$tiket, $obat] = rsData($koneksi, $idc, $p);
$a = rsRingkasan($tiket, $obat, $p);
$r = $a['ringkasan'];
$penjamin = rsPenjamin($koneksi);
$klinik = rsKlinik($koneksi, $idc);
$periode = rsTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . rsTglIndo($p['to']) : '');
$num = fn($v) => ['v' => (int) $v, 's' => 6];

$x = new XlsxRingan();

/* ---------------- Ringkasan ---------------- */
$x->sheet('Ringkasan', [36, 18, 14, 10]);
$x->row([['v' => 'LAPORAN PELAYANAN RESEP — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
$x->row([['v' => 'Periode (tanggal resep): ' . $periode, 's' => 4]]);
$x->row([['v' => 'Filter: ' . rsLabelFilter($p, $penjamin) . ' · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
$x->kosong();
$x->row(['Ringkasan', 'Nilai'], 1);
foreach ([
   ['Jumlah resep', $r['resep']], ['Kunjungan', $r['kunjungan']], ['Pasien', $r['pasien']], ['Resep selesai (diserahkan)', $r['selesai']],
   ['Resep dalam proses', $r['proses']], ['Draf dokter', $r['draf']], ['Resep racikan', $r['racikan']], ['Jumlah item', $r['item']],
   ['Jumlah obat keluar (qty)', $r['qty']], ['Nilai obat (Rp)', $r['nilai']], ['Nilai obat diserahkan (Rp)', $r['nilai_diserahkan']],
   ['Rata-rata nilai per resep (Rp)', $r['rata_nilai']],
] as [$l, $v]) $x->row([['v' => $l, 's' => 5], $num($v)]);
$x->row([['v' => 'Rata-rata item per resep', 's' => 5], $r['rata_item']]);
foreach (['Per status' => 'status', 'Per jenis' => 'jenis', 'Per dokter' => 'dokter', 'Per poli' => 'poli', 'Per penjamin' => 'penjamin'] as $judul => $g) {
   $x->kosong();
   $x->row([$judul, 'Resep', 'Nilai (Rp)', '%'], 1);
   foreach ($a['grup'][$g] as $o) $x->row([['v' => $o['nama'], 's' => 5], $num($o['resep']), $num($o['nilai']), ['v' => $r['resep'] ? $o['resep'] / $r['resep'] : 0, 's' => 2]]);
}

/* ---------------- Obat ---------------- */
$x->sheet('Obat', [6, 40, 12, 14, 12, 18]);
$x->row(['No', 'Obat / BMHP', 'Satuan', 'Qty keluar', 'Resep', 'Nilai (Rp)'], 1);
$x->freezeHeader();
$x->autoFilter(6);
foreach ($a['obat_semua'] as $i => $o) $x->row([$i + 1, $o['nama'], $o['satuan'], $num($o['qty']), $num($o['resep']), $num($o['nilai'])], 5);

/* ---------------- Harian ---------------- */
$x->sheet('Harian', [14, 12, 12, 18]);
$x->row(['Tanggal', 'Resep', 'Selesai', 'Nilai (Rp)'], 1);
$x->freezeHeader();
foreach ($a['harian'] as $h) $x->row([$h['tanggal'], $num($h['resep']), $num($h['selesai']), $num($h['nilai'])], 5);
$x->row([['v' => 'TOTAL', 's' => 7], $num($r['resep']), $num($r['selesai']), $num($r['nilai'])]);

/* ---------------- Resep ---------------- */
$x->sheet('Resep', [6, 12, 7, 20, 22, 28, 12, 26, 16, 18, 14, 14, 8, 10, 14]);
$x->row(['No', 'Tanggal', 'Jam', 'No. resep', 'No. kunjungan', 'Pasien', 'No. RM', 'Dokter', 'Poli', 'Penjamin', 'Jenis', 'Status', 'Item', 'Qty', 'Nilai (Rp)'], 1);
$x->freezeHeader();
$x->autoFilter(15);
foreach ($tiket as $i => $d) {
   $x->row([
      $i + 1, $d['tanggal'], $d['jam'], ['v' => $d['nomor'], 't' => 'str', 's' => 5], $d['visit_ID'], $d['pasien'], ['v' => $d['nomor_rm'], 't' => 'str', 's' => 5],
      $d['dokter'], $d['poli'], $d['penjamin'], $d['jenis'] . ($d['racikan'] ? ' (' . $d['racikan'] . ')' : ''), $d['status_label'],
      $num($d['item']), $num($d['qty']), $num($d['nilai']),
   ], 5);
}
unset($tiket);

setcookie('rs_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
$x->kirim('laporan_resep_' . $p['from'] . ($p['from'] !== $p['to'] ? '_' . $p['to'] : '') . '.xlsx');
exit;
