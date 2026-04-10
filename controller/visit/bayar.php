<?php
include '../../database/connect.php';
session_start();
header('Content-Type: application/json');
$id_customer = $_SESSION['id_customer'];
$data = json_decode(file_get_contents("php://input"), true);

$id_visit = $data['id_visit'] ?? null;
$metode   = $data['metode_bayar'] ?? null;
$nomor    = date('Ymd') . rand(1000, 9999);
$total   = $data['total'] ?? null;
$bayar   = $data['bayar'] ?? null;
$kembalian = $data['kembalian'] ?? 0;
// 🔥 HAPUS TITIK (format ribuan)
$kembalian = str_replace('.', '', $kembalian);
// 🔥 pastikan integer
$kembalian = (int)$kembalian;

if (!$id_visit) {
   echo json_encode([
      "status" => "error",
      "message" => "ID Visit tidak ditemukan"
   ]);
   exit;
}

// update status bayar jadi lunas
$query = "UPDATE pasien_visit SET status_bayar = 1,
              metode_bayar = '$metode',
              nomor_transaksi = '$nomor',
              amount_results  = $total,
              amount_payment = $bayar,
              amount_changes = $kembalian
          WHERE visit_ID = '$id_visit' AND id_customer = '$id_customer'";

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
