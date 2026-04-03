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
$user = validateBpjsToken($username);

$url = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
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

// query
// $stmt = $koneksi->prepare("
//     SELECT COUNT(*) as sisa
//     FROM antrean
//     WHERE kode_poli = ? 
//     AND tanggal_periksa = ?
//     AND status = 'menunggu'
// ");
// $stmt->bind_param("ss", $kode_poli, $tanggal);
// $stmt->execute();

// $result = $stmt->get_result()->fetch_assoc();

// response
echo json_encode([
    "response" => [
        [
            "namapoli" => "Poli Umum",
            "totalantrean" => "25",
            "sisaantrean" => 4,
            "antreanpanggil" => "A1-21",
            "keterangan" => "",
            "kodedokter" => 123456,
            "namadokter" => "Dr. Ali",
            "jampraktek" => "08=>00-13=>00"
        ],
        [
            "namapoli" => "Poli Umum",
            "totalantrean" => "11",
            "sisaantrean" => 1,
            "antreanpanggil" => "A2-10",
            "keterangan" => "",
            "kodedokter" => 123466,
            "namadokter" => "Dr. Adi",
            "jampraktek" => "08:00-12:00"
        ]
    ],
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);
