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
$stmt = $koneksi->prepare("SELECT poli_name, poli_queue FROM ms_poli WHERE poli_code = ? AND id_customer = ?");
$stmt->bind_param('ss', $kodepoli, $id_customer);
$stmt->execute();
$result1 = $stmt->get_result()->fetch_assoc();
$nmPoli = $result1['poli_name'];
$kodeAntri = $result1['poli_queue'];
$stmt->close();

$stmt2 = $koneksi->prepare("SELECT * FROM master_poli WHERE status_poli = 1 AND kdPoli = ?");
$stmt2->bind_param('s', $kodepoli);
$stmt2->execute();
$result = $stmt2->get_result();
if ($result->num_rows > 0) {
    $stmt1 = $koneksi->prepare("SELECT * FROM pasien_visit WHERE visit_date = ? AND noKartu = ? AND id_customer = ? AND id_poli = ? AND visit_status != 99");
    $stmt1->bind_param("ssss", $tanggal, $noKartu, $id_customer, $nmPoli);
    $stmt1->execute();
    $cek = $stmt1->get_result();
    if ($cek->num_rows > 0) {
        http_response_code(201);
        echo json_encode([
            "metadata" => [
                "message" => "Nomor Antrean Hanya Dapat Diambil 1 Kali Pada Tanggal Yang Sama",
                "code" => 201
            ]
        ]);
        exit;
    } else {
        if ($status_pasien_baru == true) {
            echo json_encode([
                "metadata" => [
                    "message" => "Data pasien ini tidak ditemukan, silahkan Melakukan Registrasi Pasien Baru",
                    "code" => 202
                ]
            ]);
        } else {
            try {
                $visit_ID = generateVisitID($koneksi, $id_customer);
                $koneksi->begin_transaction();
                $stmt2 = $koneksi->prepare("SELECT COALESCE(MAX(nomor),0) AS last FROM antrian_poli WHERE poli = ? AND tanggal = ? AND id_customer = ? FOR UPDATE");
                $stmt2->bind_param("sss", $kodepoli, $tanggal, $id_customer);
                $stmt2->execute();
                $row = $stmt2->get_result()->fetch_assoc();
                $next = (int)$row['last'] + 1;
                $stmt3 = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit)VALUES (?, ?, ?, ?, ?)");
                $stmt3->bind_param("issss", $next, $kodepoli, $tanggal, $id_customer, $visit_ID);
                $stmt3->execute();
                $created_user = "MobileJKN";
                $source_hub = "Poliklinik";
                $status_antrian = 0;
                $status_visit = 10;
                $stmt4 = $koneksi->prepare("INSERT INTO pasien_visit (id_patient,visit_ID,visit_date,id_poli,source_hub,created_user,visit_antrian,status_antrian,id_customer,visit_status,patient_name_pcare,noKartu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt4->bind_param(
                    "isssssiiisss",
                    $id_patient,
                    $visit_ID,
                    $tanggal,
                    $nmPoli,
                    $source_hub,
                    $created_user,
                    $next,
                    $status_antrian,
                    $id_customer,
                    $status_visit,
                    $patient_name,
                    $patient_bpjs
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
                            "nomorantrean" => $next,
                            "angkaantrean" => $next,
                            "namapoli" => $nmPoli,
                            "sisaantrean" => $dataAntrian['sisa_antrean'],
                            "antreanpanggil" => $dataAntrian['antrean_terakhir'],
                            "keterangan" => "Apabila antrean terlewat harap mengambil antrean kembali."
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
} else {
    echo json_encode([
        "metadata" => [
            "message" => "Pendaftaran ke Poli Ini Sedang Tutup",
            "code" => 201
        ]
    ]);
}

function generateVisitID($koneksi, $idcustomer)
{
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
