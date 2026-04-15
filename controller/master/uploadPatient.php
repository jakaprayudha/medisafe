<?php
include "../../database/connect.php";

header("Content-Type: application/json");

// 🔥 folder upload
$uploadDir = "../../uploads/patient/";
if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0777, true);
}

$response = ["status" => "error", "message" => ""];

// 🔥 mapping field
$allowedFiles = [
   "ktp"  => "ktp",
   "kk"   => "kk",
   "bpjs" => "bpjs",
   "foto" => "foto"
];

// 🔥 ambil patient_number
$id_patient = $_POST['id_patient']
   ?? ($_GET['id_patient'] ?? null);

if (!$id_patient) {
   echo json_encode([
      "status" => "error",
      "message" => "ID pasien tidak ditemukan."
   ]);
   exit;
}
// 🔥 validasi file
$allowedExt = ['jpg', 'jpeg', 'png'];
$maxSize = 2 * 1024 * 1024; // 2MB

$uploadedFiles = [];

foreach ($allowedFiles as $field => $prefix) {

   if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {

      $fileTmp  = $_FILES[$field]['tmp_name'];
      $fileName = $_FILES[$field]['name'];
      $fileSize = $_FILES[$field]['size'];

      $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

      // 🔥 validasi extension
      if (!in_array($ext, $allowedExt)) {
         echo json_encode([
            "status" => "error",
            "message" => "Format file $field tidak diizinkan"
         ]);
         exit;
      }

      // 🔥 validasi size
      if ($fileSize > $maxSize) {
         echo json_encode([
            "status" => "error",
            "message" => "Ukuran file $field terlalu besar (max 2MB)"
         ]);
         exit;
      }

      // 🔥 nama unik (anti overwrite)
      $filename = $prefix . "_" . $id_patient . "_" . time() . "." . $ext;
      $destination = $uploadDir . $filename;

      if (move_uploaded_file($fileTmp, $destination)) {
         $uploadedFiles[$field] = $filename;
      }
   }
}

// 🔥 kalau tidak ada file
if (empty($uploadedFiles)) {
   echo json_encode([
      "status" => "error",
      "message" => "Tidak ada file yang diupload"
   ]);
   exit;
}

// 🔥 dynamic update (INI YANG PALING PENTING)
$fields = [];
$params = [];
$types  = "";

// mapping DB field
$dbMap = [
   "ktp"  => "patient_ktp_file",
   "kk"   => "patient_kk_file",
   "bpjs" => "patient_bpjs_file",
   "foto" => "patient_foto"
];

foreach ($uploadedFiles as $key => $file) {
   $fields[] = $dbMap[$key] . "=?";
   $params[] = $file;
   $types   .= "s";
}

// tambah where
$params[] = $id_patient;
$types   .= "s";

$sql = "UPDATE ms_patient SET " . implode(",", $fields) . " WHERE id_patient=?";
$stmt = $koneksi->prepare($sql);

if (!$stmt) {
   echo json_encode([
      "status" => "error",
      "message" => "Prepare gagal: " . $koneksi->error
   ]);
   exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
   echo json_encode([
      "status" => "success",
      "message" => "Upload berhasil",
      "files" => $uploadedFiles
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => "Gagal update DB: " . $stmt->error
   ]);
}

$stmt->close();
