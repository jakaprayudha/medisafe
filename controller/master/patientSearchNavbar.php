<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode(['data' => []]);
   exit;
}

$search = "%" . $search . "%";

$stmt = $koneksi->prepare("
  SELECT id_patient, patient_name, nomor_rm, patient_nik
  FROM ms_patient
  WHERE id_customer = ?
  AND (
    patient_name LIKE ?
    OR nomor_rm LIKE ?
    OR patient_nik LIKE ?
  )
  LIMIT 15
");

$stmt->bind_param("isss", $id_customer, $search, $search, $search);
$stmt->execute();

$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
   'data' => $result
]);
