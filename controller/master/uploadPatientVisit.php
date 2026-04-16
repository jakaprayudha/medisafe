<?php
include "../../database/connect.php";

header("Content-Type: application/json");

// 🔥 folder upload
$uploadDir = "../../uploads/dokumen/";
if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0777, true);
}

$response = ["status" => "error", "message" => ""];

// 🔥 mapping field
$allowedFiles = [
   "sep"  => "sep",
   "fkpp"   => "fkpp",
   "informed" => "informed"
];

// 🔥 ambil patient_number
$no = $_POST['no']
   ?? ($_GET['no'] ?? null);

if (!$no) {
   echo json_encode([
      "status" => "error",
      "message" => "ID pasien tidak ditemukan."
   ]);
   exit;
}
// 🔥 validasi file
$allowedExt = ['pdf'];
$maxSize = 2 * 1024 * 1024; // 2MB

$uploadedFiles = [];

foreach ($allowedFiles as $field => $prefix) {

   if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {

      $fileTmp  = $_FILES[$field]['tmp_name'];
      $mime = mime_content_type($fileTmp);
      if ($mime !== 'application/pdf') {
         echo json_encode([
            "status" => "error",
            "message" => "File harus PDF"
         ]);
         exit;
      }
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
      $filename = $prefix . "_" . $no . "_" . time() . "." . $ext;
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
   "sep"  => "file_spp",
   "fkpp"   => "file_fkpp",
   "informed" => "file_informed"
];

foreach ($uploadedFiles as $key => $file) {
   $fields[] = $dbMap[$key] . "=?";
   $params[] = $file;
   $types   .= "s";
}

// tambah where
$params[] = $no;
$types   .= "s";

$sql = "UPDATE pasien_visit SET " . implode(",", $fields) . " WHERE visit_ID=?";
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
