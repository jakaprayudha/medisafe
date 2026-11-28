<?php
/**
 * Tanda tangan "yang membuat pernyataan" (pasien / keluarga) di form persetujuan tindakan (module/admin/form_persetujuan).
 * POST JSON {id_visit, image:"data:image/png;base64,...", nama, umur, jk, hubungan}
 *   image kosong -> hanya ubah data penanda tangan (tanda tangan yang sudah ada dipertahankan)
 * -> uploads/ttd/{file}, pasien_visit.signature_opname + identitas penanda tangan.
 * Dibaca cetak module/admin/print/formulir_surat_persetujuan.php.
 *
 * Dulu: semua nilai (id_visit, nama, umur, ...) disisipkan langsung ke SQL (SQL injection; nama berisi tanda kutip
 * seperti "Ma'ruf" juga membuat simpan gagal tanpa pesan), tanpa cek faskes, isi gambar tidak divalidasi,
 * nama file time() bisa bentrok antar petugas.
 */
header('Content-Type: application/json; charset=utf-8');
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';

function skJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) skJawab(['status' => 'error', 'message' => 'Data tidak valid'], 400);

$id_visit = (int) ($in['id_visit'] ?? 0);
$image = (string) ($in['image'] ?? '');
$nama = trim((string) ($in['nama'] ?? ''));
$umur = trim((string) ($in['umur'] ?? ''));
$jk = trim((string) ($in['jk'] ?? ''));
$hubungan = trim((string) ($in['hubungan'] ?? ''));

if ($id_visit <= 0 || $nama === '' || $umur === '' || $jk === '') {
   skJawab(['status' => 'error', 'message' => 'Nama, umur, dan jenis kelamin wajib diisi'], 422);
}
// data lama kadang "30 tahun" -> ambil angkanya saja
if (!preg_match('/^\s*(\d{1,3})\b/', $umur, $mu) || (int) $mu[1] > 130) skJawab(['status' => 'error', 'message' => 'Umur tidak valid'], 422);
$umur = $mu[1];

$kunj = visitGuard($koneksi, 'id_visit', $id_visit);
session_write_close();
$idc = (int) $kunj['id_customer'];
$nama = mb_substr($nama, 0, 150);
$jk = mb_substr($jk, 0, 20);
$hubungan = mb_substr($hubungan, 0, 50);

// tanpa gambar baru = ubah data penanda tangan saja (tanda tangan lama dipertahankan)
if ($image === '') {
   $st = $koneksi->prepare("SELECT signature_opname FROM pasien_visit WHERE id_visit = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('ii', $id_visit, $idc);
   $st->execute();
   $lama = (string) (($st->get_result()->fetch_assoc() ?: [])['signature_opname'] ?? '');
   $st->close();
   if ($lama === '') skJawab(['status' => 'error', 'message' => 'Tanda tangan belum ada. Silakan tanda tangani terlebih dahulu.'], 422);
   $st = $koneksi->prepare("UPDATE pasien_visit SET opname_keluarga_name = ?, opname_umur = ?, opname_gender = ?, opname_hubungan_pasien = ? WHERE id_visit = ? AND id_customer = ?");
   $st->bind_param('ssssii', $nama, $umur, $jk, $hubungan, $id_visit, $idc);
   if (!$st->execute()) {
      error_log('[saveSignatureKeluarga] ' . $st->error);
      skJawab(['status' => 'error', 'message' => 'Gagal menyimpan data'], 500);
   }
   $st->close();
   logAktivitas($koneksi, 'dokumen', 'ubah', ['tipe' => 'ttd_keluarga', 'id' => $id_visit, 'label' => 'Penanda tangan keluarga ' . $nama, 'visit_ID' => $kunj['visit_ID']],
      'Data penanda tangan keluarga (persetujuan rawat inap) diperbarui', null, ['nama' => $nama, 'umur' => $umur, 'gender' => $jk, 'hubungan' => $hubungan]);
   skJawab(['status' => 'success', 'file' => $lama, 'message' => 'Data penanda tangan diperbarui']);
}

$data = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/png;base64,#i', '', $image)), true);
$info = $data ? @getimagesizefromstring($data) : false;
if (!$data || strlen($data) > 2 * 1024 * 1024 || !$info || $info[2] !== IMAGETYPE_PNG) {
   skJawab(['status' => 'error', 'message' => 'Gambar tanda tangan tidak valid'], 422);
}

$folder = __DIR__ . '/../../uploads/ttd/';
if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) skJawab(['status' => 'error', 'message' => 'Folder tanda tangan tidak tersedia'], 500);
$filename = 'ttd_kel_' . $id_visit . '_' . bin2hex(random_bytes(5)) . '.png';
if (@file_put_contents($folder . $filename, $data, LOCK_EX) === false) skJawab(['status' => 'error', 'message' => 'Gagal menyimpan file tanda tangan'], 500);

$st = $koneksi->prepare("UPDATE pasien_visit SET signature_opname = ?, opname_keluarga_name = ?, opname_umur = ?, opname_gender = ?, opname_hubungan_pasien = ?
   WHERE id_visit = ? AND id_customer = ?");
$st->bind_param('sssssii', $filename, $nama, $umur, $jk, $hubungan, $id_visit, $idc);
if (!$st->execute()) {
   @unlink($folder . $filename);
   error_log('[saveSignatureKeluarga] ' . $st->error);
   skJawab(['status' => 'error', 'message' => 'Gagal menyimpan tanda tangan'], 500);
}
$st->close();
logAktivitas($koneksi, 'dokumen', 'tanda_tangan', ['tipe' => 'ttd_keluarga', 'id' => $id_visit, 'label' => 'Tanda tangan keluarga ' . $nama, 'visit_ID' => $kunj['visit_ID']],
   'Tanda tangan keluarga (persetujuan rawat inap) disimpan', null, ['signature_opname' => $filename, 'nama' => $nama, 'umur' => $umur, 'gender' => $jk, 'hubungan' => $hubungan]);

skJawab(['status' => 'success', 'file' => $filename, 'message' => 'Tanda tangan tersimpan']);
