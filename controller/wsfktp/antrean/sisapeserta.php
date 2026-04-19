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
// $nokartu = $segments[5] ?? null;
// $kodepoli = $segments[6] ?? null;
// $tanggalperiksa = $segments[7] ?? null;
$nokartu = $segments[4] ?? null;
$kdpoli = $segments[5] ?? null;
$tanggalperiksa = $segments[6] ?? null;

$stmt = $koneksi->prepare("SELECT COUNT(*) as total,pasien_visit.id_poli,pasien_visit.noKartu,SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as total_panggil,COUNT(*) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as sisa_antrean,COALESCE(MAX(CASE WHEN ap.status = 1 THEN ap.nomor END),MAX(ap.nomor)) as antrean_terakhir FROM antrian_poli AS ap INNER JOIN pasien_visit ON pasien_visit.visit_ID = ap.nomor_visit WHERE ap.id_customer = ? AND ap.poli = ? AND ap.tanggal = ? AND pasien_visit.noKartu = ?");
$stmt->bind_param("ssss", $id_customer, $kodepoli, $tanggalperiksa, $nokartu);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$adaData = ($result['total'] > 0);
if ($adaData) {
    echo json_encode([
        "response" => [
            [
                "nomorantrean" => $result['noKartu'],
                "namapoli" => $result['id_poli'],
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
            "message" => "Gagal",
            "code" => 201
        ]
    ]);
}
