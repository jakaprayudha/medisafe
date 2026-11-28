<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$idKlp = $_POST['id'];
$noKartu = $_POST['no'];
$tgl = $_POST['tgl'];
$noKlp = null;
$lgLama = null;
$lgSt = $koneksi->prepare("SELECT * FROM pcare_pstKelompok WHERE idKelompok = ? AND noKartu = ? LIMIT 1");
if ($lgSt) {
    $lgSt->bind_param('ss', $idKlp, $noKartu);
    $lgSt->execute();
    $lgLama = $lgSt->get_result()->fetch_assoc() ?: null;
    $lgSt->close();
}
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
// log aktivitas PCare (berhasil maupun ditolak BPJS)
logAktivitas($koneksi, 'diagnosa', 'hapus_pcare', ['tipe' => 'pcare_pstKelompok', 'id' => $idKlp, 'label' => 'Peserta kelompok ' . $idKlp . ' · kartu ' . $noKartu],
    (!empty($response['success']) ? 'Peserta dikeluarkan dari kelompok di PCare' : 'Keluarkan peserta kelompok PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama ?? ['idKelompok' => $idKlp, 'noKartu' => $noKartu], null);
echo json_encode($response);