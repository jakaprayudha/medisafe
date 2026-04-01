<?php
header('Content-Type: application/json');
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
$today   = date('Y-m-d');
$listQuery = "
   SELECT q.id_queue,q.status, q.no_antrian, q.nama_pasien, p.poli_name
   FROM transaction_queue q
   LEFT JOIN ms_poli p ON p.id_poli = q.id_poli
   WHERE q.queue_date = '$today'
     AND q.status != 'selesai'
   ORDER BY q.created_at ASC
";

$list = mysqli_query($koneksi, $listQuery);
while ($row = mysqli_fetch_assoc($list)) {
    $response['data'][] = [
        'id'  => $row['id_queue'],
        'no_antrian'  => $row['no_antrian'],
        'nama_pasien' => $row['nama_pasien'],
        'poli'        => $row['poli_name'],
        'status'      => $row['status'],
    ];
}
echo json_encode($response);
