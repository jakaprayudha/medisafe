<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$visit_ID = $input['visit_ID'] ?? null;

if (!$visit_ID) {
   echo json_encode([
      'status' => 'error',
      'message' => 'visit_ID tidak ditemukan'
   ]);
   exit;
}

// mulai transaction
$koneksi->begin_transaction();

try {

   // 1. update status visit
   $stmt = $koneksi->prepare("
      UPDATE pasien_visit 
      SET visit_status = 4 
      WHERE visit_ID = ?
   ");
   $stmt->bind_param("s", $visit_ID);
   $stmt->execute();

   // 2. ambil id_bed dari permintaan_ranap
   $getBed = $koneksi->prepare("
      SELECT id_bed 
      FROM permintaan_ranap 
      WHERE visit_ID_inpatient = ?
      LIMIT 1
   ");
   $getBed->bind_param("s", $visit_ID);
   $getBed->execute();
   $result = $getBed->get_result();

   if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $id_bed = $row['id_bed'];

      // 3. update bed jadi kosong (1)
      $updateBed = $koneksi->prepare("
         UPDATE ms_room_bed 
         SET bed_status = 1 
         WHERE id_bed = ?
      ");
      $updateBed->bind_param("i", $id_bed);
      $updateBed->execute();
   }

   // commit
   $koneksi->commit();

   echo json_encode([
      'status' => 'success',
      'message' => 'Pemeriksaan selesai & bed dikosongkan'
   ]);
} catch (Exception $e) {

   // rollback kalau gagal
   $koneksi->rollback();

   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal proses',
      'error' => $e->getMessage()
   ]);
}
