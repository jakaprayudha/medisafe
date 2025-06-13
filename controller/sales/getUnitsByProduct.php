<?php
ob_start();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../database/connect.php';

$id_product = $_GET['id_product'] ?? null;

if (!$id_product) {
   echo json_encode([]);
   exit;
}

try {
   // Gunakan MySQLi style
   $stmt = $koneksi->prepare("
      SELECT 
         p.price, 
         u.unit_name 
      FROM ms_product_price p
      INNER JOIN ms_product_unit u ON u.id_unit = p.id_unit 
      WHERE p.id_product = ?
   ");
   $stmt->bind_param('s', $id_product);
   $stmt->execute();
   $result = $stmt->get_result();

   $unitPrices = [];
   while ($row = $result->fetch_assoc()) {
      $unitPrices[] = $row;
   }

   if (!empty($unitPrices)) {
      echo json_encode($unitPrices);
      exit;
   }

   // Fallback
   $fallback = $koneksi->prepare("SELECT product_price FROM ms_product WHERE id_product = ?");
   $fallback->bind_param('s', $id_product);
   $fallback->execute();
   $res = $fallback->get_result();
   $product = $res->fetch_assoc();

   if ($product) {
      echo json_encode([
         [
            'unit_name' => 'pcs',
            'price' => (float)$product['product_price']
         ]
      ]);
   } else {
      echo json_encode([]);
   }
} catch (Exception $e) {
   http_response_code(500);
   echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

ob_end_flush();
