<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$nomor_visit = $input['nomor_visit'] ?? '';
$nomor_rm = $input['nomor_rm'] ?? '';
$id_patient = $input['id_patient'] ?? '';
$ttd_data = $input['ttd'] ?? '';

if (!$ttd_data) {
   echo json_encode(['status' => 'error', 'message' => 'Tanda tangan kosong']);
   exit;
}

$folder = "../../uploads/ttd/";
if (!file_exists($folder)) mkdir($folder, 0777, true);

$filename = "ttd_" . $nomor_rm . "_" . time() . ".png";
$path = $folder . $filename;

// Hapus prefix data URL dan ubah ke binary
$ttd_data = str_replace('data:image/png;base64,', '', $ttd_data);
$ttd_data = str_replace(' ', '+', $ttd_data);
$decoded = base64_decode($ttd_data);
file_put_contents($path, $decoded);

// Simpan ke database
$stmt = $koneksi->prepare("INSERT INTO pasien_ttd_pernyataan (visit_ID, nomor_rm, id_patient, file_ttd, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssis", $nomor_visit, $nomor_rm, $id_patient, $filename);
$stmt->execute();

echo json_encode(['status' => 'success', 'file' => $filename]);
