<?php
require '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

if (!$username || !$password) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Username & Password wajib diisi'
   ]);
   exit;
}

// update hanya 2 field
$query = mysqli_query($koneksi, "
    UPDATE setting_pcare 
    SET username = '$username',
        password = '$password'
    WHERE id_customer = '$id_customer'
");

if ($query) {
   echo json_encode([
      'status' => 'success',
      'message' => 'Berhasil update'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal update'
   ]);
}
