<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
   echo json_encode([
      "status" => "error",
      "message" => "ID tidak valid"
   ]);
   exit;
}

// update status (misal jadi 1 = diproses)
$query = "UPDATE permintaan_pharmacy 
          SET status_permintaan = '1' 
          WHERE id_permintaan_farmasi = '$id'";

$result = mysqli_query($koneksi, $query);

if ($result) {
   echo json_encode([
      "status" => "success"
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);
}
