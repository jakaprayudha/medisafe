<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

header('Content-Type: application/json');

$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
if (!ctype_digit($nomor_kartu)) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus berupa angka'
    ];
}
elseif (!in_array($lengthkartu, [13, 16])) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus 13 digit (BPJS) atau 16 digit (NIK)'
    ];
}
else {
    $result = bpjsGet('/peserta/' . $tipe . '/' . $nomor_kartu);
    // $result = testingBPJS_GET("http://localhost/medisafe/controller/admisi/api/getpeserta.php");

    if (($result['code'] ?? '') != "200") {
        $msg = $result['message'] ?? 
            "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";

        $response = [
            'success' => false,
            'message' => $msg
        ];
    } else {
        $response = $result;
    }
}

echo json_encode($response);
