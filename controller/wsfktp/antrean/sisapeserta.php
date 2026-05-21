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

$nokartu = $segments[count($segments) - 3] ?? null;
$kodepoli = $segments[count($segments) - 2] ?? null;
$tanggalperiksa = $segments[count($segments) - 1] ?? null;

if (strtotime($tanggalperiksa) < strtotime(date('Y-m-d'))) {
    echo json_encode([
        "metadata" => [
            "message" => "Tanggal Periksa Tidak Berlaku",
            "code" => 201
        ]
    ]);
    exit;
}

// var_dump($segments);die();
if (!$nokartu || !$kodepoli || !$tanggalperiksa) {
    echo json_encode([
        "metadata" => [
            "message" => "Parameter tidak lengkap",
            "code" => 201
        ]
    ]);
    exit;
}

$stmtCekPatient = $koneksi->prepare("SELECT * FROM pasien_visit WHERE noKartu = ? AND visit_date = ? AND id_customer = ? AND visit_status != '99'");
$stmtCekPatient->bind_param('sss', $nokartu, $tanggalperiksa, $id_customer);
$stmtCekPatient->execute();
$cekPasien = $stmtCekPatient->get_result();

if ($cekPasien->num_rows == 0) {
    echo json_encode([
        "metadata" => [
            "message" => "Antrean Tidak Ditemukan",
            "code" => 201
        ]
    ]);
    exit;
}

$stmt = $koneksi->prepare("
    SELECT 
    COUNT(
        CASE 
            WHEN p.visit_status = '10'
            THEN 1
        END
    ) AS total,

    SUM(
        CASE 
            WHEN ap.status = 1
            AND p.visit_status = '10'
            THEN 1
            ELSE 0
        END
    ) AS total_panggil,

    SUM(
        CASE
            WHEN p.visit_status = '10'
            AND ap.status != 1
            AND ap.nomor < (
                SELECT MAX(ap2.nomor)
                FROM antrian_poli ap2
                INNER JOIN pasien_visit p2 
                    ON p2.visit_ID = ap2.nomor_visit
                WHERE p2.noKartu = ?
                AND ap2.id_customer = ?
                AND ap2.poli = ?
                AND ap2.tanggal = ?
                AND p2.visit_status = '10'
            )
            THEN 1
            ELSE 0
        END
    ) AS sisa_antrean,


    IFNULL(
        MAX(
            CASE
                WHEN ap.status = 1
                AND p.visit_status = '10'
                THEN CONCAT(ap.kode_antri, ap.nomor)
            END
        ),
        '0'
    ) AS antrean_terakhir,

    MAX(
        CASE
            WHEN p.noKartu = ?
            THEN CONCAT(ap.kode_antri, ap.nomor)
        END
    ) AS nomorantrean,

    MAX(
        CASE
            WHEN p.noKartu = ?
            THEN ap.nomor
        END
    ) AS angkaantrean,

    p.id_poli

FROM antrian_poli AS ap

INNER JOIN pasien_visit AS p
    ON p.visit_ID = ap.nomor_visit

WHERE ap.id_customer = ?
AND ap.poli = ?
AND ap.tanggal = ?

GROUP BY p.id_poli
");

$stmt->bind_param("sssssssss", $nokartu, $id_customer, $kodepoli, $tanggalperiksa, $nokartu, $nokartu, $id_customer, $kodepoli, $tanggalperiksa);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$adaData = ($result && (int)$result['total'] > 0);

if ($adaData) {
    echo json_encode([
        "response" => [
            "nomorantrean" => $result['nomorantrean'] ?? 1,
            "namapoli" => ucwords(strtolower($result['id_poli'])),
            "sisaantrean" => $result['sisa_antrean'],
            "antreanpanggil" =>  $result['antrean_terakhir'],
            "keterangan" => ""
        ],
        "metadata" => [
            "message" => "Ok",
            "code" => 200
        ]
    ]);
} else {
    echo json_encode([
        "metadata" => [
            "message" => "Antrean Tidak Ditemukan",
            "code" => 201
        ]
    ]);
}
