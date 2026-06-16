<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$tipe = $_GET['tipe'];
$nomor_kartu = $_GET['nokartu'];
$result = bpjsGet('/peserta/' . $tipe . '/' . $nomor_kartu);
// $result = testingBPJS_GET("http://localhost/medisafe/controller/admisi/api/getpeserta.php");
if ($result['code'] != "200") {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $response = $result;
}
echo json_encode($response);
