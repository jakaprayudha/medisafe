<?php
require '../../database/connect.php';

header("Content-Type: application/json");
session_start();

// 🔥 AMBIL SESSION
$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 19;

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
$stmt = $koneksi->prepare("SELECT 
   t.*, 
   v.keluhan_penyerta,
   v.riwayat_penyakit_sekarang,
   v.riwayat_pengobatan,
   v.riwayat_penyakit_pribadi,
   v.riwayat_alergi,
   v.diagnosa,
   v.diagnosa_utama,
   v.diagnosa_sekunder,
   v.signature_path,
   v.id_doctor,
   u.signature_user,

   i.code,
   i.icd10 as icd10

FROM pasien_triase t

LEFT JOIN pasien_visit v 
   ON t.visit_ID = v.visit_ID 
   AND v.id_customer = ?

LEFT JOIN icd_10 i 
   ON i.code = v.diagnosa

LEFT JOIN ms_users u
   ON u.fullname = v.id_doctor

WHERE t.visit_ID = ?
ORDER BY t.id_triase DESC
LIMIT 1
");

$stmt->bind_param("is", $id_customer, $no);
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
