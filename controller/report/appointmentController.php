<?php
include '../../database/connect.php';
header("Content-Type: application/json");
session_start();

$id_customer = $_SESSION['id_customer'] ?? null;

$fromDate = $_GET['fromDate'] ?? null;
$toDate   = $_GET['toDate'] ?? null;
$doctor   = $_GET['doctor'] ?? null;
$provider = $_GET['provider'] ?? null;
$poli     = $_GET['poli'] ?? null;

$sql = "
SELECT pv.*, mp.provider_name
FROM pasien_visit pv
LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
WHERE pv.id_customer = '$id_customer'
";


// =============================
// FILTER TANGGAL
// =============================
if ($fromDate && $toDate) {
   $sql .= " AND DATE(pv.visit_date) BETWEEN '$fromDate' AND '$toDate' ";
}

// =============================
// FILTER PROVIDER (FIX 1x SAJA)
// =============================
if (!empty($provider)) {
   $sql .= " AND pv.id_provider = '$provider' ";
}

// =============================
// FILTER DOKTER
// =============================
if (!empty($doctor)) {
   $sql .= " AND pv.id_doctor = '$doctor' ";
}

// =============================
// FILTER POLI
// =============================
if (!empty($poli)) {
   $sql .= " AND pv.id_poli = '$poli' ";
}


// =============================
$sql .= " ORDER BY pv.visit_date DESC, pv.visit_time DESC ";

$query = mysqli_query($koneksi, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($query)) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
