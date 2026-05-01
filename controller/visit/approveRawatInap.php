<?php
require_once '../../database/connect.php';
header('Content-Type: application/json');

$input = $_POST;

// === Ambil data input ===
$id_ranap   = $input['id_ranap'] ?? null;
$id_patient = $input['id_patient'] ?? null;
$id_room    = $input['room_name'] ?? null;
$id_bed     = $input['bed_name'] ?? null;
$user       = $_SESSION['fullname'] ?? ($input['user'] ?? 'System');

// === Validasi dasar ===
// if (!$id_ranap || !$id_patient || !$id_room || !$id_bed) {
//    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
//    exit;
// }

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
$visit_ID = $d['visit_ID_inpatient'];
$status = 1;
$stmt = $koneksi->prepare("UPDATE pasien_visit SET
   status_rawatinap = ?
   WHERE visit_ID = ?
");

$stmt->bind_param(
   "is",
   $status,
   $visit_ID
);
// === Jalankan proses insert utama ===
if ($stmt->execute()) {

   // === Step 1: Update permintaan_ranap ===
   $update = $koneksi->prepare("
      UPDATE permintaan_ranap 
      SET ranap_booking = 1, id_room = ?, id_bed = ?, status = 'aktif'
      WHERE id_ranap = ?
   ");
   $update->bind_param("iii", $id_room, $id_bed, $id_ranap);

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
