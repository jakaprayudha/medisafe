<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$kdProviderPeserta = $_POST['kdProviderPeserta'];
$tglDaftarDB = $_POST['tglDaftar'];
$tglDaftar = date("d-m-Y", strtotime($tglDaftarDB));
$noKartu = $_POST['noKartu'] ?? '';
$kdPoli = $_POST['kdPoli'];
$keluhan = isset($_POST['keluhan']) && !empty($_POST['keluhan']) ? $_POST['keluhan'] : null;
$kunjSakit = $_POST['kunjSakit'] === 'true';
$sistole = (int) $_POST['sistole'];
$diastole = (int) $_POST['diastole'];
$beratBadan = (int) $_POST['beratBadan'];
$tinggiBadan = (int) $_POST['tinggiBadan'];
$respRate = (int) $_POST['respRate'];
$lingkarPerut = (int) $_POST['lingkarPerut'];
$heartRate = (int) $_POST['heartRate'];
$kdTkp = $_POST['kdTkp'];
$nmPoli = $_POST['nmPoli'];
$kdDokter = $_POST['kdDokter'] ?? null;
$nmDokter = $_POST['nmDokter'] ?? null;
$noNIK = $_POST['noNik'] ?? '';
$nama = $_POST['nama'];
$jnsKlamin = $_POST['jnsKlamin'];
$tglLahir = date("Y-m-d", strtotime($_POST['tglLahir']));
$suhu    =  $_POST['suhu'];
$saturasi    =  $_POST['saturasiOksigen'];
$type = $_POST['typePatient'];
$kdProv = $kunjSakit == 'true' ? $_POST['kdProv'] : '1';
$norm = $_POST['norm'];
$noHp = $_POST['noHp'];
$id_patient = $_POST['id_patient'];
$SProlanis = $_POST['SProlanis'];
$SPRB = $_POST['SPRB'];
$jampraktek = $_POST['jampraktek'];
$payload = [
    "kdProviderPeserta" => $kdProviderPeserta,
    "tglDaftar" => $tglDaftar,
    "noKartu" => $noKartu,
    "kdPoli" => $kdPoli,
    "keluhan" => $keluhan,
    "kunjSakit" => $kunjSakit,
    "sistole" => $sistole,
    "diastole" => $diastole,
    "beratBadan" => $beratBadan,
    "tinggiBadan" => $tinggiBadan,
    "respRate" => $respRate,
    "lingkarPerut" => $lingkarPerut,
    "heartRate" => $heartRate,
    "rujukBalik" => 0,
    "kdTkp" => $kdTkp
];

if (empty($kdDokter) && $kunjSakit == 'true') {
    echo json_encode([
        "status" => false,
        "message" => "Dokter harus diisi"
    ]);
    exit;
}
if ($type == "BPJS") {
    // echo $kdProv;
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    $result = bpjsPost("/pendaftaran", $payload);
    // echo json_encode($result);die();
    // $result = testingBPJS_POST("http://localhost/medisafe/controller/admisi/api/getpeserta.php", $payload);
    if ($result['code'] != '200') {
        $msg = $result['metadata'];
        if ($msg == null) {
            $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        }
        $response = [
            'success' => false,
            'message' => $msg,
            'result' => $result
        ];
    } else {
        $noUrut = $result['data']['message'];
        $noUrut = (string) $noUrut;
        $sistole      = (int)$sistole;
        $diastole     = (int)$diastole;
        $beratBadan   = (int)$beratBadan;
        $tinggiBadan  = (int)$tinggiBadan;
        $respRate     = (int)$respRate;
        $lingkarPerut = (int)$lingkarPerut;
        $heartRate    = (int)$heartRate;
        $visit_ID = $_POST['visit_id'];
        $antrian = $_POST['antrian'];
        $angkaantrean = $_POST['angkaantrean'];
        $kodeAntri       = $_POST['kodeAntri'];
        $created_user = "JKNOnsite";
        if (!$kunjSakit) {
            $visit_ID = generateVisitID($koneksi, $idcustomer);
            $antrian = $noUrut;
            $created_user = "JKNSehat";
        }
        $stmt = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`, `saturasi`, `suhu`, `jampraktek`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "ssssssiiiiiiisssssss",
            $tglDaftarDB,
            $noKartu,
            $kdPoli,
            $nmPoli,
            $keluhan,
            $kunjSakit,
            $sistole,
            $diastole,
            $beratBadan,
            $tinggiBadan,
            $respRate,
            $lingkarPerut,
            $heartRate,
            $rujukbalik,
            $kdTkp,
            $noUrut,
            $visit_ID,
            $saturasi,
            $suhu,
            $jampraktek
        );
        $hasil = $stmt->execute();
        $stmt->close();
        $source_hub = "Poliklinik";
        $visit_time = date('H:i:s');
        $bmi = $_POST['bmi'];
        $bmiKet = $_POST['bmiKet'];
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
                id_customer,
                id_doctor,
                noKartu,
                visit_time,
                anamnesa,
                tekanan_darah, 
                nadi,
                respirasi, 
                tinggi_badan,
                berat_badan,
                patient_name_pcare,
                suhu,
                saturasi,
                bmi,
                bmi_keterangan,
                code_doctor,
                id_provider
            )VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?)
        ");
        $visit_status = 1;
        $status_antrian = 0;
        $td = $sistole . "/" . $diastole;
        $stmt->bind_param(
            "sssssssssssssssssssssssss",
            $id_patient,
            $visit_ID,
            $tglDaftarDB,
            $nmPoli,
            $source_hub,
            $created_user,
            $antrian,
            $status_antrian,
            $idcustomer,
            $nmDokter,
            $noKartu,
            $visit_time,
            $keluhan,
            $td,
            $heartRate,
            $respRate,
            $tinggiBadan,
            $beratBadan,
            $nama,
            $suhu,
            $saturasi,
            $bmi,
            $bmiKet,
            $kdDokter,
            $kdProv
        );
        $stmtUpdate = $koneksi->prepare("UPDATE ms_patient SET Sprolanis = ?, SPRB = ? WHERE id_patient = ?");
        $stmtUpdate->bind_param("sss", $SProlanis, $SPRB, $id_patient);
        $stmtUpdate->execute();
        $stmtUpdate->close();
        $hasil1 = $stmt->execute();
        if ($hasil1) {
            $response = [
                'success'  => true,
                'message'  => "Berhasil Mendaftar Pasien",
                'type' => "BPJS"
            ];
        } else {
            $response = [
                'success' => false,
                'message' => "Gagal Mendaftar",
            ];
        }
    }
} else {
    $visit_ID = generateVisitID($koneksi, $idcustomer);
    $resultAntrian = createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek);
    $nomorantrean = $resultAntrian['display'];
    $angkaantrean = $resultAntrian['nomor'];
    $kodeAntri       = $resultAntrian['kode'];
    $created_user = "Onsite";
    $source_hub = "Poliklinik";
    $visit_time = date('H:i:s');
    $bmi = $_POST['bmi'];
    $bmiKet = $_POST['bmiKet'];
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
                id_customer,
                id_doctor,
                visit_time,
                keluhan_penyerta,
                tekanan_darah, 
                nadi,
                respirasi, 
                tinggi_badan,
                berat_badan,
                patient_name_pcare,
                suhu,
                saturasi,
                bmi,
                bmi_keterangan,
                code_doctor,
                id_provider
            )VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?)
        ");
    $visit_status = 1;
    $status_antrian = 0;
    $td = $sistole . "/" . $diastole;
    $stmt->bind_param(
        "ssssssssssssssssssssssss",
        $id_patient,
        $visit_ID,
        $tglDaftarDB,
        $nmPoli,
        $source_hub,
        $created_user,
        $nomorantrean,
        $status_antrian,
        $idcustomer,
        $nmDokter,
        $visit_time,
        $keluhan,
        $td,
        $heartRate,
        $respRate,
        $tinggiBadan,
        $beratBadan,
        $nama,
        $suhu,
        $saturasi,
        $bmi,
        $bmiKet,
        $kdDokter,
        $kdProv
    );
    $hasil = $stmt->execute();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien",
            'type' => 'UMUM'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Mendaftar",
        ];
    }
}
echo json_encode($response);
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
function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek)
{
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
