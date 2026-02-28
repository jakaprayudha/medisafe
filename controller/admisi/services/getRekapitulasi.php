<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$nomor = $_GET['noKartu'];
$data = [];
$start  = intval($_POST['start'] ?? 0);
$limit  = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);
$total = 0;

$result = bpjsGet('/skrinning/peserta/' . $nomor . '/' . $start . '/' . $limit);
$total = $result['data']['count'] ?? 0;
 
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $total,
    "recordsFiltered" => $total,
    "data" => $result['data']['list']
]);
