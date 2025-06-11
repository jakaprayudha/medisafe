<?php
include '../../database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $produk = $_POST['product'] ?? [];

   if (empty($produk)) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data produk yang dikirim.']);
      exit;
   }

   $stmt = $koneksi->prepare("INSERT INTO sales_order (id_product, harga_satuan, diskon, qty, id_quotation) VALUES (?,?,?,?,?)");

   foreach ($produk as $item) {
      $kode   = $item['code'] ?? '';
      $nama   = $item['name'] ?? '';
      $unit   = $item['unit'] ?? '';
      $harga  = $item['price'] ?? 0;
      $qty    = $item['qty'] ?? 0;

      if (empty($kode) || empty($nama)) continue;

      $stmt->bind_param("sssdi", $kode, $nama, $unit, $harga, $qty);
      $stmt->execute();
   }

   $stmt->close();
   echo json_encode(['status' => 'success']);
}
