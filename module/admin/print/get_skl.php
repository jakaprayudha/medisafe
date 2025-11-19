<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? null;
$rm = $_GET['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter kurang"]);
   exit;
}

$q = $koneksi->query("SELECT * FROM skl_kelahiran 
                      WHERE visit_id='$no' AND nomor_rm='$rm' LIMIT 1");

if ($q->num_rows == 0) {
   echo json_encode(["status" => "not_found"]);
   exit;
}

echo json_encode([
   "status" => "success",
   "data" => $q->fetch_assoc()
]);
