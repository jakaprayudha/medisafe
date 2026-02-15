<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

$status  = false;
$tanggalInput = $_POST['tanggal'] ?? date('Y-m-d');
$data = [];
$start  = intval($_POST['start'] ?? 0);
$limit  = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);
$total = 0;
if ($status) {

    $tanggalDB = date('Y-m-d', strtotime($tanggalInput));

    $result = mysqli_query(
        $koneksi,
        "SELECT pp.*,p.nomor_rm,p.patient_name,p.patient_gender 
         FROM pcare_pendaftaran as pp 
         INNER JOIN ms_patient as p 
            ON pp.noKartu = p.patient_bpjs 
         WHERE pp.tanggal_daftar = '$tanggalDB'"
    );

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            "tanggal_daftar" => $row['tanggal_daftar'],
            "noUrut" => $row['noUrut'],
            "noKartu" => $row['noKartu'],
            "nama" => $row['patient_name'],
            "kelamin" => $row['patient_gender'],
            "poli" => $row['nmPoli'],
            "kdpoli" => $row['kdPoli'],
            "sumber" => $row['sumber'],
        ];
    }
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
    "data" => $result['data']['list']
]);