<?php
include '../../database/connect.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? '';

$query = "SELECT 
PV.id_doctor,
pv.visit_date,
pv.visit_time,
pv.id_poli,
pv.patient_name_pcare,
pv.anamnesa,
pv.catatan_screening,
pv.kdDiag1,
pv.kdDiag2,
pv.kdDiag3,
pv.nmDiag1,
pv.kdDiag2,
pv.kdDiag3,
pv.tindakan,
pv.tekanan_darah,
pv.suhu,
pv.nadi,
pv.respirasi,
pv.tinggi_badan,
pv.berat_badan,
pv.bmi,
pv.bmi_keterangan,
pv.saturasi,
mp.patient_datebirth
FROM pasien_visit AS PV LEFT JOIN ms_patient MP ON mp.id_patient = pv.id_patient WHERE id_visit = ? LIMIT 1;
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    'status' => true,
    'data' => $data
]);

// ==========================
// AMBIL DATA OBAT
// ==========================

// $obat = [];

// $qObat = "SELECT *
// FROM permintaan_pharmacy p
// LEFT JOIN permintaan_pharmacy_details d
//     ON d.id_permintaan_farmasi = p.id_permintaan_farmasi
// LEFT JOIN ms_pharmacy ph
//     ON ph.id_pharmacy = d.id_pharmacy

// WHERE p.id_visit = ?
// ORDER BY p.id_permintaan_farmasi DESC
// ";

// $stmtObat = $koneksi->prepare($qObat);
// $stmtObat->bind_param("s", $id);
// $stmtObat->execute();

// $resObat = $stmtObat->get_result();

// while ($row = $resObat->fetch_assoc()) {
//     $obat[] = $row;
// }

// inject ke response
// $data['tindakan'] = $obat;
