<?php
require_once "../../database/connect.php";
header('Content-Type: application/json');

$id_patient = $_GET['id_patient'] ?? null;
$visit_ID_inpatient = $_GET['visit_ID_inpatient'] ?? null;

if (!$id_patient || !$visit_ID_inpatient) {
   echo json_encode([
      "status" => "error",
      "message" => "Parameter id_patient dan visit_ID_inpatient wajib diisi."
   ]);
   exit;
}

$query = $koneksi->query("
    SELECT pr.*, d.doctor_name 
    FROM permintaan_ranap pr
    LEFT JOIN ms_doctor d ON pr.id_doctor = d.id_doctor
    WHERE pr.id_patient = '$id_patient'
      AND pr.visit_ID_inpatient = '$visit_ID_inpatient'
    ORDER BY pr.id_ranap DESC
    LIMIT 1
");

if ($data = $query->fetch_assoc()) {
   echo json_encode([
      "status" => "success",
      "data" => $data
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => "Belum ada data rawat inap untuk kunjungan ini."
   ]);
}
