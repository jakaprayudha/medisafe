<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

$q = $koneksi->query("
    SELECT vt.*, mp.patient_datebirth, mp.patient_name, mp.patient_gender,
           mp.patient_address, mp.nomor_rm
    FROM visit_kb_status vt 
    INNER JOIN ms_patient mp ON mp.nomor_rm = vt.nomor_rm
    WHERE vt.visit_ID='$no' AND mp.nomor_rm='$rm'
    LIMIT 1
");

if ($q->num_rows == 0) {
   echo json_encode(["status" => "empty"]);
   exit;
}

$data = $q->fetch_assoc();

/* ===============================
   HITUNG USIA (Tahun Bulan Hari)
================================= */
$usia_format = "-";

if (!empty($data['patient_datebirth'])) {
   $tgl_lahir = new DateTime($data['patient_datebirth']);
   $today = new DateTime(); // hari ini
   $diff = $tgl_lahir->diff($today);

   $usia_format = $diff->y . " tahun " . $diff->m . " bulan " . $diff->d . " hari";
}

$data['usia_format'] = $usia_format;

/* ========== OUTPUT JSON ========== */
echo json_encode([
   "status" => "success",
   "data" => $data
]);
