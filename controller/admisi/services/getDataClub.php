<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$id = $_GET['id'];
$result = bpjsGet('/kelompok/club/' . $id);
echo json_encode([
    'success' => true,
    'data' => $result['data']['list']
], JSON_PRETTY_PRINT);
