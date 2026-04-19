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


$stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM pasien_visit WHERE noKartu = ? AND id_poli = ? AND visit_date = ? AND id_customer = ?");
$stmt->bind_param("ssss", $noKartu, $kodepoli, $tanggal, $id_customer);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total = (int)$result['total'];
$stmt->close();
if ($total > 0) {
    $stmt = $koneksi->prepare("DELETE FROM pasien_visit WHERE noKartu = ? AND id_poli = ? AND visit_date = ? AND id_customer = ?");
    $stmt->bind_param("ssss", $noKartu, $kodepoli, $tanggal, $id_customer);
    $result = $stmt->execute();

    $stmt1 = $koneksi->prepare("UPDATE antrian_poli SET status = ? WHERE id_customer = ? AND id_poli = ? AND visit_date = ?");
    $stmt1->bind_param("ssss", $noKartu, $kodepoli, $tanggal, $id_customer);
    $result1 = $stmt1->execute();

    if ($result) {
        echo json_encode([
            "metadata" => [
                "message" => "Ok",
                "code" => 200
            ]
        ]);
    } else {
        echo json_encode([
            "metadata" => [
                "message" => "Error",
                "code" => 201
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
