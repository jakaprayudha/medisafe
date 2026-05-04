<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../../../database/connect.php';
require_once __DIR__ . '/../../../../wsbpjs/serviceantrian.php';
require_once __DIR__ . '/../../../validateToken.php';
date_default_timezone_set('Asia/Jakarta');
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
$kodedokter = $data['kodedokter'] ?? null;
$jampraktek = $data['jampraktek'] ?? null;
$norm       = $data['norm'] ?? null;
$nohp       = $data['nohp'] ?? null;
$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE patient_bpjs = ? OR patient_nik = ? LIMIT 1");
$stmt->bind_param('ss', $noKartu, $nik);
$stmt->execute();
$result = $stmt->get_result();
$status_pasien_baru = true;
$id_patient = null;
if ($result->num_rows > 0) {
    $status_pasien_baru = false;
    $row = $result->fetch_assoc();
    $id_patient = $row['id_patient'];
    $patient_name = $row['patient_name'];
    $patient_bpjs = $row['patient_bpjs'];
}

$stmt = $koneksi->prepare("SELECT nmPoli FROM master_poli WHERE kdPoli = ?");
$stmt->bind_param('s', $kodepoli);
$stmt->execute();
$result1 = $stmt->get_result()->fetch_assoc();
$nmPoli = $result1['nmPoli'];
$stmt->close();

$hariInggris = date('l', strtotime($tanggal));
$mapHari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hariIndonesia = $mapHari[$hariInggris];

$config = getConfigBPJS($id_customer, $koneksi);
$result = bpjsGet('/ref/dokter/kodepoli/' . $kodepoli . '/tanggal/' . $tanggal, $config);
// echo json_encode($result);
foreach ($result as $dokter) {
    if ((int)$dokter['kodedokter'] === (int)$kodedokter) {
        $dokterDipilih = $dokter;
        break;
    }
}
if (!$dokterDipilih) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Jadwal dokter tidak ditemukan",
            "code" => 201
        ]
    ]);
    exit;
}
$namaDokter = $dokterDipilih['namadokter'];
$kodeDokter = $dokterDipilih['kodedokter'];
$jamPraktek = $dokterDipilih['jampraktek'];

$cekpoli = $koneksi->prepare("SELECT d.doctor_name, mds.day_of_week, mds.start_time, mds.end_time, mds.sch_status, mds.kuota FROM ms_doctor AS d INNER JOIN ms_doctor_schedule AS mds ON d.id_doctor = mds.id_doctor AND d.id_customer = mds.id_customer WHERE d.doctor_code = ? AND d.id_customer = ? AND mds.day_of_week = ?");
$cekpoli->bind_param('sss', $kodedokter, $id_customer, $hariIndonesia);
$cekpoli->execute();
$status_antrian = $cekpoli->get_result()->fetch_assoc();
$cekpoli->close();

if ($status_antrian['sch_status'] == '0') {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Pendaftaran Ke Poli Ini Sedang Tutup",
            "code" => 201
        ]
    ]);
    exit;
}
$pecah_jam = explode('-', $jampraktek);
$jamMulai_db = $status_antrian['start_time'];
$jamSelesai_db = $status_antrian['end_time'];
$jamMulai_user = $pecah_jam[0];
$jamSelesai_user = $pecah_jam[1];
if ($jamMulai_user != $jamMulai_db || $jamSelesai_user != $jamSelesai_db) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Jadwal Dokter " . $status_antrian['doctor_name'] . " tersebut Belum tersedia, Silahkan Reschedule Tanggal dan Jam Praktek Lainnya",
            "code" => 201
        ]
    ]);
    exit;
}
// $jamsekarang = "19:00";
$now = new DateTime();
$jadwal_selesai = new DateTime($tanggal . ' ' . $jamSelesai_db);
if ($now > $jadwal_selesai) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Pendaftaran Ke Poli " . $nmPoli . " Sudah Tutup Jam " . $jamSelesai_db,
            "code" => 201,
        ]
    ]);
    exit;
}
// die();
$cekkuota = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE id_customer = ? AND id_poli = ? AND code_doctor = ? AND visit_date = ?");
$cekkuota->bind_param('ssss', $id_customer, $nmPoli, $kodedokter, $tanggal);
$cekkuota->execute();
$cekkuota->bind_result($total_antrian);
$cekkuota->fetch();
$cekkuota->close();

$kuota = (int)$status_antrian['kuota'];
$total = (int)$total_antrian;

if ($kuota == 0 || $total >= $kuota) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Kuota penuh, tidak bisa mendaftar",
            "code" => 201
        ]
    ]);
    exit;
}
// die();
$stmt1 = $koneksi->prepare("SELECT * FROM pasien_visit WHERE visit_date = ? AND noKartu = ? AND id_customer = ? AND id_poli = ?");
$stmt1->bind_param("ssss", $tanggal, $noKartu, $id_customer, $nmPoli);
$stmt1->execute();
$cek = $stmt1->get_result();
if ($cek->num_rows > 0) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Pasien sudah terdaftar di tanggal dan poli yg sama",
            "code" => 201
        ]
    ]);
    exit;
} else {
    if ($status_pasien_baru == true) {
        echo json_encode([
            "metadata" => [
                "message" => "Anda belum terdaftar di sistem klinik. Silakan ke bagian administrasi untuk melengkapi data.",
                "code" => 202
            ]
        ]);
    } else {
        try {
            $visit_ID = generateVisitID($koneksi, $id_customer);
            $koneksi->begin_transaction();
            $resultAntrian = createAntrian($koneksi, $kodepoli, $id_customer, $visit_ID, $kodedokter, $tanggal, $jampraktek);
            $nomorantrean = $resultAntrian['display'];
            $angkaantrean = $resultAntrian['nomor'];
            $kodeAntri       = $resultAntrian['kode'];

            $created_user = "MobileJKN";
            $source_hub = "Poliklinik";
            $status_antrian = 0;
            $status_visit = '10';
            $stmt4 = $koneksi->prepare("INSERT INTO pasien_visit (id_patient,visit_ID,visit_date,id_poli,source_hub,created_user,visit_antrian,status_antrian,id_customer,visit_status,patient_name_pcare,noKartu, code_doctor, jampraktek) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt4->bind_param(
                "issssssiisssss",
                $id_patient,
                $visit_ID,
                $tanggal,
                $nmPoli,
                $source_hub,
                $created_user,
                $nomorantrean,
                $status_antrian,
                $id_customer,
                $status_visit,
                $patient_name,
                $patient_bpjs,
                $kodedokter,
                $jampraktek
            );
            if (!$stmt4->execute()) {
                throw new Exception($stmt4->error);
            }
            $stmt = $koneksi->prepare("SELECT COUNT(*) as total,SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as total_panggil,COUNT(*) - SUM(CASE WHEN ap.status = 1 THEN 1 ELSE 0 END) as sisa_antrean,COALESCE(MAX(CASE WHEN ap.status = 1 THEN ap.nomor END),MAX(ap.nomor)) as antrean_terakhir FROM antrian_poli ap WHERE ap.id_customer = ? AND ap.poli = ? AND ap.tanggal = ?");
            $stmt->bind_param("sss", $id_customer, $kodepoli, $tanggal);
            $stmt->execute();
            $dataAntrian = $stmt->get_result()->fetch_assoc();
            $koneksi->commit();
            echo json_encode([
                "response" => [
                    [
                        "nomorantrean" => $nomorantrean,
                        "angkaantrean" => $angkaantrean,
                        "namapoli" => $nmPoli,
                        "sisaantrean" => $dataAntrian['sisa_antrean'],
                        "antreanpanggil" => $dataAntrian['antrean_terakhir'],
                        "keterangan" => "Apabila antrean terlewat harap mengambil antrean kembali."
                    ]
                ],
                "metadata" => [
                    "message" => "Ok",
                    "code" => 200
                ]
            ]);
        } catch (Exception $e) {
            $koneksi->rollback();

            echo json_encode([
                "metadata" => [
                    "message" => $e->getMessage(),
                    "code" => 201
                ]
            ]);
        }
    }
}
function generateVisitID($koneksi, $idcustomer){
    do {
        $date = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $visitID = "VIS-" . $idcustomer . "-" . $date . "-" . $random;
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
function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek){
    $cekantrian = $koneksi->prepare("SELECT 
                                    COALESCE(MAX(a.nomor), 0) AS last,
                                    (
                                        SELECT d.doctor_antrean
                                        FROM ms_doctor d
                                        WHERE d.doctor_code = ?
                                        AND d.id_customer = ?
                                        LIMIT 1
                                    ) AS kode_antrian
                                FROM antrian_poli a
                                WHERE a.poli = ?
                                AND a.tanggal = ?
                                AND a.id_customer = ?
                                AND a.kode_antri = (
                                    SELECT d.doctor_antrean
                                    FROM ms_doctor d
                                    WHERE d.doctor_code = ?
                                    AND d.id_customer = ?
                                    LIMIT 1
                                )
                                FOR UPDATE");
    $cekantrian->bind_param("sssssss", $kdDokter, $idcustomer, $kdPoli, $tglDaftarDB, $idcustomer, $kdDokter, $idcustomer);
    $cekantrian->execute();
    $rowantrian = $cekantrian->get_result()->fetch_assoc();
    $next = (int)$rowantrian['last'] + 1;
    $kode_antrian = $rowantrian['kode_antrian'];
    $createantrian = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit,id_dokter, kode_antri, jampraktek)VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $createantrian->bind_param("isssssss", $next, $kdPoli, $tglDaftarDB, $idcustomer, $visit_ID, $kdDokter, $kode_antrian, $jampraktek);
    $createantrian->execute();
    return [
        'nomor' => $next,
        'kode' => $kode_antrian,
        'display' => $kode_antrian . $next
    ];
}
