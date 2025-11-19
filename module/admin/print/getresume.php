<?php
require '../../../database/connect.php';

$visit = $_GET['visit'] ?? '';
$rm    = $_GET['rm'] ?? '';

$q = $koneksi->query("
   SELECT *
   FROM resume_medis
   WHERE visit_ID = '$visit' AND nomor_rm = '$rm'
   LIMIT 1
");

$data = $q->fetch_assoc() ?: [];

echo json_encode([
   "status" => "success",
   "data" => $data
]);
