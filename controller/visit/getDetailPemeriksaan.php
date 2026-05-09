<?php
include '../../database/connect.php';

$id = $_GET['id'] ?? '';

$query = "SELECT 
    pasien_visit.*,
    ms_patient.patient_name,
    ms_patient.patient_gender,
    ms_patient.patient_datebirth,
    ms_patient.patient_nik,
    ms_patient.patient_bpjs,

    COALESCE(
        NULLIF(pasien_visit.diagnosa, ''),
        NULLIF(pasien_visit.kdDiag1, '')
    ) AS diagnosa_final,

    icd_10.icd10,
    icd_10.code

FROM pasien_visit

LEFT JOIN ms_patient 
    ON ms_patient.id_patient = pasien_visit.id_patient

LEFT JOIN icd_10 
    ON icd_10.code = COALESCE(
        NULLIF(pasien_visit.diagnosa, ''),
        NULLIF(pasien_visit.kdDiag1, '')
    )

WHERE pasien_visit.visit_ID = ?
LIMIT 1
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();


// ==========================
// AMBIL DATA OBAT
// ==========================

$obat = [];

$qObat = "SELECT *
FROM permintaan_pharmacy p
LEFT JOIN permintaan_pharmacy_details d
    ON d.id_permintaan_farmasi = p.id_permintaan_farmasi
LEFT JOIN ms_pharmacy ph
    ON ph.id_pharmacy = d.id_pharmacy

WHERE p.id_visit = ?
ORDER BY p.id_permintaan_farmasi DESC
";

$stmtObat = $koneksi->prepare($qObat);
$stmtObat->bind_param("s", $id);
$stmtObat->execute();

$resObat = $stmtObat->get_result();

while ($row = $resObat->fetch_assoc()) {
    $obat[] = $row;
}

// inject ke response
$data['tindakan'] = $obat;

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
