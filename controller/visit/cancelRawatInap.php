<?php
require_once "../../database/connect.php";
header('Content-Type: application/json');

$id_ranap = $_POST['id_ranap'] ?? null;

if (!$id_ranap) {
   echo json_encode(["status" => "error", "message" => "ID rawat inap tidak ditemukan."]);
   exit;
}

// Hapus hanya jika belum dibooking
$query = $koneksi->query("DELETE FROM permintaan_ranap 
                          WHERE id_ranap = '$id_ranap' 
                          AND ranap_booking = 0 
                          LIMIT 1");

if ($query) {
   echo json_encode(["status" => "success", "message" => "Data permintaan rawat inap berhasil dibatalkan."]);
} else {
   echo json_encode(["status" => "error", "message" => "Gagal menghapus data atau data sudah dibooking."]);
}
