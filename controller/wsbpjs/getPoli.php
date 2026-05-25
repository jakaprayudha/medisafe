<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
$tanggal = $_GET['tanggal'];
$result = bpjsGetService('/ref/poli/tanggal/' . $tanggal);
echo json_encode($result);
