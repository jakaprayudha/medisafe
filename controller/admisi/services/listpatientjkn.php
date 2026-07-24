<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');
$idcustomer = $idcustomer ?? $_SESSION['id_customer'] ?? null;
$fromDate = !empty($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-d');
$toDate   = !empty($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-d');
$doctor   = !empty($_GET['doctor']) ? $_GET['doctor'] : null;
$poli     = !empty($_GET['poli']) ? $_GET['poli'] : null;
$query = "
    SELECT DISTINCT
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
        md.nmDokter,
        pv.id_poli AS poli_name,
        prov.provider_name
    FROM pasien_visit AS pv

    -- Gunakan LEFT JOIN agar data pasien tidak hilang jika master dokter/provider kosong
    LEFT JOIN ms_patient AS mp 
        ON mp.id_patient = pv.id_patient

    LEFT JOIN master_doctor_bpjs AS md 
        ON md.kdDokter = pv.code_doctor AND md.id_customer = ?

    -- INNER JOIN master_poli DIHAPUS (karena sering buat duplikat dan datanya tidak dipakai)

    LEFT JOIN ms_provider AS prov 
        ON prov.id_provider = pv.id_provider

    WHERE pv.id_customer = ?
    AND DATE(pv.visit_date) BETWEEN ? AND ?
    AND pv.created_user = 'MobileJKN'
";

$params = [];
$types  = "";
$params[] = $idcustomer;
$types .= "s";
$params[] = $idcustomer;
$types .= "s";
$params[] = $fromDate;
$params[] = $toDate;
$types .= "ss";
if (!empty($doctor)) {
    $query .= " AND pv.id_doctor = ?";
    $params[] = $doctor;
    $types .= "s";
}
if (!empty($poli)) {
    $query .= " AND pv.id_poli = ?";
    $params[] = $poli;
    $types .= "s";
}
$query .= "
    ORDER BY 
        pv.id_poli ASC,
        pv.code_doctor ASC,
        LEFT(pv.visit_antrian, 1) ASC,
        CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED) ASC
";
$stmt = $koneksi->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}

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
        "doctor_name"     => $row['nmDokter'],
        "poli_name"       => $row['poli_name'],
        "provider_name"   => $row['provider_name']
    ];
}

$stmt->close();

echo json_encode([
    "data" => $data
]);