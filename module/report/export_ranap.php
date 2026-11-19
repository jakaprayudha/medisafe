<?php
/** Export Excel laporan rawat inap: Ringkasan + Data pasien + Per kamar. */
require_once '../../database/connect.php';
require_once '../../controller/report/ranapService.php';
require_once '../../controller/report/xlsxRingan.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = rnParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
@set_time_limit(300);

$rows = rnRows($koneksi, $idc, $p);
$km = rnKamar($koneksi, $idc);
$r = rnRingkasan($rows, $p, $km['bed'], $koneksi);
$klinik = apKlinik($koneksi, $idc);
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$tot = max(1, $r['total']);

$x = new XlsxRingan();
$x->sheet('Ringkasan', [36, 14, 10]);
$x->row([['v' => 'LAPORAN RAWAT INAP — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
$x->row([['v' => 'Periode masuk: ' . $periode, 's' => 4]]);
$x->row([['v' => 'Filter: ' . rnLabelFilter($p, $km['rooms']) . ' · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
$x->kosong();
$x->row(['Indikator', 'Nilai'], 1);
foreach ([
   ['Pasien masuk rawat inap', $r['total']],
   ['Sudah pulang', $r['status']['pulang']],
   ['Masih dirawat', $r['status']['dirawat']],
   ['Belum ada CPPT', $r['status']['belum']],
   ['Total hari rawat (dalam periode)', $r['hari_pasien']],
   ['Jumlah tempat tidur', $r['bed']],
] as [$k, $v]) $x->row([$k, ['v' => $v, 's' => 6]]);
$x->row(['Rata-rata lama rawat / ALOS (hari)', (float) $r['alos']]);
$x->row(['Median lama rawat (hari)', (float) $r['los_median']]);
$x->row(['BOR (pemakaian tempat tidur)', $r['bor'] === null ? '-' : ['v' => $r['bor'] / 100, 's' => 2]]);
$x->row(['Pulang sembuh / membaik', ['v' => $r['pct_sembuh'] / 100, 's' => 2]]);
if ($r['los_tidak_valid']) $x->row([['v' => $r['los_tidak_valid'] . ' pasien pulang dengan tanggal pulang kosong/tidak wajar tidak dihitung di lama rawat.', 's' => 4]]);

$blok = function (string $judul, array $data) use ($x, $tot) {
   $x->kosong();
   $x->row([$judul, 'Jumlah', '%'], 1);
   if (!$data) $x->row([['v' => 'Tidak ada data', 's' => 4]]);
   foreach ($data as $d) $x->row([$d['nama'], ['v' => $d['jumlah'], 's' => 6], ['v' => $d['jumlah'] / $tot, 's' => 2]]);
};
$blok('Kondisi pulang', $r['kondisi']);
$blok('Lama rawat', $r['los_bin']);
$blok('Kamar', $r['kamar']);
$blok('Dokter', $r['dokter']);
$blok('Jenis bayar', $r['bayar']);
$blok('Kelompok umur', $r['umur']);
$blok('Diagnosis teratas', array_map(fn($d) => ['nama' => $d['kode'] . ($d['nama'] ? ' — ' . $d['nama'] : ''), 'jumlah' => $d['jumlah']], $r['diagnosa']));

$x->sheet('Data Pasien', [6, 11, 7, 11, 7, 9, 14, 30, 12, 18, 16, 5, 6, 26, 9, 16, 10, 16, 18, 14, 18, 7]);
$x->freezeHeader();
$x->autoFilter(22);
$x->row(['No', 'Masuk', 'Jam', 'Pulang', 'Jam', 'Lama (hr)', 'Status', 'Nama Pasien', 'No. RM', 'NIK', 'No. BPJS', 'L/P', 'Umur', 'Dokter', 'Diagnosis',
   'Kamar', 'Bed', 'Kelas', 'Jenis Bayar', 'Kondisi Pulang', 'Cara Keluar', 'CPPT'], 1);
foreach ($rows as $i => $d) {
   $x->row([$i + 1, $d['masuk'], $d['jam'], $d['pulang'] ?? '', $d['jam_pulang'], $d['los'] ?? ($d['los_valid'] ? '' : '?'), RN_STATUS_LABEL[$d['status']],
      $d['nama'], ['v' => $d['rm'], 't' => 'str', 's' => 5], ['v' => $d['nik'], 't' => 'str', 's' => 5], ['v' => $d['noKartu'], 't' => 'str', 's' => 5],
      $d['gender'], $d['umur'] ?? '', $d['dokter'], $d['diagnosa'], $d['kamar'], $d['bed'], $d['kelas'], $d['bayar'], $d['kondisi'], $d['cara_keluar'], $d['cppt']], 5);
}

$x->sheet('Per Kamar', [24, 18, 10, 12]);
$x->row(['Kamar', 'Kelas', 'Bed', 'Pasien masuk'], 1);
$perKamar = [];
foreach ($rows as $d) if ($d['id_room']) $perKamar[$d['id_room']] = ($perKamar[$d['id_room']] ?? 0) + 1;
foreach ($km['rooms'] as $rm) $x->row([$rm['room_name'], $rm['service_class'], (int) $rm['bed'], $perKamar[$rm['id_room']] ?? 0], 5);

setcookie('rn_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
$x->kirim('laporan_rawat_inap_' . $p['from'] . '_' . $p['to'] . '.xlsx');
exit;
