<?php
include '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

if (!$id) {
   echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
   exit;
}

// 🔥 TEST QUERY
$q = mysqli_query($koneksi, "DELETE FROM pasien_cpo WHERE id='$id'");

if ($q) {
   echo json_encode(['status' => 'success', 'message' => 'Berhasil dihapus']);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => mysqli_error($koneksi)
   ]);
}
