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

$dayMap = [
    'monday' => 'senin',
    'tuesday' => 'selasa',
    'wednesday' => 'rabu',
    'thursday' => 'kamis',
    'friday' => 'jumat',
    'saturday' => 'sabtu',
    'sunday' => 'minggu'
];

$today = new DateTimeImmutable('today');
$tanggalRange = [];
for ($i = 0; $i <= 6; $i++) {
    $tanggalRange[] = $today->modify("+{$i} day")->format('Y-m-d');
}

$tanggalAwal = $tanggalRange[0];
$tanggalAkhir = $tanggalRange[count($tanggalRange) - 1];

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

    $deleteSchedule = $koneksi->prepare("DELETE FROM ms_doctor_schedule WHERE id_customer = ?");
    $deleteSchedule->bind_param("s", $clinic['id_customer']);
    $deleteSchedule->execute();
    $deleteSchedule->close();

    $policlinics = tampildata("SELECT * FROM master_poli");
    $clinicResult = [];

    foreach ($policlinics as $policlinic) {
        $kdpoli = $policlinic['kdPoli'];
        $idPoli = $policlinic['id'];

        foreach ($tanggalRange as $tanggal) {
            $hari = strtolower(date('l', strtotime($tanggal)));
            $hariIndonesia = $dayMap[$hari] ?? null;

            if ($hariIndonesia === null) {
                continue;
            }

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
                'tanggal' => $tanggal,
                'hari' => $hariIndonesia,
                'result' => $result,
            ];
        }

    }

    $syncResults[] = [
        'id_customer' => $clinic['id_customer'],
        'tanggal_awal' => $tanggalAwal,
        'tanggal_akhir' => $tanggalAkhir,
        'data' => $clinicResult,
    ];
}

echo json_encode($syncResults);
