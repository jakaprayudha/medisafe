<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$id = $_POST['kode'];
$nomor = $_POST['no'];

$result = bpjsDelete('/obat/' . $id . '/kunjungan/' . $nomor);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_obat WHERE kdObatSK = ? AND noKunjungan = ?");
    $stmt->bind_param("ss", $id, $nomor);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Obat",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Obat",
        ];
    }
}
echo json_encode($response);
