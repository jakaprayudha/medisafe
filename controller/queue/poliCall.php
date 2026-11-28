<?php
/**
 * Panggil pasien ke ruang periksa (module/nurse/pemeriksaan, pemeriksaan-rawat-inap). POST JSON { visit_ID }.
 * Pasien yang sebelumnya sedang dipanggil (status_antrian 1) dikembalikan ke 0 dan dianggap sedang diperiksa (visit_status 1).
 *
 * Perbaikan: visit_ID ditempel langsung ke SQL (SQL injection); reset panggilan sebelumnya berlaku untuk SEMUA faskes
 * (pasien yang sedang dipanggil di faskes lain ikut berubah status); kunjungan faskes lain / batal bisa dipanggil;
 * tidak ada log.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitLog.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$idc = (int) ($_SESSION['id_customer'] ?? 0);

$data = json_decode(file_get_contents('php://input'), true);
$visitID = trim((string) ($data['visit_ID'] ?? ''));

if ($visitID === '' || $idc <= 0) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid visit']);
  exit;
}

$kunj = logKunjunganSnapshot($koneksi, 'visit_ID', $visitID, $idc);
if (!$kunj) {
  http_response_code(404);
  echo json_encode(['status' => 'error', 'message' => 'Kunjungan tidak ditemukan di faskes ini.']);
  exit;
}
if ((int) $kunj['visit_status'] === 99) {
  echo json_encode(['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
  exit;
}

/* reset panggilan sebelumnya (hanya faskes ini) */
$st = $koneksi->prepare("UPDATE pasien_visit SET status_antrian = '0', visit_status = '1'
  WHERE visit_date = ? AND status_antrian = '1' AND id_customer = ?");
$st->bind_param('si', $today, $idc);
$st->execute();
$st->close();

/* set panggilan baru */
$st = $koneksi->prepare("UPDATE pasien_visit SET status_antrian = '1' WHERE visit_ID = ? AND id_customer = ?");
$st->bind_param('si', $visitID, $idc);
$st->execute();
$st->close();

logKunjungan($koneksi, 'panggil', $kunj, 'Dipanggil ke ruang periksa (perawat)', ['status_antrian' => 0], ['status_antrian' => 1]);

echo json_encode(['status' => 'success']);
