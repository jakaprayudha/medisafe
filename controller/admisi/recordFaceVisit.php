<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$image = $data['image'];

// hapus prefix base64
$image = str_replace('data:image/png;base64,', '', $image);
$image = base64_decode($image);

$filename = '../../uploads/faces_visit/' . time() . '_' . $id . '.png';

file_put_contents($filename, $image);

// simpan ke DB kalau perlu
mysqli_query($koneksi, "UPDATE pasien_visit SET face_image_visit = '$filename' WHERE id_visit = '$id'");

echo json_encode(["status" => "success"]);
