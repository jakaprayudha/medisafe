<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$tgl = $_GET['tanggal'];
$result = bpjsGet('/kelompok/kegiatan/' . $tgl);
echo json_encode([
    "data" => $result['data']['list'] ?? []
]);
