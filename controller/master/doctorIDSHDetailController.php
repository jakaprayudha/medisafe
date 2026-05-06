<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$id_customer = $_GET['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'id_customer tidak ada'
   ]);
   exit;
}

$query = "SELECT 
            doctor_name,
            doctor_nik,
            idsh
          FROM ms_doctor
          WHERE id_customer = ? 
          AND doctor_status = 1
          ORDER BY doctor_name ASC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_customer);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
