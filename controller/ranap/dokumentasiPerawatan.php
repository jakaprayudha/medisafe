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
   $tgl   = $_POST['tgl_upload'] ?? date('Y-m-d');

   if (!$rm || !$visit) {
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

   $dir = "../../uploads/perawatan/";
   $newName = uniqid("perawatan_", true) . "." . $ext;

   $pathDB = "uploads/perawatan/" . $newName;
   $pathFS = $dir . $newName;

   if (!move_uploaded_file($file['tmp_name'], $pathFS)) {
      echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
      exit;
   }

   // INSERT DB
   mysqli_query($koneksi, "
      INSERT INTO pasien_dokumen 
      (nomor_rm, visit_ID, jenis_dokumen, foto_path, rilis)
      VALUES ('$rm', '$visit', 'FOTO_PERAWATAN', '$pathDB', '$tgl')
   ");

   echo json_encode(["status" => "success", "message" => "Dokumentasi berhasil disimpan"]);
}



// ======================================================
// GET LIST
// ======================================================
function getData()
{
   global $koneksi;

   $no = $_GET['no'] ?? '';
   $rm = $_GET['rm'] ?? '';

   $q = mysqli_query($koneksi, "
      SELECT * FROM pasien_dokumen
      WHERE visit_ID='$no' 
      AND nomor_rm='$rm'
      AND jenis_dokumen='FOTO_PERAWATAN'
      ORDER BY id_dokumen DESC
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

   $st = $koneksi->prepare("SELECT * FROM pasien_dokumen WHERE id_dokumen=?");
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

   $st = $koneksi->prepare("DELETE FROM pasien_dokumen WHERE id_dokumen=?");
   $st->bind_param("i", $id);

   if ($st->execute()) {
      echo json_encode(["status" => "success", "message" => "Data dihapus"]);
   } else {
      echo json_encode(["status" => "error", "message" => "Gagal menghapus"]);
   }
}
