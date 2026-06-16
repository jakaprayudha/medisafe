<?php
require '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter kurang"]);
   exit;
}

// gabungkan data pasien + visit + tabel persalinan
$q = mysqli_query($koneksi, "
    SELECT 
        ms_patient.patient_name,
        ms_patient.patient_nik,
        ms_patient.patient_place,
        ms_patient.patient_datebirth,
        ms_patient.patient_address,
        ms_patient.patient_phone,

        visit_persalinan.gravid,
        visit_persalinan.abortus,
        visit_persalinan.partus,
        visit_persalinan.jenis_persalinan,
        visit_persalinan.tarif_paket,
        visit_persalinan.ttd_file

    FROM pasien_visit
    INNER JOIN ms_patient 
        ON ms_patient.id_patient = pasien_visit.id_patient
    LEFT JOIN visit_persalinan
        ON visit_persalinan.visit_ID = pasien_visit.visit_ID
       AND visit_persalinan.nomor_rm = '$rm'
    WHERE pasien_visit.visit_ID = '$no'
");

$data = mysqli_fetch_assoc($q);

echo json_encode([
   "status" => "success",
   "data" => $data
]);
