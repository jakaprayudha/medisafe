<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$noKunjungan = $_POST['noKunjung'];
$nomcu = $_POST['kdmcu'];
$lgLama = null;
$lgSt = $koneksi->prepare("SELECT * FROM pcare_mcu WHERE kdMCU = ? AND noKunjungan = ? LIMIT 1");
if ($lgSt) {
    $lgSt->bind_param('ss', $nomcu, $noKunjungan);
    $lgSt->execute();
    $lgLama = $lgSt->get_result()->fetch_assoc() ?: null;
    $lgSt->close();
}
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
// log aktivitas PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_mcu', 'id' => $nomcu, 'label' => 'MCU ' . $nomcu . ' · kunjungan ' . $noKunjungan, 'visit_ID' => (logAmbilBaris($koneksi, 'pcare_kunjungan', 'noKunjungan', $noKunjungan) ?? [])['nomor_visit'] ?? null],
    (!empty($response['success']) ? 'MCU dihapus di PCare dan lokal' : 'Hapus MCU PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama, null);
echo json_encode($response);
