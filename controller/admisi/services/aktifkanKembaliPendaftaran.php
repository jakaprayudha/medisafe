<?php
/**
 * Aktifkan kembali kunjungan yang sudah dibatalkan (visit_status 99) agar RME/CPPT bisa diisi lagi.
 * Dulu tidak ada jalan kembali: kunjungan batal terkunci (CPPT menolak "Kunjungan sudah dibatalkan").
 *
 * - alasan wajib (min. 10 karakter), dicatat di log_batal_pendaftaran (alasan diawali "[DIAKTIFKAN KEMBALI]")
 * - status dipulihkan: 3 (Dilayani) bila sudah ada data pemeriksaan/CPPT/rawat inap, selain itu 0 (Menunggu)
 * - ditolak bila pasien sudah punya kunjungan aktif lain di poli & tanggal yang sama
 * - pasien BPJS: pendaftaran PCare yang sudah dihapus TIDAK dibuat ulang otomatis; hasilnya memberi peringatan
 */
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../utility/visitGuard.php';
require_once __DIR__ . '/../../../utility/batalGuard.php';
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}
$visit = trim((string) ($_POST['novisit'] ?? ''));
$alasan = trim((string) ($_POST['alasan'] ?? ''));
$kunj = visitGuard($koneksi, 'visit_ID', $visit);
$uname = (string) ($_SESSION['username'] ?? '-');
session_write_close();
$keluar = function (int $kode, array $isi) {
    http_response_code($kode);
    echo json_encode($isi + ['success' => false], JSON_UNESCAPED_UNICODE);
    exit;
};
if ((int) $kunj['visit_status'] !== 99) $keluar(409, ['message' => 'Kunjungan ini tidak berstatus batal.']);
if (mb_strlen($alasan) < 10) $keluar(422, ['message' => 'Alasan wajib diisi (minimal 10 karakter).', 'field' => 'alasan']);

$idc = (int) $kunj['id_customer'];
$idv = (int) $kunj['id_visit'];
$klinis = batalCekKlinis($koneksi, $kunj);
$baru = $klinis['butuh_konfirmasi'] ? 3 : 0;
$antri = $klinis['butuh_konfirmasi'] ? 1 : 0;

$koneksi->begin_transaction();
try {
    $st = $koneksi->prepare("SELECT visit_date, id_poli, id_patient, id_provider FROM pasien_visit WHERE id_visit = ? AND id_customer = ? FOR UPDATE");
    $st->bind_param('ii', $idv, $idc);
    $st->execute();
    $v = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$v) throw new RuntimeException('Kunjungan tidak ditemukan.');
    $st = $koneksi->prepare("SELECT visit_ID FROM pasien_visit WHERE id_patient = ? AND id_customer = ? AND visit_date = ? AND id_poli = ? AND visit_status <> 99 AND id_visit <> ? LIMIT 1");
    $st->bind_param('iissi', $v['id_patient'], $idc, $v['visit_date'], $v['id_poli'], $idv);
    $st->execute();
    $lain = $st->get_result()->fetch_assoc();
    $st->close();
    if ($lain) {
        $koneksi->rollback();
        $keluar(409, ['message' => 'Pasien sudah punya kunjungan aktif lain di poli dan tanggal yang sama (' . $lain['visit_ID'] . '). Gunakan kunjungan tersebut.']);
    }
    $st = $koneksi->prepare("UPDATE pasien_visit SET visit_status = ? WHERE id_visit = ? AND id_customer = ? AND visit_status = 99");
    $st->bind_param('iii', $baru, $idv, $idc);
    $st->execute();
    $ubah = $st->affected_rows;
    $st->close();
    if ($ubah !== 1) throw new RuntimeException('Status kunjungan sudah berubah, muat ulang halaman.');
    $st = $koneksi->prepare("UPDATE antrian_poli SET `status` = ? WHERE nomor_visit = ? AND id_customer = ? AND `status` = 99");
    $st->bind_param('isi', $antri, $visit, $idc);
    $st->execute();
    $st->close();
    $koneksi->commit();
} catch (Throwable $e) {
    $koneksi->rollback();
    error_log('[aktifkanKembali] ' . $visit . ' ' . $e->getMessage());
    $keluar(500, ['message' => 'Gagal mengaktifkan kembali kunjungan. Muat ulang halaman lalu coba lagi.']);
}

$peringatan = null;
if ((string) $v['id_provider'] === '1') {
    $st = $koneksi->prepare("SELECT 1 FROM pcare_pendaftaran WHERE nomor_visit = ? LIMIT 1");
    $st->bind_param('s', $visit);
    $st->execute();
    $adaPcare = (bool) $st->get_result()->fetch_row();
    $st->close();
    if (!$adaPcare) $peringatan = 'Pendaftaran PCare/antrean BPJS kunjungan ini sudah dihapus saat dibatalkan dan belum dibuat ulang. Daftarkan ulang peserta di PCare sebelum menyimpan pemeriksaan ke BPJS.';
}
$_SESSION['username'] = $uname;
batalCatat($koneksi, $kunj, $klinis, '[DIAKTIFKAN KEMBALI] ' . $alasan, (string) $v['id_provider'], 'Status dipulihkan ke ' . $baru);
echo json_encode(['success' => true, 'message' => 'Kunjungan diaktifkan kembali.', 'status' => $baru, 'peringatan' => $peringatan], JSON_UNESCAPED_UNICODE);
