<?php
include '../../database/connect.php';

$nomor_visit = $_GET['nomor_visit'] ?? '';

if (!$nomor_visit) {
   echo json_encode(['status' => 'error', 'message' => 'Nomor visit tidak ditemukan']);
   exit;
}

// ambil data pemeriksaan
$stmt = $koneksi->prepare("SELECT * FROM visit_pemeriksaan WHERE nomor_visit=?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$pemeriksaan = $result->fetch_assoc();
$stmt->close();

// ambil anamnesa
$stmt = $koneksi->prepare("SELECT * FROM visit_anamnesa WHERE nomor_visit=?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$anamnesa = [];
while ($row = $result->fetch_assoc()) $anamnesa[] = $row;
$stmt->close();

// ambil terapi
$stmt = $koneksi->prepare("SELECT * FROM visit_terapi WHERE nomor_visit=?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$terapi = [];
while ($row = $result->fetch_assoc()) $terapi[] = $row;
$stmt->close();

echo json_encode([
   'status' => 'success',
   'pemeriksaan' => $pemeriksaan,
   'anamnesa' => $anamnesa,
   'terapi' => $terapi
]);
