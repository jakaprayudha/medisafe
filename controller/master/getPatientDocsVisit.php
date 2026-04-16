<?php
include "../../database/connect.php";

header("Content-Type: application/json");

// 🔥 ambil id_patient
$no = $_GET['no'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$no) {
   echo json_encode([
      "status" => "error",
      "message" => "ID pasien tidak ditemukan."
   ]);
   exit;
}

// 🔥 ambil data dokumen
$stmt = $koneksi->prepare("
   SELECT 
      file_spp, 
      file_fkpp, 
      file_informed
   FROM pasien_visit 
   WHERE visit_ID=? AND id_customer=?
   LIMIT 1
");

$stmt->bind_param("si", $no, $id_customer);
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
      "message" => "Data File tidak ditemukan."
   ]);
}

$stmt->close();
