<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$noKunjungan = $_POST['noKunjungan'] ?? null;
$noKartu = $_POST['noKartu'];
$DBtglDatar = $_POST['tglDaftar'];
$DBtglEstRujuk = $_POST['tglRujukan'];
$DBtglPulang = $_POST['tglPulang'];
$tglDaftar = date("d-m-Y", strtotime($DBtglDatar));
$tglEstRujuk = date("d-m-Y", strtotime($DBtglEstRujuk));
$tglPulang = date("d-m-Y", strtotime($DBtglPulang));
$kdPoli = $_POST['kdPoli'];
$keluhan = $_POST['keluhan'];
$kdSadar = $_POST['kdSadar'];
$sistole = (int) $_POST['sistole'];
$diastole = (int) $_POST['diastole'];
$beratBadan = (int) $_POST['beratBadan'];
$tinggiBadan = (int) $_POST['tinggiBadan'];
$respRate = (int) $_POST['respRate'];
$lingkarPerut = (int) $_POST['lingkarPerut'];
$heartRate = (int) $_POST['heartRate'];
$kdStatusPulang = $_POST['kdStatusPulang'];
$kdDokter = $_POST['kdDokter'];
$kdPoliRujukInternal = $_POST['kdPoliRujukInternal'] ?? null;
$kdppk = $_POST['kdppk'] ?? null;
$kdSubSpesialis1 = $_POST['kdSubSpesialis1'] ?? null;
$kdspesialiskhusus = $_POST['kdSubSpesialiskhusus'] ?? null;
$kdSarana = $_POST['kdSarana'] ?? null;
$kdkategori = null;
$kdTacc = "-1";
$alasanTacc = null;
$anamnesa = $_POST['anamnesa'];
$alergiMakan = $_POST['alergiMakan'];
$alergiUdara = $_POST['alergiUdara'];
$alergiObat = $_POST['alergiObat'];
$kdPrognosa = $_POST['kdPrognosa'];
$terapiObat = $_POST['terapiObat'];
$terapiNonObat = $_POST['terapiNonObat'];
$bmhp = $_POST['bmhp'];
$suhu = $_POST['suhu'];
$diag1 = $_POST['diag1'] ?? null;
$diag2 = $_POST['diag2'] ?? null;
$diag3 = $_POST['diag3'] ?? null;
$catatan = $_POST['catatan'] ?? null;
$typeRujukan = $_POST['typeRujukan'];
$payload = [
    "noKunjungan" => $noKunjungan,
    "noKartu" => $noKartu,
    "tglDaftar" => $tglDaftar,
    "kdPoli" => $kdPoli,
    "keluhan" => $keluhan,
    "kdSadar" => $kdSadar,
    "sistole" => $sistole,
    "diastole" => $diastole,
    "beratBadan" => $beratBadan,
    "tinggiBadan" => $tinggiBadan,
    "respRate" => $respRate,
    "heartRate" => $heartRate,
    "lingkarPerut" => $lingkarPerut,
    "kdStatusPulang" => $kdStatusPulang,
    "tglPulang" => $tglPulang,
    "kdDokter" => $kdDokter,
    "kdDiag1" => $diag1,
    "kdDiag2" => $diag2,
    "kdDiag3" => $diag3,
    "kdPoliRujukInternal" => $kdPoliRujukInternal,
    "rujukLanjut" => null,
    "kdTacc" => $kdTacc,
    "alasanTacc" => $alasanTacc,
    "anamnesa" => $anamnesa,
    "alergiMakan" => $alergiMakan,
    "alergiUdara" => $alergiUdara,
    "alergiObat" => $alergiObat,
    "kdPrognosa" => $kdPrognosa,
    "terapiObat" => $terapiObat,
    "terapiNonObat" => $terapiNonObat,
    "bmhp" => $bmhp,
    "suhu" => $suhu
];
switch ($typeRujukan) {
    case 'normal':
        $payload['rujukLanjut'] = null;
        break;
    case 'spesialis':
        $payload['rujukLanjut'] = [
            "kdppk" => $kdppk,
            "tglEstRujuk" => $tglEstRujuk,
            "subSpesialis" => [
                "kdSubSpesialis1" => $kdSubSpesialis1,
                "kdSarana" => $kdSarana
            ],
            "khusus" => null
        ];
        break;
    case 'khusus':
        $payload['rujukLanjut'] = [
            "kdppk" => $kdppk,
            "tglEstRujuk" => $tglEstRujuk,
            "subSpesialis" => null,
            "khusus" => [
                "kdKhusus" => $kdKhusus,
                "kdSubSpesialis" => null,
                "catatan" => $catatan
            ]
        ];
        break;
}


echo json_encode($payload, JSON_PRETTY_PRINT);
die();
// $result = bpjsPost("/kunjungan", $payload);
// $result = testingBPJS_POST("http://localhost/medisafe/controller/admisi/api/getpeserta.php", $payload);
if ($result['code'] != "200") {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {

    $noKunjungan = $result['data']['message'];
    $stmt = $koneksi->prepare("INSERT INTO pcare_kunjungan (
        noKunjungan, noKartu, tglDaftar, kdPoli, keluhan, kdSadar,
        sistole, diastole, beratBadan, tinggiBadan, respRate, heartRate,
        lingkarPerut, kdStatusPulang, tglPulang, kdDokter,
        kdDiag1, kdDiag2, kdDiag3, kdPoliRujukInternal,
        tglEstRujuk, kdppk, subSpesialis, kdsarana, kdKhusus, kdkhSpesialis,
        catatan, kdTacc, alasanTacc, anamnesa,
        alergiMakan, alergiUdara, alergiObat,
        kdPrognosa, terapiObat, terapiNonObat,
        bmhp, suhu
        ) VALUES (
        ?,?,?,?,?,?,
        ?,?,?,?,?,?,
        ?,?,?,?,?,
        ?,?,?,?,?,
        ?,?,?,?,?,
        ?,?,?,?,?,
        ?,?,?,?,?,?)");

    $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssssss",
        $noKunjungan,
        $noKartu,
        $DBtglDatar,
        $kdPoli,
        $keluhan,
        $kdSadar,
        $sistole,
        $diastole,
        $beratBadan,
        $tinggiBadan,
        $respRate,
        $heartRate,
        $lingkarPerut,
        $kdStatusPulang,
        $DBtglPulang,
        $kdDokter,
        $diag1,
        $diag2,
        $diag3,
        $kdPoliRujukInternal,
        $DBtglEstRujuk,
        $kdppk,
        $kdSubSpesialis1,
        $kdSarana,
        $kdkategori,
        $kdspesialiskhusus,
        $catatan,
        $kdTacc,
        $alasanTacc,
        $anamnesa,
        $alergiMakan,
        $alergiUdara,
        $alergiObat,
        $kdPrognosa,
        $terapiObat,
        $terapiNonObat,
        $bmhp,
        $suhu
    );
    $simpan = $stmt->execute();
    $stmt->close();
    if ($simpan) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Membuat Kunjungan",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Membuat SEP (Sistem)" . mysqli_error($koneksi),
        ];
    }
}
echo json_encode($response);
