<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$id_doctor = $_GET['id_doctor'] ?? '';

if (!$id_doctor) {
   echo json_encode(['status' => 'error', 'message' => 'id_doctor kosong']);
   exit;
}

$stmt = $koneksi->prepare(
   "SELECT * FROM ms_doctor_schedule 
    WHERE id_doctor=? AND sch_status='1'"
);

$stmt->bind_param("s", $id_doctor);
$stmt->execute();

$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
