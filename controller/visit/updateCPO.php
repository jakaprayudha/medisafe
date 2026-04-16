<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$id = $_POST['id'];

$stmt = $koneksi->prepare("
   UPDATE pasien_cpo SET
      tanggal=?,
      nama_item=?,
      dosis=?,
      signature=?,
      jam_pagi=?,
      jam_siang=?,
      jam_sore=?,
      jam_malam=?
   WHERE id=?
");

$stmt->bind_param(
   "ssssssssi",
   $_POST['tanggal'],
   $_POST['nama_obat'],
   $_POST['dosis'],
   $_POST['signature'],
   $_POST['jam_pagi'],
   $_POST['jam_siang'],
   $_POST['jam_sore'],
   $_POST['jam_malam'],
   $id
);

$stmt->execute();

echo json_encode(["status" => "success"]);
