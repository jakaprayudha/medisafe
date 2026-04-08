<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id_visit = $data['id_visit'] ?? null;
$metode   = $data['metode_bayar'] ?? null;
$nomor    = $data['nomor_transaksi'] ?? null;

if (!$id_visit) {
   echo json_encode([
      "status" => "error",
      "message" => "ID Visit tidak ditemukan"
   ]);
   exit;
}

// update status bayar jadi lunas
$query = "UPDATE pasien_visit 
          SET status_bayar = 1,
              metode_bayar = '$metode',
              nomor_transaksi = '$nomor'
          WHERE visit_ID = '$id_visit'";

$result = mysqli_query($koneksi, $query);

if ($result) {
   echo json_encode([
      "status" => "success",
      "message" => "Pembayaran berhasil"
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);
}
