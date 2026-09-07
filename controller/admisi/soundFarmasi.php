<?php
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');
$uid = $_SESSION['uid_user'];
$text = strtolower($_POST['text']);
$nama = $_POST['nama_pasien'];
$poli = $_POST['asalpoli'];
$visit_id = $_POST['visit_id'];
$result = farmasiupdate([
    "rs_id" => $kdRumahSakit,
    "target_role" => "farmasi_order_detail_ADMIN_" . $uid,
    "text" => $text,
    "requestId" => $_POST['requestIdFarmasi'],
    "uid" => $uid,
    "nama_pasien" => $nama,
    "poli" => $poli,
    "status" => '2',
    "visit_id" => $visit_id
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