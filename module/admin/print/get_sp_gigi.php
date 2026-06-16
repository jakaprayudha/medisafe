<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

$q = $koneksi->query("
   SELECT *
   FROM sp_gigi
   WHERE visit_ID = '$no' AND nomor_rm = '$rm'
   LIMIT 1
");

if ($q->num_rows == 0) {
   echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   exit;
}

echo json_encode([
   "status" => "success",
   "data" => $q->fetch_assoc()
]);
