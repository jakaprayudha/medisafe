
<?php
include '../../database/connect.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([]);
   exit;
}

$stmt = $koneksi->prepare("SELECT id_doctor, doctor_name FROM ms_doctor WHERE id_customer = ? ORDER BY doctor_name ASC");
$stmt->bind_param("i", $id_customer);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

$stmt->close();

echo json_encode($data);
