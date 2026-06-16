<?php
header('Content-Type: application/json');
include '../../database/connect.php';

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

// Ambil nama dokter (bisa dari session login)
$doctorName = $_GET['doctor_name'] ?? '';

if (!$doctorName) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Doctor name required'
    ]);
    exit;
}

/* =========================
   METRIC
========================= */
$metricQuery = "
SELECT
    COUNT(*) AS total_pasien,
    SUM(CASE WHEN pv.visit_status = '0' THEN 1 ELSE 0 END) AS menunggu,
    SUM(CASE WHEN pv.visit_status = '1' THEN 1 ELSE 0 END) AS diperiksa,
    SUM(CASE WHEN pv.visit_status = '2' THEN 1 ELSE 0 END) AS selesai
FROM pasien_visit pv
INNER JOIN ms_doctor d ON d.id_doctor = pv.id_doctor
WHERE d.doctor_name = ?
  AND DATE(pv.visit_date) = ?
";

$stmt = $koneksi->prepare($metricQuery);
$stmt->bind_param("ss", $doctorName, $today);
$stmt->execute();
$metric = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* =========================
   LIST ANTRIAN PASIEN
========================= */
$listQuery = "
SELECT
    pv.visit_ID,
    pv.visit_antrian,
    p.patient_name,
    pv.visit_notes,
    pv.visit_status
FROM pasien_visit pv
INNER JOIN ms_patient p ON p.id_patient = pv.id_patient
INNER JOIN ms_doctor d ON d.id_doctor = pv.id_doctor
WHERE d.doctor_name = ?
  AND DATE(pv.visit_date) = ?
ORDER BY pv.created_at ASC
";

$stmt = $koneksi->prepare($listQuery);
$stmt->bind_param("ss", $doctorName, $today);
$stmt->execute();
$list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* =========================
   JADWAL PRAKTEK DOKTER
========================= */
$scheduleQuery = "SELECT
    p.poli_name,
    ds.day_of_week,
    ds.start_time,
    ds.end_time
FROM ms_doctor_schedule ds
INNER JOIN ms_doctor d ON d.id_doctor = ds.id_doctor
INNER JOIN ms_poli p ON p.id_poli = ds.id_poli
WHERE d.doctor_name = ?
  AND ds.sch_status = 'Hadir'
ORDER BY FIELD(ds.day_of_week,
    'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'
), ds.start_time
";

$stmt = $koneksi->prepare($scheduleQuery);
$stmt->bind_param("s", $doctorName);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* =========================
   RESPONSE
========================= */
echo json_encode([
    'status' => 'success',
    'metric' => [
        'total'     => (int)$metric['total_pasien'],
        'menunggu'  => (int)$metric['menunggu'],
        'diperiksa' => (int)$metric['diperiksa'],
        'selesai'   => (int)$metric['selesai'],
    ],
    'queue' => $list,
    'schedule' => $schedule
]);