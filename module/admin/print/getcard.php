<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

$q = $koneksi->query("
   SELECT * FROM kb_card 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
   LIMIT 1
");

if ($q->num_rows == 0) {
   echo json_encode(["status" => "empty"]);
   exit;
}

echo json_encode([
   "status" => "success",
   "data" => $q->fetch_assoc()
]);
