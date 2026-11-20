<?php
/**
 * Check-in booking Mobile JKN (status 10 -> 0 = menunggu dilayani).
 * Dulu: tanpa cek faskes & status -> kunjungan faskes lain bisa diubah, dan kunjungan selesai/batal bisa "direset" ke 0.
 */
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

$kunj = visitGuard($koneksi, 'visit_ID', $_POST['visit'] ?? '');
session_write_close();
if ((int) $kunj['visit_status'] !== 10) {
    echo json_encode(['success' => false, 'message' => 'Pasien ini tidak dalam status belum check-in (mungkin sudah check-in / batal).']);
    exit;
}
$st = $koneksi->prepare("SELECT visit_date FROM pasien_visit WHERE id_visit = ?");
$id = (int) $kunj['id_visit'];
$st->bind_param('i', $id);
$st->execute();
$tgl = (string) (($st->get_result()->fetch_assoc() ?: [])['visit_date'] ?? '');
$st->close();
if ($tgl > date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Booking untuk tanggal ' . date('d-m-Y', strtotime($tgl)) . ' belum bisa check-in hari ini.']);
    exit;
}
$idc = (int) $kunj['id_customer'];
$st = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '0' WHERE id_visit = ? AND id_customer = ? AND visit_status = 10");
$st->bind_param('ii', $id, $idc);
$ok = $st->execute() && $st->affected_rows > 0;
$st->close();
echo json_encode($ok ? ['success' => true, 'message' => 'Pasien berhasil check-in'] : ['success' => false, 'message' => 'Gagal check-in, muat ulang data.']);
