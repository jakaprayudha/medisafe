<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? null;
$rm = $_GET['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

$q = mysqli_query($koneksi, "
    SELECT * FROM partograf_header 
    WHERE visit_ID='$no' AND nomor_rm='$rm'
");

$header = mysqli_fetch_assoc($q);

if (!$header) {
   echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   exit;
}

$hid = $header['id'];

$q2 = mysqli_query($koneksi, "
    SELECT * FROM partograf_detail 
    WHERE header_id='$hid'
");

$detail = [];
while ($r = mysqli_fetch_assoc($q2)) {
   $detail[] = $r;
}

echo json_encode([
   "status" => "success",
   "header" => $header,
   "detail" => $detail
]);
