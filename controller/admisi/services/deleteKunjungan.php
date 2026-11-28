<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nomor = $_POST['nomor'];
$tanggal = $_POST['tanggal'];
$kdpoli = $_POST['poli'];
$kartu = $_POST['kartu'];
require_once __DIR__ . '/../../../utility/activityLog.php';
$pkLama = logAmbilBaris($koneksi, 'pcare_kunjungan', 'noKunjungan', $nomor);
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
// log hapus kunjungan PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_kunjungan', 'id' => $nomor, 'label' => 'Kunjungan PCare ' . $nomor, 'visit_ID' => $pkLama['nomor_visit'] ?? null],
    (!empty($response['success']) ? 'Kunjungan PCare dihapus di BPJS dan lokal' : 'Hapus kunjungan PCare GAGAL: ' . mb_substr((string) ($response['message'] ?? ''), 0, 300))
    . ' · kartu ' . $kartu . ' · ' . $tanggal . ' · poli ' . $kdpoli, $pkLama, null);
echo json_encode($response);