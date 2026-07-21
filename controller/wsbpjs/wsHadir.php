<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
$tanggalperiksa = $_POST['tanggalperiksa'];
$kdPoli = $_POST['kdPoli'];
$nomorkartu = $_POST['nomorkartu'];
$status_hadir = $_POST['status'];
$payload = [
        "tanggalperiksa" => $tanggalperiksa,
        "kodepoli" => $kdPoli,
        "nomorkartu" => $nomorkartu,
        "status" => (int)$status_hadir,
        "waktu" => round(microtime(true) * 1000)
    ];
    // echo json_encode($payload, JSON_PRETTY_PRINT);
    $result = bpjsPost('/antrean/panggil', $payload);
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
        'message'  => "Berhasil",
    ];
}
echo json_encode($response);