<?php
require '../../../database/connect.php';

$no   = $_GET['no'] ?? null;
$rm   = $_GET['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter invalid"]);
   exit;
}

$sql = mysqli_query(
   $koneksi,
   "SELECT * FROM ekg_results er 
    INNER JOIN ms_patient mp ON er.nomor_rm = mp.nomor_rm 
    WHERE er.visit_ID='$no' 
    AND mp.nomor_rm='$rm'
    LIMIT 1"
);

if (mysqli_num_rows($sql) == 0) {
   echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
   exit;
}

$data = mysqli_fetch_assoc($sql);

// ================================
//  HITUNG USIA DARI TGL LAHIR
// ================================
$today = new DateTime();
$birth = new DateTime($data['patient_datebirth']);

$age = $birth->diff($today);

$usia = $age->y . " tahun " . $age->m . " bulan " . $age->d . " hari";

// Tambahkan usia ke response
$data['usia'] = $usia;

echo json_encode([
   "status" => "success",
   "data" => $data
]);
