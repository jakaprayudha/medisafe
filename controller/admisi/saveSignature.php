<?php
/**
 * Tanda tangan pasien/keluarga per kunjungan (registrasi poliklinik, form pernyataan).
 * POST JSON {id_visit, image:"data:image/png;base64,..."} -> uploads/ttd/{file}, pasien_visit.signature_path
 *
 * Dulu: id_visit disisipkan langsung ke SQL (SQL injection: "1 OR 1=1" menimpa tanda tangan semua kunjungan),
 * tanpa cek faskes, isi file tidak divalidasi, nama file time() bisa bentrok antar petugas.
 */
header('Content-Type: application/json; charset=utf-8');
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_visit = (int) ($input['id_visit'] ?? 0);
$image = (string) ($input['image'] ?? '');

if ($id_visit <= 0 || $image === '') {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
   exit;
}
$kunj = visitGuard($koneksi, 'id_visit', $id_visit);
session_write_close();

$data = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/png;base64,#i', '', $image)), true);
$info = $data ? @getimagesizefromstring($data) : false;
if (!$data || strlen($data) > 2 * 1024 * 1024 || !$info || $info[2] !== IMAGETYPE_PNG) {
   http_response_code(422);
   echo json_encode(['status' => 'error', 'message' => 'Gambar tanda tangan tidak valid']);
   exit;
}

$folder = __DIR__ . '/../../uploads/ttd/';
if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) {
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Folder tanda tangan tidak tersedia']);
   exit;
}

$filename = 'ttd_' . $id_visit . '_' . bin2hex(random_bytes(5)) . '.png';
if (@file_put_contents($folder . $filename, $data, LOCK_EX) === false) {
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file tanda tangan']);
   exit;
}

$idc = (int) $kunj['id_customer'];
$st = $koneksi->prepare("UPDATE pasien_visit SET signature_path = ? WHERE id_visit = ? AND id_customer = ?");
$st->bind_param('sii', $filename, $id_visit, $idc);
if (!$st->execute()) {
   @unlink($folder . $filename);
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan tanda tangan']);
   exit;
}
$st->close();

echo json_encode([
   'status' => 'success',
   'file' => $filename
]);
