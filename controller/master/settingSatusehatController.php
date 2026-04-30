<?php
require '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   // 🔹 GET: ambil data setting
   case 'GET':
      $query = mysqli_query($koneksi, "
            SELECT * FROM setting_satusehat 
            WHERE id_customer = '$id_customer' 
            LIMIT 1
        ");

      if ($data = mysqli_fetch_assoc($query)) {
         echo json_encode([
            'status' => 'success',
            'data' => $data
         ]);
      } else {
         echo json_encode([
            'status' => 'success',
            'data' => null
         ]);
      }
      break;


   // 🔹 POST: insert / update
   case 'POST':

      $organization_id = $_POST['organization_id'] ?? '';
      $client_id       = $_POST['client_id'] ?? '';
      $client_secret   = $_POST['client_secret'] ?? '';
      $latitude        = $_POST['latitude'] ?? '';
      $longitude       = $_POST['longitude'] ?? '';
      $address         = $_POST['address'] ?? '';
      $user            = $_SESSION['username'] ?? 'system';

      // cek apakah sudah ada
      $check = mysqli_query($koneksi, "
            SELECT id_ss FROM setting_satusehat 
            WHERE id_customer = '$id_customer'
        ");

      if (mysqli_num_rows($check) > 0) {

         // 🔸 UPDATE
         $update = mysqli_query($koneksi, "
                UPDATE setting_satusehat SET
                    organization_id = '$organization_id',
                    client_id = '$client_id',
                    client_secret = '$client_secret',
                    latitude = '$latitude',
                    longitude = '$longitude',
                    address = '$address',
                    user = '$user'
                WHERE id_customer = '$id_customer'
            ");

         echo json_encode([
            'status' => $update ? 'success' : 'error',
            'message' => $update ? 'Berhasil update data' : 'Gagal update data'
         ]);
      } else {

         // 🔸 INSERT
         $insert = mysqli_query($koneksi, "
                INSERT INTO setting_satusehat (
                    id_customer,
                    organization_id,
                    client_id,
                    client_secret,
                    latitude,
                    longitude,
                    address,
                    user
                ) VALUES (
                    '$id_customer',
                    '$organization_id',
                    '$client_id',
                    '$client_secret',
                    '$latitude',
                    '$longitude',
                    '$address',
                    '$user'
                )
            ");

         echo json_encode([
            'status' => $insert ? 'success' : 'error',
            'message' => $insert ? 'Berhasil simpan data' : 'Gagal simpan data'
         ]);
      }

      break;


   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan'
      ]);
      break;
}
