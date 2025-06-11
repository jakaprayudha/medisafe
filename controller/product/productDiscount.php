<?php
session_start();
include '../../database/connect.php'; // Sesuaikan path koneksi

header('Content-Type: application/json');

// Debug log
file_put_contents('debug_log.txt', print_r($_POST, true));

try {
   // Ambil data dari POST
   $id_product = $_POST['id_product'] ?? null;
   $discount_start = $_POST['discount_start'] ?? '';
   $discount_end = $_POST['discount_end'] ?? '';
   $discount_type = $_POST['discount_type'] ?? '';
   $discount_value = $_POST['discount_value'] ?? '';

   if ($id_product) {
      // UPDATE ke tabel ms_product
      $stmt = $koneksi->prepare("
         UPDATE ms_product SET  
            discount_start = ?, 
            discount_end = ?, 
            discount_type = ?, 
            discount_value = ?
         WHERE id_product = ?
      ");

      if (!$stmt) {
         throw new Exception("Prepare statement gagal: " . $koneksi->error);
      }

      // Bind parameter sesuai urutan dan jumlah pada query
      $stmt->bind_param(
         "sssss",
         $discount_start,
         $discount_end,
         $discount_type,
         $discount_value,
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
