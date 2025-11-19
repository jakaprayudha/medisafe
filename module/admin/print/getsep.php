<?php
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$q = $koneksi->query("SELECT * FROM pasien_sep pv 
   WHERE nomor_rm = '$rm' AND visit_ID = '$visit' LIMIT 1");

$data = [];
while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
