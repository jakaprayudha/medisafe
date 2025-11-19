<?php
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

if (!$visit || !$rm) {
   echo json_encode(['status' => 'error', 'message' => 'Parameter kurang']);
   exit;
}

$query = $koneksi->query("
    SELECT * FROM visit_ranap
    WHERE visit_ID = '$visit' AND nomor_rm = '$rm'
    LIMIT 1
");

$data = $query->fetch_assoc();

if ($data) {
   echo json_encode(['status' => 'success', 'data' => $data]);
} else {
   echo json_encode(['status' => 'empty']);
}
