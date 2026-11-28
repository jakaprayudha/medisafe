<?php
/**
 * Jadwal praktik dokter (module/admin/doctor_details): GET ?no=doctor_number, POST tambah, POST + _method=DELETE id_schedule.
 *
 * Perbaikan: doctor_number ditempel langsung (SQL injection); jadwal dokter faskes lain bisa dibaca/ditambah/dihapus;
 * tidak ada log.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);
header('Content-Type: application/json');

function djBalas(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d);
   exit;
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if ($idc <= 0) djBalas(['success' => false, 'message' => 'Session tidak valid / expired'], 401);

function djDokter(mysqli $k, string $no, int $idc): ?array
{
   $st = $k->prepare("SELECT id_doctor, doctor_name FROM ms_doctor WHERE doctor_number = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET':
      if (!isset($_GET['no'])) djBalas(['success' => false, 'message' => 'No doctor_number']);
      $dok = djDokter($koneksi, (string) $_GET['no'], $idc);
      if (!$dok) djBalas(['success' => true, 'data' => []]);
      $stmt = $koneksi->prepare("SELECT * FROM ms_doctor_schedule WHERE id_doctor = ?");
      $stmt->bind_param("s", $dok['id_doctor']);
      $stmt->execute();
      djBalas(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
      break;

   case 'POST': // Create Jadwal
      $dok = djDokter($koneksi, (string) ($_POST['doctor_number'] ?? ''), $idc);
      if (!$dok) djBalas(['success' => false, 'message' => 'Dokter tidak ditemukan'], 404);
      $day_of_week = $_POST['day_of_week'] ?? '';
      $start_time = $_POST['start_time'] ?? '';
      $end_time = $_POST['end_time'] ?? '';

      $stmt = $koneksi->prepare("INSERT INTO ms_doctor_schedule (id_doctor, day_of_week, start_time, end_time) VALUES (?,?,?,?)");
      $stmt->bind_param("ssss", $dok['id_doctor'], $day_of_week, $start_time, $end_time);
      if (!$stmt->execute()) djBalas(['success' => false, 'message' => $stmt->error]);
      logBaris($koneksi, 'master', 'ms_doctor_schedule', 'id_schedule', $stmt->insert_id, null, ['tipe' => 'jadwal_dokter', 'label' => $dok['doctor_name'] . " · $day_of_week $start_time-$end_time"], 'Jadwal dokter ditambahkan');
      djBalas(['success' => true]);
      break;

   case 'DELETE':
      $id = (int) ($_POST['id_schedule'] ?? 0);
      $st = $koneksi->prepare("SELECT s.*, d.doctor_name FROM ms_doctor_schedule s JOIN ms_doctor d ON d.id_doctor = s.id_doctor
         WHERE s.id_schedule = ? AND d.id_customer = ? LIMIT 1");
      $st->bind_param('ii', $id, $idc);
      $st->execute();
      $lama = $st->get_result()->fetch_assoc();
      $st->close();
      if (!$lama) djBalas(['success' => false, 'message' => 'Jadwal tidak ditemukan'], 404);

      $stmt = $koneksi->prepare("DELETE FROM ms_doctor_schedule WHERE id_schedule = ?");
      $stmt->bind_param("i", $id);
      if (!$stmt->execute()) djBalas(['success' => false, 'message' => $stmt->error]);
      $nama = $lama['doctor_name'];
      unset($lama['doctor_name']);
      logBaris($koneksi, 'master', 'ms_doctor_schedule', 'id_schedule', $id, $lama, ['tipe' => 'jadwal_dokter', 'label' => "$nama · {$lama['day_of_week']} {$lama['start_time']}-{$lama['end_time']}"], 'Jadwal dokter dihapus');
      djBalas(['success' => true]);
      break;

   default:
      djBalas(['success' => false, 'message' => 'Invalid Method']);
      break;
}
