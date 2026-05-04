<?php
include '../../database/connect.php';
session_start();

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// spoof PUT
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}


switch ($method) {

   // =============================
   // 🟢 POST (MASTER / DETAIL)
   // =============================
   case 'POST':

      // 🔥 INSERT DETAIL PARAMETER
      if (isset($_POST['kode_lab'])) {
         $kode_lab = $_POST['kode_lab'];
         $urutan   = $_POST['urutan'] ?? null;
         $nama     = $_POST['nama'] ?? null;
         $satuan   = $_POST['satuan'] ?? null;
         $minimum  = $_POST['minimum'] ?? null;
         $maksimum = $_POST['maksimum'] ?? null;

         $insert = mysqli_query($koneksi, "
               INSERT INTO laboratorium_item 
               (kode, urutan, assemen, satuan, minimum, maksimum)
               VALUES 
               ('$kode_lab', '$urutan', '$nama', '$satuan', '$minimum', '$maksimum')
            ");
         echo json_encode([
            "status" => $insert ? "success" : "error",
            "message" => $insert ? "Parameter berhasil ditambahkan" : mysqli_error($koneksi)
         ]);
         exit;
      }

      // 🔥 INSERT MASTER LAB
      $assemen = $_POST['assemen'] ?? null;

      if (!$assemen) {
         echo json_encode([
            "status" => "error",
            "message" => "Nama pemeriksaan wajib diisi"
         ]);
         exit;
      }

      $kode = generateKodeLab($koneksi);

      $insert = mysqli_query($koneksi, "
         INSERT INTO laboratorium_detail (kode, assemen, status)
         VALUES ('$kode', '$assemen', 1)
      ");

      echo json_encode([
         "status" => $insert ? "success" : "error",
         "message" => $insert ? "Data berhasil ditambahkan" : mysqli_error($koneksi),
         "kode" => $kode
      ]);

      break;

   // =============================
   // 🔵 GET (LIST / DETAIL / PARAMETER)
   // =============================
   case 'GET':

      // 🔥 DETAIL PARAMETER BY KODE
      if (isset($_GET['kode'])) {

         $kode = $_GET['kode'];

         $query = mysqli_query($koneksi, "
            SELECT *
            FROM laboratorium_item
            WHERE kode = '$kode'
            ORDER BY urutan ASC
         ");

         $data = [];
         while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
         }

         echo json_encode([
            "status" => "success",
            "data" => $data
         ]);
         exit;
      }

      // 🔥 DETAIL MASTER BY ID
      if (isset($_GET['id'])) {

         $id = $_GET['id'];

         $query = mysqli_query($koneksi, "
            SELECT * FROM laboratorium_item 
            WHERE id = '$id'
         ");

         echo json_encode([
            "status" => "success",
            "data" => mysqli_fetch_assoc($query)
         ]);
         exit;
      }

      // 🔥 LIST MASTER
      $query = mysqli_query($koneksi, "
         SELECT *
         FROM laboratorium_detail
         ORDER BY status = 1 DESC, assemen ASC
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
   // 🟡 PUT (UPDATE / TOGGLE)
   // =============================
   case 'PUT':

      parse_str(file_get_contents("php://input"), $_PUT);

      // 🔥 TOGGLE STATUS MASTER
      if (isset($_GET['toggle_status'])) {

         $id_lab = $_PUT['id_lab'] ?? null;
         $status = $_PUT['status'] ?? 0;

         $update = mysqli_query($koneksi, "
            UPDATE laboratorium_item
            SET 
               urutan = '$urutan',
               nama = '$nama',
               satuan = '$satuan',
               minimum = '$minimum',
               maksimum = '$maksimum'
            WHERE id_item = '$id_item'
         ");

         echo json_encode([
            "status" => $update ? "success" : "error",
            "message" => $update ? "Status diupdate" : mysqli_error($koneksi)
         ]);
         exit;
      }

      // 🔥 UPDATE MASTER
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

      // 🔥 UPDATE PARAMETER
      if (isset($_PUT['id_item'])) {

         $id_item = $_PUT['id_item'];
         $urutan  = $_PUT['urutan'] ?? null;
         $assemen = $_PUT['nama'] ?? null;
         $satuan  = $_PUT['satuan'] ?? null;
         $minimum = $_PUT['minimum'] ?? null;
         $maksimum = $_PUT['maksimum'] ?? null;

         $update = mysqli_query($koneksi, "
      UPDATE laboratorium_item
      SET 
         urutan = '$urutan',
         assemen = '$assemen',
         satuan = '$satuan',
         minimum = '$minimum',
         maksimum = '$maksimum'
      WHERE id = '$id_item'
   ");

         echo json_encode([
            "status" => $update ? "success" : "error",
            "message" => $update ? "Parameter diupdate" : mysqli_error($koneksi)
         ]);
         exit;
      }

      break;

   // =============================
   // 🔴 DELETE (MASTER / PARAMETER)
   // =============================
   case 'DELETE':

      parse_str(file_get_contents("php://input"), $_DELETE);

      // 🔥 DELETE PARAMETER
      if (isset($_GET['id_item'])) {

         $id_item = $_GET['id_item'];

         $delete = mysqli_query($koneksi, "
      DELETE FROM laboratorium_item
      WHERE id = '$id_item'
   ");

         echo json_encode([
            "status" => $delete ? "success" : "error",
            "message" => $delete ? "Parameter dihapus" : mysqli_error($koneksi)
         ]);
         exit;
      }

      // 🔥 DELETE MASTER
      if (isset($_GET['id'])) {

         $id = $_GET['id'];

         // optional: hapus child dulu
         mysqli_query($koneksi, "
            DELETE FROM laboratorium_item 
            WHERE kode_lab = (
               SELECT kode FROM laboratorium_detail WHERE id_lab = '$id'
            )
         ");

         $delete = mysqli_query($koneksi, "
            DELETE FROM laboratorium_detail
            WHERE id_lab = '$id'
         ");

         echo json_encode([
            "status" => $delete ? "success" : "error",
            "message" => $delete ? "Data dihapus" : mysqli_error($koneksi)
         ]);
         exit;
      }

      break;
}
