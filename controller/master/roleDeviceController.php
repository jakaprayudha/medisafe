<?php
require '../../database/connect.php';
header('Content-Type: application/json');

session_start();
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ================= GET =================
if ($method === 'GET') {

   $q = mysqli_query($koneksi, "
      SELECT status_farmasi_kasir
      FROM ms_faskes 
      LEFT JOIN setting_clinic 
         ON ms_faskes.id_clinic = setting_clinic.id
      WHERE setting_clinic.id_customer = '$id_customer'
      LIMIT 1
   ");

   $data = mysqli_fetch_assoc($q);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}


// ================= POST (UPDATE) =================
if ($method === 'POST') {

   $input = json_decode(file_get_contents("php://input"), true);

   $status = (int) ($input['status_farmasi_kasir'] ?? 0);

   $update = mysqli_query($koneksi, "
      UPDATE ms_faskes
      SET status_farmasi_kasir = '$status'
      WHERE id_clinic = (
         SELECT id 
         FROM setting_clinic 
         WHERE id_customer = '$id_customer'
         LIMIT 1
      )
   ");

   if ($update) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil update'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal update',
         'error' => mysqli_error($koneksi)
      ]);
   }
}
