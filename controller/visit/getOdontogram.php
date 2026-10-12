<?php

require_once __DIR__ . '/../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$visit_ID = $_GET['visit_ID'] ?? '';
$id_customer = filter_var($_SESSION['id_customer'] ?? null, FILTER_VALIDATE_INT);

if (!$id_customer) {
   http_response_code(401);
   echo json_encode(["status" => "error", "message" => "Session klinik tidak ditemukan", "data" => []]);
   exit;
}

if (!is_string($visit_ID) || trim($visit_ID) === '') {

   http_response_code(400);
   echo json_encode([
      "status" => "error",
      "message" => "visit_ID tidak ditemukan",
      "data" => []
   ]);

   exit;
}

try {
   $query = $koneksi->prepare("
      SELECT * FROM odontogram
      WHERE visit_ID = ? AND id_customer = ?
   ");

   $query->bind_param("si", $visit_ID, $id_customer);

   $query->execute();

   $result = $query->get_result();

   $data = [];

   while ($row = $result->fetch_assoc()) {
      $data[] = $row;
   }

   echo json_encode(["status" => "success", "data" => $data]);
} catch (mysqli_sql_exception $error) {
   error_log('Load odontogram: ' . $error->getMessage());
   http_response_code(500);
   echo json_encode(["status" => "error", "message" => "Gagal mengambil data odontogram", "data" => []]);
}
