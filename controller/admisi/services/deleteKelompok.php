<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$id = $_POST['id'];
$result = bpjsDelete('/kelompok/kegiatan/' . $id);
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
    $stmt = $koneksi->prepare("DELETE FROM pcare_kegiatan WHERE eduid = ?");
    $stmt->bind_param("s", $id);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Kelompok",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Kelompok",
        ];
    }
}
echo json_encode($response);
