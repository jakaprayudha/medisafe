<?php
include '../../database/connect.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

$data = json_decode(file_get_contents('php://input'), true);
$visitID = $data['visit_ID'] ?? null;

if (!$visitID) {
  echo json_encode(['status'=>'error','message'=>'Invalid visit']);
  exit;
}

/* reset panggilan sebelumnya */
mysqli_query($koneksi, "
  UPDATE pasien_visit
  SET status_antrian = '0'
  WHERE visit_date = '$today'
    AND status_antrian = '1'
");

/* set panggilan baru */
mysqli_query($koneksi, "
  UPDATE pasien_visit
  SET status_antrian = '1'
  WHERE visit_ID = '$visitID'
");

echo json_encode(['status'=>'success']);