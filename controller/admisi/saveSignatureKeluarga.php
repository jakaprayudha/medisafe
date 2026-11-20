<?php
/**
 * Tanda tangan "yang membuat pernyataan" (pasien / keluarga) di form persetujuan tindakan (module/admin/form_persetujuan).
 * POST JSON {id_visit, image:"data:image/png;base64,...", nama, umur, jk, hubungan}
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

if ($id_visit <= 0 || $image === '' || $nama === '' || $umur === '' || $jk === '') {
   skJawab(['status' => 'error', 'message' => 'Nama, umur, jenis kelamin, dan tanda tangan wajib diisi'], 422);
}
if (!preg_match('/^\d{1,3}$/', $umur) || (int) $umur > 130) skJawab(['status' => 'error', 'message' => 'Umur tidak valid'], 422);

$kunj = visitGuard($koneksi, 'id_visit', $id_visit);
session_write_close();

$data = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/png;base64,#i', '', $image)), true);
$info = $data ? @getimagesizefromstring($data) : false;
if (!$data || strlen($data) > 2 * 1024 * 1024 || !$info || $info[2] !== IMAGETYPE_PNG) {
   skJawab(['status' => 'error', 'message' => 'Gambar tanda tangan tidak valid'], 422);
}

$folder = __DIR__ . '/../../uploads/ttd/';
if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) skJawab(['status' => 'error', 'message' => 'Folder tanda tangan tidak tersedia'], 500);
$filename = 'ttd_kel_' . $id_visit . '_' . bin2hex(random_bytes(5)) . '.png';
if (@file_put_contents($folder . $filename, $data, LOCK_EX) === false) skJawab(['status' => 'error', 'message' => 'Gagal menyimpan file tanda tangan'], 500);

$nama = mb_substr($nama, 0, 150);
$jk = mb_substr($jk, 0, 20);
$hubungan = mb_substr($hubungan, 0, 50);
$idc = (int) $kunj['id_customer'];
$st = $koneksi->prepare("UPDATE pasien_visit SET signature_opname = ?, opname_keluarga_name = ?, opname_umur = ?, opname_gender = ?, opname_hubungan_pasien = ?
   WHERE id_visit = ? AND id_customer = ?");
$st->bind_param('sssssii', $filename, $nama, $umur, $jk, $hubungan, $id_visit, $idc);
if (!$st->execute()) {
   @unlink($folder . $filename);
   error_log('[saveSignatureKeluarga] ' . $st->error);
   skJawab(['status' => 'error', 'message' => 'Gagal menyimpan tanda tangan'], 500);
}
$st->close();

skJawab(['status' => 'success', 'file' => $filename]);
