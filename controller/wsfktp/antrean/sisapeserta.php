<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../database/connect.php';
require_once __DIR__ . '/../validateToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
$url = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
$nokartu = $segments[5] ?? null;
$kdpoli = $segments[6] ?? null;
$tanggalperiksa = $segments[7] ?? null;

$stmt = $koneksi->prepare("SELECT pasien_visit.*, ms_poli.poli_name FROM pasien_visit INNER JOIN ms_poli ON ms_poli.poli_code = pasien_visit.id_poli WHERE pasien_visit.id_customer = ? AND ms_poli.id_customer = ? AND pasien_visit.id_poli = ? AND visit_date = ? AND noKartu = ?");
$stmt->bind_param("sssss", $id_customer, $id_customer, $kdpoli, $tanggalperiksa, $nokartu);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();


echo json_encode([
    "response" => [
        [
            "nomorantrean" => $result['visit_antrian'],
            "namapoli" => $result['poli_name'],
            "sisaantrean" => "19",
            "antreanpanggil" => '1',
            "keterangan" => ""
        ],
    ],
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);
