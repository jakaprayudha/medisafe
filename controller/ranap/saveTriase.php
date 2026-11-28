<?php
/**
 * Simpan triase kegawatdaruratan (module/admin/triase). POST JSON.
 * 1 data per kunjungan per faskes: bila sudah ada -> diperbarui, bila belum -> ditambah.
 * Perbaikan: visit_ID / nomor_rm dulu ditempel langsung ke SQL (injection) dan pencarian data lama tidak dibatasi faskes;
 * angka kosong disimpan '' ke kolom INT; pesan error SQL dikirim ke browser.
 */
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');

function balas(int $kode, string $status, string $pesan): void
{
   http_response_code($kode);
   echo json_encode(['status' => $status, 'message' => $pesan], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') balas(405, 'error', 'Method tidak diizinkan.');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
if ($idc <= 0) balas(401, 'error', 'Sesi berakhir, silakan login ulang.');

$d = json_decode(file_get_contents('php://input'), true);
if (!is_array($d)) balas(422, 'error', 'Data tidak valid.');
$teks = fn($k, $max = 2000) => mb_substr(trim((string) ($d[$k] ?? '')), 0, $max);
$angka = function ($k, $min, $max, $label) use ($d) {
   $v = trim(str_replace(',', '.', (string) ($d[$k] ?? '')));
   if ($v === '') return null;
   if (!is_numeric($v) || (float) $v < $min || (float) $v > $max) balas(422, 'error', "$label harus di antara $min–$max.");
   return $v;
};

$visit = $teks('visit_ID', 255);
$rm = $teks('nomor_rm', 255);
if ($visit === '' || $rm === '') balas(422, 'error', 'Parameter kunjungan tidak lengkap.');

$tgl = $teks('tanggal_masuk', 10);
if ($tgl !== '' && !(($x = DateTime::createFromFormat('!Y-m-d', $tgl)) && $x->format('Y-m-d') === $tgl)) balas(422, 'error', 'Tanggal masuk tidak valid.');
$jam = $teks('jam_masuk', 8);
if ($jam !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $jam)) balas(422, 'error', 'Jam masuk tidak valid.');
$td = $teks('tekanan_darah', 20);
if ($td !== '' && !preg_match('/^\d{2,3}\/\d{2,3}$/', $td)) balas(422, 'error', 'Tekanan darah harus berformat sistol/diastol, mis. 120/80.');
$nadi = $angka('nadi', 0, 300, 'Nadi');
$rr = $angka('rr', 0, 100, 'Respirasi');
$suhu = $angka('suhu', 25, 45, 'Suhu');
$spo2 = $angka('spo2', 0, 100, 'SpO₂');
$e = $angka('gcs_e', 1, 4, 'GCS mata');
$v = $angka('gcs_v', 1, 5, 'GCS verbal');
$m = $angka('gcs_m', 1, 6, 'GCS motorik');
$gcs = ($e !== null && $v !== null && $m !== null) ? (string) ((int) $e + (int) $v + (int) $m) : null;   // dihitung ulang di server
$nyeri = $angka('nyeri', 0, 10, 'Skala nyeri');
$triase = $teks('triase', 20);
if ($triase !== '' && !in_array($triase, ['ATS 1', 'ATS 2', 'ATS 3', 'ATS 4', 'ATS 5'], true)) balas(422, 'error', 'Kategori triase tidak valid.');
$ref = json_decode((string) ($d['referensi_triase'] ?? '[]'), true);
$ref = json_encode(is_array($ref) ? array_values(array_map(fn($x) => mb_substr((string) $x, 0, 200), $ref)) : [], JSON_UNESCAPED_UNICODE);
$anamnesa = $teks('anamnesa_choice', 50);
$keluhan = $teks('keluhan_utama');
$catatan = $teks('catatan');

try {
   // kunjungan harus milik faskes ini
   $st = $koneksi->prepare("SELECT 1 FROM pasien_visit pv JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      WHERE pv.visit_ID = ? AND mp.nomor_rm = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('ssi', $visit, $rm, $idc);
   $st->execute();
   $ada = (bool) $st->get_result()->fetch_row();
   $st->close();
   if (!$ada) balas(404, 'error', 'Kunjungan tidak ditemukan untuk faskes ini.');

   $st = $koneksi->prepare("SELECT id_triase FROM pasien_triase WHERE visit_ID = ? AND id_customer = ? ORDER BY id_triase DESC LIMIT 1");
   $st->bind_param('si', $visit, $idc);
   $st->execute();
   $lama = $st->get_result()->fetch_assoc();
   $st->close();

   $tglV = $tgl ?: null;
   $jamV = $jam ?: null;
   if ($lama) {
      $st = $koneksi->prepare("UPDATE pasien_triase SET tanggal_masuk = ?, jam_masuk = ?, keluhan_utama = ?, tekanan_darah = ?, nadi = ?, rr = ?, suhu = ?, spo2 = ?,
            gcs_e = ?, gcs_v = ?, gcs_m = ?, gcs_total = ?, skala_nyeri = ?, triase = ?, referensi_triase = ?, anamnesa_choice = ?, catatan = ?, nomor_rm = ?, updated_at = NOW()
         WHERE id_triase = ? AND id_customer = ?");
      $id = (int) $lama['id_triase'];
      $st->bind_param('ssssssssssssssssssii', $tglV, $jamV, $keluhan, $td, $nadi, $rr, $suhu, $spo2, $e, $v, $m, $gcs, $nyeri, $triase, $ref, $anamnesa, $catatan, $rm, $id, $idc);
      $pesan = 'Data triase diperbarui.';
   } else {
      $st = $koneksi->prepare("INSERT INTO pasien_triase (visit_ID, nomor_rm, tanggal_masuk, jam_masuk, keluhan_utama, tekanan_darah, nadi, rr, suhu, spo2,
            gcs_e, gcs_v, gcs_m, gcs_total, skala_nyeri, triase, referensi_triase, anamnesa_choice, catatan, id_customer)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $st->bind_param('sssssssssssssssssssi', $visit, $rm, $tglV, $jamV, $keluhan, $td, $nadi, $rr, $suhu, $spo2, $e, $v, $m, $gcs, $nyeri, $triase, $ref, $anamnesa, $catatan, $idc);
      $pesan = 'Data triase disimpan.';
   }
   $trLama = $lama ? logAmbilBaris($koneksi, 'pasien_triase', 'id_triase', (int) $lama['id_triase']) : null;
   $st->execute();
   $trId = $lama ? (int) $lama['id_triase'] : (int) $st->insert_id;
   $st->close();
   logBaris($koneksi, 'klinis', 'pasien_triase', 'id_triase', $trId, $trLama, ['tipe' => 'triase', 'label' => 'Triase ' . $triase . ' · RM ' . $rm, 'visit_ID' => $visit], $pesan);
} catch (Throwable $ex) {
   error_log('[ranap/saveTriase] ' . $ex->getMessage());
   balas(500, 'error', 'Gagal menyimpan triase.');
}
balas(200, 'success', $pesan);
