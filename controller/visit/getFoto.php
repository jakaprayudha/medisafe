<?php
/**
 * Foto pasien per kunjungan (module/admin/form_capture_patient). pasien_dokumen jenis FOTO_PASIEN, file di uploads/foto/.
 *   GET ?no=<visit_ID> -> { status, foto:<path relatif>|null, ada, waktu }
 * Perbaikan: rm & no disisipkan langsung ke SQL (SQL injection), tanpa cek login / batas faskes (pasien_dokumen tidak
 * punya id_customer -> dibatasi lewat kunjungan), difilter nomor RM dari URL (format beda -> foto tidak tampil).
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
session_write_close();
$no = (string) $kunj['visit_ID'];

$st = $koneksi->prepare("SELECT foto_path, tgl_upload FROM pasien_dokumen WHERE visit_ID = ? AND jenis_dokumen = 'FOTO_PASIEN' ORDER BY id_dokumen DESC LIMIT 1");
$st->bind_param('s', $no);
$st->execute();
$r = $st->get_result()->fetch_assoc();
$st->close();

$path = $r ? (string) $r['foto_path'] : '';
if ($path === '' || strpos($path, 'uploads/foto/') !== 0) {
   echo json_encode(['status' => 'success', 'foto' => null]);
   exit;
}
$file = basename($path);
echo json_encode([
   'status' => 'success',
   'foto' => 'uploads/foto/' . rawurlencode($file),
   'ada' => is_file(dirname(__DIR__, 2) . '/uploads/foto/' . $file),
   'waktu' => $r['tgl_upload'] ? substr((string) $r['tgl_upload'], 0, 16) : null,
], JSON_UNESCAPED_SLASHES);
