<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$id_room = $_POST['id_room'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_room) {
   echo json_encode([
      'status' => 'error',
      'message' => 'id_room tidak ditemukan'
   ]);
   exit;
}

$stmt = $koneksi->prepare("
   UPDATE ms_room_bed 
   SET bed_status = 1 
   WHERE id_room = ? AND id_customer = ?
");

$stmt->bind_param("ii", $id_room, $id_customer);

if ($stmt->execute()) {
   echo json_encode([
      'status' => 'success',
      'message' => 'Semua tempat tidur berhasil dikosongkan'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal reset bed',
      'error' => $stmt->error
   ]);
}
