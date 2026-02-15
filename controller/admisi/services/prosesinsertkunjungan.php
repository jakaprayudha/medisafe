<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$noKunjungan = $_POST['noKunjungan'];
$noKartu = $_POST['noKartu'];
$tglDaftar = date("d-m-Y", strtotime($_POST['tglDaftar']));
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
$tglPulang = date("d-m-Y", strtotime($_POST['tglPulang']));
$kdDokter = $_POST['kdDokter'];
$kdPoliRujukInternal = $_POST['kdPoliRujukInternal'] ?? null;
$kdppk = $_POST['kdppk'] ?? null;
$tglEstRujuk = $_POST['tglEstRujuk'] ?? null;
$kdSubSpesialis1 = $_POST['kdSubSpesialis1'] ?? null;
$kdSarana = $_POST['kdSarana'];
$khusus = null;
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
    "rujukLanjut" => [
        "kdppk" => $kdppk,
        "tglEstRujuk" => $tglEstRujuk,
        "subSpesialis" => [
            "kdSubSpesialis1" => $kdSubSpesialis1,
            "kdSarana" => null
        ],
        "khusus" => null
    ],
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
echo json_encode($payload, JSON_PRETTY_PRINT);die();