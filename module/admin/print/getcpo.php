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

// kolom eksplisit: dulu SELECT * ikut ms_users -> hash password petugas terkirim ke browser
$query = $koneksi->prepare("SELECT pasien_cpo.*, ms_users.fullname, ms_users.roles, ms_users.signature_user
   FROM pasien_cpo
   LEFT JOIN ms_users ON pasien_cpo.petugas = ms_users.id_user
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
