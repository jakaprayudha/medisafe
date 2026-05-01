<?php
include '../../database/connect.php';

header("Content-Type: application/json");

$id = $_POST['id_doctor'] ?? null;
$nik = $_POST['doctor_nik'] ?? null;

if (!$id || !$nik) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

// validasi panjang nik
if (strlen($nik) != 16) {
   echo json_encode([
      'status' => 'error',
      'message' => 'NIK harus 16 digit'
   ]);
   exit;
}

$stmt = $koneksi->prepare("
   UPDATE ms_doctor 
   SET doctor_nik = ?, updated_at = NOW()
   WHERE id_doctor = ?
");

$stmt->bind_param("si", $nik, $id);

if ($stmt->execute()) {
   echo json_encode(['status' => 'success']);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => $stmt->error
   ]);
}

$stmt->close();
