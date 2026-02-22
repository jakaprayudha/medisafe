<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$noKunjungan = $_POST['nomor'];
$id = $_POST['id'];
$result = bpjsDelete('/tindakan/' . $id . '/kunjungan/' . $noKunjungan);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_tindakan WHERE kdTindakanSK = ? AND noKunjungan = ?");
    $stmt->bind_param("ss", $id, $noKunjungan);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Tindakan",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Tindakan",
        ];
    }
}
echo json_encode($response);
