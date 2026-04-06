<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../database/connect.php';
require_once __DIR__ . '/../validateToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
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
$nik        = $data['nik'] ?? null;
$kodepoli   = $data['kodepoli'] ?? null;
$tanggal    = $data['tanggalperiksa'] ?? null;
$keluhan    = $data['keluhan'] ?? null;
$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE patient_bpjs = ? OR patient_nik = ?");
$stmt->bind_param('ss', $noKartu, $nik);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$stmt = $koneksi->prepare("SELECT poli_name FROM ms_poli WHERE poli_code = ? AND id_customer = ?");
$stmt->bind_param('ss', $kodepoli, $id_customer);
$stmt->execute();
$result1 = $stmt->get_result()->fetch_assoc();
$nmPoli = $result1['poli_name'];
$stmt->close();
if ($result) {
    $created_user = "MobileJKN";
    $source_hub = "Poliklinik";
    $id_patient = $result['id_patient'];
    $visit_ID = generateVisitID($koneksi);
    $stmtQueue = $koneksi->prepare("SELECT COUNT(*) as total FROM pasien_visit WHERE visit_date=? AND id_poli=? AND id_customer=?");
    $stmtQueue->bind_param("sii", $tanggal, $kodepoli, $id_customer);
    $stmtQueue->execute();
    $resultQueue = $stmtQueue->get_result()->fetch_assoc();
    $stmtQueue->close();
    $visit_antrian = $resultQueue['total'] + 1;
    $stmt = $koneksi->prepare("
            INSERT INTO pasien_visit (
                id_patient,
                visit_ID,
                visit_date,
                id_poli,
                source_hub,
                created_user,
                visit_antrian,
                status_antrian,
                id_customer
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    $visit_status = 1;
    $status_antrian = 0;
    $stmt->bind_param(
        "sssssssss",
        $id_patient,
        $visit_ID,
        $tanggal,
        $kodepoli,
        $source_hub,
        $created_user,
        $visit_antrian,
        $status_antrian,
        $id_customer
    );
    if (!$stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "metadata" => [
                "message" => "Ok",
                "code" => 201
            ]
        ]);
        exit;
    }
    echo json_encode([
        "response" => [
            [
                "nomorantrean" => $visit_antrian,
                "angkaantrean" => $visit_antrian,
                "namapoli" => $nmPoli,
                "sisaantrean" => "20",
                "antreanpanggil" => $visit_antrian,
                "keterangan" => "Apabila antrean terlewat harap mengambil antrean kembali."
            ],
        ],
        "metadata" => [
            "message" => "Ok",
            "code" => 200
        ]
    ]);
} else {
    http_response_code(202);
    echo json_encode([
        "metadata" => [
            "message" => "Pasien Baru",
            "code" => 202
        ]
    ]);
}
function generateVisitID($koneksi)
{
    do {
        $date = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $visitID = "VIS-" . $date . "-" . $random;
        $count = '';
        $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
        $check->bind_param("s", $visitID);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
    } while ($count > 0);

    return $visitID;
}
