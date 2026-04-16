<?php
include '../../database/connect.php';
session_start();
header("Content-Type: application/json");

$visit = $_POST['nomor_visit'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit || !$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session / visit tidak valid"
   ]);
   exit;
}

// 🔥 VALIDASI visit benar milik customer
$check = $koneksi->prepare("
   SELECT visit_ID FROM pasien_visit 
   WHERE visit_ID=? AND id_customer=?
");
$check->bind_param("si", $visit, $id_customer);
$check->execute();
$res = $check->get_result();

if ($res->num_rows == 0) {
   echo json_encode([
      "status" => "error",
      "message" => "Visit tidak ditemukan / tidak valid"
   ]);
   exit;
}

// 🔥 ambil data
$tanggal   = $_POST['tanggal'] ?? [];
$id_obat   = $_POST['nama_obat'] ?? [];
$dosis     = $_POST['dosis'] ?? [];
$signature = $_POST['signature'] ?? [];
$jam_pagi  = $_POST['jam_pagi'] ?? [];
$jam_siang = $_POST['jam_siang'] ?? [];
$jam_sore  = $_POST['jam_sore'] ?? [];
$jam_malam = $_POST['jam_malam'] ?? [];
$petugas   = $_POST['petugas'] ?? [];

// 🔥 reset data lama (mode replace)
// $koneksi->query("DELETE FROM pasien_cpo WHERE visit_ID='$visit'");

$stmt = $koneksi->prepare("
   INSERT INTO pasien_cpo
   (visit_ID, id_customer, tanggal, nama_item, dosis, signature, jam_pagi, jam_siang, jam_sore, jam_malam, petugas)
   VALUES (?,?,?,?,?,?,?,?,?,?,?)
");

for ($i = 0; $i < count($tanggal); $i++) {

   if (empty($id_obat[$i])) continue;

   $stmt->bind_param(
      "sisssssssss",
      $visit,
      $id_customer,
      $tanggal[$i],
      $id_obat[$i],
      $dosis[$i],
      $signature[$i],
      $jam_pagi[$i],
      $jam_siang[$i],
      $jam_sore[$i],
      $jam_malam[$i],
      $petugas[$i]
   );

   $stmt->execute();
}

echo json_encode([
   "status" => "success"
]);
