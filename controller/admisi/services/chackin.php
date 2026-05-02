<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$visit_id = $_POST['visit'];
$stmt = $koneksi->prepare("SELECT pv.visit_antrian,pv.jampraktek, ms_patient.nomor_rm, pv.id_poli ,pv.visit_date, pv.noKartu, mp.kdPoli, pv.code_doctor, md.doctor_name, ms_patient.patient_bpjs, ms_patient.patient_nik, ms_patient.patient_phone FROM pasien_visit AS pv INNER JOIN master_poli AS mp ON mp.nmPoli = pv.id_poli INNER JOIN ms_doctor AS md ON md.doctor_code = pv.code_doctor INNER JOIN ms_patient ON pv.id_patient = ms_patient.id_patient WHERE pv.id_customer = ? AND md.id_customer = ? AND visit_ID = ? AND ms_patient.id_customer = ?");
$stmt->bind_param('ssss', $idcustomer, $idcustomer, $visit_id, $idcustomer);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$kdProviderPeserta = $kodeppk;
$jamSekarang = date('H:i:s');
$tglDaftarDB = $data['visit_date'];
$tglDaftar = date("d-m-Y", strtotime($data['visit_date']));
$noKartu = $data['noKartu'];
$kdPoli = $data['kdPoli'];
$jampraktek = $data['jampraktek'];
$kdDokter = $data['code_doctor'];
$nmDokter = $data['doctor_name'];
$noHp = $data['patient_phone'];
$noNIK = $data['patient_nik'];
$nomor_rm = $data['nomor_rm'];
$visitantri = $data['visit_antrian'];
preg_match('/([A-Za-z]+)(\d+)/', $visitantri, $match);
$kodeAntri = $match[1];
$angkaantrean = $match[2];

$keluhan = isset($_POST['keluhan']) && !empty($_POST['keluhan']) ? $_POST['keluhan'] : null;
$kunjSakit = true;
$sistole       = (int) ($_POST['sistole'] ?? 0);
$diastole      = (int) ($_POST['diastole'] ?? 0);
$beratBadan    = (int) ($_POST['beratBadan'] ?? 0);
$tinggiBadan   = (int) ($_POST['tinggiBadan'] ?? 0);
$respRate      = (int) ($_POST['respRate'] ?? 0);
$lingkarPerut  = (int) ($_POST['lingkarPerut'] ?? 0);
$heartRate     = (int) ($_POST['heartRate'] ?? 0);
$kdTkp = '10';
$nmPoli = $data['id_poli'];
$rujukbalik = 0;

$payload = [
    "kdProviderPeserta" => $kdProviderPeserta,
    "tglDaftar" => $tglDaftar,
    "noKartu" => $noKartu,
    "kdPoli" => $kdPoli,
    "keluhan" => null,
    "kunjSakit" => $kunjSakit,
    "sistole" => $sistole,
    "diastole" => $diastole,
    "beratBadan" => $beratBadan,
    "tinggiBadan" => $tinggiBadan,
    "respRate" => $respRate,
    "lingkarPerut" => $lingkarPerut,
    "heartRate" => $heartRate,
    "rujukBalik" => $rujukbalik,
    "kdTkp" => $kdTkp
];
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/pendaftaran", $payload);
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
    $stmt = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`, `saturasi`, `suhu`, `jamperaktek`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
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

    $update = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '0', id_doctor = ?, visit_time = ? WHERE id_customer = ? AND visit_id = ?");
    $update->bind_param('ssss', $nmDokter, $jamSekarang, $idcustomer, $visit_id);
    $update->execute();
    $update->close();

    if ($hasil and $update) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien",
            'result' => $result,
            "nomorkartu"      => $noKartu,
            "nik"             => $noNIK,
            "nohp"            => $noHp ?? '0',
            "kodepoli"        => $kdPoli,
            "namapoli"        => $nmPoli,
            "norm"            => $nomor_rm,
            "tanggalperiksa"  => $tglDaftarDB,
            "kodedokter"      => $kdDokter,
            "namadokter"      => $nmDokter,
            "jampraktek"      => $jampraktek,
            "nomorantrean"    => $visitantri,
            "angkaantrean"    => $angkaantrean,
            "kodeAntri"       => $kodeAntri,
            "keterangan"      => "",
            'type'            => "BPJS"
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Mendaftar",
        ];
    }
}
echo json_encode($response);
