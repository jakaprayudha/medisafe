<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$id_patient = $_POST['id_patient'] ?? '';
$id_doctor = $_POST['id_doctor'] ?? '';
$ranap_date = $_POST['ranap_date'] ?? '';
$ranap_time = $_POST['ranap_time'] ?? '';
$visit_ID_inpatient = $_POST['visit_ID_inpatient'] ?? '';
$diagnosa_awal = $_POST['diagnosa_awal'] ?? '';
$ranap_notes = $_POST['ranap_notes'] ?? '';
$created_at = date('Y-m-d H:i:s');
$ranap_booking = 0;

if (!$id_patient || !$id_doctor || !$ranap_date || !$ranap_time || !$visit_ID_inpatient) {
   echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
   exit;
}

$stmt = $koneksi->prepare("INSERT INTO permintaan_ranap 
(id_patient, id_doctor, ranap_date, ranap_time, visit_ID_inpatient, diagnosa_awal, ranap_notes, ranap_booking, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssis", $id_patient, $id_doctor, $ranap_date, $ranap_time, $visit_ID_inpatient, $diagnosa_awal, $ranap_notes, $ranap_booking, $created_at);

if ($stmt->execute()) {
   // Ambil nama dokter untuk ditampilkan di form
   $getDoctor = mysqli_query($koneksi, "SELECT doctor_name FROM ms_doctor WHERE id_doctor = '$id_doctor'");
   $doctor = mysqli_fetch_assoc($getDoctor);

   echo json_encode([
      "status" => "success",
      "message" => "Permintaan Rawat Inap berhasil disimpan.",
      "data" => [
         "doctor_name" => $doctor['doctor_name'] ?? 'Tidak diketahui',
         "ranap_date" => $ranap_date,
         "ranap_time" => $ranap_time,
         "diagnosa_awal" => $diagnosa_awal,
         "ranap_notes" => $ranap_notes
      ]
   ]);
} else {
   echo json_encode(["status" => "error", "message" => "Gagal menyimpan data."]);
}
$stmt->close();
