<?php
require '../../../database/connect.php';

$visit = $_GET['visit'] ?? '';
$rm    = $_GET['rm'] ?? '';

if (!$visit || !$rm) {
   echo json_encode([
      'status' => 'error',
      'message' => 'visit dan rm wajib diisi'
   ]);
   exit;
}

$query = "SELECT * FROM lbp_bpjs 
          WHERE visit_ID = '$visit' 
          AND nomor_rm = '$rm'
          ORDER BY tgl_pelayanan ASC";

$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
