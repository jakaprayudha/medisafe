<?php
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;

$q = $koneksi->query("SELECT file_fkpp FROM pasien_visit pv 
   WHERE id_customer = '$id_customer' AND visit_ID = '$visit' LIMIT 1");

$data = [];
while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
