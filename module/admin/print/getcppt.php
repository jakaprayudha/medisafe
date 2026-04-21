<?php
require '../../../database/connect.php';

$visit = $_GET['visit'] ?? '';
$rm    = $_GET['rm'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? '';

$q = $koneksi->query("SELECT *
   FROM visit_cppt LEFT JOIN ms_users ON visit_cppt.users_entry = ms_users.id_user
   WHERE visit_cppt.visit_ID = '$visit' AND visit_cppt.id_customer = '$id_customer'
   ORDER BY visit_cppt.cppt_date ASC, visit_cppt.cppt_time ASC
");

$data = [];
while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
