<?php
header('Content-Type: application/json');
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

$response = [
   'status' => 'success',
   'metrics' => [],
   'jadwal_dokter' => [],
   'poli' => []
];

/* =========================
   METRICS ADMISI
========================= */
$metricQuery = "SELECT
      COUNT(*) AS pasien_hari_ini,
      SUM(CASE WHEN status_antrian = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
      SUM(CASE WHEN status_antrian = 'aktif' THEN 1 ELSE 0 END) AS antrian_aktif,
      SUM(CASE WHEN status_antrian = 'selesai' THEN 1 ELSE 0 END) AS selesai
   FROM pasien_visit
   WHERE DATE(visit_date) = '$today'
     AND visit_status != 99
";

$metricResult = mysqli_query($koneksi, $metricQuery);
$metrics = mysqli_fetch_assoc($metricResult);

$response['metrics'] = [
   'pasien_hari_ini' => (int)$metrics['pasien_hari_ini'],
   'menunggu'       => (int)$metrics['menunggu'],
   'antrian_aktif'  => (int)$metrics['antrian_aktif'],
   'selesai'        => (int)$metrics['selesai']
];

/* =========================
   JADWAL DOKTER HARI INI
========================= */
$jadwalQuery = "SELECT
      d.doctor_name,
      p.poli_name,
      j.start_time,
      j.end_time,
      j.sch_status
   FROM ms_doctor_schedule j
   INNER JOIN ms_doctor d ON d.id_doctor = j.id_doctor
   INNER JOIN ms_poli p ON p.id_poli = j.id_poli
   ORDER BY j.start_time ASC
";

$jadwalResult = mysqli_query($koneksi, $jadwalQuery);

while ($row = mysqli_fetch_assoc($jadwalResult)) {
   $response['jadwal_dokter'][] = [
      'nama_dokter'  => $row['doctor_name'],
      'poli'         => $row['poli_name'],
      'jam_mulai'    => substr($row['start_time'], 0, 5),
      'jam_selesai'  => substr($row['end_time'], 0, 5),
      'status'       => $row['sch_status'],
      'status_label' => ucfirst($row['sch_status'])
   ];
}

/* =========================
   STATUS POLI
========================= */
$poliQuery = "SELECT
      poli_name,
      poli_status
   FROM ms_poli
   ORDER BY poli_name ASC
";

$poliResult = mysqli_query($koneksi, $poliQuery);

while ($row = mysqli_fetch_assoc($poliResult)) {
   $label = $row['poli_status'] === '1'
      ? 'Buka'
      : ($row['poli_status'] === 'siang' ? 'Siang' : 'Tutup');

   $response['poli'][] = [
      'nama_poli' => $row['poli_name'],
      'status'    => $row['poli_status'],
      'label'     => $label
   ];
}

echo json_encode($response);