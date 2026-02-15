<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nokartu = $_GET['noKartu'];
$kategori = $_GET['Kategori'];
$kdSarana = $_GET['kdsarana'];
$kdspel = $_GET['kdsubspesialis'];
$tgl = date("d-m-Y", strtotime($_GET['estRujuk']));

$poliKhusus = ["IGD", "HDL", "JIW", "KLT", "PAR", "KEM", "RAT", "HIV"];

if (in_array(strtoupper($kategori), $poliKhusus) && empty($kdSarana)) {
    $url = "/spesialis/rujuk/khusus/" . $kategori . "/noKartu/" . $nokartu . "/tglEstRujuk/" . $tgl;
} else {
    $url = "/spesialis/rujuk/subspesialis/" . $kdspel . "/sarana/" . $kdSarana . "/tglEstRujuk/" . $tgl;
}
$result = bpjsGet($url);
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
    $response = [
        'success' => true,
        'list' => $result['data']['list']
    ];
}
echo json_encode($response);
