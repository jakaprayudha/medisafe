<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nomor = $_POST['nomor'];
$tanggal = $_POST['tanggal'];
$kdpoli = $_POST['poli'];
$kartu = $_POST['kartu'];
$result = bpjsDelete('/kunjungan/' . $nomor);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_kunjungan WHERE noKunjungan = ?");
    $stmt->bind_param("s", $nomor);
    $hasil = $stmt->execute();
    $stmt1 = $koneksi->prepare("DELETE FROM pcare_pendaftaran WHERE noKartu = ? AND tanggal_daftar = ? AND kdPoli = ?");
    $stmt1->bind_param("sss", $kartu, $tanggal, $kdpoli);
    $hasil1 = $stmt1->execute();
    $stmt->close();
    if ($hasil AND $hasil1) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Kunjungan",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Kunjungan",
        ];
    }
}
echo json_encode($response);