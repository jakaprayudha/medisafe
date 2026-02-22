<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$q = $_GET['keyword'];
$result = bpjsGet('/obat/dpho/'.$q.'/0/50');
echo json_encode([
    'success' => true,
    'data' => $result['data']['list']
], JSON_PRETTY_PRINT);