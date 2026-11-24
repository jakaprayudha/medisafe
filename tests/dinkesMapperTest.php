<?php
// Cek mapper payload Dinkes tanpa DB/jaringan: php tests/dinkesMapperTest.php
if (PHP_SAPI !== 'cli') {
   http_response_code(405);
   exit('CLI only');
}
require_once __DIR__ . '/../controller/dinkes/dinkesService.php';

function dkAssert(bool $ok, string $msg): void
{
   if (!$ok) throw new RuntimeException($msg);
}

$c = ['visit_ID' => 'VIS-1-261124-AB12CD', 'visit_date' => '2026-11-24', 'patient_nik' => '1207 0203 0397 0001', 'patient_kk' => '1207020303970002',
   'patient_name' => 'Pasien Uji', 'patient_gender' => 'Perempuan', 'patient_datebirth' => '2024-06-11', 'patient_place' => 'Lubuk Pakam',
   'patient_address' => 'Jl. Uji', 'patient_provinsi' => 'SUMATERA UTARA', 'patient_kabupaten' => 'DELI SERDANG', 'patient_kecamatan' => 'LUBUK PAKAM',
   'patient_kelurahan' => 'PAGAR MERBAU I', 'tekanan_darah' => '120/80', 'suhu' => '36.5', 'nadi' => '80', 'respirasi' => '', 'tinggi' => '75.0',
   'berat' => '10.2', 'bmi' => '18.1', 'bmi_ket' => 'Normal', 'anamnesa' => 'demam', 'icd' => 'A09', 'jk' => 'P'];

$d = dkMapDewasa($c, 2);
dkAssert($d['nik'] === '1207020303970001', 'NIK harus dinormalkan ke 16 digit');
dkAssert($d['puskes'] === 2 && $d['diagnosa'] === 'A09' && $d['jenis_kelamin'] === 'P', 'field dewasa');
dkAssert(!array_key_exists('respirasi', $d['detail_data']) && $d['detail_data']['suhu'] === 36.5, 'detail_data membuang kosong & numerik');

$f = ['nama_orang_tua' => 'Fulanah', 'bb_lahir' => 3.3, 'tb_lahir' => 48.0, 'lila_cm' => 14.4, 'lingkar_kepala_cm' => 46.2, 'imunisasi_lengkap' => true, 'ttd_jumlah' => 0];
$t = dkMapBalitaTambah($c, $f, 1);
dkAssert($t['bb_ukur'] === 10.2 && $t['tb_ukur'] === 75.0 && $t['puskes'] === 1, 'balita tambah ukur');
dkAssert($t['detail_data']['identitas']['nomor_kk'] === '1207020303970002', 'KK default dari pasien');
dkAssert($t['detail_data']['riwayat']['data_kesehatan_dan_riwayat_sakit']['status_imunisasi_dasar_lengkap'] === true, 'bool imunisasi');
dkAssert($t['detail_data']['riwayat']['data_kesehatan_dan_riwayat_sakit']['riwayat_sakit_kronis'] === '-', 'default kronis');

$respon = ['detail_zscore' => ['bb_u' => ['zscore' => -1.8, 'interpretasi' => 'Berat badan normal']]];
$u = dkMapBalitaUpdate($c, $f, 1, 15, $respon);
dkAssert($u['id'] === 15 && $u['data_ukur']['z_score_bb_u'] === -1.8 && $u['data_ukur']['z_score_tb_u'] === null, 'update memakai z-score GET terakhir');
dkAssert($u['data_ukur']['bb_terupdate_kg'] === 10.2, 'update bb_terupdate_kg');

dkAssert(dkUmurTahun('2024-06-11', '2026-11-24') === 2 && dkUmurTahun('2021-11-25', '2026-11-24') === 4 && dkUmurTahun('2021-11-24', '2026-11-24') === 5, 'batas umur 5 tahun');
dkAssert(dkUmurTahun('0000-00-00', '2026-11-24') === null, 'tanggal lahir tidak valid');
echo "dinkesMapperTest OK\n";
