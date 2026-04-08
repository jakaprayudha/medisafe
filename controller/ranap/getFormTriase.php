<?php
require '../../database/connect.php';

header("Content-Type: application/json");
session_start();

// 🔥 AMBIL SESSION
// $id_customer = $_SESSION['id_customer'] ?? null;
$id_customer = 19;

if (!$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);
   exit;
}

// 🔥 PARAMETER
$no = $_GET['no'] ?? null; // visit_ID

if (!$no) {
   echo json_encode([
      "status" => "error",
      "message" => "Parameter visit_ID tidak ada"
   ]);
   exit;
}

/* ================================================
   GET DATA TRIASE SAJA
================================================= */
$stmt = $koneksi->prepare("SELECT * FROM pasien_triase LEFT JOIN pasien_visit ON pasien_triase.visit_ID = pasien_visit.visit_ID 
LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa
   WHERE pasien_visit.visit_ID = ? AND pasien_visit.id_customer = ?
   ORDER BY pasien_triase.id_triase DESC
   LIMIT 1
");

$stmt->bind_param("ss", $no, $id_customer);
$stmt->execute();

$result = $stmt->get_result();
$triase = $result->fetch_assoc();

$stmt->close();

/* ================================================
   RESPONSE
================================================= */
echo json_encode([
   "status" => "success",
   "data" => $triase ?? null
]);
