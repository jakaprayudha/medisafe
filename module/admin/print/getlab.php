<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

$q = $koneksi->query("
    SELECT * FROM lab_results
    WHERE visit_id='$no' AND nomor_rm='$rm'
    LIMIT 1
");

if ($q->num_rows == 0) {
   echo json_encode(["status" => "empty"]);
   exit;
}

$data = $q->fetch_assoc();

echo json_encode([
   "status" => "success",
   "data" => $data
]);
