<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../../../database/connect.php';
require_once __DIR__ . '/../../../validateToken.php';
require_once __DIR__ . '/../../../../wsbpjs/serviceantrian.php';

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

// $config = getConfigBPJS($id_customer, $koneksi);
// $bpjsResult = bpjsGetService('/ref/dokter/kodepoli/' . $kodepoli . '/tanggal/' . $tanggalperiksa, $config);
// echo json_encode($bpjsResult);die();
// $mapDokter = [];
// foreach ($bpjsResult as $d) {
//     $mapDokter[(string)$d['kodedokter']] = $d;
// }

$stmt = $koneksi->prepare("
SELECT 

    COUNT(ap.id) AS total,

    COALESCE(
        SUM(
            CASE 
                WHEN ap.status = 1 
                AND COALESCE(p.visit_status, '') != '99'
                THEN 1 
                ELSE 0 
            END
        ), 
        0
    ) AS total_panggil,

    COALESCE(
        SUM(
            CASE 
                WHEN ap.status != 1
                AND p.visit_status IN ('0', '10')
                THEN 1 
                ELSE 0 
            END
        ),
        0
    ) AS sisa_antrean,

    COALESCE(
        MAX(
            CASE 
                WHEN ap.status = 1 
                AND COALESCE(p.visit_status, '') != '99'
                THEN ap.nomor 
            END
        ),
        0
    ) AS antrean_panggil,

    d.doctor_category AS poli,
    d.doctor_code,
    d.doctor_name,
    jd.start_time,
    jd.end_time,
    mp.nmPoli,
    d.doctor_antrean AS kode_antri

FROM ms_doctor_schedule AS jd

INNER JOIN ms_doctor AS d
    ON d.doctor_code = jd.id_doctor AND d.id_customer = jd.id_customer

INNER JOIN master_poli AS mp
    ON d.doctor_category = mp.kdPoli

LEFT JOIN antrian_poli AS ap 
    ON ap.poli = d.doctor_category
    AND ap.tanggal = ?
    AND ap.id_customer = ?
    AND ap.kode_antri = d.doctor_antrean

LEFT JOIN pasien_visit AS p
    ON p.visit_ID = ap.nomor_visit

WHERE d.doctor_category = ?
AND LOWER(jd.day_of_week) = ?
AND jd.sch_status = 1

GROUP BY jd.id_doctor, jd.start_time, jd.end_time

ORDER BY jd.start_time ASC");
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
    $antrean_terakhir = ($row['antrean_panggil'] == 0 ? '-' : $row['kode_antri'].$row['antrean_panggil']);
    $kode = (string)$row['doctor_code'];
    $nama = $row['doctor_name'];
    $jam  = $row['start_time'] . '-' . $row['end_time'];
    $nama = $row['doctor_name'];
    $data[] = [
        "namapoli" => ucwords(strtolower($row['nmPoli'])),
        "totalantrean" => (string)$row['total'],
        "sisaantrean" => (int)$row['sisa_antrean'],
        "antreanpanggil" => $antrean_terakhir,
        "keterangan" => "",
        "kodedokter" => $kode,
        "namadokter" => $nama,
        "jampraktek" => $jam 
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
