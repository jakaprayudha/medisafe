<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kdTindakanSK = $_POST['kdTindakanSK'] ?? '0';
$noKunjungan = $_POST['noKunjungan'];
$kdTindakan = $_POST['kdTindakan'];
$biaya = preg_replace('/[^0-9]/', '', $_POST['biaya']);
$keterangan = $_POST['keterangan'];
$hasil = $_POST['hasil'] ?? '0';
$nmtindakan = $_POST['nmTindakan'];

$payload = [
    "kdTindakanSK" => $kdTindakanSK,
    "noKunjungan" => $noKunjungan,
    "kdTindakan" => $kdTindakan,
    "biaya" => $biaya,
    "keterangan" => $keterangan,
    "hasil" => $hasil
];
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost('/tindakan', $payload);
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
    $kdTindakanSK = $result['data']['message'];
    $stmt = $koneksi->prepare("INSERT INTO pcare_tindakan(kdTindakanSK, noKunjungan, kdTindakan, nmTindakan, biaya, keterangan, hasil)VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $kdTindakan, $noKunjungan, $kdTindakan, $nmtindakan, $biaya, $keterangan, $hasil);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Menambahkan Tindakan",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Menambah Tindakan",
        ];
    }
}
echo json_encode($response);