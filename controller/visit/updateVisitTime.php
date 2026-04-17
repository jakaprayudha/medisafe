<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id_patient = $data['id_patient'] ?? null;
$visit_date = $data['visit_date'] ?? null;
$visit_time = $data['visit_time'] ?? null;

if (!$id_patient || !$visit_time) {
   echo json_encode([
      "status" => "error",
      "message" => "Data tidak lengkap"
   ]);
   exit;
}

$query = $koneksi->prepare("
   UPDATE pasien_visit 
   SET visit_date=?, visit_time=? 
   WHERE id_patient=?
");

$query->bind_param("ssi", $visit_date, $visit_time, $id_patient);

if ($query->execute()) {
   echo json_encode([
      "status" => "success",
      "message" => "Berhasil update visit"
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => "Gagal update"
   ]);
}
