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
    d.doctor_code,
    d.doctor_name,
    jd.start_time,
    jd.end_time,
    mp.nmPoli,
    ap.kode_antri

FROM ms_doctor_schedule AS jd

INNER JOIN ms_doctor AS d
	ON d.id_doctor = jd.id_doctor
	
LEFT JOIN antrian_poli AS ap 
    ON ap.poli = d.id_poli
    AND ap.tanggal = ?
    AND ap.id_customer = ?
    AND ap.kode_antri = d.doctor_antrean
    
INNER JOIN master_poli AS mp
	ON ap.poli = mp.kdPoli

WHERE d.id_poli = ?
AND LOWER(jd.day_of_week) = ?
AND jd.sch_status = 1

GROUP BY jd.id_doctor, jd.start_time, jd.end_time");
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
// $antrean_terakhir = ($row['antrean_panggil'] == 0 ? '-' : $row['kode_antri'].$row['antrean_panggil']);
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "namapoli" => $row['nmPoli'],
        "totalantrean" => (string)$row['total'],
        "sisaantrean" => (int)$row['sisa_antrean'],
        "antreanpanggil" => $row['kode_antri'] . $row['antrean_panggil'],
        "keterangan" => "",
        "kodedokter" => $row['doctor_code'],
        "namadokter" => $row['doctor_name'],
        "jampraktek" => $row['start_time'] . '-' . $row['end_time']
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
