<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? null;      // visit_ID
$rm = $_GET['rm'] ?? null;      // nomor RM

if (!$no || !$rm) {
   echo json_encode([
      "status" => "error",
      "message" => "Parameter kurang"
   ]);
   exit;
}

// ======================= GET HEADER =======================
$sql_header = "
    SELECT * 
    FROM ibu_hamil_header 
    WHERE visit_ID = '$no' AND nomor_rm = '$rm'
    LIMIT 1
";

$res_header = mysqli_query($koneksi, $sql_header);
$header = mysqli_fetch_assoc($res_header);

if (!$header) {
   echo json_encode([
      "status" => "error",
      "message" => "Data tidak ditemukan"
   ]);
   exit;
}

$header_id = $header['id'];

// ======================= GET DETAIL =======================
$sql_detail = "
    SELECT * 
    FROM ibu_hamil_detail 
    WHERE header_id = $header_id
    ORDER BY tanggal ASC
";

$res_detail = mysqli_query($koneksi, $sql_detail);
$detail = [];

while ($row = mysqli_fetch_assoc($res_detail)) {
   $detail[] = $row;
}

// ======================= RESPONSE =======================
echo json_encode([
   "status" => "success",
   "header" => $header,
   "detail" => $detail
], JSON_PRETTY_PRINT);
