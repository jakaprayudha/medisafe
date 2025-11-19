<?php
require '../../../database/connect.php';

$visit = $_GET['visit'] ?? '';
$rm    = $_GET['rm'] ?? '';

$q = $koneksi->query("SELECT *
   FROM visit_cppt
   WHERE visit_ID = '$visit'
   ORDER BY cppt_date ASC, cppt_time ASC
");

$data = [];
while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
