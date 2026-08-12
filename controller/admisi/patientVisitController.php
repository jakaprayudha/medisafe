<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_customer'])) {

   echo json_encode([
      'status' => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}


$id_customer = $_SESSION['id_customer'];

$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| QUERY
|--------------------------------------------------------------------------
|
| pasien_visit menjadi tabel utama.
| ms_patient hanya untuk mengambil identitas pasien.
|
*/

$query = "
    SELECT

        pv.id_visit,
        pv.id_patient,
        pv.visit_ID,
        pv.visit_date,
        pv.visit_time,
        pv.id_doctor,
        pv.id_poli,

        /* IDENTITAS / PASIEN */

        mp.patient_name,
        mp.nomor_rm,
        mp.patient_nik,
        mp.patient_bpjs,
        mp.patient_datebirth,

        /* TANDA VITAL */

        pv.tekanan_darah,
        pv.suhu,
        pv.nadi,
        pv.respirasi,

        pv.tinggi_badan,
        pv.berat_badan,

        pv.bmi,
        pv.bmi_keterangan,

        /* TAMBAHAN */

        pv.saturasi,
        pv.kondisi_masuk,
        pv.visit_notes

    FROM pasien_visit pv

    INNER JOIN ms_patient mp
        ON mp.id_patient = pv.id_patient

    WHERE pv.id_customer = ?

      AND (
            mp.patient_name LIKE ?
            OR mp.patient_nik LIKE ?
            OR mp.patient_bpjs LIKE ?
            OR mp.nomor_rm LIKE ?
          )

    ORDER BY
        pv.visit_date DESC,
        pv.id_visit DESC

    LIMIT 20
";


$stmt = $koneksi->prepare($query);


if (!$stmt) {

   echo json_encode([
      'status' => 'error',
      'message' => 'Prepare query gagal: ' . $koneksi->error
   ]);

   exit;
}


$like = '%' . $search . '%';


$stmt->bind_param(
   "issss",
   $id_customer,
   $like,
   $like,
   $like,
   $like
);


$stmt->execute();


$result = $stmt->get_result();


$data = [];


while ($row = $result->fetch_assoc()) {

   $data[] = $row;
}


$stmt->close();


echo json_encode([
   'status' => 'success',
   'data' => $data
]);
