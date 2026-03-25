<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$image = $data['image'];

// hapus prefix base64
$image = str_replace('data:image/png;base64,', '', $image);
$image = base64_decode($image);

$filename = '../../uploads/faces/' . time() . '_' . $id . '.png';

file_put_contents($filename, $image);

// simpan ke DB kalau perlu
mysqli_query($koneksi, "UPDATE ms_patient SET face_image = '$filename' WHERE id_patient = '$id'");

echo json_encode(["status" => "success"]);
