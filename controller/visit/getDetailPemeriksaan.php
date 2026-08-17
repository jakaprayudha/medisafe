<?php

include '../../database/connect.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (empty($id)) {

    echo json_encode([
        'status' => false,
        'message' => 'ID visit tidak ditemukan.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA VISIT
|--------------------------------------------------------------------------
*/

$query = "SELECT 
    pv.id_visit,
    pv.visit_ID,
    pv.id_patient,
    pv.id_doctor,
    pv.visit_date,
    pv.visit_time,
    pv.id_poli,
    pv.patient_name_pcare,
    pv.anamnesa,
    pv.catatan_screening,

    pv.kdDiag1,
    pv.kdDiag2,
    pv.kdDiag3,

    pv.nmDiag1,
    pv.nmDiag2,
    pv.nmDiag3,

    pv.tindakan,

    pv.tekanan_darah,
    pv.suhu,
    pv.nadi,
    pv.respirasi,
    pv.tinggi_badan,
    pv.berat_badan,
    pv.bmi,
    pv.bmi_keterangan,
    pv.saturasi,

    mp.patient_datebirth

FROM pasien_visit AS pv

LEFT JOIN ms_patient AS mp
    ON mp.id_patient = pv.id_patient

WHERE pv.id_visit = ?

LIMIT 1
";


$stmt = $koneksi->prepare($query);

if (!$stmt) {

    echo json_encode([
        'status' => false,
        'message' => 'Prepare query visit gagal: ' . $koneksi->error
    ]);

    exit;
}


$stmt->bind_param("s", $id);

$stmt->execute();

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| CEK DATA VISIT
|--------------------------------------------------------------------------
*/

if (!$data) {

    echo json_encode([
        'status' => false,
        'message' => 'Data pemeriksaan tidak ditemukan.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL VISIT_ID
|--------------------------------------------------------------------------
|
| pasien_billing menggunakan visit_ID dari pasien_visit.
|
*/

$visitID = $data['visit_ID'] ?? '';


/*
|--------------------------------------------------------------------------
| AMBIL DATA TINDAKAN / BILLING
|--------------------------------------------------------------------------
*/

$tindakan = [];


if (!empty($visitID)) {

    $qTindakan = "SELECT
        id_billing,
        id_visit,
        billing_item,
        billing_category

    FROM pasien_billing

    WHERE id_visit = ?

    ORDER BY id_billing ASC
    ";


    $stmtTindakan = $koneksi->prepare($qTindakan);


    if ($stmtTindakan) {

        $stmtTindakan->bind_param(
            "s",
            $visitID
        );

        $stmtTindakan->execute();

        $resultTindakan =
            $stmtTindakan->get_result();


        while ($row =
            $resultTindakan->fetch_assoc()
        ) {

            $tindakan[] = $row;
        }


        $stmtTindakan->close();
    }
}


/*
|--------------------------------------------------------------------------
| MASUKKAN DATA BILLING KE RESPONSE
|--------------------------------------------------------------------------
*/

$data['tindakan'] = $tindakan;


/*
|--------------------------------------------------------------------------
| BUAT VERSI TEXT
|--------------------------------------------------------------------------
|
| Untuk frontend:
|
| $('#d_tindakan').text(d.tindakan_text);
|
*/

$tindakanText = [];


foreach ($tindakan as $item) {

    if (!empty($item['billing_item'])) {

        $tindakanText[] =
            $item['billing_item'];
    }
}


$data['tindakan_text'] =
    !empty($tindakanText)
    ? implode(', ', $tindakanText)
    : '-';


/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode([

    'status' => true,

    'data' => $data

]);
