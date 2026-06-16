<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$result = bpjsGet('/poli/fktp/0/100');
// $result = testingBPJS_GET("http://localhost/medisafe/controller/admisi/services/testing.php");
echo json_encode($result, JSON_PRETTY_PRINT);
die();
if ($result['code'] == "200") {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $response = [
        'success' => true,
        'data' => $result,
    ];
}
echo json_encode($response);
