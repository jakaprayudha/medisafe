<?php
/**
 * Data USG kunjungan (JSON). Cetak print/formulir_usg kini dirender di server; file ini dipertahankan untuk kompatibilitas.
 * Dulu: no & rm disisipkan ke SQL (SQL injection), tanpa batas faskes, SELECT * gabungan kunjungan + pasien.
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
$st = $koneksi->prepare("SELECT u.id_usg, u.tanggal_pemeriksaan, u.usia_kandungan, u.usg1, u.usg2, u.usg3, u.dokter, u.interpretasi, p.patient_name, p.nomor_rm
   FROM usg_results u JOIN pasien_visit v ON v.visit_ID = u.visit_ID LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   WHERE u.visit_ID = ? AND v.id_customer = ? ORDER BY u.tanggal_pemeriksaan ASC, u.id_usg ASC");
$st->bind_param('si', $no, $idc);
$st->execute();
$data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
if (!$data) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
   exit;
}
echo json_encode(['status' => 'success', 'data' => $data[0], 'semua' => $data], JSON_UNESCAPED_UNICODE);
