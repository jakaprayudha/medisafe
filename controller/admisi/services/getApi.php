<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$url = $_POST['url'];
$result = bpjsGet($url);
echo json_encode($result['data']);
