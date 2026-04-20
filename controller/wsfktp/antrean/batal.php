<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../database/connect.php';
require_once __DIR__ . '/../validateToken.php';

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
$keterangan = $data['keterangan'] ?? null;

$status_visit = '99';

$stmt = $koneksi->prepare("
  SELECT pp.nomor_visit 
  FROM pcare_pendaftaran AS pp 
  INNER JOIN pasien_visit AS pv 
    ON pp.nomor_visit = pv.visit_ID 
  WHERE pv.noKartu = ? 
    AND pp.tanggal_daftar = ? 
    AND pp.kdPoli = ? 
    AND (pv.visit_status IS NULL OR pv.visit_status != ?)
  LIMIT 1
");
$stmt->bind_param("ssss", $noKartu, $tanggal, $kodepoli, $status_visit);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $nomor_visit = $row['nomor_visit'];
    $stmt = $koneksi->prepare("
        UPDATE pasien_visit 
        SET visit_status = ? 
        WHERE noKartu = ? 
        AND visit_date = ? 
        AND id_customer = ?
        AND visit_ID = ?
    ");
    $stmt->bind_param("sssss", $status_visit, $noKartu, $tanggal, $id_customer, $nomor_visit);
    $result = $stmt->execute();

    $status = '9';
    $stmt1 = $koneksi->prepare("
        UPDATE antrian_poli 
        SET status = ? 
        WHERE id_customer = ? 
        AND poli = ? 
        AND tanggal = ? 
        AND nomor_visit = ?
    ");
    $stmt1->bind_param("sssss", $status, $id_customer, $kodepoli, $tanggal, $nomor_visit);
    $result1 = $stmt1->execute();
    if ($result && $result1) {
        echo json_encode([
            "metadata" => [
                "message" => "Ok",
                "code" => 200
            ]
        ]);
    } else {
        echo json_encode([
            "metadata" => [
                "message" => "Gagal update",
                "code" => 500
            ]
        ]);
    }
} else {
    echo json_encode([
        "metadata" => [
            "message" => "Antrian tidak ditemukan",
            "code" => 201
        ]
    ]);
}
