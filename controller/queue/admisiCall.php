<?php
/**
 * Panggil antrean admisi (module/admisi/counter-call). POST type=call, idAntri, id (counter), nomor, name, loket.
 *
 * Perbaikan: idAntri / counter ditempel langsung ke SQL (SQL injection); tanpa cek login; tidak ada log.
 * Catatan: transaction_queue belum punya kolom faskes, jadi antrean belum terpisah per faskes.
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
$type = (string) ($_POST['type'] ?? '');
$nomor = (string) ($_POST['nomor'] ?? '');
$loket = (string) ($_POST['loket'] ?? '');
$name = (string) ($_POST['name'] ?? '');
$id = (string) ($_POST['id'] ?? '');
$idAntri = (string) ($_POST['idAntri'] ?? '');

/* =========================
   PANGGIL BERIKUTNYA
========================= */
if ($type === 'call') {
   $st = $koneksi->prepare("SELECT * FROM `transaction_queue` WHERE `id_queue` = ? LIMIT 1");
   $st->bind_param('s', $idAntri);
   $st->execute();
   $result = $st->get_result()->fetch_assoc();
   $st->close();

   if ($result && $result['counter'] === null) {
      $st = $koneksi->prepare("UPDATE `transaction_queue` SET status = 'dipanggil', counter = ? WHERE `id_queue` = ?");
      $st->bind_param('ss', $id, $idAntri);
      $st->execute();
      $st->close();
   }
   callAntrian([
      "rs_id" => $kdRumahSakit,
      "target_role" => "display-admisi",
      "idantrian" => $idAntri,
      "type" => $name,
      "nomor"  => $nomor,
      "loket" => $loket
   ]);
   if ($result) {
      $ulang = $result['counter'] !== null;
      logAktivitas($koneksi, 'antrean', 'panggil', ['tipe' => 'antrean_admisi', 'id' => $idAntri, 'label' => trim(($result['no_antrian'] ?? $nomor) . ' · ' . ($result['nama_pasien'] ?? $name), ' ·')],
         ($ulang ? 'Panggil ulang' : 'Dipanggil') . ' ke loket ' . ($ulang ? $result['counter'] : $id),
         $ulang ? null : ['status' => $result['status'], 'counter' => null], $ulang ? null : ['status' => 'dipanggil', 'counter' => $id]);
   }
   echo json_encode(['status' => 'success']);
}
