<?php
include "../../database/connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$rm = $data['rm'];
$visit = $data['visit'];
$image = $data['image'];

if (!$image) {
   echo json_encode(["status" => "error", "message" => "Tidak ada gambar"]);
   exit;
}

// Buang prefix Base64
$image = str_replace("data:image/jpeg;base64,", "", $image);
$image = base64_decode($image);

// Nama file unik
$filename = "capture_{$rm}_{$visit}.jpg";
$path = "../../uploads/foto/" . $filename;

// Hapus foto lama jika ada
if (file_exists($path)) unlink($path);

// Simpan file
file_put_contents($path, $image);

// Simpan DB
$query = "
  INSERT INTO capture_patient (rm, visit, foto_path)
  VALUES ('$rm', '$visit', '$filename')
  ON DUPLICATE KEY UPDATE foto_path = '$filename'
";

if (mysqli_query($koneksi, $query)) {
   echo json_encode([
      "status" => "success",
      "message" => "Foto berhasil disimpan!",
      "foto" => $filename
   ]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
