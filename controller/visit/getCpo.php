<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$visit = $_GET['visit_ID'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit) {
   echo json_encode([
      "status" => "error",
      "message" => "visit kosong"
   ]);
   exit;
}

$query = $koneksi->prepare("
   SELECT * FROM pasien_cpo 
   WHERE visit_ID=? AND id_customer=? 
   ORDER BY tanggal ASC
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
