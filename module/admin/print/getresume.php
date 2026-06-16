<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$q = $koneksi->query("
   SELECT *
   FROM resume_medis
   WHERE visit_ID = '$no' AND nomor_rm = '$rm'
   LIMIT 1
");

$data = $q->fetch_assoc() ?: [];

echo json_encode([
   "status" => "success",
   "data" => $data
]);
