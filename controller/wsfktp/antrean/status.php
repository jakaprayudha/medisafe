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
// $kodepoli = $segments[4] ?? null;
// $tanggalperiksa = $segments[5] ?? null;

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
$stmt = $koneksi->prepare("SELECT COUNT(*) as total,SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as total_panggil,COUNT(*) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as sisa_antrean,COALESCE(MAX(CASE WHEN ap.status = 1 THEN ap.nomor END),MIN(ap.nomor)) as antrean_terakhir, p.id_poli FROM antrian_poli AS ap INNER JOIN pasien_visit AS p ON p.visit_ID = ap.nomor_visit WHERE ap.id_customer = ? AND ap.poli = ? AND ap.tanggal = ? GROUP BY p.id_poli");
$stmt->bind_param("sss", $id_customer, $kodepoli, $tanggalperiksa);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
// response
if ($result) {
    echo json_encode([
        "response" => [
            [
                "namapoli" => $result['id_poli'],
                "totalantrean" => (string)$result['total'],
                "sisaantrean" => $result['sisa_antrean'],
                "antreanpanggil" => $result['antrean_terakhir'],
                "keterangan" => ""
            ],
        ],
        "metadata" => [
            "message" => "Ok",
            "code" => 200
        ]
    ]);
} else {
    echo json_encode([
        "metadata" => [
            "message" => "tidak ada antrian",
            "code" => 201
        ]
    ]);
}
