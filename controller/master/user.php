<?php
session_start();
include '../../database/connect.php';
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'];
$q = mysqli_query($koneksi, "SELECT * FROM ms_users WHERE id_customer='$id_customer' AND roles IN('dokter','bidan','perawat','apoteker') ");

$data = [];

while ($row = mysqli_fetch_assoc($q)) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
