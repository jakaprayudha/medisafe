<?php
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');
pemanggilanAntrian([
    "rs_id" => $kdRumahSakit,
    "target_role" => "pemeriksaan_DOCTOR",
    "text" => strtolower($_POST['text']),
]);

echo json_encode([
    'success' => true,
    'message' => 'Panggilan berhasil dikirim',
    'text' => $_POST['text']
]);
exit;