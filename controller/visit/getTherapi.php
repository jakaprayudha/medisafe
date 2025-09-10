<?php
include '../../database/connect.php';

$id_anamnesa = $_GET['id_anamnesa'] ?? '';
$response = ['status' => 'error', 'data' => []];

if ($id_anamnesa) {
   $query = mysqli_query($koneksi, "SELECT * FROM ms_therapi WHERE id_anamnesa='$id_anamnesa' AND terapi_status='1'");

   $therapi = [];
   while ($row = mysqli_fetch_assoc($query)) {
      $therapi[] = $row;
   }

   $response['status'] = 'success';
   $response['data'] = $therapi;
}

header('Content-Type: application/json');
echo json_encode($response);
