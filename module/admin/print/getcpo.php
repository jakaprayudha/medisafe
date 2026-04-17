<?php
require '../../../database/connect.php';
header("Content-Type: application/json");
session_start();
$visit = $_GET['visit_ID'] ?? $_GET['no'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit) {
   echo json_encode([
      "status" => "error",
      "message" => "visit kosong"
   ]);
   exit;
}

$query = $koneksi->prepare("SELECT * FROM pasien_cpo 
   LEFT JOIN pasien_visit ON pasien_cpo.visit_ID=pasien_visit.visit_ID
   LEFT JOIN ms_users ON pasien_cpo.petugas=ms_users.id_user
   WHERE pasien_cpo.visit_ID=? AND pasien_cpo.id_customer=? 
   ORDER BY pasien_cpo.tanggal ASC
");



$query->bind_param("si", $visit, $id_customer);
$query->execute();
$result = $query->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
