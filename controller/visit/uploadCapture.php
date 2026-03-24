<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$image = $data['image'];
$id_visit = $data['id_visit'];

// remove header base64
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$imageData = base64_decode($image);

// nama file
$filename = 'capture_' . time() . '.png';
$path = '../../uploads/foto/' . $filename;

// simpan file
file_put_contents($path, $imageData);

// simpan ke database (optional)
$query = "UPDATE pasien_visit SET foto_capture=? WHERE id_visit=?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ss", $filename, $id_visit);
$stmt->execute();

echo json_encode([
   'status' => 'success',
   'file' => $filename
]);
