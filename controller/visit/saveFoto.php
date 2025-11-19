<?php
include "../../database/connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$rm = $data['rm'];
$visit = $data['visit'];
$image = $data['image'];
$tgl = date('Y-m-d');

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

// Simpan file baru
file_put_contents($path, $image);

// Path yang disimpan ke database
$savePath = "uploads/foto/" . $filename;

// Simpan DB
$query = "
  INSERT INTO pasien_dokumen (nomor_rm, visit_ID, foto_path, jenis_dokumen, rilis)
  VALUES ('$rm', '$visit', '$savePath', 'FOTO_PASIEN', '$tgl')
  ON DUPLICATE KEY UPDATE foto_path = '$savePath'
";

if (mysqli_query($koneksi, $query)) {
   echo json_encode([
      "status" => "success",
      "message" => "Foto berhasil disimpan!",
      "foto" => $savePath
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);
}
