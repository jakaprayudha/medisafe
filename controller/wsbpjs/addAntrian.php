<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
$nomorkartu     = $_POST['nomorkartu'] ?? '';
$nik            = $_POST['nik'] ?? '';
$nohp           = $_POST['nohp'] ?? '';
$kodepoli       = $_POST['kodepoli'] ?? '';
$namapoli       = $_POST['namapoli'] ?? '';
$norm           = $_POST['norm'] ?? '';
$tanggalperiksa = $_POST['tanggalperiksa'] ?? '';
$kodedokter     = $_POST['kodedokter'] ?? '';
$namadokter     = $_POST['namadokter'] ?? '';
$jampraktek     = $_POST['jampraktek'] ?? '';
$nomorantrean   = $_POST['nomorantrean'] ?? '';
$angkaantrean   = $_POST['angkaantrean'] ?? '';

$payload = [
    "nomorkartu"      => $nomorkartu,
    "nik"             => $nik,
    "nohp"            => $nohp,
    "kodepoli"        => $kodepoli,
    "namapoli"        => $namapoli,
    "norm"            => $norm,
    "tanggalperiksa"  => $tanggalperiksa,
    "kodedokter"      => $kodedokter,
    "namadokter"      => $namadokter,
    "jampraktek"      => $jampraktek,
    "nomorantrean"    => $nomorantrean,
    "angkaantrean"    => $angkaantrean,
    "keterangan"      => ""
];

// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/antrean/add", $payload);
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
        'message'  => "Berhasil Mendaftar Pasien",
        'result' => $result
    ];
}
echo json_encode($response);