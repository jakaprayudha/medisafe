<?php
require '../../database/connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$visit  = $data['visit'] ?? '';
$status = $data['status'] ?? '';

session_start();
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit || !$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

try {

   $stmt = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = ?
      WHERE id_visit = ? 
      AND id_customer = ?
      AND status_permintaan != 3
   ");

   $stmt->bind_param("isi", $status, $visit, $id_customer);
   $stmt->execute();

   echo json_encode([
      'status' => 'success',
      'message' => 'Tiket selain status selesai berhasil diupdate'
   ]);

} catch (Exception $e) {

   echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
   ]);
}