<?php
include '../../database/connect.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

/* =========================
   ANTRIAN YANG DIPANGGIL
========================= */
$called = mysqli_fetch_assoc(mysqli_query($koneksi, "
  SELECT 
    pv.visit_antrian,
    mp.poli_name,
    md.doctor_name,
    p.patient_name
  FROM pasien_visit pv
  INNER JOIN ms_poli mp ON mp.id_poli = pv.id_poli
  INNER JOIN ms_doctor md ON md.id_doctor = pv.id_doctor
  INNER JOIN ms_patient p ON p.id_patient = pv.id_patient
  WHERE pv.visit_date = '$today'
    AND pv.status_antrian = '1'
  ORDER BY pv.created_at DESC
  LIMIT 1
"));

/* =========================
   ANTRIAN MENUNGGU
========================= */
$waiting = [];
$q = mysqli_query($koneksi, "
  SELECT 
    mp.poli_name,
    pv.visit_antrian,
    p.patient_name
  FROM pasien_visit pv
  INNER JOIN ms_poli mp ON mp.id_poli = pv.id_poli
  INNER JOIN ms_patient p ON p.id_patient = pv.id_patient
  WHERE pv.visit_date = '$today'
    AND pv.status_antrian = '0'
  ORDER BY pv.created_at ASC
  LIMIT 10
");

while ($row = mysqli_fetch_assoc($q)) {
  $waiting[] = $row;
}

echo json_encode([
  'called'  => $called,
  'waiting'=> $waiting
]);