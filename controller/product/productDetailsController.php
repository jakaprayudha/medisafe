<?php
include '../../database/connect.php';
header('Content-Type: application/json');

function jsonResponse($success, $message, $data = null)
{
   echo json_encode([
      'success' => $success,
      'message' => $message,
      'data' => $data
   ]);
   exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_product'])) {
   $kode = $koneksi->real_escape_string($_GET['id_product']);

   $query = "SELECT * FROM ms_product LEFT OUTER JOIN ms_merk ON ms_merk.id_merk = ms_product.id_merk LEFT OUTER JOIN ms_product_category ON ms_product_category.id_category = ms_product.id_category LEFT OUTER JOIN ms_account ON ms_account.id_account = ms_product.id_account   WHERE ms_product.id_product = '$kode' LIMIT 1";
   $result = $koneksi->query($query);
   if ($result && $result->num_rows > 0) {
      $data = $result->fetch_assoc();
      jsonResponse(true, "Data ditemukan", $data);
   } else {
      jsonResponse(false, "Data tidak ditemukan");
   }
}

jsonResponse(false, "Metode request tidak valid");
