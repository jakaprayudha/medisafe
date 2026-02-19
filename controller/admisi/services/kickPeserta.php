<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$idKlp = $_POST['id'];
$noKartu = $_POST['no'];
$tgl = $_POST['tgl'];
$noKlp = null;
$result = bpjsDelete('/kelompok/peserta/' . $idKlp . '/' . $noKartu);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_pstKelompok WHERE idKelompok = ? AND noKartu = ?");
    $stmt->bind_param("ss", $idKlp, $noKartu);
    $stmt->execute();
    $stmt1 = $koneksi->prepare("UPDATE pcare_pendaftaran SET idKlp = ? WHERE noKartu = ? AND tanggal_daftar = ?");
    $stmt1->bind_param("sss", $noKlp, $noKartu, $tgl);
    $stmt1->execute();
    if ($stmt && $stmt1) {
        $response = [
            'success' => true,
            'message' => "Berhasil Keluarkan Pasien",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Keluarkan Pasien Dari Kelompok",
        ];
    }
}
echo json_encode($response);