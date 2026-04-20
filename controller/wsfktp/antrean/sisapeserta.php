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
$username = $headers['x-username'] ?? null;
$id_customer = validateBpjsToken($username);

$url = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));

$nokartu = $segments[4] ?? null;
$kodepoli = $segments[5] ?? null;
$tanggalperiksa = $segments[6] ?? null;

if (!$nokartu || !$kodepoli || !$tanggalperiksa) {
    echo json_encode([
        "metadata" => [
            "message" => "Parameter tidak lengkap",
            "code" => 201
        ]
    ]);
    exit;
}

$stmt = $koneksi->prepare("
    SELECT 
        COUNT(ap.id) AS total,
        p.id_poli,
        p.noKartu,

        SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) AS total_panggil,

        COUNT(ap.id) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) AS sisa_antrean,

        COALESCE(
            MAX(CASE WHEN ap.status = 1 THEN ap.nomor END),
            MIN(ap.nomor),
            1
        ) AS antrean_terakhir,

        MIN(ap.nomor) AS nomor_antrean

    FROM antrian_poli ap
    INNER JOIN pasien_visit p 
        ON p.visit_ID = ap.nomor_visit

    WHERE ap.id_customer = ?
    AND ap.poli = ?
    AND ap.tanggal = ?
    AND p.noKartu = ?
");

$stmt->bind_param("ssss", $id_customer, $kodepoli, $tanggalperiksa, $nokartu);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

$adaData = ($result && (int)$result['total'] > 0);

if ($adaData) {

    echo json_encode([
        "response" => [
            [
                "nomorantrean" => $result['nomor_antrean'] ?? 1,
                "namapoli" => $result['id_poli'],
                "sisaantrean" => (int)$result['sisa_antrean'],
                "antreanpanggil" => $result['antrean_terakhir'] ?? 1,
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
