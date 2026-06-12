<?php
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');
$uid = $_SESSION['uid_user'];
$text = strtolower($_POST['text']);
$result = pemanggilanAntrian([
    "rs_id" => $kdRumahSakit,
    "target_role" => "farmasi_order_detail_ADMIN_" . $uid,
    "text" => $text,
    "requestId" => $_POST['requestIdFarmasi'],
]);

if (!$result['success']) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Panggilan berhasil dikirim',
    'text' => $text
]);