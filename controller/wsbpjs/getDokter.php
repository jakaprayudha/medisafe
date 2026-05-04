<?php
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
$kdpoli = $_GET['kdpoli'];
$tanggal = $_GET['tanggal'];
$result = bpjsGet('/ref/dokter/kodepoli/'. $kdpoli .'/tanggal/' . $tanggal);
echo json_encode($result);
