<?php
// Data cetak Instruksi Dokter (print/formulir_instruksi). Perbaikan: query dirangkai dari URL (SQL injection),
// tanpa batas faskes, difilter nomor RM dari URL (format beda -> kosong).
require '../../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
$visit = (string) ($_GET['visit'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$st = $koneksi->prepare("SELECT d.cppt_date, d.cppt_time, d.perjalanan, d.tindakan, d.tanda_tangan, d.users_entry, d.ku, d.pemeriksaan_fisik, d.diagnosa, d.pengobatan
   FROM visit_cppt_dokter d
   JOIN pasien_visit v ON v.visit_ID = d.visit_ID AND v.id_customer = ?
   WHERE d.visit_ID = ?
   ORDER BY d.cppt_date ASC, d.cppt_time ASC, d.id_cppt ASC");
$st->bind_param('is', $idc, $visit);
$st->execute();
$data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
