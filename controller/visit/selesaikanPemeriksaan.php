<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$visit_ID = $input['visit_ID'] ?? null;

if (!$visit_ID) {
   echo json_encode([
      'status' => 'error',
      'message' => 'visit_ID tidak ditemukan'
   ]);
   exit;
}

// update status jadi 4
$stmt = $koneksi->prepare("
  UPDATE pasien_visit 
  SET visit_status = 4 
  WHERE visit_ID = ?
");

$stmt->bind_param("s", $visit_ID);

if ($stmt->execute()) {
   echo json_encode([
      'status' => 'success',
      'message' => 'Pemeriksaan selesai'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal update status',
      'error' => $stmt->error
   ]);
}
