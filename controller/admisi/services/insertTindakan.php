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


$statusEdit = false;
$method = "POST";
if ($kdTindakanSK > 0) {
    $statusEdit = true;
    $method = "PUT";
}

// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost('/tindakan', $payload, $method);
if ($result['code'] != '200') {
    $msg = $result['metadata'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    if ($statusEdit) {
        $message = "Berhasil Update Tindakan";
        $kdTindakanSKBaru = $result['data']['message'];
        $stmt = $koneksi->prepare("UPDATE pcare_tindakan SET kdTindakanSK = ? ,kdTindakan = ?, nmTindakan = ?,biaya = ?,keterangan = ?, hasil = ? WHERE noKunjungan = ? AND kdTindakanSK = ?");
        $stmt->bind_param("ssssssss", $kdTindakanSKBaru, $kdTindakan, $nmtindakan, $biaya, $keterangan, $hasil, $noKunjungan, $kdTindakanSK);
        $hasil = $stmt->execute();
        $stmt->close();
    } else {
        $message = "Berhasil Membuat Tindakan";
        $kdTindakanSKBaru = $result['data']['message'];
        $stmt = $koneksi->prepare("INSERT INTO pcare_tindakan(kdTindakanSK, noKunjungan, kdTindakan, nmTindakan, biaya, keterangan, hasil)VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $kdTkdTindakanSKBaruindakanSK, $noKunjungan, $kdTindakan, $nmtindakan, $biaya, $keterangan, $hasil);
        $hasil = $stmt->execute();
        $stmt->close();
    }
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => $message,
            'kode' => $kdTindakanSKBaru
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal",
        ];
    }
}
echo json_encode($response);
