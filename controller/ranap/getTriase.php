<?php
/**
 * Data triase terakhir satu kunjungan (module/admin/triase).
 * GET ?no=<visit_ID> -> { status, data:{...}|null }
 * Dulu id_customer dikunci ke 19 -> triase faskes lain tidak pernah termuat (form selalu kosong); juga SQL injection.
 */
require '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
if ($idc <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
   exit;
}
$no = trim((string) ($_GET['no'] ?? ''));
$st = $koneksi->prepare("SELECT * FROM pasien_triase WHERE visit_ID = ? AND id_customer = ? ORDER BY id_triase DESC LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$data = $st->get_result()->fetch_assoc() ?: null;
$st->close();

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
