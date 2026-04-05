<?php
require '../../database/connect.php';

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'];

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM setting_pcare WHERE id_customer = '$id_customer' LIMIT 1");

if ($data = mysqli_fetch_assoc($query)) {
   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
} else {
   echo json_encode([
      'status' => 'empty',
      'message' => 'Data tidak ditemukan'
   ]);
}
