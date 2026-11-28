<?php
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/visitLog.php';
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

// kunjungan wajib milik faskes sesi (dulu permintaan ranap bisa dibuat untuk kunjungan faskes mana pun)
$kunjRanap = visitGuard($koneksi, 'visit_ID', $visit_ID_inpatient);

$stmt = $koneksi->prepare("INSERT INTO permintaan_ranap 
(id_patient, id_doctor, ranap_date, ranap_time, visit_ID_inpatient, diagnosa_awal, ranap_notes, ranap_booking, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssis", $id_patient, $id_doctor, $ranap_date, $ranap_time, $visit_ID_inpatient, $diagnosa_awal, $ranap_notes, $ranap_booking, $created_at);

if ($stmt->execute()) {
   // Ambil nama dokter untuk ditampilkan di form
   $idRanapBaru = (int) $stmt->insert_id;
   // dulu id_doctor ditempel langsung ke SQL
   $getDoctor = $koneksi->prepare("SELECT doctor_name FROM ms_doctor WHERE id_doctor = ? LIMIT 1");
   $getDoctor->bind_param('s', $id_doctor);
   $getDoctor->execute();
   $doctor = $getDoctor->get_result()->fetch_assoc();
   $getDoctor->close();
   logKunjungan($koneksi, 'ranap_minta', $kunjRanap, 'Permintaan rawat inap dibuat · DPJP ' . ($doctor['doctor_name'] ?? $id_doctor) . ($diagnosa_awal !== '' ? ' · diagnosa awal: ' . $diagnosa_awal : ''),
      null, ['id_ranap' => $idRanapBaru, 'ranap_date' => $ranap_date, 'ranap_time' => $ranap_time, 'diagnosa_awal' => $diagnosa_awal, 'ranap_notes' => $ranap_notes], 'Rawat Inap');

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
