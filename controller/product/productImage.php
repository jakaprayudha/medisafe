<?php
header('Content-Type: application/json');
require_once '../../database/connect.php'; // sesuaikan dengan nama file koneksi kamu

$uploadDir = '../../uploads/';
$defaultImage = '../../uploads/default.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   // Ambil id_product dari POST (bisa juga dari $_SESSION)
   $productId = isset($_POST['product_id']) ? $_POST['product_id'] : null;

   if (!$productId) {
      echo json_encode(['status' => 'error', 'message' => 'ID produk tidak tersedia']);
      exit;
   }

   if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] != 0) {
      echo json_encode(['status' => 'error', 'message' => 'File tidak valid']);
      exit;
   }

   $fileTmp  = $_FILES['gambar']['tmp_name'];
   $fileName = basename($_FILES['gambar']['name']);
   $target   = $uploadDir . time() . '_' . $fileName;

   $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
   $allowed = ['jpg', 'jpeg', 'png', 'gif'];

   if (!in_array($ext, $allowed)) {
      echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung']);
      exit;
   }

   if (move_uploaded_file($fileTmp, $target)) {
      // Simpan ke database
      global $koneksi;
      $stmt = $koneksi->prepare("UPDATE ms_product SET product_image = ? WHERE id_product = ?");
      $stmt->bind_param("ss", $target, $productId);
      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Gambar berhasil diupload dan disimpan.',
            'url' => $target
         ]);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Upload berhasil, tapi gagal update DB.']);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file']);
   }
}
