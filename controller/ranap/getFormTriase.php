<?php
require '../../database/connect.php';
header("Content-Type: application/json");

$no = $_GET['no'] ?? null;   // visit_ID
$rm = $_GET['rm'] ?? null;   // nomor RM

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

/* ================================================
   1. GET IDENTITAS PASIEN + DOKTER IGD
================================================= */
$q = mysqli_query($koneksi, "
   SELECT 
      p.id_patient,
      p.nomor_rm,
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

/* ================================================
   2. GET DATA TRIASE PASIEN
================================================= */
$q_triase = mysqli_query($koneksi, "SELECT 
      id_triase,
      tanggal_masuk,
      jam_masuk,
      keluhan_utama,

      tekanan_darah,
      nadi,
      rr,
      suhu,
      spo2,

      gcs_e,
      gcs_v,
      gcs_m,
      gcs_total,

      skala_nyeri,
      triase,
      referensi_triase,
      catatan,

      created_at,
      updated_at
   FROM pasien_triase
   WHERE visit_ID = '$no' AND nomor_rm = '$rm'
   ORDER BY id_triase DESC
   LIMIT 1
");

$triase = mysqli_fetch_assoc($q_triase);

/* ================================================
   3. FINAL OUTPUT JSON
================================================= */
echo json_encode([
   "status" => "success",
   "pasien" => $pasien,
   "triase" => $triase
]);
