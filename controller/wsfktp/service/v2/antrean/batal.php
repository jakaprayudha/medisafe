<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../../../database/connect.php';
require_once __DIR__ . '/../../../validateToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode([
        "metadata" => [
            "message" => "Method not allowed",
            "code" => 405
        ]
    ]);
    exit;
}
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$token = $headers['x-token'] ?? null;
$username = $headers['x-username'] ?? null;
$id_customer = validateBpjsToken($username);
$json = file_get_contents("php://input");
$data = json_decode($json, true);
$noKartu = $data['nomorkartu'] ?? null;
$kodepoli   = $data['kodepoli'] ?? null;
$tanggal    = $data['tanggalperiksa'] ?? null;
$status_antrian_batal = '99';

try {
    $stmt = $koneksi->prepare("
        SELECT visit_ID, visit_status 
        FROM pasien_visit
        WHERE noKartu = ?
        AND visit_date = ?
        AND id_customer = ?
        LIMIT 1
    ");
    $stmt->bind_param("sss", $noKartu, $tanggal, $id_customer);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        echo json_encode([
            "metadata" => [
                "message" => "Antrean Tidak Ditemukan atau Sudah Dibatalkan",
                "code" => 201
            ]
        ]);
        exit;
    }
    $row = $res->fetch_assoc();
    if ($row['visit_status'] == '99') {
        echo json_encode([
            "metadata" => [
                "message" => "Antrean Tidak Ditemukan atau Sudah Dibatalkan",
                "code" => 201
            ]
        ]);
        exit;
    }
    $nomor_visit = $row['visit_ID'];
    $visit_status_db = $row['visit_status'];
    $stmtCek = $koneksi->prepare("
        SELECT 1 FROM pcare_pendaftaran 
        WHERE nomor_visit = ?
        LIMIT 1
    ");
    $stmtCek->bind_param("s", $nomor_visit);
    $stmtCek->execute();
    $cekPcare = $stmtCek->get_result();
    if ($cekPcare->num_rows > 0) {
        echo json_encode([
            "metadata" => [
                "message" => "Pasien Sudah Dilayani, Antrean Tidak Dapat Dibatalkan",
                "code" => 201
            ]
        ]);
        exit;
    }
    if ($visit_status_db != '10') {
        echo json_encode([
            "metadata" => [
                "message" => "Pasien Sudah Dilayani, Antrean Tidak Dapat Dibatalkan",
                "code" => 201
            ]
        ]);
        exit;
    }
    $stmtUpdateVisit = $koneksi->prepare("
        UPDATE pasien_visit 
        SET visit_status = ? 
        WHERE visit_ID = ?
    ");
    $stmtUpdateVisit->bind_param("ss", $status_antrian_batal, $nomor_visit);
    $resultVisit = $stmtUpdateVisit->execute();
    $stmtUpdateAntrian = $koneksi->prepare("
        UPDATE antrian_poli 
        SET status = ? 
        WHERE nomor_visit = ?
        AND id_customer = ?
        AND poli = ?
        AND tanggal = ?
    ");
    $stmtUpdateAntrian->bind_param("sssss", $status_antrian_batal, $nomor_visit, $id_customer, $kodepoli, $tanggal);
    $resultAntrian = $stmtUpdateAntrian->execute();
    if ($resultVisit && $resultAntrian) {
        echo json_encode([
            "metadata" => [
                "message" => "OK",
                "code" => 200
            ]
        ]);
    } else {
        echo json_encode([
            "metadata" => [
                "message" => "Gagal",
                "code" => 201
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "metadata" => [
            "message" => "Error",
            "code" => 201
        ]
    ]);
}
