<?php
include '../../database/connect.php';

$id = $_GET['id'] ?? '';

$query = "SELECT 
  pasien_visit.*,
  ms_patient.patient_name
FROM pasien_visit
LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
WHERE pasien_visit.id_visit = ?
LIMIT 1";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
  'status' => 'success',
  'data' => $data
]);
