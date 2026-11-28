<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$noKunjungan = $_POST['nomor'];
$id = $_POST['id'];
$lgLama = null;
$lgSt = $koneksi->prepare("SELECT * FROM pcare_tindakan WHERE kdTindakanSK = ? AND noKunjungan = ? LIMIT 1");
if ($lgSt) {
    $lgSt->bind_param('ss', $id, $noKunjungan);
    $lgSt->execute();
    $lgLama = $lgSt->get_result()->fetch_assoc() ?: null;
    $lgSt->close();
}
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
// log aktivitas PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_tindakan', 'id' => $id, 'label' => 'Tindakan PCare · ' . ($lgLama['nmTindakan'] ?? $id) . ' · kunjungan ' . $noKunjungan, 'visit_ID' => (logAmbilBaris($koneksi, 'pcare_kunjungan', 'noKunjungan', $noKunjungan) ?? [])['nomor_visit'] ?? null],
    (!empty($response['success']) ? 'Tindakan dihapus di PCare dan lokal' : 'Hapus tindakan PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama, null);
echo json_encode($response);
