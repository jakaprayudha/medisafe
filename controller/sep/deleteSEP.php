<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$no = $_POST['no_visit'] ?? null;
$rm = $_POST['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
   exit;
}

$q = mysqli_query($koneksi, "
   SELECT sep_file FROM pasien_sep 
   WHERE visit_ID='$no' AND nomor_rm='$rm' LIMIT 1
");

$d = mysqli_fetch_assoc($q);

if (!$d) {
   echo json_encode(["status" => "error", "message" => "Data SEP tidak ditemukan"]);
   exit;
}

$filePath = $_SERVER['DOCUMENT_ROOT'] . "/medisafe/uploads/sep/" . $d['sep_file'];

if (file_exists($filePath)) {
   unlink($filePath);
}

mysqli_query($koneksi, "
   DELETE FROM pasien_sep 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

echo json_encode(["status" => "success", "message" => "File SEP berhasil dihapus"]);
