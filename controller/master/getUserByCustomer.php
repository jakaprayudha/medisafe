<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$id_customer = $_GET['id_customer'] ?? null;

$q = mysqli_query($koneksi, "
   SELECT * FROM ms_users WHERE id_customer='$id_customer' LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if ($data) {
   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
} else {
   echo json_encode([
      'status' => 'empty'
   ]);
}
