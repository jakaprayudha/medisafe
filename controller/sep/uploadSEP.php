<?php
include '../../database/connect.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header("Content-Type: application/json");

$username = $_SESSION['fullname'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   echo json_encode(["status" => "error", "message" => "Invalid request"]);
   exit;
}

$no_visit = $_POST['no_visit'] ?? null;

if (!$no_visit || !$id_customer) {
   echo json_encode(["status" => "error", "message" => "Data tidak lengkap / session"]);
   exit;
}

if (!isset($_FILES['sep_file']) || $_FILES['sep_file']['error'] != UPLOAD_ERR_OK) {
   echo json_encode(["status" => "error", "message" => "File tidak valid"]);
   exit;
}

$file = $_FILES['sep_file'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSize = 5 * 1024 * 1024;
$uploadDir = "../../uploads/sep/";

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
   echo json_encode(["status" => "error", "message" => "Format file tidak valid"]);
   exit;
}

if ($file['size'] > $maxSize) {
   echo json_encode(["status" => "error", "message" => "Maksimal 5MB"]);
   exit;
}

if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0777, true);
}

// 🔥 nama file baru
$newFile = uniqid("sep_", true) . "." . $ext;
$targetPath = $uploadDir . $newFile;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {

   // 🔥 UPDATE ke pasien_visit (BUKAN pasien_sep lagi)
   $stmt = $koneksi->prepare("
      UPDATE pasien_visit 
      SET sep_file = ?
      WHERE visit_ID = ? AND id_customer = ?
   ");

   $stmt->bind_param("ssi", $newFile,  $no_visit, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         "status" => "success",
         "message" => "File SEP berhasil diupload",
         "file" => $newFile
      ]);
   } else {
      echo json_encode([
         "status" => "error",
         "message" => $stmt->error
      ]);
   }

   $stmt->close();
   exit;
}

echo json_encode(["status" => "error", "message" => "Gagal upload"]);
