<?php
/**
 * Export Excel laporan appointment: sheet "Ringkasan" (dashboard) + "Data".
 * Memakai XlsxRingan (streaming) agar rentang panjang (puluhan ribu baris) tetap cepat & hemat memori.
 */
require_once '../../database/connect.php';
require_once '../../controller/report/appointmentService.php';
require_once '../../controller/report/xlsxRingan.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = apParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
if (!class_exists('ZipArchive')) {
   http_response_code(500);
   exit('Ekstensi PHP zip belum aktif di server.');
}
@set_time_limit(300);

$rows = apRows($koneksi, $idc, $p);
$r = apRingkasan($rows, $p);
$klinik = apKlinik($koneksi, $idc);
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$aktif = max(1, $r['aktif']);

$x = new XlsxRingan();

/* ---------------- Ringkasan ---------------- */
$x->sheet('Ringkasan', [34, 14, 10]);
$x->row([['v' => 'LAPORAN APPOINTMENT — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
$x->row([['v' => 'Periode: ' . $periode, 's' => 4]]);
$x->row([['v' => 'Filter: ' . apLabelFilter($p) . ' · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
$x->kosong();

$x->row(['Indikator', 'Nilai', ''], 1);
foreach ([
   ['Total pendaftaran', $r['total']],
   ['Selesai dilayani', $r['status']['selesai']],
   ['Dalam proses', $r['status']['proses']],
   ['Belum check-in', $r['status']['booking']],
   ['Batal', $r['status']['batal']],
   ['Pasien unik (tanpa batal)', $r['pasien_unik']],
   ['Kunjungan BPJS (tanpa batal)', $r['bpjs']],
   ['Rata-rata kunjungan / hari', (float) $r['rata_hari']],
   ['Jam tersibuk', $r['jam_sibuk'] === null ? '-' : sprintf('%02d:00–%02d:00', $r['jam_sibuk'], $r['jam_sibuk'] + 1)],
] as [$k, $v]) {
   $x->row([$k, is_int($v) ? ['v' => $v, 's' => 6] : $v]);
}
$x->row(['Tingkat selesai (tanpa batal)', ['v' => $r['rate_selesai'] / 100, 's' => 2]]);
$x->row(['Tingkat batal', ['v' => $r['rate_batal'] / 100, 's' => 2]]);

$blok = function (string $judul, array $data) use ($x, $aktif) {
   $x->kosong();
   $x->row([$judul, 'Jumlah', '%'], 1);
   if (!$data) $x->row([['v' => 'Tidak ada data', 's' => 4]]);
   foreach ($data as $d) {
      $x->row([$d['nama'], ['v' => $d['jumlah'], 's' => 6], ['v' => $d['jumlah'] / $aktif, 's' => 2]]);
   }
};
$blok('Dokter', $r['dokter']);
$blok('Poli', $r['poli']);
$blok('Jenis bayar', $r['bayar']);
$gen = [['nama' => 'Laki-laki', 'jumlah' => $r['gender']['L']], ['nama' => 'Perempuan', 'jumlah' => $r['gender']['P']]];
if ($r['gender']['-']) $gen[] = ['nama' => 'Tidak diketahui', 'jumlah' => $r['gender']['-']];
$blok('Jenis kelamin', $gen);

$x->kosong();
$x->row([$r['periode'] === 'hari' ? 'Tanggal' : 'Bulan', 'Selesai', 'Proses', 'Belum check-in', 'Batal', 'Total'], 1);
foreach ($r['tren'] as $t) {
   $x->row([$t['t'], $t['selesai'], $t['proses'], $t['booking'], $t['batal'], $t['selesai'] + $t['proses'] + $t['booking'] + $t['batal']]);
}

/* ---------------- Data ---------------- */
$x->sheet('Data', [7, 12, 7, 15, 17, 19, 32, 5, 28, 22, 18]);
$x->freezeHeader();
$x->autoFilter(11);
$x->row(['No', 'Tanggal', 'Jam', 'Status', 'No. BPJS', 'NIK', 'Nama Pasien', 'L/P', 'Dokter', 'Poli', 'Jenis Bayar'], 1);
foreach ($rows as $i => $d) {
   $x->row([
      $i + 1, $d['tanggal'], $d['jam'], $d['status_label'],
      ['v' => $d['noKartu'], 't' => 'str', 's' => 5], // teks: angka 13–16 digit jangan jadi 1,2E+15
      ['v' => $d['nik'], 't' => 'str', 's' => 5],
      $d['nama'], $d['gender'], $d['dokter'], $d['poli'], $d['bayar'],
   ], 5);
}
unset($rows);

// cookie ini dibaca halaman laporan untuk menutup indikator "menyiapkan file"
setcookie('ap_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
$x->kirim('laporan_appointment_' . $p['from'] . ($p['from'] !== $p['to'] ? '_' . $p['to'] : '') . '.xlsx');
exit;
