<?php
/**
 * Data visit_ranap (JSON). Cetak formulir_inout_ranap kini dirender di server; file ini dipertahankan untuk kompatibilitas.
 * Dulu: no & rm disisipkan ke SQL (SQL injection) tanpa cek login / batas faskes.
 */
require '../../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
$no = trim((string) ($_GET['no'] ?? ''));
if ($idc <= 0 || $no === '') {
   http_response_code($idc <= 0 ? 401 : 400);
   echo json_encode(['status' => 'error', 'message' => $idc <= 0 ? 'Sesi berakhir.' : 'Parameter kurang']);
   exit;
}
$st = $koneksi->prepare("SELECT r.* FROM visit_ranap r JOIN pasien_visit v ON v.visit_ID = r.visit_ID WHERE r.visit_ID = ? AND v.id_customer = ? ORDER BY r.id_inap DESC LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();
echo json_encode($data ? ['status' => 'success', 'data' => $data] : ['status' => 'empty'], JSON_UNESCAPED_UNICODE);
