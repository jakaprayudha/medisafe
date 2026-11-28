<?php
/**
 * Pra-cek sebelum membatalkan pendaftaran: data klinis apa yang sudah ada pada kunjungan
 * (asesmen, CPPT, rawat inap, tagihan). Hanya membaca. Dipakai dialog batal di registrasi & daftar pendaftaran.
 */
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../utility/visitGuard.php';
require_once __DIR__ . '/../../../utility/batalGuard.php';
header('Content-Type: application/json; charset=utf-8');
$visit = trim((string) ($_POST['novisit'] ?? $_GET['novisit'] ?? ''));
$kunj = visitGuard($koneksi, 'visit_ID', $visit);
session_write_close();
if (kunjunganSelesai($kunj['visit_status']) && !bolehBukaKunciSelesai()) {
    echo json_encode(['success' => false, 'message' => 'Kunjungan sudah selesai dilayani, tidak dapat dibatalkan.']);
    exit;
}
echo json_encode(['success' => true, 'sudah_batal' => (int) $kunj['visit_status'] === 99, 'klinis' => batalCekKlinis($koneksi, $kunj)], JSON_UNESCAPED_UNICODE);
