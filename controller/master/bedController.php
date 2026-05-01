<?php
include '../../database/connect.php';
session_start();

header("Content-Type: application/json");

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);
   exit;
}

// 🔥 HANDLE METHOD
$method = $_SERVER['REQUEST_METHOD'];

// support spoof PUT (kalau pakai fetch POST + _method)
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {

   // =============================
   // 🔹 GET DATA BED
   // =============================
   case 'GET':

      $query = mysqli_query($koneksi, "
         SELECT *
         FROM ms_room_bed
         LEFT JOIN ms_room ON ms_room.id_room = ms_room_bed.id_room
         WHERE ms_room_bed.id_customer = '$id_customer'
         ORDER BY ms_room_bed.id_bed DESC
      ");

      $data = [];

      while ($row = mysqli_fetch_assoc($query)) {
         $data[] = $row;
      }

      echo json_encode([
         "status" => "success",
         "data" => $data
      ]);

      break;


   // =============================
   // 🔥 TOGGLE STATUS BED
   // =============================
   case 'PUT':

      parse_str(file_get_contents("php://input"), $_PUT);

      if (isset($_GET['toggle_status'])) {

         $id_bed = $_PUT['id_bed'] ?? null;
         $status = $_PUT['bed_status'] ?? 0;

         if (!$id_bed) {
            echo json_encode([
               "status" => "error",
               "message" => "ID bed tidak ditemukan"
            ]);
            exit;
         }

         // 🔥 VALIDASI MILIK CUSTOMER (PENTING!)
         $check = mysqli_query($koneksi, "
            SELECT id_bed 
            FROM ms_room_bed 
            WHERE id_bed = '$id_bed' 
            AND id_customer = '$id_customer'
         ");

         if (mysqli_num_rows($check) == 0) {
            echo json_encode([
               "status" => "error",
               "message" => "Data tidak valid"
            ]);
            exit;
         }

         // 🔥 UPDATE STATUS
         $update = mysqli_query($koneksi, "
            UPDATE ms_room_bed 
            SET bed_status = '$status'
            WHERE id_bed = '$id_bed'
         ");

         if ($update) {
            echo json_encode([
               "status" => "success",
               "message" => "Status berhasil diupdate"
            ]);
         } else {
            echo json_encode([
               "status" => "error",
               "message" => mysqli_error($koneksi)
            ]);
         }

         exit;
      }

      break;


   default:
      echo json_encode([
         "status" => "error",
         "message" => "Method tidak diizinkan"
      ]);
      break;
}
