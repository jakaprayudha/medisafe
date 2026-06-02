<?php

require_once __DIR__ . '/../../../database/connect.php';
require_once __DIR__ . '/../../../controller/wsbpjs/serviceantrian.php';

function tampildata($query)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

header('Content-Type: application/json');
$clinics = tampildata("SELECT * FROM setting_antrol");
$syncResults = [];

function getOrCreateDoctorId($koneksi, $idCustomer, $idPoli, $kodeDokter, $namaDokter)
{
    $doctorId = null;

    $stmt = $koneksi->prepare("SELECT id_doctor FROM ms_doctor WHERE id_customer = ? AND doctor_code = ? LIMIT 1");
    $stmt->bind_param("ss", $idCustomer, $kodeDokter);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $doctorId = $existing['id_doctor'];

        $update = $koneksi->prepare("UPDATE ms_doctor SET doctor_name = ?, id_poli = ? WHERE id_doctor = ?");
        $update->bind_param("sss", $namaDokter, $idPoli, $doctorId);
        $update->execute();
        $update->close();

        return $doctorId;
    }

    $doctorNumber = 'BPJS-' . $kodeDokter;
    $insert = $koneksi->prepare("INSERT INTO ms_doctor (doctor_number, doctor_name, doctor_code, id_poli, id_customer) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("sssss", $doctorNumber, $namaDokter, $kodeDokter, $idPoli, $idCustomer);
    $insert->execute();
    $doctorId = $insert->insert_id;
    $insert->close();

    return $doctorId;
}


foreach ($clinics as $clinic) {
    $config = getConfigBPJS($clinic['id_customer'], $koneksi);

    $tanggal = date('Y-m-d');

    $hari = strtolower(date('l', strtotime($tanggal)));
    $map = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu'
    ];
    $hariIndonesia = $map[$hari];

    $deleteSchedule = $koneksi->prepare("DELETE FROM ms_doctor_schedule WHERE id_customer = ?");
    $deleteSchedule->bind_param("s", $clinic['id_customer']);
    $deleteSchedule->execute();
    $deleteSchedule->close();

    $policlinics = tampildata("SELECT * FROM ms_poli WHERE id_customer = '{$clinic['id_customer']}'");
    $clinicResult = [];

    foreach ($policlinics as $policlinic) {
        $kdpoli = $policlinic['poli_code'];
        $idPoli = $policlinic['id_poli'];

        $result = bpjsGet('/ref/dokter/kodepoli/' . $kdpoli . '/tanggal/' . $tanggal, $config);

        $responseData = [];
        if (isset($result['response']) && is_array($result['response'])) {
            $responseData = $result['response'];
        } elseif (is_array($result)) {
            $responseData = $result;
        }

        foreach ($responseData as $doctor) {
            if (!isset($doctor['kodedokter'], $doctor['namadokter'], $doctor['jampraktek'])) {
                continue;
            }

            $kodeDokter = (string) $doctor['kodedokter'];
            $namaDokter = (string) $doctor['namadokter'];
            $jamPraktek = (string) $doctor['jampraktek'];
            $kapasitas = isset($doctor['kapasitas']) ? (int) $doctor['kapasitas'] : 0;
            $jam = explode('-', $jamPraktek);

            if (count($jam) !== 2) {
                continue;
            }

            $startTime = trim($jam[0]);
            $endTime = trim($jam[1]);
            $doctorId = getOrCreateDoctorId($koneksi, $clinic['id_customer'], $idPoli, $kodeDokter, $namaDokter);

            $insertSchedule = $koneksi->prepare("INSERT INTO ms_doctor_schedule (id_doctor, id_poli, id_customer, day_of_week, start_time, end_time, sch_status, kuota) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
            $insertSchedule->bind_param("ssssssi", $doctorId, $idPoli, $clinic['id_customer'], $hariIndonesia, $startTime, $endTime, $kapasitas);
            $insertSchedule->execute();
            $insertSchedule->close();
        }

        $clinicResult[] = [
            'kodepoli' => $kdpoli,
            'result' => $result,
        ];

    }

    $syncResults[] = [
        'id_customer' => $clinic['id_customer'],
        'tanggal' => $tanggal,
        'hari' => $hariIndonesia,
        'data' => $clinicResult,
    ];
}

echo json_encode($syncResults);
