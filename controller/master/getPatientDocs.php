<?php
include "../../database/connect.php";

$patient_number = $_GET['patient_number'] ?? null;

if (!$patient_number) {
   echo json_encode([
      "status" => "error",
      "message" => "Nomor pasien tidak ditemukan."
   ]);
   exit;
}

$stmt = $koneksi->prepare("SELECT patient_ktp_file, patient_kk_file, patient_bpjs_file, patient_foto 
                           FROM ms_patient 
                           WHERE patient_number=? LIMIT 1");
$stmt->bind_param("s", $patient_number);
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
