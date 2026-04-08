<?php
include '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id || !$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak valid'
   ]);
   exit;
}

// 🔥 UPDATE STATUS PERMINTAAN JADI 1
$stmt = $koneksi->prepare("
    UPDATE permintaan_pharmacy 
    SET status_permintaan = 1
    WHERE id_permintaan_farmasi = ?
    AND id_customer = ?
");

$stmt->bind_param("ii", $id, $id_customer);

if ($stmt->execute()) {
   echo json_encode([
      'status' => 'success',
      'message' => 'Berhasil kirim ke farmasi'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => $stmt->error
   ]);
}

$stmt->close();
