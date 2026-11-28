<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$id = $_POST['id'];
$lgLama = logAmbilBaris($koneksi, 'pcare_kegiatan', 'eduId', $id);
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
// log aktivitas PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_kegiatan', 'id' => $id, 'label' => 'Kegiatan kelompok ' . $id . (!empty($lgLama['materi']) ? ' · ' . $lgLama['materi'] : '')],
    (!empty($response['success']) ? 'Kegiatan kelompok dihapus di PCare dan lokal' : 'Hapus kegiatan kelompok PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama, null);
echo json_encode($response);
