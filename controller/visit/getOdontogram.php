<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$visit_ID = $_GET['visit_ID'] ?? '';

if ($visit_ID === '') {

   echo json_encode([
      "status" => "error",
      "message" => "visit_ID tidak ditemukan",
      "data" => []
   ]);

   exit;
}

$query = $koneksi->prepare(
   "SELECT *
     FROM odontogram
     WHERE visit_ID = ?"
);

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
