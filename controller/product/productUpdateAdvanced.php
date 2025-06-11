<?php
session_start();
include '../../database/connect.php'; // Sesuaikan path koneksi

header('Content-Type: application/json');

// Debug log
file_put_contents('debug_log.txt', print_r($_POST, true));

try {
   // Ambil data dari POST
   $id_product = $_POST['id_product'] ?? null;
   $product_max = $_POST['product_max'] ?? '';
   $product_min = $_POST['product_min'] ?? '';
   $product_alert = $_POST['product_alert'] ?? '';
   $product_weight = $_POST['product_weight'] ?? '';
   $tax_id = $_POST['tax_id'] ?? '';
   $account_id = $_POST['account_id'] ?? '';
   $product_status = $_POST['product_status'] ?? '';
   $now = date('Y-m-d H:i:s');
   $user = $_SESSION['fullname'] ?? 'unknown';

   if ($id_product) {
      // UPDATE ke tabel ms_product
      $stmt = $koneksi->prepare("
         UPDATE ms_product SET  
            product_min = ?, 
            product_max = ?, 
            product_alert = ?, 
            update_at = ?, 
            user_update = ?, 
            product_weight = ?, 
            tax_id = ?, 
            id_account = ?, 
            product_status = ?
         WHERE id_product = ?
      ");

      if (!$stmt) {
         throw new Exception("Prepare statement gagal: " . $koneksi->error);
      }

      // Bind parameter sesuai urutan dan jumlah pada query
      $stmt->bind_param(
         "ssssssssss",
         $product_min,
         $product_max,
         $product_alert,
         $now,
         $user,
         $product_weight,
         $tax_id,
         $account_id,
         $product_status,
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
