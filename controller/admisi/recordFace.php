<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$image = $data['image'] ?? '';

// hapus prefix base64 (png / jpeg)
$image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
$image = base64_decode($image, true);

if (!$id || $image === false || $image === '') {
   echo json_encode(["status" => "error", "message" => "Data gambar tidak valid"]);
   exit;
}

$filename = '../../uploads/faces/' . time() . '_' . $id . '.jpg';

if (file_put_contents($filename, $image) === false) {
   echo json_encode(["status" => "error", "message" => "Gagal menyimpan file"]);
   exit;
}

// simpan ke DB kalau perlu
mysqli_query($koneksi, "UPDATE ms_patient SET face_image = '$filename' WHERE id_patient = '$id'");

echo json_encode(["status" => "success"]);
