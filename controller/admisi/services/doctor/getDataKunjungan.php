<?php
require_once __DIR__ . '/../view.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../servicebpjs.php';

header('Content-Type: application/json');

$tanggal = $_GET['tglDaftar'] ?? '';
$nomor_visit = $_GET['nomor_visit'] ?? '';

$stmt = $koneksi->prepare("
    SELECT 
        pp.*, 
        p.nomor_rm, 
        p.patient_name, 
        p.patient_gender,
        CASE 
            WHEN pk.noKunjungan IS NOT NULL THEN TRUE 
            ELSE FALSE 
        END AS status_kunjungan 
    FROM pcare_pendaftaran AS pp 
    INNER JOIN ms_patient AS p 
        ON pp.noKartu = p.patient_bpjs 
    LEFT JOIN pcare_kunjungan AS pk 
        ON pk.noKartu = pp.noKartu 
        AND pk.tglDaftar = pp.tanggal_daftar 
        AND pk.kdPoli = pp.kdPoli 
    WHERE id_customer = ? 
    AND nomor_visit = ?
");

$stmt->bind_param('ss', $idcustomer, $nomor_visit);
$stmt->execute();

$hasil = $stmt->get_result();
$data = [];

while ($row = $hasil->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
exit; // penting