<?php
/**
 * Data kartu status KB (JSON). Cetak formulir_status_kb kini membaca data langsung; file ini dipertahankan untuk
 * kompatibilitas. Dulu: no & rm disisipkan ke SQL (SQL injection), tanpa batas faskes, dan ms_patient digabung lewat
 * nomor_rm saja (bisa menampilkan pasien faskes lain).
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
$st = $koneksi->prepare("SELECT vt.*, mp.patient_datebirth, mp.patient_name, mp.patient_gender, mp.patient_address, mp.nomor_rm,
      mp.patient_education AS pendidikan_istri, COALESCE(NULLIF(mp.patient_occupation, ''), mp.patient_worker) AS pekerjaan_istri, v.visit_date
   FROM visit_kb_status vt JOIN pasien_visit v ON v.visit_ID = vt.visit_ID JOIN ms_patient mp ON mp.id_patient = v.id_patient
   WHERE vt.visit_ID = ? AND v.id_customer = ? ORDER BY vt.id_kb_status DESC LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();
if (!$data) {
   echo json_encode(['status' => 'empty']);
   exit;
}
$data['usia_format'] = '-';
if (!empty($data['patient_datebirth'])) {
   $d = (new DateTime($data['patient_datebirth']))->diff(new DateTime(substr((string) $data['visit_date'], 0, 10) ?: 'today'));
   $data['usia_format'] = "{$d->y} tahun {$d->m} bulan {$d->d} hari";
}
echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
