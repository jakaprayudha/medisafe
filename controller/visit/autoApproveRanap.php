<?php
require '../../database/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
    exit;
}

$id_customer = $_SESSION['id_customer'] ?? null;
$created_user = $_SESSION['fullname'] ?? null;

$id_patient = $_POST['id_patient'] ?? '';
$id_doctor = $_POST['id_doctor'] ?? '';
$visit_date = $_POST['visit_date'] ?? '';
$visit_time = $_POST['visit_time'] ?? '';
$id_provider = $_POST['id_provider'] ?? null;
$id_room = $_POST['room_name'] ?? '';
$id_bed = $_POST['bed_name'] ?? '';
$diagnosa_awal = $_POST['diagnosa_awal'] ?? '';
$ranap_notes = $_POST['ranap_notes'] ?? '';

if (!$id_customer) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session habis'
    ]);
    exit;
}

if (!$id_patient || !$id_doctor || !$visit_date || !$visit_time || !$id_room || !$id_bed) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$stmtPoli = $koneksi->prepare("SELECT id_poli FROM ms_doctor WHERE id_doctor=? AND id_customer=?");
$stmtPoli->bind_param("ii", $id_doctor, $id_customer);
$stmtPoli->execute();
$resPoli = $stmtPoli->get_result()->fetch_assoc();
$stmtPoli->close();

$id_poli = $resPoli['id_poli'] ?? null;

$visit_ID = generateVisitID($koneksi);

$stmtQueue = $koneksi->prepare("SELECT COUNT(*) as total FROM pasien_visit WHERE visit_date=? AND id_doctor=? AND id_customer=?");
$stmtQueue->bind_param("sii", $visit_date, $id_doctor, $id_customer);
$stmtQueue->execute();
$resultQueue = $stmtQueue->get_result()->fetch_assoc();
$stmtQueue->close();

$visit_antrian = ($resultQueue['total'] ?? 0) + 1;

$koneksi->begin_transaction();

try {
    $source_hub = 'Rawat Inap';
    $visit_status = 1;
    $status_antrian = 0;
    $status_rawatinap = 1;

    $stmtVisit = $koneksi->prepare(
        "INSERT INTO pasien_visit (
            id_patient,
            visit_ID,
            visit_date,
            visit_time,
            id_doctor,
            id_poli,
            source_hub,
            visit_status,
            created_user,
            visit_antrian,
            status_antrian,
            id_customer,
            id_provider,
            status_rawatinap
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmtVisit->bind_param(
        "sssssssisiiisi",
        $id_patient,
        $visit_ID,
        $visit_date,
        $visit_time,
        $id_doctor,
        $id_poli,
        $source_hub,
        $visit_status,
        $created_user,
        $visit_antrian,
        $status_antrian,
        $id_customer,
        $id_provider,
        $status_rawatinap
    );

    if (!$stmtVisit->execute()) {
        throw new Exception($stmtVisit->error);
    }
    $stmtVisit->close();

    $ranap_booking = 1;
    $created_at = date('Y-m-d H:i:s');

    $stmtRanap = $koneksi->prepare(
        "INSERT INTO permintaan_ranap (
            id_patient,
            id_doctor,
            ranap_date,
            ranap_time,
            visit_ID_inpatient,
            diagnosa_awal,
            ranap_notes,
            ranap_booking,
            created_at,
            id_room,
            id_bed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmtRanap->bind_param(
        "sssssssisii",
        $id_patient,
        $id_doctor,
        $visit_date,
        $visit_time,
        $visit_ID,
        $diagnosa_awal,
        $ranap_notes,
        $ranap_booking,
        $created_at,
        $id_room,
        $id_bed
    );

    if (!$stmtRanap->execute()) {
        throw new Exception($stmtRanap->error);
    }
    $stmtRanap->close();

    $updateBed = $koneksi->prepare("UPDATE ms_room_bed SET bed_status = '0' WHERE id_bed = ?");
    $updateBed->bind_param("i", $id_bed);
    if (!$updateBed->execute()) {
        throw new Exception($updateBed->error);
    }
    $updateBed->close();

    $koneksi->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi rawat inap berhasil.',
        'data' => [
            'visit_ID' => $visit_ID,
            'antrian' => $visit_antrian
        ]
    ]);
} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function generateVisitID($koneksi)
{
    do {
        $date = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $visitID = 'VIS-' . $date . '-' . $random;
        $count = '';
        $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
        $check->bind_param("s", $visitID);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
    } while ($count > 0);

    return $visitID;
}
