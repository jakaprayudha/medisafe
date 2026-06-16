<?php
require '../../database/connect.php';
header("Content-Type: application/json");

$no = $_GET['no'] ?? null;
$rm = $_GET['rm'] ?? null;

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

/* ==========================
   GET IDENTITAS PASIEN + DOKTER
============================= */
$q = mysqli_query($koneksi, "SELECT 
      p.patient_name AS nama_pasien,
      p.patient_gender AS jk,
      CONCAT(
         FLOOR(DATEDIFF(CURDATE(), p.patient_datebirth) / 365), ' Tahun'
      ) AS usia,
      d.doctor_name
   FROM pasien_visit v
   LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN ms_doctor d ON d.id_doctor = v.id_doctor
   WHERE v.visit_ID = '$no' AND p.nomor_rm = '$rm'
");

$pasien = mysqli_fetch_assoc($q);

/* ==========================
   GET DATA RAWAT INAP (Jika ada)
============================= */
$q2 = mysqli_query($koneksi, "
   SELECT * FROM visit_ranap 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

$inap = mysqli_fetch_assoc($q2);

echo json_encode([
   "status" => "success",
   "pasien" => $pasien,
   "inap" => $inap
]);
