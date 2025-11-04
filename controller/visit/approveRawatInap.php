<?php
require_once '../../database/connect.php';
header('Content-Type: application/json');
session_start();

$input = $_POST; // data dikirim dari form modal
$id_ranap   = $input['id_ranap'] ?? null;
$id_patient = $input['id_patient'] ?? null;
$id_doctor  = $input['id_doctor'] ?? null;
$id_poli    = $input['id_poli'] ?? 99; // default poli rawat inap
$user       = $_SESSION['fullname'] ?? 'System';

// 🔹 Validasi dasar
if (!$id_ranap || !$id_patient) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
   exit;
}

// 🔹 Ambil data permintaan rawat inap
$q = $koneksi->prepare("SELECT * FROM permintaan_ranap WHERE id_ranap = ?");
$q->bind_param("i", $id_ranap);
$q->execute();
$d = $q->get_result()->fetch_assoc();

if (!$d) {
   echo json_encode(['status' => 'error', 'message' => 'Data permintaan tidak ditemukan.']);
   exit;
}

// 🔹 Generate kode unik
$visit_ID = "VIS-" . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$timeranap = date('H:i:s');
$source = "Rawat Inap";

// 🔹 Siapkan query insert (9 kolom = 9 tanda ?)
$stmt = $koneksi->prepare("
  INSERT INTO pasien_visit 
  (id_patient, visit_ID, visit_date, visit_time, id_doctor, id_poli, source_hub, created_user, visit_notes)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// 🔹 Bind parameter (9 data sesuai urutan kolom)
$stmt->bind_param(
   "sssssssss",
   $d['id_patient'],
   $visit_ID,
   $d['ranap_date'],
   $timeranap,
   $d['id_doctor'],
   $id_poli,
   $source,
   $user,
   $d['diagnosa_awal']
);

if ($stmt->execute()) {
   // 🔹 Update permintaan_ranap jadi approved
   $update = $koneksi->prepare("UPDATE permintaan_ranap SET ranap_booking = 1 WHERE id_ranap = ?");
   $update->bind_param("i", $id_ranap);
   $update->execute();

   echo json_encode([
      'status' => 'success',
      'message' => 'Permintaan rawat inap disetujui dan data kunjungan telah dibuat.'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal menyimpan data kunjungan.',
      'error' => $stmt->error
   ]);
}
