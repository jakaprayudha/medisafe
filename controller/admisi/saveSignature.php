<?php
header('Content-Type: application/json');
include '../../database/connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$id_visit = $input['id_visit'] ?? null;
$image    = $input['image'] ?? null;

if (!$id_visit || !$image) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
   exit;
}

// decode base64
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$data = base64_decode($image);

// path simpan
$folder = '../../uploads/ttd/';
if (!is_dir($folder)) {
   mkdir($folder, 0777, true);
}

$filename = 'ttd_' . time() . '.png';
$filePath = $folder . $filename;

// simpan file
file_put_contents($filePath, $data);

// simpan ke DB
mysqli_query($koneksi, "UPDATE pasien_visit 
    SET signature_path = '$filename'
    WHERE id_visit = $id_visit
");

echo json_encode([
   'status' => 'success',
   'file' => $filename
]);
