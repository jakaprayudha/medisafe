<?php
include '../../database/connect.php';

$id = $_GET['id'] ?? '';

if (!$id) {
   echo json_encode(['status' => 'error']);
   exit;
}

$stmt = $koneksi->prepare("
  SELECT * FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
