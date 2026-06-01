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

$q = mysqli_query(
   $koneksi,
   "SELECT $dbField AS filename
     FROM ms_patient
     WHERE id_patient='$id_patient'
     LIMIT 1"
);

$data = mysqli_fetch_assoc($q);

if (!$data) {
   echo json_encode([
      "status" => "error",
      "message" => "Data pasien tidak ditemukan"
   ]);
   exit;
}

$filename = $data['filename'];

if (!empty($filename)) {

   $filepath = "../../uploads/patient/" . $filename;

   if (file_exists($filepath)) {
      unlink($filepath);
   }
}

$stmt = $koneksi->prepare(
   "UPDATE ms_patient
     SET $dbField=NULL
     WHERE id_patient=?"
);

$stmt->bind_param("s", $id_patient);

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
