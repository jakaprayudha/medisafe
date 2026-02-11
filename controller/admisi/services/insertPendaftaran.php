<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

$kdProviderPeserta = $_POST['kdProviderPeserta'];
$tglDaftar = date("d-m-Y", strtotime($_POST['tglDaftar']));
$noKartu = $_POST['noKartu'];
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
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/pendaftaran", $payload);
// $result = testingBPJS_POST("http://localhost/medisafe/controller/admisi/api/getpeserta.php", $payload);
if ($result['code'] != '201') {
    $msg = $result['message'];
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
    $tglDaftarDB = date('Y-m-d', strtotime(str_replace('-', '/', $tglDaftar)));
    $stmt = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "sssssiiiiiiisss",
        $tglDaftarDB,
        $noKartu,
        $kdPoli,
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
        $noUrut
    );
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien Dengan No Urut " . $noUrut,
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Mendaftar",
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($response);
