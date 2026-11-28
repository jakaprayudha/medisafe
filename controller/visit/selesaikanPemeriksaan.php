<?php
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
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

// kunjungan wajib milik faskes sesi & belum selesai/batal (dulu bisa memproses kunjungan faskes lain / yang sudah pulang)
$idCustomer = (int) ($_SESSION['id_customer'] ?? 0);
$cek = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$cek->bind_param("si", $visit_ID, $idCustomer);
$cek->execute();
$status = $cek->get_result()->fetch_row()[0] ?? null;
$cek->close();
if ($status === null) {
   http_response_code(404);
   echo json_encode(['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   exit;
}
if (kunjunganSelesai($status) && !bolehBukaKunciSelesai()) {
   echo json_encode(['status' => 'error', 'message' => 'Pemeriksaan kunjungan ini sudah diselesaikan.']);
   exit;
}
if ((int) $status === 99) {
   echo json_encode(['status' => 'error', 'message' => 'Kunjungan ini sudah dibatalkan.']);
   exit;
}

// mulai transaction
$koneksi->begin_transaction();

try {

   // 1. update status pasien visit
   $stmt = $koneksi->prepare("
      UPDATE pasien_visit 
      SET visit_status = 4
      WHERE visit_ID = ? AND id_customer = ?
   ");
   $stmt->bind_param("si", $visit_ID, $idCustomer);
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

      // 4. kosongkan bed (1 = kosong)
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
   require_once __DIR__ . '/../../utility/visitLog.php';
   logKunjungan($koneksi, 'selesai', (string) $visit_ID, kunjunganSelesai($status) ? 'Diselesaikan ulang (buka kunci admin)' : 'Selesaikan pemeriksaan', ['visit_status' => (int) $status], ['visit_status' => 4]);

   echo json_encode([
      'status' => 'success',
      'message' => 'Pasien berhasil dipulangkan & bed dikosongkan'
   ]);
} catch (Exception $e) {

   // rollback
   $koneksi->rollback();
   error_log('[visit/selesaikanPemeriksaan] ' . $e->getMessage());

   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal memproses, silakan coba lagi.'
   ]);
}
