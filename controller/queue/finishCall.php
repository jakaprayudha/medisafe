<?php
/**
 * Selesaikan antrean admisi yang sedang dipanggil. POST idAntri.
 * Perbaikan: idAntri ditempel langsung ke SQL (SQL injection); tanpa cek login; tidak ada log.
 */
header('Content-Type: application/json');
include '../../database/connect.php';
require_once __DIR__ . '/../socket/sendSocket.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);

$kdRumahSakit = (int) ($_SESSION['id_customer'] ?? 0);
if ($kdRumahSakit <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}
$idAntri = (string) ($_POST['idAntri'] ?? '');

$lama = logAmbilBaris($koneksi, 'transaction_queue', 'id_queue', $idAntri);
$st = $koneksi->prepare("UPDATE transaction_queue SET status = 'selesai' WHERE status = 'dipanggil' AND id_queue = ?");
$ok = $st && $st->bind_param('s', $idAntri) && $st->execute();
if ($ok) {
   if ($st->affected_rows > 0 && $lama) {
      logAktivitas($koneksi, 'antrean', 'selesai', ['tipe' => 'antrean_admisi', 'id' => $idAntri, 'label' => trim(($lama['no_antrian'] ?? '') . ' · ' . ($lama['nama_pasien'] ?? ''), ' ·')],
         'Antrean admisi selesai', ['status' => $lama['status']], ['status' => 'selesai']);
   }
   trigger([
      "rs_id" => $kdRumahSakit,
      "target_role" => "display-admisi",
   ]);

   echo json_encode(['status' => 'success']);
} else {
   error_log('[finishCall] ' . ($st ? $st->error : $koneksi->error));
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal menyelesaikan antrean'
   ]);
}
