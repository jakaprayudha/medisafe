<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$visit_ID = $data['visit_ID'] ?? null;
$id_patient = $data['id_patient'] ?? null;
$visit_date = $data['visit_date'] ?? null;
$visit_time = $data['visit_time'] ?? null;

if (!$id_patient || !$visit_time) {
   echo json_encode([
      "status" => "error",
      "message" => "Data tidak lengkap"
   ]);
   exit;
}

// hanya kunjungan faskes di session, dan id_patient harus cocok
require_once __DIR__ . '/../../utility/visitGuard.php';
$kunj = visitGuard($koneksi, 'visit_ID', $visit_ID);
session_write_close();
if ((string) $kunj['id_patient'] !== (string) $id_patient) {
   echo json_encode(["status" => "error", "message" => "Data kunjungan tidak sesuai"]);
   exit;
}
if ((int) $kunj['visit_status'] === 99 || (kunjunganSelesai($kunj['visit_status']) && !bolehBukaKunciSelesai())) {
   echo json_encode(["status" => "error", "message" => "Kunjungan sudah dilayani / batal, waktu tidak dapat diubah"]);
   exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $visit_date) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string) $visit_time)) {
   echo json_encode(["status" => "error", "message" => "Format tanggal / jam tidak valid"]);
   exit;
}
// rentang wajar: koreksi s.d. 7 hari ke belakang, penjadwalan ulang s.d. 90 hari ke depan
$hariIni = date('Y-m-d');
if ($visit_date < date('Y-m-d', strtotime('-7 days')) || $visit_date > date('Y-m-d', strtotime('+90 days'))) {
   echo json_encode(["status" => "error", "message" => "Tanggal harus antara 7 hari ke belakang dan 90 hari ke depan"]);
   exit;
}

require_once __DIR__ . '/../../utility/visitLog.php';
$snapLama = logKunjunganSnapshot($koneksi, 'visit_ID', $visit_ID, (int) $kunj['id_customer']);
$query = $koneksi->prepare("
   UPDATE pasien_visit 
   SET visit_date=?, visit_time=? 
   WHERE id_patient=? AND visit_ID=? AND id_customer=?
");
$idcSesi = (int) $kunj['id_customer'];
$query->bind_param("ssssi", $visit_date, $visit_time, $id_patient, $visit_ID, $idcSesi);

if ($query->execute()) {
   if ($query->affected_rows > 0 && $snapLama) {
      logKunjungan($koneksi, 'ubah_waktu', $snapLama, '',
         ['visit_date' => $snapLama['visit_date'], 'visit_time' => $snapLama['visit_time']], ['visit_date' => $visit_date, 'visit_time' => $visit_time]);
   }
   echo json_encode([
      "status" => "success",
      "message" => "Berhasil update visit"
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => "Gagal update"
   ]);
}
