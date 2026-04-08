<?php
require '../../database/connect.php';
session_start();

header("Content-Type: application/json");

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = $_GET['id_customer'] ?? null;
$no = $_GET['no'] ?? null;

if (!$id_customer || !$no) {
   echo json_encode(["status" => "error"]);
   exit;
}

$stmt = $koneksi->prepare("SELECT 
      v.visit_ID,
      v.visit_status,
      p.patient_name,
      p.nomor_rm,
      p.patient_gender,
      pr.provider_name,
      v.id_doctor,
      CONCAT(
        FLOOR(DATEDIFF(CURDATE(), p.patient_datebirth)/365),
        ' Th'
      ) as usia
   FROM pasien_visit v
   LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN ms_provider pr ON pr.id_provider = v.id_provider

   WHERE v.visit_ID=? AND v.id_customer=?
");

$stmt->bind_param("ii", $no, $id_customer);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
   "status" => "success",
   "data" => $data
]);
