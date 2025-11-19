<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$id = $_GET['rm'] ?? 0;

// Ambil semua dokumen berdasarkan id_patient
$q = mysqli_query($koneksi, "
   SELECT * FROM dokumen_kependudukan 
   WHERE id_patient = '$id'
");

if (!$q) {
   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);
   exit;
}

// Ambil semua baris (BUKAN 1 baris)
$data = mysqli_fetch_all($q, MYSQLI_ASSOC);

echo json_encode([
   "status" => "success",
   "count" => count($data),
   "data" => $data
]);
