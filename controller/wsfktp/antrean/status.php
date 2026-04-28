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

$listPoli = [
    ["kdPoli" => "001"],
    ["kdPoli" => "002"],
    ["kdPoli" => "003"],
    ["kdPoli" => "004"],
    ["kdPoli" => "005"],
    ["kdPoli" => "008"],
    ["kdPoli" => "010"],
    ["kdPoli" => "011"],
    ["kdPoli" => "012"],
    ["kdPoli" => "020"],
    ["kdPoli" => "021"],
    ["kdPoli" => "023"],
    ["kdPoli" => "024"],
    ["kdPoli" => "025"],
    ["kdPoli" => "026"],
    ["kdPoli" => "027"],
    ["kdPoli" => "036"],
    ["kdPoli" => "037"],
    ["kdPoli" => "999"],
    ["kdPoli" => "998"],
];
$url = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
// $kodepoli = $segments[4] ?? null;
// $tanggalperiksa = $segments[5] ?? null;

$kodepoli = $segments[count($segments) - 2] ?? null;
$tanggalperiksa = $segments[count($segments) - 1] ?? null;
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
            "message" => "Format Tanggal Tidak Sesuai, format yang benar adalah yyyy-mm-dd",
            "code" => 201
        ]
    ]);
    exit;
}
if (strtotime($tanggalperiksa) < strtotime(date('Y-m-d'))) {
    echo json_encode([
        "metadata" => [
            "message" => "Tanggal Periksa Tidak Berlaku",
            "code" => 201
        ]
    ]);
    exit;
}
$validKodePoli = array_column($listPoli, 'kdPoli');
if (!in_array($kodepoli, $validKodePoli)) {
    echo json_encode([
        "metadata" => [
            "message" => "Poli tidak ditemukan",
            "code" => 201
        ]
    ]);
    exit;
}
$status = "selesai";
$stmt = $koneksi->prepare("SELECT COUNT(*) as total,SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as total_panggil,COUNT(*) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as sisa_antrean,IFNULL(CAST(MAX(CASE WHEN ap.status = 1 THEN ap.nomor END) AS CHAR),'0') AS antrean_terakhir, p.id_poli, ap.kode_antri FROM antrian_poli AS ap INNER JOIN pasien_visit AS p ON p.visit_ID = ap.nomor_visit WHERE ap.id_customer = ? AND ap.poli = ? AND ap.tanggal = ? GROUP BY p.id_poli");
$stmt->bind_param("sss", $id_customer, $kodepoli, $tanggalperiksa);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
// response
if ($result) {
    $antrean_terakhir = ($result['antrean_terakhir'] == 0 ? '-' : $result['kode_antri'].$result['antrean_terakhir']);
    echo json_encode([
        "response" => [
            [
                "namapoli" => $result['id_poli'],
                "totalantrean" => (string)$result['total'],
                "sisaantrean" => $result['sisa_antrean'],
                "antreanpanggil" => $antrean_terakhir,
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
