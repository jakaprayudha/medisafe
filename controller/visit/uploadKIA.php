<?php
include "../../database/connect.php";

header("Content-Type: application/json");

$rm    = $_POST['rm'] ?? '';
$visit = $_POST['visit'] ?? '';

if (!isset($_FILES['file_foto'])) {
   echo json_encode(["status" => "error", "message" => "File tidak ditemukan"]);
   exit;
}

$file = $_FILES['file_foto'];

$allowed = ['jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
   echo json_encode(["status" => "error", "message" => "Format harus JPG/PNG"]);
   exit;
}

// Folder upload
$folder = "../../uploads/bukukia/";
if (!is_dir($folder)) mkdir($folder, 0777, true);

// Nama file
$newName = "bukuKIA_" . $rm . "_" . time() . "." . $ext;
$path = $folder . $newName;

if (move_uploaded_file($file['tmp_name'], $path)) {

   // Path untuk disimpan di DB
   $url = "uploads/bukukia/" . $newName;

   // INSERT KE TABEL
   $insert = $koneksi->query("
        INSERT INTO buku_kia (nomor_rm, nomor_visit, file_path, created_at)
        VALUES ('$rm', '$visit', '$url', NOW())
    ");

   if ($insert) {
      echo json_encode([
         "status" => "success",
         "file_url" => "../../" . $url
      ]);
   } else {
      echo json_encode([
         "status" => "error",
         "message" => "Gagal insert ke database"
      ]);
   }
} else {
   echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
}
