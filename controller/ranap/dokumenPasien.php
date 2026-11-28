<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';
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
   $pilih_jenis   = $_POST['pilih_jenis'] ?? ' ';

   if (!$rm || !$visit) {
      echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
      exit;
   }
   visitGuard($koneksi, 'visit_ID', $visit); // hanya kunjungan faskes sesi

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

   $dir = "../../uploads/dokumen/";
   $newName = uniqid("dokumen_", true) . "." . $ext;

   $pathDB = "uploads/dokumen/" . $newName;
   $pathFS = $dir . $newName;

   if (!move_uploaded_file($file['tmp_name'], $pathFS)) {
      echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
      exit;
   }

   // INSERT DB
   // dulu nilai POST ditempel langsung ke SQL (SQL injection)
   $st = $koneksi->prepare("INSERT INTO pasien_dokumen (nomor_rm, visit_ID, jenis_dokumen, foto_path, rilis) VALUES (?, ?, ?, ?, ?)");
   $st->bind_param('sssss', $rm, $visit, $pilih_jenis, $pathDB, $tgl);
   $st->execute();
   $idDok = (int) $st->insert_id;
   $st->close();
   logBaris($koneksi, 'dokumen', 'pasien_dokumen', 'id_dokumen', $idDok, null, ['label' => 'Dokumen pasien ' . $pilih_jenis, 'visit_ID' => $visit], 'Dokumen pasien diunggah');

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
   WHERE visit_ID = '$no'
   AND nomor_rm = '$rm'
   AND jenis_dokumen <> 'FOTO_PERAWATAN'
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

   // hanya dokumen kunjungan faskes sesi (dulu dokumen faskes mana pun bisa dihapus lewat id)
   $idc = (int) ($_SESSION['id_customer'] ?? 0);
   $dokLama = logAmbilBaris($koneksi, 'pasien_dokumen', 'id_dokumen', $id);
   $st = $koneksi->prepare("DELETE d FROM pasien_dokumen d JOIN pasien_visit pv ON pv.visit_ID = d.visit_ID WHERE d.id_dokumen = ? AND pv.id_customer = ?");
   $st->bind_param("ii", $id, $idc);

   if ($st->execute() && $st->affected_rows > 0) {
      logBaris($koneksi, 'dokumen', 'pasien_dokumen', 'id_dokumen', $id, $dokLama, ['label' => 'Dokumen pasien ' . ($dokLama['jenis_dokumen'] ?? ''), 'visit_ID' => $dokLama['visit_ID'] ?? null], 'Dokumen pasien dihapus');
      echo json_encode(["status" => "success", "message" => "Data dihapus"]);
   } else {
      echo json_encode(["status" => "error", "message" => "Data tidak ditemukan di faskes ini atau gagal dihapus"]);
   }
}
