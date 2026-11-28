<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
// dulu: novisit disisipkan langsung ke SQL (SQL injection) & tanpa cek faskes
require_once __DIR__ . '/../../utility/visitGuard.php';
$nomor_visit = (string) ($_POST['novisit'] ?? '');
$kunjBatal = visitGuard($koneksi, 'visit_ID', $nomor_visit);
// alasan wajib + konfirmasi bila kunjungan sudah punya asesmen/CPPT/rawat inap (sebelum antrean BPJS dibatalkan)
require_once __DIR__ . '/../../utility/batalGuard.php';
if (kunjunganSelesai($kunjBatal['visit_status']) && !bolehBukaKunciSelesai()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Kunjungan sudah selesai dilayani, tidak dapat dibatalkan.']);
    exit;
}
batalPastikan($koneksi, $kunjBatal, (string) ($_POST['alasan'] ?? ''));
$stBatal = $koneksi->prepare("SELECT pv.noKartu, pv.visit_date, p.kdPoli FROM pasien_visit AS pv INNER JOIN master_poli AS p ON p.nmPoli = pv.id_poli WHERE pv.id_visit = ? LIMIT 1");
$idVisitBatal = (int) $kunjBatal['id_visit'];
$stBatal->bind_param('i', $idVisitBatal);
$stBatal->execute();
$sql = $stBatal->get_result()->fetch_assoc() ?: [];
$stBatal->close();
$tanggalperiksa = $sql['visit_date'];
$kdPoli = $sql['kdPoli'];
$nomorkartu = $sql['noKartu'];
$alasan = trim((string) ($_POST['alasan'] ?? '')) ?: 'Dibatalkan petugas';
if ($status_antrol) {
    $payload = [
        "tanggalperiksa"  => $tanggalperiksa,
        "kodepoli" => $kdPoli,
        "nomorkartu" => $nomorkartu,
        "alasan"      => $alasan
    ];
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    $result = antrolPost("/antrean/batal", $payload);
    // if ($result['code'] != '200') {
    //     $msg = $result['message'];
    //     if ($msg == null) {
    //         $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    //     }
    //     $response = [
    //         'success' => false,
    //         'message' => $msg,
    //         'result' => $result
    //     ];
    // } else {
    //     $response = [
    //         'success'  => true,
    //         'message'  => "Berhasil Batal Pasien",
    //     ];
    // }
    // pembatalan lokal tetap dilanjutkan, tetapi kegagalan BPJS diteruskan sebagai peringatan (dulu dibuang)
    $kodeBpjs = (string) ($result['code'] ?? ($result['metadata']['code'] ?? ''));
    $response = [
        'success'  => true,
        'message'  => "Berhasil Batal Pasien",
        'peringatan' => in_array($kodeBpjs, ['200', '1'], true) ? null
            : 'Antrean di BPJS belum terbatalkan: ' . ($result['message'] ?? ($result['metadata']['message'] ?? 'layanan BPJS tidak merespons')) . '. Batalkan manual bila perlu.',
    ];
} else {
    // Klinik tidak terdaftar di setting_antrol, maka lewati BPJS,
    // di deletePendaftaran.php yang mengubah pasien_visit.
    $response = [
        'success'  => true,
        'message'  => "Berhasil Batal Pasien",
    ];
}
echo json_encode($response);
