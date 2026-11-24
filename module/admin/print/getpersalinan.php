<?php
/**
 * Data rekap persalinan (JSON). Cetak formulir_persalinan kini membaca data langsung; file ini dipertahankan untuk
 * kompatibilitas. Dulu: no & rm disisipkan ke SQL (SQL injection) tanpa cek login / batas faskes.
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
$st = $koneksi->prepare("SELECT p.patient_name, p.patient_nik, p.patient_place, p.patient_datebirth, p.patient_address, p.patient_phone,
      vp.gravid, vp.abortus, vp.partus, vp.jenis_persalinan, vp.tarif_paket, vp.ttd_file
   FROM pasien_visit v JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN visit_persalinan vp ON vp.visit_ID = v.visit_ID
   WHERE v.visit_ID = ? AND v.id_customer = ? ORDER BY vp.id_persalinan DESC LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();
echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
