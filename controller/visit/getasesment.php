<?php
include '../../database/connect.php';

$nomor_visit = $_GET['nomor_visit'] ?? '';

if (!$nomor_visit) {
   echo json_encode(['status' => 'error', 'message' => 'Nomor visit tidak ditemukan']);
   exit;
}

// ambil data pemeriksaan
$stmt = $koneksi->prepare("SELECT visit_ID AS nomor_visit, id_patient AS nomor_rm, kondisi_masuk, tekanan_darah, suhu, nadi, respirasi, tinggi_badan AS tinggi, berat_badan AS berat, bmi, bmi_keterangan AS bmi_ket, pemeriksaan_fisik, diagnosa, anamnesa, NULL AS analyst, NULL AS riwayat_konsumsi FROM pasien_visit WHERE visit_ID=?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$pemeriksaan = $result->fetch_assoc();
$stmt->close();

// ambil anamnesa
$stmt = $koneksi->prepare("SELECT * FROM visit_anamnesa INNER JOIN ms_anamnesa_detail ON ms_anamnesa_detail.id_ass = visit_anamnesa.id_anamnesa_detail WHERE nomor_visit=?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$anamnesa = [];
while ($row = $result->fetch_assoc()) $anamnesa[] = $row;
$stmt->close();

// ambil terapi
$stmt = $koneksi->prepare("SELECT * FROM visit_terapi INNER JOIN ms_therapi ON ms_therapi.id_terapi =visit_terapi.id_terapi WHERE nomor_visit=?");
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
