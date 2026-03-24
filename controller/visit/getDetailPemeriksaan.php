<?php
include '../../database/connect.php';

$id = $_GET['id'] ?? '';

$query = "SELECT 
  pasien_visit.*,
  ms_patient.patient_name,
  ms_doctor.doctor_name,
  ms_poli.poli_name
FROM pasien_visit
INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
INNER JOIN ms_poli ON ms_poli.id_poli = pasien_visit.id_poli
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
