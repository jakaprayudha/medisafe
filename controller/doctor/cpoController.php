<?php

include '../../../database/connect.php';

$ruangan = $_POST['ruangan'];
$diagnosa = $_POST['diagnosa'];

$tanggal = $_POST['tanggal'];
$nama_obat = $_POST['nama_obat'];
$dosis = $_POST['dosis'];
$signature = $_POST['signature'];
$jam_pagi = $_POST['jam_pagi'];
$jam_siang = $_POST['jam_siang'];
$jam_sore = $_POST['jam_sore'];
$jam_malam = $_POST['jam_malam'];

foreach ($tanggal as $i => $tgl) {

   $stmt = $koneksi->prepare("
        INSERT INTO cpo 
        (tanggal, nama_obat, dosis, signature, jam_pagi, jam_siang, jam_sore, jam_malam, ruangan, diagnosa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

   $stmt->bind_param(
      "ssssssssss",
      $tgl,
      $nama_obat[$i],
      $dosis[$i],
      $signature[$i],
      $jam_pagi[$i],
      $jam_siang[$i],
      $jam_sore[$i],
      $jam_malam[$i],
      $ruangan,
      $diagnosa
   );

   $stmt->execute();
}

echo json_encode([
   'status' => 'success',
   'no' => $_GET['no'] ?? 1,
   'rm' => $_GET['rm'] ?? 1
]);
