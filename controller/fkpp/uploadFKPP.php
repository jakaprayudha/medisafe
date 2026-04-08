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
   echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
   exit;
}

// 🔥 FIX DISINI
if (!isset($_FILES['fkpp_file']) || $_FILES['fkpp_file']['error'] != UPLOAD_ERR_OK) {
   echo json_encode(["status" => "error", "message" => "File tidak valid"]);
   exit;
}

$file = $_FILES['fkpp_file'];

$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSize = 5 * 1024 * 1024;
$uploadDir = "../../uploads/fkpp/";

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

$newFile = uniqid("fkpp_", true) . "." . $ext;
$targetPath = $uploadDir . $newFile;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {

   $stmt = $koneksi->prepare("
      UPDATE pasien_visit 
      SET fkpp_file = ?
      WHERE visit_ID = ? AND id_customer = ?
   ");

   if (!$stmt) {
      echo json_encode(["status" => "error", "message" => $koneksi->error]);
      exit;
   }

   $stmt->bind_param("ssi", $newFile, $no_visit, $id_customer);
   $stmt->execute();

   if ($stmt->affected_rows === 0) {
      echo json_encode([
         "status" => "error",
         "message" => "Data tidak ditemukan"
      ]);
      exit;
   }

   echo json_encode([
      "status" => "success",
      "message" => "File FKPP berhasil diupload",
      "file" => $newFile
   ]);

   $stmt->close();
   exit;
}

echo json_encode(["status" => "error", "message" => "Gagal upload"]);
