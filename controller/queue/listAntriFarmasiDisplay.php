<?php
header('Content-Type: application/json');
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
$today   = date('Y-m-d');
session_start();
$id_customer = $_SESSION['id_customer'];
$listQuery = "SELECT pp.id_permintaan_farmasi, pp.id_visit, pp.status_permintaan, pv.patient_name_pcare, pv.id_poli, pp.created_at FROM permintaan_pharmacy AS pp INNER JOIN pasien_visit AS pv ON pv.visit_ID = pp.id_visit AND pv.id_customer = pp.id_customer WHERE pp.id_customer = '$id_customer' AND pv.visit_date = '$today' ORDER BY pp.created_at ASC";
$list = mysqli_query($koneksi, $listQuery);
while ($row = mysqli_fetch_assoc($list)) {
    $response['data'][] = [
        'nama_pasien' => $row['patient_name_pcare'],
        'poli'        => $row['id_poli'],
        'status'      => $row['status_permintaan'],
        'visit_id'      => $row['id_visit'],
    ];
}
echo json_encode($response);
