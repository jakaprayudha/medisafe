<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

if (!$no || !$rm) {
   echo json_encode(['status' => 'error', 'message' => 'Param kurang']);
   exit;
}

$header = mysqli_query($koneksi, "
    SELECT * FROM persalinan_header 
    WHERE visit_ID = '$no' AND nomor_rm='$rm'
");
$h = mysqli_fetch_assoc($header);

if (!$h) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
   exit;
}

$detail = mysqli_query($koneksi, "
    SELECT * FROM persalinan_kala4 WHERE header_id = '{$h['id']}'
");

$data_detail = mysqli_fetch_all($detail, MYSQLI_ASSOC);

echo json_encode([
   'status' => 'success',
   'header' => $h,
   'detail' => $data_detail
]);
