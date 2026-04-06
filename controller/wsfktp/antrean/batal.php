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

$stmt = $koneksi->prepare("DELETE FROM pasien_visit WHERE noKartu = ? AND id_poli = ? AND visit_date = ? AND id_customer = ?");
$stmt->bind_param("ssss", $noKartu, $kodepoli, $tanggal, $id_customer);
$result = $stmt->execute();

if ($result){
    echo json_encode([
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);
}else{
    echo json_encode([
    "metadata" => [
        "message" => "Error",
        "code" => 201
    ]
]);
}
