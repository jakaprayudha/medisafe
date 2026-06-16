<?php
include '../../database/connect.php';

header("Content-Type: application/json");

$no = $_GET['no'] ?? null;   // visit_ID
$rm = $_GET['rm'] ?? null;   // nomor_rm

if (!$no || !$rm) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
    exit;
}

// Ambil data pasien & dokter
$q = mysqli_query($koneksi, "
    SELECT 
        p.patient_name,
        p.patient_gender,
        p.patient_datebirth,
        d.doctor_name,
        v.visit_date
    FROM ms_patient p
    JOIN pasien_visit v ON v.id_patient = p.id_patient
    INNER JOIN ms_doctor d ON d.id_doctor = v.id_doctor
    WHERE v.visit_ID = '$no' AND p.nomor_rm = '$rm'
    LIMIT 1
");

$pasien = mysqli_fetch_assoc($q);

// Jika tidak ditemukan
if (!$pasien) {
    echo json_encode(["status" => "error", "message" => "Data pasien tidak ditemukan"]);
    exit;
}

// ===============================
// HITUNG USIA (tahun–bulan–hari)
// ===============================
$usia = "-";

if (!empty($pasien['patient_datebirth']) && !empty($pasien['visit_date'])) {

    $dob = new DateTime($pasien['patient_datebirth']);  // tanggal lahir
    $visit = new DateTime($pasien['visit_date']);        // tanggal kunjungan

    $diff = $dob->diff($visit);

    $usia = $diff->y . " tahun " . $diff->m . " bulan " . $diff->d . " hari";
}

// Ambil data SEP
$qSep = mysqli_query($koneksi, "SELECT sep_file 
    FROM pasien_sep
    WHERE nomor_rm = '$rm'
    AND visit_ID = '$no'
    LIMIT 1
");

$sep = mysqli_fetch_assoc($qSep);

echo json_encode([
    "status" => "success",
    "pasien" => $pasien,
    "usia"   => $usia, // ← usia dikirim ke view
    "sep"    => $sep
]);
