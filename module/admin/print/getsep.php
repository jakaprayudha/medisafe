<?php
require '../../../database/connect.php';

$visit = $_GET['visit'] ?? '';
$rm    = $_GET['rm'] ?? '';

$q = $koneksi->query("SELECT * FROM pasien_visit pv INNER JOIN ms_patient mp ON pv.id_patient = mp.id_patient
   WHERE pv.visit_ID = '$visit' AND mp.nomor_rm = '$rm'
");

$data = [];
while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
