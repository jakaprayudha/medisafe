<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kunci = $_GET['q'] ?? '';
if (!is_string($kunci) || trim($kunci) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kata kunci diagnosa wajib diisi', 'data' => []]);
    exit;
}
$result = bpjsGet('/diagnosa/' . rawurlencode(trim($kunci)) . '/0/100');
if (!$result['success'] || !isset($result['data']['list']) || !is_array($result['data']['list'])) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $result['success'] ? 'Format diagnosa BPJS tidak valid' : ($result['message'] ?? 'Gagal mengambil diagnosa BPJS'),
        'data' => []
    ]);
    exit;
}
echo json_encode([
    'success' => true,
    'data' => $result['data']['list']
], JSON_PRETTY_PRINT);