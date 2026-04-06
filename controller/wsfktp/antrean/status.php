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
var_dump($segments);
$kodepoli = $segments[5] ?? null;
$tanggalperiksa = $segments[6] ?? null;
if (!$kodepoli || !$tanggalperiksa) {
    http_response_code(400);
    echo json_encode([
        "metadata" => [
            "message" => "Parameter tidak lengkap",
            "code" => 201
        ]
    ]);
    exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalperiksa)) {
    echo json_encode([
        "metadata" => [
            "message" => "Format tanggal salah (yyyy-mm-dd)",
            "code" => 201
        ]
    ]);
    exit;
}
$status = "selesai";
$stmt = $koneksi->prepare("SELECT COUNT(*) as total, ms_poli.poli_name FROM pasien_visit INNER JOIN ms_poli ON ms_poli.poli_code = pasien_visit.id_poli WHERE pasien_visit.id_customer = ? AND ms_poli.id_customer = ? AND pasien_visit.id_poli = ? AND visit_date = ?");
$stmt->bind_param("ssss", $id_customer, $id_customer, $kodepoli, $tanggalperiksa);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$stmt1 = $koneksi->prepare("SELECT COUNT(*) as total FROM transaction_queue WHERE id_customer = ? AND id_poli = ? AND DATE(created_at) = ? AND `status` = ?");
$stmt1->bind_param("ssss", $id_customer, $kodepoli, $tanggalperiksa, $status);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc();

// response
echo json_encode([
    "response" => [
        [
            "namapoli" => $result['poli_name'],
            "totalantrean" => $result['total'],
            "sisaantrean" => $result['total'] - $result1['total'],
            "antreanpanggil" => $result1['counter'] ?? 0,
            "keterangan" => ""
        ],
    ],
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);
