<?php
require '../../../database/connect.php';

$no   = $_GET['no'] ?? null;
$rm   = $_GET['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter invalid"]);
   exit;
}

$sql = mysqli_query($koneksi, "SELECT * FROM ekg_results WHERE visit_id='$no' AND nomor_rm='$rm' LIMIT 1");

if (mysqli_num_rows($sql) == 0) {
   echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   exit;
}

$data = mysqli_fetch_assoc($sql);

echo json_encode([
   "status" => "success",
   "data" => $data
]);
