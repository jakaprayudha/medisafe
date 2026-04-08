<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$status) {
   echo json_encode([
      "status" => "error",
      "message" => "ID / Status tidak valid"
   ]);
   exit;
}

// validasi hanya boleh 2 atau 3
if (!in_array($status, [2, 3])) {
   echo json_encode([
      "status" => "error",
      "message" => "Status tidak diperbolehkan"
   ]);
   exit;
}

$query = "UPDATE permintaan_pharmacy 
          SET status_permintaan = '$status' 
          WHERE id_permintaan_farmasi = '$id'";

$result = mysqli_query($koneksi, $query);

echo json_encode([
   "status" => $result ? "success" : "error",
   "message" => $result ? "Berhasil update status" : mysqli_error($koneksi)
]);
