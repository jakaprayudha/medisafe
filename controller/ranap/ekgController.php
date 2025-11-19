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
   $pilih_ekg = $_POST['pilih_ekg'] ?? '';
   $dokter = $_POST['dokter'] ?? '';
   $interpretasi = $_POST['interpretasi'] ?? '';

   if (!$rm || !$visit || !$pilih_ekg) {
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
   $dir = "../../uploads/ekg/";
   if (!is_dir($dir)) mkdir($dir, 0777, true);

   $newName = uniqid("ekg_", true) . "." . $ext;

   $pathDB = "uploads/ekg/" . $newName;
   $pathFS = $dir . $newName;

   if (!move_uploaded_file($file['tmp_name'], $pathFS)) {
      echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
      exit;
   }

   // ============================
   // EKG2 = UPDATE, NOT INSERT
   // ============================
   if ($pilih_ekg == "2") {

      // cek apakah sudah ada EKG1 untuk visit + RM ini
      $cek = mysqli_query($koneksi, "
         SELECT id_ekg FROM ekg_results 
         WHERE nomor_rm='$rm' AND visit_ID='$visit'
         LIMIT 1
      ");

      if (mysqli_num_rows($cek) == 0) {
         echo json_encode([
            "status" => "error",
            "message" => "Belum ada EKG1. Tidak dapat upload EKG2."
         ]);
         exit;
      }

      $d = mysqli_fetch_assoc($cek);
      $id_ekg = $d['id_ekg'];

      // UPDATE kolom ekg2
      $update = mysqli_query($koneksi, "
         UPDATE ekg_results 
         SET ekg2='$pathDB'
         WHERE id_ekg='$id_ekg'
      ");

      if ($update) {
         echo json_encode(["status" => "success", "message" => "EKG2 berhasil ditambahkan"]);
      } else {
         echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
      }

      exit;
   }

   // ============================
   // EKG1 = INSERT BARU
   // ============================
   $query = "INSERT INTO ekg_results
         (nomor_rm, visit_ID, tanggal_pemeriksaan, ekg1, interpretasi, dokter)
      VALUES 
         ('$rm', '$visit', '$tanggal_pemeriksaan', '$pathDB', '$interpretasi', '$dokter')
   ";

   if (mysqli_query($koneksi, $query)) {
      echo json_encode(["status" => "success", "message" => "EKG1 berhasil disimpan"]);
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

   $q = mysqli_query($koneksi, "SELECT * FROM ekg_results
   WHERE visit_ID = '$no'
   AND nomor_rm = '$rm'
   ORDER BY id_ekg DESC
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

   $st = $koneksi->prepare("SELECT * FROM ekg_results WHERE id_ekg=?");
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

   $st = $koneksi->prepare("DELETE FROM ekg_results WHERE id_ekg=?");
   $st->bind_param("i", $id);

   if ($st->execute()) {
      echo json_encode(["status" => "success", "message" => "Data dihapus"]);
   } else {
      echo json_encode(["status" => "error", "message" => "Gagal menghapus"]);
   }
}
