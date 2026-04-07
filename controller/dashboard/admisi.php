<?php
header('Content-Type: application/json');
include '../../database/connect.php';
session_start();

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

// =========================
// SESSION CHECK
// =========================
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

// =========================
// RESPONSE INIT
// =========================
$response = [
   'status' => 'success',
   'metrics' => [],
   'jadwal_dokter' => [],
   'poli' => []
];

// =========================
// METRICS ADMISI
// =========================
$stmt = $koneksi->prepare("
   SELECT
      COUNT(*) AS pasien_hari_ini,
      SUM(CASE WHEN status_antrian = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
      SUM(CASE WHEN status_antrian = 'aktif' THEN 1 ELSE 0 END) AS antrian_aktif,
      SUM(CASE WHEN status_antrian = 'selesai' THEN 1 ELSE 0 END) AS selesai
   FROM pasien_visit
   WHERE DATE(visit_date) = ?
   AND visit_status != 99
   AND id_customer = ?
");

$stmt->bind_param("si", $today, $id_customer);
$stmt->execute();
$result = $stmt->get_result();
$metrics = $result->fetch_assoc();

$response['metrics'] = [
   'pasien_hari_ini' => (int)($metrics['pasien_hari_ini'] ?? 0),
   'menunggu'       => (int)($metrics['menunggu'] ?? 0),
   'antrian_aktif'  => (int)($metrics['antrian_aktif'] ?? 0),
   'selesai'        => (int)($metrics['selesai'] ?? 0)
];

$stmt->close();

// =========================
// JADWAL DOKTER
// =========================
$stmt = $koneksi->prepare("
   SELECT
      d.doctor_name,
      p.poli_name,
      j.start_time,
      j.end_time,
      j.sch_status
   FROM ms_doctor_schedule j
   INNER JOIN ms_doctor d ON d.id_doctor = j.id_doctor
   INNER JOIN ms_poli p ON p.id_poli = j.id_poli
   WHERE d.id_customer = ?
   ORDER BY j.start_time ASC
");

$stmt->bind_param("i", $id_customer);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
   $response['jadwal_dokter'][] = [
      'nama_dokter'  => $row['doctor_name'],
      'poli'         => $row['poli_name'],
      'jam_mulai'    => substr($row['start_time'], 0, 5),
      'jam_selesai'  => substr($row['end_time'], 0, 5),
      'status'       => $row['sch_status'],
      'status_label' => ucfirst($row['sch_status'])
   ];
}

$stmt->close();

// =========================
// STATUS POLI
// =========================
$stmt = $koneksi->prepare("
   SELECT poli_name, poli_status
   FROM ms_poli
   WHERE id_customer = ?
   ORDER BY poli_name ASC
");

$stmt->bind_param("i", $id_customer);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
   $label = $row['poli_status'] === '1'
      ? 'Buka'
      : ($row['poli_status'] === 'siang' ? 'Siang' : 'Buka');

   $response['poli'][] = [
      'nama_poli' => $row['poli_name'],
      'status'    => $row['poli_status'],
      'label'     => $label
   ];
}

$stmt->close();

// =========================
// OUTPUT
// =========================
echo json_encode($response);
