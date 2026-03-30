<?php
include '../../database/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

   if (isset($_GET['no'])) {
      $no = $_GET['no'];

      $q = mysqli_query($koneksi, "
            SELECT * FROM ms_faskes 
            WHERE order_number = '$no'
            LIMIT 1
        ");

      if ($q && mysqli_num_rows($q) > 0) {
         $data = mysqli_fetch_assoc($q);

         echo json_encode([
            'status' => 'success',
            'data' => $data
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak ditemukan'
         ]);
      }
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter no wajib'
      ]);
   }
}
