<?php
require_once '../../database/connect.php';
header('Content-Type: application/json');
session_start();

$input = $_POST;

// === Ambil data input ===
$id_ranap   = $input['id_ranap'] ?? null;
$id_patient = $input['id_patient'] ?? null;
$id_room    = $input['room_name'] ?? null;
$id_bed     = $input['bed_name'] ?? null;
$user       = $_SESSION['fullname'] ?? ($input['user'] ?? 'System');

// === Validasi dasar ===
if (!$id_ranap || !$id_patient || !$id_room || !$id_bed) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
   exit;
}

// === Ambil data permintaan ranap ===
$q = $koneksi->prepare("SELECT * FROM permintaan_ranap WHERE id_ranap = ?");
$q->bind_param("i", $id_ranap);
$q->execute();
$d = $q->get_result()->fetch_assoc();

if (!$d) {
   echo json_encode(['status' => 'error', 'message' => 'Data permintaan tidak ditemukan.']);
   exit;
}

// === Generate visit ID unik ===
$visit_ID = "VIS-" . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$timeranap = date('H:i:s');
$source = "Rawat Inap";
$id_poli = 99; // default poli rawat inap

// === Insert ke pasien_visit ===
$stmt = $koneksi->prepare("
   INSERT INTO pasien_visit 
   (id_patient, visit_ID, visit_date, visit_time, id_doctor, id_poli, source_hub, created_user, visit_notes)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

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

// === Jalankan proses insert utama ===
if ($stmt->execute()) {

   // === Step 1: Update permintaan_ranap ===
   $update = $koneksi->prepare("
      UPDATE permintaan_ranap 
      SET ranap_booking = 1, id_room = ?, id_bed = ?, visit_ID_outpatient = ?
      WHERE id_ranap = ?
   ");
   $update->bind_param("iisi", $id_room, $id_bed, $visit_ID, $id_ranap);

   if ($update->execute()) {

      // === Step 2: Update status bed (0 = terpakai) ===
      $updateBed = $koneksi->prepare("UPDATE ms_room_bed SET bed_status = '0' WHERE id_bed = ?");
      $updateBed->bind_param("i", $id_bed);
      $updateBed->execute();

      echo json_encode([
         'status' => 'success',
         'message' => 'Permintaan rawat inap disetujui dan data kunjungan berhasil dibuat.',
         'visit_ID' => $visit_ID
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal memperbarui data permintaan ranap.',
         'error' => $update->error
      ]);
   }
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal menyimpan data kunjungan pasien.',
      'error' => $stmt->error
   ]);
}
