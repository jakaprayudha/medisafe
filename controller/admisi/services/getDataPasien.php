<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$status  = true;
$tanggalInput = $_POST['tanggal'] ?? date('Y-m-d');
$data = [];
$start  = intval($_POST['start'] ?? 0);
$limit  = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);
$total = 0;
if ($status) {
    $tanggalDB = date('Y-m-d', strtotime($tanggalInput));
    $result = mysqli_query($koneksi, "SELECT 
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
    ON pk.noKartu   = pp.noKartu
   AND pk.tglDaftar = pp.tanggal_daftar
   AND pk.kdPoli    = pp.kdPoli
WHERE pp.tanggal_daftar = '$tanggalDB'
");

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    $total = count($data);
} else {

    $tanggalBPJS = date("d-m-Y", strtotime($tanggalInput));
    $result = bpjsGet('/pendaftaran/tglDaftar/' . $tanggalBPJS . '/' . $start . '/' . $limit);
    $total = $result['data']['count'] ?? 0;
}
// echo json_encode($result['data']['list'], JSON_PRETTY_PRINT);die();
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $total,
    "recordsFiltered" => $total,
    // "data" => $result['data']['list']
    "data" => $data
]);
