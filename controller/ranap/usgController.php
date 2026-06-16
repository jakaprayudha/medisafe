<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      uploadPerawatan();
      break;

   case 'GET':
      isset($_GET['id']) ? getID($_GET['id']) : getData();
      break;

   case 'DELETE':
      deleteData();
      break;

   default:
      echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
}



// ======================================================
// CREATE — UPLOAD FOTO PERAWATAN
// ======================================================
function uploadPerawatan()
{
   global $koneksi;

   $rm    = $_POST['nomor_rm'] ?? '';
   $visit = $_POST['visit_ID'] ?? '';
   $tanggal_pemeriksaan = $_POST['tanggal_pemeriksaan'] ?? date('Y-m-d');
   $pilih_usg = $_POST['pilih_usg'] ?? '';
   $dokter = $_POST['dokter'] ?? '';
   $interpretasi = $_POST['interpretasi'] ?? '';
   $usia_kandungan = $_POST['usia_kandungan'] ?? '';

   if (!$rm || !$visit || !$pilih_usg) {
      echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
      exit;
   }

   if (!isset($_FILES['foto_path']) || $_FILES['foto_path']['error'] !== UPLOAD_ERR_OK) {
      echo json_encode(["status" => "error", "message" => "File tidak valid"]);
      exit;
   }

   // FILE VALIDATION
   $file = $_FILES['foto_path'];
   $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
   $allowed = ['jpg', 'jpeg', 'png'];

   if (!in_array($ext, $allowed)) {
      echo json_encode(["status" => "error", "message" => "Format harus JPG/PNG"]);
      exit;
   }

   // Upload folder
   $dir = "../../uploads/usg/";
   if (!is_dir($dir)) mkdir($dir, 0777, true);

   $newName = uniqid("USG_", true) . "." . $ext;

   $pathDB = "uploads/usg/" . $newName;
   $pathFS = $dir . $newName;

   if (!move_uploaded_file($file['tmp_name'], $pathFS)) {
      echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
      exit;
   }

   // ============================
   // USG2 = UPDATE, NOT INSERT
   // ============================
   if ($pilih_usg == "2") {

      // cek apakah sudah ada USG1 untuk visit + RM ini
      $cek = mysqli_query($koneksi, "
         SELECT id_usg FROM usg_results 
         WHERE nomor_rm='$rm' AND visit_ID='$visit'
         LIMIT 1
      ");

      if (mysqli_num_rows($cek) == 0) {
         echo json_encode([
            "status" => "error",
            "message" => "Belum ada USG1. Tidak dapat upload USG2."
         ]);
         exit;
      }

      $d = mysqli_fetch_assoc($cek);
      $id_usg = $d['id_usg'];

      // UPDATE kolom USG2
      $update = mysqli_query($koneksi, "
         UPDATE usg_results 
         SET usg2='$pathDB'
         WHERE id_usg='$id_usg'
      ");

      if ($update) {
         echo json_encode(["status" => "success", "message" => "USG2 berhasil ditambahkan"]);
      } else {
         echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
      }

      exit;
   } else if ($pilih_usg == "3") {

      // cek apakah sudah ada USG1 untuk visit + RM ini
      $cek = mysqli_query($koneksi, "
         SELECT id_usg FROM usg_results 
         WHERE nomor_rm='$rm' AND visit_ID='$visit'
         LIMIT 1
      ");

      if (mysqli_num_rows($cek) == 0) {
         echo json_encode([
            "status" => "error",
            "message" => "Belum ada USG1. Tidak dapat upload USG2."
         ]);
         exit;
      }

      $d = mysqli_fetch_assoc($cek);
      $id_usg = $d['id_usg'];

      // UPDATE kolom USG2
      $update = mysqli_query($koneksi, "
         UPDATE usg_results 
         SET usg3='$pathDB'
         WHERE id_usg='$id_usg'
      ");

      if ($update) {
         echo json_encode(["status" => "success", "message" => "USG3 berhasil ditambahkan"]);
      } else {
         echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
      }

      exit;
   }

   // ============================
   // USG1 = INSERT BARU
   // ============================
   $query = "INSERT INTO usg_results
         (nomor_rm, visit_ID, usia_kandungan, tanggal_pemeriksaan, usg1, interpretasi, dokter)
      VALUES 
         ('$rm', '$visit', '$usia_kandungan', '$tanggal_pemeriksaan', '$pathDB', '$interpretasi', '$dokter')
   ";

   if (mysqli_query($koneksi, $query)) {
      echo json_encode(["status" => "success", "message" => "USG1 berhasil disimpan"]);
   } else {
      echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
   }
}



// ======================================================
// GET LIST
// ======================================================
function getData()
{
   global $koneksi;

   $no = $_GET['no'] ?? '';
   $rm = $_GET['rm'] ?? '';

   $q = mysqli_query($koneksi, "SELECT * FROM usg_results
   WHERE visit_ID = '$no'
   AND nomor_rm = '$rm'
   ORDER BY id_usg DESC
");

   echo json_encode([
      "status" => "success",
      "data" => mysqli_fetch_all($q, MYSQLI_ASSOC)
   ]);
}



// ======================================================
// GET BY ID
// ======================================================
function getID($id)
{
   global $koneksi;

   $st = $koneksi->prepare("SELECT * FROM usg_results WHERE id_usg=?");
   $st->bind_param("i", $id);
   $st->execute();

   $res = $st->get_result();

   if ($res->num_rows) {
      echo json_encode(["status" => "success", "data" => $res->fetch_assoc()]);
   } else {
      echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   }
}




// ======================================================
// DELETE
// ======================================================
function deleteData()
{
   global $koneksi;

   $id = $_GET['id'] ?? '';
   if (!$id) {
      echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
      return;
   }

   $st = $koneksi->prepare("DELETE FROM usg_results WHERE id_usg=?");
   $st->bind_param("i", $id);

   if ($st->execute()) {
      echo json_encode(["status" => "success", "message" => "Data dihapus"]);
   } else {
      echo json_encode(["status" => "error", "message" => "Gagal menghapus"]);
   }
}
