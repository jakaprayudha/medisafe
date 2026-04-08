<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$id_doctor = $_GET['id_doctor'];
$check = mysqli_query($koneksi, "SELECT * FROM ms_doctor WHERE id_doctor = '$id_doctor'");
$datacheeck = mysqli_fetch_assoc($check);
$doctor = $datacheeck['doctor_name'];
$id_customer = $_SESSION['id_customer'];

// ambil range 3 hari
$start = $_GET['start'];
$end   = $_GET['end'];

$query = "SELECT 
  v.visit_date,
  v.visit_time,
  v.visit_antrian,
  p.patient_name,
  v.status_icare,
  v.status_satusehat,
  v.visit_status
FROM pasien_visit v
JOIN ms_patient p ON p.id_patient = v.id_patient
WHERE v.id_doctor = ?
AND v.id_customer = ?
AND v.visit_date BETWEEN ? AND ?
ORDER BY v.visit_time ASC
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("iiss", $doctor, $id_customer, $start, $end);
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
