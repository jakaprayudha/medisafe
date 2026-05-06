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

   $date = date('Y-m-d H:i:s');

   // 1. update status pasien visit
   $stmt = $koneksi->prepare("
      UPDATE pasien_visit 
      SET visit_status = 4, visit_out = ? 
      WHERE visit_ID = ?
   ");
   $stmt->bind_param("ss", $date, $visit_ID);
   $stmt->execute();

   // 2. update status ranap -> pulang
   $stmtranap = $koneksi->prepare("
      UPDATE permintaan_ranap 
      SET status = 'pulang'
      WHERE visit_ID_inpatient = ?
   ");
   $stmtranap->bind_param("s", $visit_ID);
   $stmtranap->execute();

   // 3. ambil id_bed
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

      // 4. kosongkan bed
      $updateBed = $koneksi->prepare("
         UPDATE ms_room_bed 
         SET bed_status = 0 
         WHERE id_bed = ?
      ");
      $updateBed->bind_param("i", $id_bed);
      $updateBed->execute();
   }

   // commit
   $koneksi->commit();

   echo json_encode([
      'status' => 'success',
      'message' => 'Pasien berhasil dipulangkan & bed dikosongkan'
   ]);
} catch (Exception $e) {

   // rollback
   $koneksi->rollback();

   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal proses',
      'error' => $e->getMessage()
   ]);
}
