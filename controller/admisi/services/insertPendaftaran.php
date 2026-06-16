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
$kdProv = $_POST['kdProv'];
$id_patient = $_POST['id_patient'];
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

if (empty($kdDokter)) {
    echo json_encode([
        "status" => false,
        "message" => "Dokter harus diisi"
    ]);
    exit;
}

if (empty($kdPoli) || $nmPoli == "Mencari data...") {
    echo json_encode([
        "status" => false,
        "message" => "Poli harus diisi"
    ]);
    exit;
}
$visit_ID = generateVisitID($koneksi, $idcustomer);
if ($type == "BPJS") {
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
        $stmt = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`, `saturasi`, `suhu`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "ssssssiiiiiiissssss",
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
            $suhu
        );
        $hasil = $stmt->execute();
        $stmt->close();

        $stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE (patient_bpjs = ? OR patient_nik = ?) AND id_customer = ?");
        $stmt->bind_param('sss', $noKartu, $noNIK, $idcustomer);
        $stmt->execute();
        $chackpasien = $stmt->get_result()->fetch_assoc();

        $created_user = "User";
        $source_hub = "Poliklinik";
        $id_patient = $chackpasien['id_patient'];
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
            $noUrut,
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
        $hasil1 = $stmt->execute();
        $fieldWhere = '';
        $valueWhere = '';
        // if (!empty($noNIK)) {
        //     $fieldWhere = 'patient_nik';
        //     $valueWhere = $noNIK;
        // } elseif (!empty($noKartu)) {
        //     $fieldWhere = 'patient_bpjs';
        //     $valueWhere = $noKartu;
        // }

        // if (!empty($noKartu)) {
        //     $fieldWhere = 'patient_bpjs';
        //     $valueWhere = $noKartu;
        // } elseif (!empty($noNIK)) {
        //     $fieldWhere = 'patient_nik';
        //     $valueWhere = $noNIK;
        // }
        // $stmt2 = $koneksi->prepare("UPDATE ms_patient SET patient_nik = ?, patient_bpjs = ?, patient_datebirth = ? WHERE (patient_bpjs = ? OR patient_nik = ?) AND id_customer = ?");
        // $stmt2->bind_param("ssssss", $noNIK, $noKartu, $tglLahir, $noKartu, $noNIK, $idcustomer);
        // $hasil2 = $stmt2->execute();
        if ($hasil and $hasil1) {
            $response = [
                'success'  => true,
                'message'  => "Berhasil Mendaftar Pasien",
                'result' => $result
            ];
        } else {
            $response = [
                'success' => false,
                'message' => "Gagal Mendaftar",
            ];
        }
    }
} else {
    // $chackpasien = null;
    // if (!empty($noNIK)) {
    //     $stmt = $koneksi->prepare("
    //     SELECT * FROM ms_patient 
    //     WHERE patient_nik = ? AND id_customer = ?
    // ");
    //     $stmt->bind_param('ss', $noNIK, $idcustomer);
    //     $stmt->execute();
    //     $chackpasien = $stmt->get_result()->fetch_assoc();
    // }
    // if (!$chackpasien && !empty($noKartu)) {
    //     $stmt = $koneksi->prepare("
    //     SELECT * FROM ms_patient 
    //     WHERE patient_bpjs = ? AND id_customer = ?
    // ");
    //     $stmt->bind_param('ss', $noKartu, $idcustomer);
    //     $stmt->execute();
    //     $chackpasien = $stmt->get_result()->fetch_assoc();
    // }
    // if (!$chackpasien) {
    //     echo json_encode([
    //         'success' => false,
    //         'message' => 'Data pasien tidak ditemukan',
    //         'nik' => $noNIK,
    //         'kartu' => $noKartu
    //     ]);
    //     exit;
    // }
    // $id_patient = $chackpasien['id_patient'];

    $created_user = "User";
    $source_hub   = "Poliklinik";
    $visit_time   = date('H:i:s');
    $bmi          = $_POST['bmi'] ?? '';
    $bmiKet       = $_POST['bmiKet'] ?? '';
    $td           = $sistole . "/" . $diastole;
    $status_antrian = 0;

    $stmtInsert = $koneksi->prepare("
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtInsert) {
        $response = [
            'success' => false,
            'message' => $koneksi->error
        ];
        echo json_encode($response);
        exit;
    }

    $stmtInsert->bind_param(
        "ssssssssssssssssssssssss",
        $id_patient,
        $visit_ID,
        $tglDaftarDB,
        $nmPoli,
        $source_hub,
        $created_user,
        $noUrut,
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
    $hasil = $stmtInsert->execute();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien",
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
