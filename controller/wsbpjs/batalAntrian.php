<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
$tanggalperiksa = $_POST['tanggalperiksa'];
$kdPoli = $_POST['kdPoli'];
$nomorkartu = $_POST['nomorkartu'];
$alasan = $_POST['alasan'];
$payload = [
    "tanggalperiksa"  => $tanggalperiksa,
    "kodepoli" => $kdPoli,
    "nomorkartu" => $nomorkartu,
    "alasan"      => $alasan
];
//  echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/antrean/batal", $payload);
if ($result['code'] != '200') {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
        'result' => $result
    ];
} else {
    $response = [
        'success'  => true,
        'message'  => "Berhasil Batal Pasien",
    ];
}
echo json_encode($response);
