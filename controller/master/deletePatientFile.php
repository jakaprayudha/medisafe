<?php

include "../../database/connect.php";

header("Content-Type: application/json");

$id_patient = $_POST['id_patient'] ?? '';
$field      = $_POST['field'] ?? '';

if (empty($id_patient) || empty($field)) {
   echo json_encode([
      "status" => "error",
      "message" => "Parameter tidak lengkap"
   ]);
   exit;
}

$dbMap = [
   "ktp"  => "patient_ktp_file",
   "kk"   => "patient_kk_file",
   "bpjs" => "patient_bpjs_file",
   "foto" => "patient_foto"
];

if (!isset($dbMap[$field])) {
   echo json_encode([
      "status" => "error",
      "message" => "Field tidak valid"
   ]);
   exit;
}

$dbField = $dbMap[$field];

// 🔐 prepared + hanya pasien milik faskes sesi
$id_customer = (int) ($_SESSION['id_customer'] ?? 0);
$id_patient = (string) (int) $id_patient;
$q = $koneksi->prepare("SELECT $dbField AS filename FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1");
$q->bind_param("si", $id_patient, $id_customer);
$q->execute();
$data = $q->get_result()->fetch_assoc();
$q->close();

if (!$data) {
   echo json_encode([
      "status" => "error",
      "message" => "Data pasien tidak ditemukan"
   ]);
   exit;
}

$filename = $data['filename'];

if (!empty($filename)) {

   $filepath = "../../uploads/patient/" . basename($filename);

   if (file_exists($filepath)) {
      unlink($filepath);
   }
}

$stmt = $koneksi->prepare(
   "UPDATE ms_patient
     SET $dbField=NULL
     WHERE id_patient=? AND id_customer=?"
);

$stmt->bind_param("si", $id_patient, $id_customer);

if ($stmt->execute()) {

   echo json_encode([
      "status" => "success",
      "message" => "Dokumen berhasil dihapus"
   ]);
} else {

   echo json_encode([
      "status" => "error",
      "message" => $stmt->error
   ]);
}

$stmt->close();
