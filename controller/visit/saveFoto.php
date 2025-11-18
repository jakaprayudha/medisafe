
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
$filename = "capture_image_baby_" . $rm . "_" . $visit . ".jpg";
$path = "../../uploads/foto/" . $filename;

// Cek dan hapus foto lama (agar hanya 1 foto)
if (file_exists($path)) unlink($path);

// Simpan file
file_put_contents($path, $image);

// SIMPAN KE DATABASE
$query = "
  INSERT INTO capture_image_baby (rm, visit, foto_path)
  VALUES ('$rm', '$visit', '$path')
  ON DUPLICATE KEY UPDATE foto_path = '$path'
";

if (mysqli_query($koneksi, $query)) {
   echo json_encode(["status" => "success", "message" => "Foto berhasil disimpan"]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>