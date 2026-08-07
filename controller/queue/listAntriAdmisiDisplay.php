<?php
header('Content-Type: application/json');
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
$today   = date('Y-m-d');
session_start();
$id_customer = $_SESSION['id_customer'];
$listQuery = "SELECT pv.patient_name_pcare, pv.id_poli, ap.status, pv.visit_antrian FROM pasien_visit AS pv INNER JOIN antrian_poli AS ap ON pv.visit_ID = ap.nomor_visit WHERE pv.visit_date = '$today' AND ap.status = '0' AND pv.id_customer = '$id_customer' ORDER BY pv.created_at ASC";
$list = mysqli_query($koneksi, $listQuery);
while ($row = mysqli_fetch_assoc($list)) {
    $response['data'][] = [
        'id'  => $row['id_queue'],
        'no_antrian'  => $row['visit_antrian'],
        'nama_pasien' => $row['patient_name_pcare'],
        'poli'        => $row['id_poli'],
        'status'      => $row['status'],
    ];
}
echo json_encode($response);
