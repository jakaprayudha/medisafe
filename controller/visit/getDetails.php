<?php
include '../../database/connect.php';

$id_anamnesa = $_GET['id_anamnesa'] ?? '';
$response = ['status' => 'error', 'data' => []];

if ($id_anamnesa) {
   $query = mysqli_query($koneksi, "SELECT * FROM ms_anamnesa_detail WHERE id_anamnesa='$id_anamnesa' AND ass_status='1'");

   $details = [];
   while ($row = mysqli_fetch_assoc($query)) {
      $details[] = $row;
   }

   $response['status'] = 'success';
   $response['data'] = $details;
}

header('Content-Type: application/json');
echo json_encode($response);
