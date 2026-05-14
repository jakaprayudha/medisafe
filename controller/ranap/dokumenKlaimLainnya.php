<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
session_start();

switch ($method) {
   case 'POST':
      if (isset($_GET['id'])) {
         updateTanggal($_GET['id']); // 🔥 EDIT
      } else {
         uploadPerawatan(); // 🔥 INSERT
      }
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
   $judul = $_POST['judul_dokumen'] ?? '';
   $id_customer = $_SESSION['id_customer'] ?? '';
   $tgl   = $_POST['tgl_upload'] ?? date('Y-m-d');

   if (!$visit) {
      echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
      exit;
   }

   if (!isset($_FILES['dokumen_path']) || $_FILES['dokumen_path']['error'] !== UPLOAD_ERR_OK) {
      echo json_encode(["status" => "error", "message" => "File tidak valid"]);
      exit;
   }

   // FILE VALIDATION
   $file = $_FILES['dokumen_path'];
   $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
   $allowed = ['pdf'];

   if (!in_array($ext, $allowed)) {
      echo json_encode(["status" => "error", "message" => "Format harus JPG/PNG"]);
      exit;
   }

   $dir = "../../uploads/lainnya/";
   $newName = uniqid("docsklaim_", true) . "." . $ext;

   $pathDB = "uploads/lainnya/" . $newName;
   $pathFS = $dir . $newName;

   if (!move_uploaded_file($file['tmp_name'], $pathFS)) {
      echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
      exit;
   }

   // INSERT DB
   mysqli_query($koneksi, "
      INSERT INTO pasien_dokumen_klaim_lainnya 
      (visit_ID, id_customer, tanggal, judul_dokumen, dokumen_path)
      VALUES ('$visit', '$id_customer', '$tgl', '$judul', '$pathDB')
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
   $id_customer = $_SESSION['id_customer'] ?? '';

   $q = mysqli_query($koneksi, "SELECT * FROM pasien_dokumen_klaim_lainnya
      WHERE visit_ID='$no'
      AND id_customer='$id_customer'
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

   $st = $koneksi->prepare("SELECT * FROM pasien_dokumen_klaim_lainnya WHERE id_dokumen=?");
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
      echo json_encode([
         "status" => "error",
         "message" => "ID tidak ditemukan"
      ]);
      return;
   }

   // =========================
   // AMBIL DATA FILE
   // =========================
   $st = $koneksi->prepare("
      SELECT dokumen_path
      FROM pasien_dokumen_klaim_lainnya
      WHERE id_dokumen = ?
   ");

   $st->bind_param("i", $id);
   $st->execute();

   $res = $st->get_result();

   if (!$res->num_rows) {

      echo json_encode([
         "status" => "error",
         "message" => "Data tidak ditemukan"
      ]);

      return;
   }

   $data = $res->fetch_assoc();

   // =========================
   // HAPUS FILE FISIK
   // =========================
   if (!empty($data['dokumen_path'])) {

      $filePath = "../../" . $data['dokumen_path'];

      if (file_exists($filePath)) {
         unlink($filePath);
      }
   }

   // =========================
   // HAPUS DATABASE
   // =========================
   $del = $koneksi->prepare("
      DELETE FROM pasien_dokumen_klaim_lainnya
      WHERE id_dokumen = ?
   ");

   $del->bind_param("i", $id);

   if ($del->execute()) {

      echo json_encode([
         "status" => "success",
         "message" => "Data dan file berhasil dihapus"
      ]);
   } else {

      echo json_encode([
         "status" => "error",
         "message" => "Gagal menghapus data"
      ]);
   }
}


// ======================================================
// UPDATE TANGGAL SAJA
// ======================================================
function updateTanggal($id)
{
   global $koneksi;

   $tgl = $_POST['tgl_upload'] ?? null;
   $judul = $_POST['judul_dokumen'] ?? null;

   if (!$tgl) {
      echo json_encode(["status" => "error", "message" => "Tanggal wajib diisi"]);
      exit;
   }

   $st = $koneksi->prepare("
      UPDATE pasien_dokumen_klaim_lainnya 
      SET tanggal = ?, judul_dokumen = ?
      WHERE id_dokumen = ?
   ");

   $st->bind_param("ssi", $tgl, $judul, $id);

   if ($st->execute()) {
      echo json_encode([
         "status" => "success",
         "message" => "Tanggal berhasil diupdate"
      ]);
   } else {
      echo json_encode([
         "status" => "error",
         "message" => "Gagal update"
      ]);
   }
}
