<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter kurang"]);
   exit;
}

$q = mysqli_query($koneksi, "SELECT *
    FROM usg_results usg INNER JOIN pasien_visit pv ON usg.visit_ID = pv.visit_ID INNER JOIN ms_patient mp ON usg.nomor_rm = mp.nomor_rm
    WHERE usg.visit_ID = '$no' AND mp.nomor_rm = '$rm'
    LIMIT 1
");

if (!$q || mysqli_num_rows($q) == 0) {
   echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   exit;
}

$data = mysqli_fetch_assoc($q);

echo json_encode([
   "status" => "success",
   "data" => $data
]);
