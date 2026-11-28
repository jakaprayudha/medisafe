<?php
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
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
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
// ekstensi dulu tidak dicek dan nama file ditempel langsung ke SQL (SQL injection lewat nama file / upload .php)
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
   echo json_encode(['status' => 'error', 'message' => 'Hanya file JPG, PNG, GIF, WEBP yang diperbolehkan.']);
   exit;
}
$filename = 'logo_' . time() . '.' . $ext;

$uploadPath = '../../uploads/' . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

   // 🔹 update ke setting_clinic
   $lgLama = logAmbilBaris($koneksi, 'setting_clinic', 'id_customer', $id_customer);
   $st = $koneksi->prepare("UPDATE setting_clinic SET image_clinic = ? WHERE id_customer = ?");
   $idcLogo = (int) $id_customer;
   $st->bind_param('si', $filename, $idcLogo);
   $st->execute();
   $st->close();
   logAktivitas($koneksi, 'pengaturan', 'upload_file', ['tipe' => 'setting_clinic', 'id' => $lgLama['id'] ?? $idcLogo, 'label' => 'Logo faskes'], 'Logo faskes diganti',
      $lgLama ? ['image_clinic' => $lgLama['image_clinic']] : null, ['image_clinic' => $filename]);

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
