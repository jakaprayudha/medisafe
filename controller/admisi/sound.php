<?php
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');
$uid = $_SESSION['uid_user'];
$text = strtolower($_POST['text']);
pemanggilanAntrian([
    "rs_id" => $kdRumahSakit,
    "target_role" => "pemeriksaan_DOCTOR_" . $uid,
    "text" => $text,
    "requestId" => $_POST['requestId'],
]);

echo json_encode([
    'success' => true,
    'message' => 'Panggilan berhasil dikirim',
    'text' => $text
]);
exit;