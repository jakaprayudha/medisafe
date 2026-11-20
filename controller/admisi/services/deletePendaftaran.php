<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/visitGuard.php';
header('Content-Type: application/json');
$visit = trim((string) ($_POST['novisit'] ?? ''));
$provider = (string) ($_POST['provider'] ?? '');
$alasan = trim((string) ($_POST['alasan'] ?? ''));
$msg = '';

// hanya kunjungan faskes di session (dulu: kunjungan faskes mana pun bisa dibatalkan cukup dengan visit_ID)
$kunj = visitGuard($koneksi, 'visit_ID', $visit);
if ((int) $kunj['visit_status'] === 4) {
    echo json_encode(['success' => false, 'message' => 'Kunjungan sudah selesai dilayani, tidak dapat dibatalkan.']);
    exit;
}
if ((int) $kunj['visit_status'] === 99) {
    echo json_encode(['success' => true, 'message' => 'Kunjungan sudah berstatus batal.']);
    exit;
}
if ($provider == '1') {
    $stmt = $koneksi->prepare("SELECT * FROM pcare_pendaftaran WHERE nomor_visit = ?");
    $stmt->bind_param("s", $visit);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  if ($result) {
    $tglDB = $result['tanggal_daftar'];
    $nomor_kartu = $result['noKartu'];
    $noUrut = $result['noUrut'];
    $kdpoli = $result['kdPoli'];
    $tanggal = date("d-m-Y", strtotime($tglDB));

    $result = bpjsDelete('/pendaftaran/peserta/' . $nomor_kartu . '/tglDaftar/' . $tanggal . '/noUrut/' . $noUrut . '/kdPoli/' . $kdpoli);
    if ($result['code'] != "200") {
        $msg = $result['message'];
        if ($msg == null) {
            $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        }
        $msg = ". [Pcare Error]: " . $msg;
    }
    $stmt = $koneksi->prepare("DELETE FROM pcare_pendaftaran WHERE nomor_visit = ?");
    $stmt->bind_param("s", $visit);
    $hasil = $stmt->execute();
    $stmt->close();
  }
}

$idcKunj = (int) $kunj['id_customer'];
$stmt1 = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '99' WHERE visit_ID = ? AND id_customer = ?");
$stmt1->bind_param("si", $visit, $idcKunj);
$hasil1 = $stmt1->execute();

$stmt2 = $koneksi->prepare("UPDATE antrian_poli SET `status` = '99' WHERE nomor_visit = ?");
$stmt2->bind_param("s", $visit);
$hasil2 = $stmt2->execute();

$stmt1->close();
$stmt2->close();
if ($hasil1 and $hasil2) {
    $response = [
        'success' => true,
        // dulu pesan error PCare dibuang sehingga petugas tidak tahu pendaftaran di PCare masih aktif
        'message' => "Berhasil Batal Pendaftaran" . $msg,
        'peringatan' => $msg !== '' ? 'Pendaftaran di PCare mungkin belum terhapus' . $msg . ' Hapus manual di PCare bila perlu.' : null,
    ];
    error_log('[batalPendaftaran] ' . $visit . ' dibatalkan oleh ' . ($_SESSION['username'] ?? '-') . ($alasan !== '' ? ' alasan: ' . $alasan : ''));
} else {
    $response = [
        'success' => false,
        'message' => "Gagal Batal Pendaftaran" . mysqli_error($koneksi),
    ];
}

echo json_encode($response);
