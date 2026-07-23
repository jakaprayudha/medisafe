<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');
$tanggal  = $_GET['tanggal'] ?? date('Y-m-d');

// 🔥 QUERY UTAMA
$stmt = $koneksi->prepare("
    SELECT 
        pv.visit_ID,
        pv.visit_status,
        pv.visit_antrian,
        pv.visit_date,
        pv.visit_time,
        pv.noKartu,
        pv.code_doctor,
        mp.patient_name,
        mp.patient_gender,
        mp.nomor_rm,
        pv.tekanan_darah,
        md.doctor_name,
        pv.id_poli AS poli_name,
        prov.provider_name
    FROM pasien_visit pv

    LEFT JOIN ms_patient mp 
        ON mp.id_patient = pv.id_patient

    LEFT JOIN ms_doctor md 
        ON md.doctor_code = pv.code_doctor

    LEFT JOIN master_poli poli 
        ON poli.kdPoli = pv.id_poli

    LEFT JOIN ms_provider prov 
        ON prov.id_provider = pv.id_provider

    WHERE pv.id_customer = ?
    AND DATE(pv.visit_date) = ?
    AND pv.created_user = 'MobileJKN'

    ORDER BY 
        pv.id_poli ASC,
        pv.code_doctor ASC,
        LEFT(pv.visit_antrian, 1) ASC,
        CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED) ASC
");

$stmt->bind_param('ss', $idcustomer, $tanggal);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "visit_ID"        => $row['visit_ID'],
        "visit_status"    => $row['visit_status'],
        "visit_antrian"   => $row['visit_antrian'],
        "visit_date"      => $row['visit_date'],
        "visit_time"      => $row['visit_time'],
        "nomor_rm"        => $row['nomor_rm'],
        "no_bpjs"         => $row['noKartu'],
        "screening"       => $row['tekanan_darah'] ? 'Sudah' : 'Belum',
        "patient_name"    => $row['patient_name'],
        "patient_gender"  => $row['patient_gender'],
        "doctor_name"     => $row['doctor_name'],
        "poli_name"       => $row['poli_name'],
        "provider_name"   => $row['provider_name']
    ];
}

$stmt->close();
echo json_encode([
    "data" => $data
]);