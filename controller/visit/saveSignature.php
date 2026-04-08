<?php
require '../../database/connect.php';

// 🔐 SESSION SAFE
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);

$nomor_visit = $input['nomor_visit'] ?? '';
$nomor_rm = $input['nomor_rm'] ?? '';
$ttd_data = $input['ttd'] ?? '';

if (!$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}

if (!$nomor_visit || !$ttd_data) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
   exit;
}

// ===============================
// SIMPAN FILE TTD
// ===============================
$folder = "../../uploads/ttd/";

if (!file_exists($folder)) {
   mkdir($folder, 0777, true);
}

// 🔥 generate nama file
$filename = "ttd_" . time() . "_" . rand(1000, 9999) . ".png";
$path = $folder . $filename;

// 🔥 decode base64
$ttd_data = str_replace('data:image/png;base64,', '', $ttd_data);
$ttd_data = str_replace(' ', '+', $ttd_data);
$decoded = base64_decode($ttd_data);

file_put_contents($path, $decoded);

// ===============================
// UPDATE KE pasien_visit
// ===============================
$stmt = $koneksi->prepare("
   UPDATE pasien_visit 
   SET ttd_pernyataan = ?
   WHERE visit_ID = ? AND id_customer = ?
");

$stmt->bind_param("ssi", $filename, $nomor_visit, $id_customer);

if ($stmt->execute()) {

   echo json_encode([
      'status' => 'success',
      'message' => 'TTD berhasil disimpan',
      'file' => $filename
   ]);
} else {

   echo json_encode([
      'status' => 'error',
      'message' => $stmt->error
   ]);
}

$stmt->close();
