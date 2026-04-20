<?php
include '../../database/connect.php';

$no = $_GET['no'] ?? '';
$id_customer = 19;

$q = mysqli_query($koneksi, "SELECT * FROM pasien_triase 
  WHERE visit_ID='$no' AND id_customer='$id_customer'
  ORDER BY id_triase DESC LIMIT 1
");

$data = mysqli_fetch_assoc($q);

echo json_encode([
  "status" => "success",
  "data" => $data
]);
