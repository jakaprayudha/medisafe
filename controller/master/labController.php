<?php
include '../../database/connect.php';
session_start();

header("Content-Type: application/json");

// 🔥 HANDLE METHOD
$method = $_SERVER['REQUEST_METHOD'];

// support spoof PUT (kalau pakai fetch POST + _method)
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}
function generateKodeLab($koneksi)
{
   do {
      // format: LAB- + 6 karakter random
      $kode = 'LAB-' . strtoupper(substr(md5(uniqid()), 0, 6));

      $check = mysqli_query($koneksi, "
         SELECT id_lab FROM laboratorium_detail 
         WHERE kode = '$kode'
      ");
   } while (mysqli_num_rows($check) > 0);

   return $kode;
}
switch ($method) {


   case 'POST':

      $assemen = $_POST['assemen'] ?? null;

      if (!$assemen) {
         echo json_encode([
            "status" => "error",
            "message" => "Nama pemeriksaan wajib diisi"
         ]);
         exit;
      }

      // 🔥 generate kode unik
      $kode = generateKodeLab($koneksi);

      $insert = mysqli_query($koneksi, "
      INSERT INTO laboratorium_detail (kode, assemen, status)
      VALUES ('$kode', '$assemen', 1)
   ");

      if ($insert) {
         echo json_encode([
            "status" => "success",
            "message" => "Data berhasil ditambahkan",
            "kode" => $kode
         ]);
      } else {
         echo json_encode([
            "status" => "error",
            "message" => mysqli_error($koneksi)
         ]);
      }

      break;

   // =============================
   // 🔹 GET DATA LAB
   // =============================
   case 'GET':

      // 🔥 GET DETAIL (EDIT)
      if (isset($_GET['id'])) {

         $id = $_GET['id'];

         $query = mysqli_query($koneksi, "
         SELECT * FROM laboratorium_detail 
         WHERE id_lab = '$id'
      ");

         $data = mysqli_fetch_assoc($query);

         echo json_encode([
            "status" => "success",
            "data" => $data
         ]);
         exit;
      }

      // 🔥 GET LIST
      $query = mysqli_query($koneksi, "
      SELECT *
      FROM laboratorium_detail
      ORDER BY status = 1 DESC, assemen DESC
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

      // 🔥 TOGGLE STATUS
      if (isset($_GET['toggle_status'])) {

         $id_lab = $_PUT['id_lab'] ?? null;
         $status = $_PUT['status'] ?? 0;

         $update = mysqli_query($koneksi, "
         UPDATE laboratorium_detail 
         SET status = '$status'
         WHERE id_lab = '$id_lab'
      ");

         echo json_encode([
            "status" => $update ? "success" : "error",
            "message" => $update ? "Status diupdate" : mysqli_error($koneksi)
         ]);
         exit;
      }

      // 🔥 UPDATE DATA
      if (isset($_GET['id'])) {

         $id = $_GET['id'];
         $assemen = $_PUT['assemen'] ?? null;

         $update = mysqli_query($koneksi, "
         UPDATE laboratorium_detail 
         SET assemen = '$assemen'
         WHERE id_lab = '$id'
      ");

         echo json_encode([
            "status" => $update ? "success" : "error",
            "message" => $update ? "Data berhasil diupdate" : mysqli_error($koneksi)
         ]);
         exit;
      }

      break;

   case 'DELETE':

      parse_str(file_get_contents("php://input"), $_DELETE);

      $id = $_GET['id'] ?? null;

      if (!$id) {
         echo json_encode([
            "status" => "error",
            "message" => "ID tidak ditemukan"
         ]);
         exit;
      }

      $delete = mysqli_query($koneksi, "
      DELETE FROM laboratorium_detail
      WHERE id_lab = '$id'
   ");

      echo json_encode([
         "status" => $delete ? "success" : "error",
         "message" => $delete ? "Data berhasil dihapus" : mysqli_error($koneksi)
      ]);

      break;
}
