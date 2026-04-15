<?php
include "../../database/connect.php";

header("Content-Type: application/json");

// 🔥 ambil id_patient
$id_patient = $_GET['id_patient'] ?? null;

if (!$id_patient) {
   echo json_encode([
      "status" => "error",
      "message" => "ID pasien tidak ditemukan."
   ]);
   exit;
}

// 🔥 ambil data dokumen
$stmt = $koneksi->prepare("
   SELECT 
      patient_ktp_file, 
      patient_kk_file, 
      patient_bpjs_file, 
      patient_foto 
   FROM ms_patient 
   WHERE id_patient=? 
   LIMIT 1
");

$stmt->bind_param("s", $id_patient);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
   echo json_encode([
      "status" => "success",
      "files" => $row
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => "Data pasien tidak ditemukan."
   ]);
}

$stmt->close();
