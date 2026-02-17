<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nomor = $_GET['nomor'];
$result = bpjsGet("/kunjungan/rujukan/". $nomor);
echo json_encode($result, JSON_PRETTY_PRINT);