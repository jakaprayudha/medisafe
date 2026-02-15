<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kunci = $_GET['q'];
$result = bpjsGet('/diagnosa/' . $kunci . '/0/100');
echo json_encode([
    'success' => true,
    'data' => $result['data']['list']
], JSON_PRETTY_PRINT);