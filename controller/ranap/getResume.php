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
$q = mysqli_query($koneksi, "SELECT v.*,
      p.patient_name AS nama_pasien,
      p.patient_gender AS jk,
      v.visit_date AS tanggal_visit,
      r.room_name,
      b.bed_name,
      p.nomor_rm,
      CONCAT(
         FLOOR(DATEDIFF(CURDATE(), p.patient_datebirth) / 365), ' Tahun'
      ) AS usia,
      d.doctor_name
   FROM pasien_visit v
   LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN ms_doctor d ON d.id_doctor = v.id_doctor
   LEFT JOIN permintaan_ranap pr ON pr.visit_ID_inpatient = v.visit_ID
   LEFT JOIN ms_room r ON r.id_room = pr.id_room
   LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
   WHERE v.visit_ID = '$no' AND p.nomor_rm = '$rm'
");

$pasien = mysqli_fetch_assoc($q);

/* ==========================
   GET DATA RESUME MEDIS (Jika ada)
============================= */
$q2 = mysqli_query($koneksi, "SELECT * FROM resume_medis 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

$resume = mysqli_fetch_assoc($q2);

echo json_encode([
   "status" => "success",
   "pasien" => $pasien,
   "resume" => $resume
]);
