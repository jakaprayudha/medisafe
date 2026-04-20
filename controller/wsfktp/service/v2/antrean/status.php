<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../../../database/connect.php';
require_once __DIR__ . '/../../../validateToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "metadata" => [
            "message" => "Method not allowed",
            "code" => 405
        ]
    ]);
    exit;
}

/* HEADER */
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$username = $headers['x-username'] ?? null;
$id_customer = validateBpjsToken($username);

/* URL PARAM */
$url = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));

$kodepoli = $segments[count($segments) - 2] ?? null;
$tanggalperiksa = $segments[count($segments) - 1] ?? null;

if (!$kodepoli || !$tanggalperiksa) {
    echo json_encode([
        "metadata" => [
            "message" => "Parameter tidak lengkap",
            "code" => 201
        ]
    ]);
    exit;
}

/* VALIDASI TANGGAL */
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalperiksa)) {
    echo json_encode([
        "metadata" => [
            "message" => "Format tanggal salah (yyyy-mm-dd)",
            "code" => 201
        ]
    ]);
    exit;
}

/* HARI INDONESIA */
$hari = strtolower(date('l', strtotime($tanggalperiksa)));
$map = [
    'monday' => 'senin',
    'tuesday' => 'selasa',
    'wednesday' => 'rabu',
    'thursday' => 'kamis',
    'friday' => 'jumat',
    'saturday' => 'sabtu',
    'sunday' => 'minggu'
];
$hari_indonesia = $map[$hari] ?? '';
$stmt = $koneksi->prepare("SELECT 
    COUNT(ap.id) AS total,
    SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) AS total_panggil,
    COUNT(ap.id) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) AS sisa_antrean,

    COALESCE(
        MAX(CASE WHEN ap.status = 1 THEN ap.nomor END),
        MIN(ap.nomor)
    ) AS antrean_panggil,

    ap.poli,
    jd.id_dokter,
    md.doctor_name,
    jd.jam_mulai,
    jd.jam_selesai

FROM jadwal_dokter jd

LEFT JOIN antrian_poli ap 
    ON ap.poli = jd.id_poli
    AND ap.tanggal = ?
    AND ap.id_customer = ?

INNER JOIN ms_doctor md 
    ON md.doctor_code = jd.id_dokter

WHERE jd.id_poli = ?
AND LOWER(jd.hari) = ?
AND jd.status = 1

GROUP BY jd.id_dokter, jd.jam_mulai, jd.jam_selesai;");
$stmt->bind_param(
    "ssss",
    $tanggalperiksa,
    $id_customer,
    $kodepoli,
    $hari_indonesia
);

$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    $data[] = [
        "namapoli" => $row['poli'],
        "totalantrean" => (string)$row['total'],
        "sisaantrean" => $row['sisa_antrean'],
        "antreanpanggil" => $row['antrean_panggil'] ?? 1,
        "keterangan" => "",
        "kodedokter" => $row['id_dokter'],
        "namadokter" => $row['doctor_name'],
        "jampraktek" => $row['jam_mulai'] . '-' . $row['jam_selesai']
    ];
}

/* RESPONSE */
echo json_encode([
    "response" => $data,
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);