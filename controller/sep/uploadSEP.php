<?php
session_start();
$username = $_SESSION['fullname'];
include '../../database/connect.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   echo json_encode(["status" => "error", "message" => "Invalid request"]);
   exit;
}

$id_patient = $_POST['id_patient'] ?? null;
$no_visit = $_POST['no_visit'] ?? null;

if (!$id_patient || !$no_visit) {
   echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
   exit;
}

if (!isset($_FILES['sep_file']) || $_FILES['sep_file']['error'] != UPLOAD_ERR_OK) {
   echo json_encode(["status" => "error", "message" => "File tidak valid"]);
   exit;
}

$file = $_FILES['sep_file'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSize = 5 * 1024 * 1024; // 5MB
$uploadDir = "../../uploads/sep/";

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
   echo json_encode(["status" => "error", "message" => "Hanya JPG, PNG, PDF yang diperbolehkan"]);
   exit;
}

if ($file['size'] > $maxSize) {
   echo json_encode(["status" => "error", "message" => "Ukuran maksimal 5MB"]);
   exit;
}

// Generate nama file baru
$newFile = uniqid("sep_", true) . "." . $ext;
$targetPath = $uploadDir . $newFile;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {

   // Simpan di database (insert/update)

   mysqli_query($koneksi, "INSERT INTO pasien_sep (nomor_rm, visit_ID, sep_file, user)
      VALUES ('$id_patient', '$no_visit', '$newFile','$username')
      ON DUPLICATE KEY UPDATE sep_file = '$newFile'
   ");

   echo json_encode([
      "status" => "success",
      "message" => "File SEP berhasil diupload",
      "file" => $newFile
   ]);
   exit;
}

echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
