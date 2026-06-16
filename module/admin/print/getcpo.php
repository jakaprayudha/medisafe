<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

$q = $koneksi->query("
    SELECT * FROM cpo_history
    WHERE visit_ID='$no' AND nomor_rm='$rm'
    ORDER BY tanggal ASC
");

$data = [];

while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode([
   "status" => "success",
   "data" => $data
]);
