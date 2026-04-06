<?php
require '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'];

if (!$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ada']);
   exit;
}

if (!isset($_FILES['logo'])) {
   echo json_encode(['status' => 'error', 'message' => 'File tidak ada']);
   exit;
}

$file = $_FILES['logo'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'logo_' . time() . '.' . $ext;

$uploadPath = '../../uploads/' . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

   // 🔹 update ke setting_clinic
   mysqli_query($koneksi, "
      UPDATE setting_clinic 
      SET image_clinic = '$filename'
      WHERE id_customer = '$id_customer'
   ");

   echo json_encode([
      'status' => 'success',
      'file' => $filename
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Upload gagal'
   ]);
}
