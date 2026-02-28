<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kdObatSK  = !empty($_POST['kdObatSK']) ? $_POST['kdObatSK'] : '0';
// $kdRacikan = !empty($_POST['kdRacikan']) ? $_POST['kdRacikan'] : null;
$nmObat = $_POST['nmObat'];
$noKunjungan = $_POST['noKunjungan'];
$signa1 = $_POST['signa1'];
$signa2 = $_POST['signa2'];
$jmlObat = $_POST['jmlObat'];
$jmlPermintaan = $_POST['jmlPermintaan'];
$nmObatNonDPHO = $nmObat;
$kdObat = $_POST['kdObat'];
if ($_POST['jenisObat'] == 'R') {
    $sql = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM pcare_obat WHERE racikan = '1'");
    $row = mysqli_fetch_assoc($sql);
    $kdRacikan = "R.0" . $row['total'] + 1;
    $racikan = true;
    $obatDPHO = false;
} else {
    $racikan = false;
    $obatDPHO = true;
}

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
