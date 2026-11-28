<?php
/**
 * Simpan keluhan + vital sign kunjungan (modal "Vital sign" di registrasi poliklinik, juga UGD / ranap / registrasi-old).
 * POST JSON {id_visit, keluhan?, catatan?, kondisi_masuk?, sistole?, diastole?, suhu?, nadi?, respirasi?, saturasi?,
 *            tinggi?, berat?, lingkar_perut?}
 *
 * Perbaikan:
 * - Hanya kolom yang DIKIRIM yang diubah. Dulu semua kolom selalu di-update: UGD & ranap hanya mengirim keluhan/catatan
 *   sehingga seluruh vital sign kunjungan terhapus (NULL) setiap kali keluhan disimpan.
 * - kondisi_masuk kosong tidak menimpa nilai lama (kolom ini juga dipakai dokter untuk kode prognosa PCare 01-05).
 * - Validasi rentang wajar (dulu suhu 365 / TD 1200/80 pun tersimpan); BMI & tekanan darah dihitung di server.
 * - Hanya kunjungan faskes di session; kunjungan batal tidak dapat diubah.
 */
header('Content-Type: application/json; charset=utf-8');
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';

function scJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data) || empty($data['id_visit'])) scJawab(['status' => 'error', 'message' => 'ID Visit tidak ditemukan.'], 400);

$kunj = visitGuard($koneksi, 'id_visit', $data['id_visit']);
session_write_close();
if ((int) $kunj['visit_status'] === 99) scJawab(['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.'], 409);

// [kunci payload => [kolom, min, max, label, desimal]]
const SC_ANGKA = [
   'sistole' => ['sistole', 50, 300, 'Sistole', 0],
   'diastole' => ['diastole', 20, 200, 'Diastole', 0],
   'suhu' => ['suhu', 30, 45, 'Suhu', 1],
   'nadi' => ['nadi', 20, 250, 'Nadi', 0],
   'respirasi' => ['respirasi', 4, 80, 'Respirasi', 0],
   'saturasi' => ['saturasi', 50, 100, 'Saturasi', 0],
   'tinggi' => ['tinggi_badan', 30, 250, 'Tinggi badan', 1],
   'berat' => ['berat_badan', 0.5, 350, 'Berat badan', 1],
   'lingkar_perut' => ['lingkar_perut', 20, 250, 'Lingkar perut', 0],
];

$set = [];
$nilai = [];
$salah = [];

foreach (['keluhan' => 'anamnesa', 'catatan' => 'catatan_screening'] as $k => $kol) {
   if (array_key_exists($k, $data)) {
      $set[] = "$kol = ?";
      $nilai[] = mb_substr(trim((string) $data[$k]), 0, 5000);
   }
}
if (array_key_exists('kondisi_masuk', $data) && trim((string) $data['kondisi_masuk']) !== '') {
   $set[] = 'kondisi_masuk = ?';
   $nilai[] = mb_substr(trim((string) $data['kondisi_masuk']), 0, 50);
}

// kompatibel dengan pemanggil lama yang hanya mengirim tekanan_darah "120/80"
if (!array_key_exists('sistole', $data) && !array_key_exists('diastole', $data) && isset($data['tekanan_darah'])
   && preg_match('#^\s*(\d{2,3})\s*/\s*(\d{2,3})\s*$#', (string) $data['tekanan_darah'], $m)) {
   $data['sistole'] = $m[1];
   $data['diastole'] = $m[2];
}

$angka = [];
foreach (SC_ANGKA as $k => [$kol, $min, $max, $label, $dec]) {
   if (!array_key_exists($k, $data)) continue;
   $v = str_replace(',', '.', trim((string) $data[$k]));
   if ($v === '') {
      $angka[$k] = null;
      continue;
   }
   if (!is_numeric($v) || (float) $v < $min || (float) $v > $max) {
      $salah[] = "$label harus antara $min dan $max";
      continue;
   }
   $angka[$k] = $dec ? round((float) $v, $dec) : (int) round((float) $v);
}
if (isset($angka['sistole'], $angka['diastole']) && $angka['sistole'] <= $angka['diastole']) {
   $salah[] = 'Sistole harus lebih besar dari diastole';
}
if ($salah) scJawab(['status' => 'error', 'message' => implode('; ', $salah) . '.'], 422);

foreach ($angka as $k => $v) {
   $set[] = SC_ANGKA[$k][0] . ' = ?';
   $nilai[] = $v === null ? null : (string) $v;
}
// tekanan darah gabungan
if (array_key_exists('sistole', $angka) || array_key_exists('diastole', $angka)) {
   $set[] = 'tekanan_darah = ?';
   $nilai[] = (isset($angka['sistole'], $angka['diastole'])) ? $angka['sistole'] . '/' . $angka['diastole'] : null;
}
// BMI dihitung server (dulu dari input readonly di browser)
if (array_key_exists('tinggi', $angka) || array_key_exists('berat', $angka)) {
   $tb = $angka['tinggi'] ?? null;
   $bb = $angka['berat'] ?? null;
   if ($tb && $bb) {
      $bmi = round($bb / (($tb / 100) ** 2), 2);
      $ket = $bmi < 18.5 ? 'Kurus' : ($bmi < 25 ? 'Normal' : ($bmi < 30 ? 'Gemuk' : 'Obesitas'));
   } else {
      $bmi = null;
      $ket = null;
   }
   $set[] = 'bmi = ?';
   $nilai[] = $bmi === null ? null : (string) $bmi;
   $set[] = 'bmi_keterangan = ?';
   $nilai[] = $ket;
}

if (!$set) scJawab(['status' => 'error', 'message' => 'Tidak ada data yang disimpan.'], 400);

$idVisit = (int) $kunj['id_visit'];
$idc = (int) $kunj['id_customer'];
$nilai[] = $idVisit;
$nilai[] = $idc;
$scLama = logAmbilBaris($koneksi, 'pasien_visit', 'id_visit', $idVisit);
$st = $koneksi->prepare('UPDATE pasien_visit SET ' . implode(', ', $set) . ' WHERE id_visit = ? AND id_customer = ?');
if (!$st) {
   error_log('[saveScreening] ' . $koneksi->error);
   scJawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
}
$st->bind_param(str_repeat('s', count($nilai) - 2) . 'ii', ...$nilai);
if (!$st->execute()) {
   error_log('[saveScreening] ' . $st->error);
   scJawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
}
$st->close();
if ($scLama) logBaris($koneksi, 'klinis', 'pasien_visit', 'id_visit', $idVisit, $scLama,
   ['tipe' => 'vital_sign', 'label' => 'Vital sign · ' . ($scLama['patient_name_pcare'] ?? ''), 'visit_ID' => $kunj['visit_ID']], 'Vital sign / skrining disimpan');

scJawab(['status' => 'success', 'message' => 'Vital sign tersimpan.', 'bmi' => $bmi ?? null, 'bmi_keterangan' => $ket ?? null]);
