<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kdObatSK  = !empty($_POST['kdObatSK']) ? $_POST['kdObatSK'] : '0';
$kdRacikan = !empty($_POST['kdRacikan']) ? $_POST['kdRacikan'] : null;
$nmObat = $_POST['nmObat'];
$noKunjungan = $_POST['noKunjungan'];
$obatDPHO = isset($_POST['obatDPHO']) ? true : false;;
$racikan = isset($_POST['racikan']) ? true : false;
$signa1 = $_POST['signa1'];
$signa2 = $_POST['signa2'];
$jmlObat = $_POST['jmlObat'];
$jmlPermintaan = $_POST['jmlPermintaan'];
$nmObatNonDPHO = $_POST['nmObatNonDPHO'];
$kdObat = $_POST['kdObat'];

$payload = [
    "kdObatSK" => $kdObatSK,
    "noKunjungan" => $noKunjungan,
    "racikan" => $racikan,
    "kdRacikan" => $kdRacikan,
    "obatDPHO" => $obatDPHO,
    "kdObat" => $kdObat,
    "signa1" => $signa1,
    "signa2" => $signa2,
    "jmlObat" => $jmlObat,
    "jmlPermintaan" => $jmlPermintaan,
    "nmObatNonDPHO" => $nmObatNonDPHO
];
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost('/obat/kunjungan', $payload);
if ($result['code'] != '200') {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $kdObatSK  = $result['data'][0]['message'];
    $kdRacikan = $result['data'][1]['message'];
    $stmt = $koneksi->prepare("INSERT INTO pcare_obat (kdObatSK,noKunjungan,racikan,kdRacikan,obatDPHO,kdObat,nmObat,signa1,signa2,jmlObat,jmlPermintaan,nmObatNonDPHO) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssssssss",
        $kdObatSK,
        $noKunjungan,
        $racikan,
        $kdRacikan,
        $obatDPHO,
        $kdObat,
        $nmObat,
        $signa1,
        $signa2,
        $jmlObat,
        $jmlPermintaan,
        $nmObatNonDPHO
    );
    $hasil = $stmt->execute();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Menambahkan Obat",
            'data' => $result
        ];
    } else {
        $response = [
            'success'  => false,
            'message'  => "Gagal Menambahkan Obat",
        ];
    }
}
echo json_encode($response);
