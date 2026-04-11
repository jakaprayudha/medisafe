<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

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
$noNIK = $_POST['noNik'] ?? '';
$nama = $_POST['nama'];
$jnsKlamin = $_POST['jnsKlamin'];
$tglLahir = $_POST['tglLahir'];
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

$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE patient_bpjs = ? OR patient_nik = ?");
$stmt->bind_param('ss', $noKartu, $noNIK);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
if (!$result) {
    $stmt = $koneksi->prepare("SELECT nomor_rm_end FROM setting_clinic WHERE id_customer=? FOR UPDATE");
    $stmt->bind_param("i", $idcustomer);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $lastRM = (int)$row['nomor_rm_end'];
    } else {
        $lastRM = 0;

        $insert = $koneksi->prepare(
            "INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?,0)"
        );
        $insert->bind_param("i", $idcustomer);
        $insert->execute();
        $insert->close();
    }
    $stmt->close();
    $newRM   = $lastRM + 1;
    $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);
    $count = 0;
    do {
        $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4)));

        $check = $koneksi->prepare(
            "SELECT COUNT(*) FROM ms_patient WHERE patient_number=?"
        );
        $check->bind_param("s", $patientNumber);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
    } while ($count > 0);
    $update = $koneksi->prepare("UPDATE setting_clinic SET nomor_rm_end=? WHERE id_customer=?");
    $update->bind_param("ii", $newRM, $idcustomer);
    $update->execute();
    $update->close();

    $stmt = $koneksi->prepare("INSERT INTO ms_patient (patient_bpjs, patient_nik, patient_name, patient_gender, patient_datebirth, id_customer, nomor_rm, patient_number) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssssss', $noKartu, $noNIK, $nama, $jnsKlamin, $tglLahir, $idcustomer, $nomorRM, $patientNumber);
    $result = $stmt->execute();
}
// $result = bpjsPost("/pendaftaran", $payload);
// echo json_encode($result);die();
$result = testingBPJS_POST("https://app.medisafe.id/controller/admisi/api/getpeserta.php", $payload);
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
    $visit_ID = generateVisitID($koneksi);
    $noUrut = (string) $noUrut;
    $sistole      = (int)$sistole;
    $diastole     = (int)$diastole;
    $beratBadan   = (int)$beratBadan;
    $tinggiBadan  = (int)$tinggiBadan;
    $respRate     = (int)$respRate;
    $lingkarPerut = (int)$lingkarPerut;
    $heartRate    = (int)$heartRate;
    $stmt = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssiiiiiiissss",
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
        $visit_ID
    );
    $hasil = $stmt->execute();
    $stmt->close();

    $stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE patient_bpjs = ? OR patient_nik = ?");
    $stmt->bind_param('ss', $noKartu, $noNIK);
    $stmt->execute();
    $chackpasien = $stmt->get_result()->fetch_assoc();

    $created_user = "User";
    $source_hub = "Poliklinik";
    $id_patient = $chackpasien['id_patient'];
    $visit_time = date('H:i:s');
    $suhu    =  $_POST['suhu'];
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
                suhu
            )VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?)
        ");
    $visit_status = 1;
    $status_antrian = 0;
    $td = $sistole . "/" . $diastole;
    $stmt->bind_param(
        "ssssssssssssssssssss",
        $id_patient,
        $visit_ID,
        $tglDaftarDB,
        $nmPoli,
        $source_hub,
        $created_user,
        $noUrut,
        $status_antrian,
        $idcustomer,
        $kdDokter,
        $noKartu,
        $visit_time,
        $keluhan,
        $td,
        $heartRate,
        $respRate,
        $tinggiBadan,
        $beratBadan,
        $nama,
        $suhu
    );

    $hasil1 = $stmt->execute();
    if ($hasil and $hasil1) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien",
            // 'message'  => "Berhasil Mendaftar Pasien Dengan No Urut " . $noUrut,
            'result' => $result
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Mendaftar",
        ];
    }
}
echo json_encode($response);
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
