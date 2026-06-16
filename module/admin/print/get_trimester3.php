<?php
header('Content-Type: application/json');
require '../../../database/connect.php';


$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

if (!$no || !$rm) {
   echo json_encode(['status' => 'error', 'message' => 'Parameter kurang']);
   exit;
}

$q = mysqli_query($koneksi, "
   SELECT * FROM pemeriksaan_trimester3
   WHERE visit_ID = '$no' AND nomor_rm = '$rm'
   LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
   echo json_encode(['status' => 'empty']);
   exit;
}

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
