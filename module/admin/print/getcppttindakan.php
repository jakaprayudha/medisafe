<?php
// Instruksi dokter pertama kunjungan (print/formulir_history_treatment). Perbaikan: query dirangkai dari URL
// (SQL injection), tanpa batas faskes, difilter nomor RM dari URL.
require '../../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
$no = (string) ($_GET['no'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$st = $koneksi->prepare("SELECT d.* FROM visit_cppt_dokter d
   JOIN pasien_visit v ON v.visit_ID = d.visit_ID AND v.id_customer = ?
   WHERE d.visit_ID = ?
   ORDER BY d.cppt_date ASC, d.cppt_time ASC, d.id_cppt ASC LIMIT 1");
$st->bind_param('is', $idc, $no);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();
if (!$data) {
   echo json_encode(["status" => "empty"]);
   exit;
}
echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
