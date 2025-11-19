<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$id = $_GET['rm'] ?? 0;
$no = $_GET['no'] ?? 0;

// Ambil semua dokumen berdasarkan id_patient
$q = mysqli_query($koneksi, "SELECT * FROM pasien_dokumen pd INNER JOIN pasien_visit pv ON pd.visit_ID = pv.visit_ID INNER JOIN ms_patient mp ON pd.nomor_rm = mp.nomor_rm
   WHERE mp.nomor_rm = '$id' AND pv.visit_ID='$no'");

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
