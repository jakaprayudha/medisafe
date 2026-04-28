<?php
include '../../database/connect.php';

$visit_ID = $_GET['visit_ID'];

$query = $koneksi->prepare("SELECT * FROM odontogram WHERE visit_ID = ?");
$query->bind_param("s", $visit_ID);
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
