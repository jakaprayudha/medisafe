<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$id_customer = $_SESSION['id_customer'];

// 🔥 LIMIT penting biar cepat
$query = "SELECT id_patient, patient_name, nomor_rm 
  FROM ms_patient
  WHERE id_customer = ?
  AND patient_name LIKE ?
  LIMIT 20
";

$stmt = $koneksi->prepare($query);

$like = "%$search%";
$stmt->bind_param("is", $id_customer, $like);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
