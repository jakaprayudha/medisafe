<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$id = $_POST['kode'];
$nomor = $_POST['no'];

$lgLama = null;
$lgSt = $koneksi->prepare("SELECT * FROM pcare_obat WHERE kdObatSK = ? AND noKunjungan = ? LIMIT 1");
if ($lgSt) {
    $lgSt->bind_param('ss', $id, $nomor);
    $lgSt->execute();
    $lgLama = $lgSt->get_result()->fetch_assoc() ?: null;
    $lgSt->close();
}
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
// log aktivitas PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_obat', 'id' => $id, 'label' => 'Obat PCare · ' . ($lgLama['nmObat'] ?? $id) . ' · kunjungan ' . $nomor, 'visit_ID' => (logAmbilBaris($koneksi, 'pcare_kunjungan', 'noKunjungan', $nomor) ?? [])['nomor_visit'] ?? null],
    (!empty($response['success']) ? 'Obat dihapus di PCare dan lokal' : 'Hapus obat PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama, null);
echo json_encode($response);
