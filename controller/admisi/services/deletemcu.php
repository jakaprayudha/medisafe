<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$noKunjungan = $_POST['noKunjung'];
$nomcu = $_POST['kdmcu'];
$result = bpjsDelete('/MCU/' . $nomcu . '/kunjungan/' . $noKunjungan);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_mcu WHERE kdMCU = ? AND noKunjungan = ?");
    $stmt->bind_param("ss", $nomcu, $noKunjungan);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Medical Check Up",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Medical Check Up",
        ];
    }
}
echo json_encode($response);
