<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$visit_id = $_POST['visit'];
$stmt = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '0' WHERE visit_ID = ?");
$stmt->bind_param("s", $visit_id);
if ($stmt->execute()) {
    $response = [
        'success'  => true,
        'message'  => "Berhasil Chackin",
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Gagal Chackin",
    ];
}
$stmt->close();
echo json_encode($response);
