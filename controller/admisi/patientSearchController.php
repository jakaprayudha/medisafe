<?php
/**
 * Pencarian pasien (select2 / modal pendaftaran cepat).  GET search=...
 * Dulu tanpa LIMIT (kata 2 huruf mengembalikan ribuan baris) dan tidak bisa mencari nomor RM.
 */
include '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$search = trim((string) ($_GET['search'] ?? ''));
$id_customer = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
if ($id_customer <= 0 || mb_strlen($search) < 2) {
   echo json_encode(['status' => 'success', 'data' => []]);
   exit;
}

$like = '%' . addcslashes($search, '%_\\') . '%';
$awal = addcslashes($search, '%_\\') . '%';
$stmt = $koneksi->prepare("SELECT id_patient, patient_name, nomor_rm, patient_datebirth, patient_nik, patient_bpjs, patient_gender
   FROM ms_patient
   WHERE id_customer = ? AND (patient_name LIKE ? OR nomor_rm = ? OR patient_nik LIKE ? OR patient_bpjs LIKE ?)
   ORDER BY (nomor_rm = ?) DESC, (patient_name LIKE ?) DESC, patient_name
   LIMIT 20");
$stmt->bind_param("issssss", $id_customer, $like, $search, $awal, $awal, $search, $awal);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
