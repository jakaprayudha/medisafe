<?php
session_start();
include '../../database/connect.php'; // Sesuaikan path koneksi

header('Content-Type: application/json');

// Debug log
file_put_contents('debug_log.txt', print_r($_POST, true));

try {
   // Ambil data dari POST
   $id_product = $_POST['id_product'] ?? null;
   $product_code = $_POST['product_code'] ?? '';
   $barcode = $_POST['barcode'] ?? '';
   $product_name = $_POST['product_name'] ?? '';
   $id_merk = $_POST['id_merk'] ?? '';
   $id_category = $_POST['id_category'] ?? '';
   $product_description = $_POST['product_description'] ?? '';
   $product_color = $_POST['product_color'] ?? '';
   $product_size = $_POST['product_size'] ?? '';
   $product_any = $_POST['product_any'] ?? '';
   $product_sn = $_POST['product_sn'] ?? '';
   $product_base = $_POST['product_base'] ?? '';

   $now = date('Y-m-d H:i:s');
   $user = $_SESSION['fullname'] ?? 'unknown';

   if ($id_product) {
      // UPDATE ke tabel ms_product
      $stmt = $koneksi->prepare("
         UPDATE ms_product SET  
            product_name = ?, 
            product_description = ?, 
            product_code = ?, 
            update_at = ?, 
            user_update = ?, 
            product_base = ?, 
            id_category = ?, 
            id_merk = ?, 
            product_color = ?, 
            product_size = ?, 
            product_any = ?, 
            product_barcode = ?, 
            product_sn = ?
         WHERE id_product = ?
      ");

      if (!$stmt) {
         throw new Exception("Prepare statement gagal: " . $koneksi->error);
      }

      // Jumlah parameter = 15 (14 SET + 1 WHERE)
      $stmt->bind_param(
         "ssssssssssssss",
         $product_name,
         $product_description,
         $product_code,
         $now,
         $user,
         $product_base,
         $id_category,
         $id_merk,
         $product_color,
         $product_size,
         $product_any,
         $barcode,
         $product_sn,
         $id_product
      );

      if (!$stmt->execute()) {
         throw new Exception("Gagal update ms_product: " . $stmt->error);
      }

      $stmt->close();

      echo json_encode(["success" => true, "message" => "Data berhasil diperbarui."]);
   } else {
      throw new Exception("ID produk tidak ditemukan. (debug: " . var_export($id_product, true) . ")");
   }

   $koneksi->close();
} catch (Exception $e) {
   echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
