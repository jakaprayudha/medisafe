<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$kdProvider            = $kodeppk;
$kdMCU                 = $_POST['kdMCU'] ?? 0;
$noKunjungan           = $_POST['noKunjungan'] ?? '';
$DBtglPelayanan        = $_POST['tglPelayanan'] ?? '';
$tglPelayanan          = date("d-m-Y", strtotime($DBtglPelayanan));
$radiologiFoto         = $_POST['radiologiFoto'] ?? null;
$tekananDarahSistole   = $_POST['tekananDarahSistole'] ?? 0;
$tekananDarahDiastole  = $_POST['tekananDarahDiastole'] ?? 0;
$darahRutinHemo        = $_POST['darahRutinHemo'] ?? 0;
$darahRutinLeu         = $_POST['darahRutinLeu'] ?? 0;
$darahRutinErit        = $_POST['darahRutinErit'] ?? 0;
$darahRutinTrom        = $_POST['darahRutinTrom'] ?? 0;
$darahRutinLaju        = $_POST['darahRutinLaju'] ?? 0;
$darahRutinHema        = $_POST['darahRutinHema'] ?? 0;
$lemakDarahHDL         = $_POST['lemakDarahHDL'] ?? 0;
$lemakDarahLDL         = $_POST['lemakDarahLDL'] ?? 0;
$lemakDarahChol        = $_POST['lemakDarahChol'] ?? 0;
$lemakDarahTrigli      = $_POST['lemakDarahTrigli'] ?? 0;
$gulaDarahSewaktu      = $_POST['gulaDarahSewaktu'] ?? 0;
$gulaDarahPuasa        = $_POST['gulaDarahPuasa'] ?? 0;
$gulaDarahPostPrandial = $_POST['gulaDarahPostPrandial'] ?? 0;
$gulaDarahHbA1c        = $_POST['gulaDarahHbA1c'] ?? 0;
$fungsiHatiSGOT        = $_POST['fungsiHatiSGOT'] ?? 0;
$fungsiHatiSGPT        = $_POST['fungsiHatiSGPT'] ?? 0;
$fungsiHatiGamma       = $_POST['fungsiHatiGamma'] ?? 0;
$fungsiHatiProtKual    = $_POST['fungsiHatiProtKual'] ?? 0;
$fungsiHatiAlbumin     = $_POST['fungsiHatiAlbumin'] ?? 0;
$fungsiGinjalCrea      = $_POST['fungsiGinjalCrea'] ?? 0;
$fungsiGinjalUreum     = $_POST['fungsiGinjalUreum'] ?? 0;
$fungsiGinjalAsam      = $_POST['fungsiGinjalAsam'] ?? 0;
$fungsiJantungABI      = $_POST['fungsiJantungABI'] ?? 0;
$fungsiJantungEKG      = $_POST['fungsiJantungEKG'] ?? null;
$fungsiJantungEcho     = $_POST['fungsiJantungEcho'] ?? null;
$funduskopi            = $_POST['funduskopi'] ?? null;
$pemeriksaanLain       = $_POST['pemeriksaanLain'] ?? null;
$keterangan            = $_POST['keterangan'] ?? null;

$payload = [
    "kdMCU" => $kdMCU,
    "noKunjungan" => $noKunjungan,
    "kdProvider" => $kdProvider,
    "tglPelayanan" => $tglPelayanan,
    "tekananDarahSistole" => $tekananDarahSistole,
    "tekananDarahDiastole" => $tekananDarahDiastole,
    "radiologiFoto" => $radiologiFoto,
    "darahRutinHemo" => $darahRutinHemo,
    "darahRutinLeu" => $darahRutinLeu,
    "darahRutinErit" => $darahRutinErit,
    "darahRutinLaju" => $darahRutinLaju,
    "darahRutinHema" => $darahRutinHema,
    "darahRutinTrom" => $darahRutinTrom,
    "lemakDarahHDL" => $lemakDarahHDL,
    "lemakDarahLDL" => $lemakDarahLDL,
    "lemakDarahChol" => $lemakDarahChol,
    "lemakDarahTrigli" => $lemakDarahTrigli,
    "gulaDarahSewaktu" => $gulaDarahSewaktu,
    "gulaDarahPuasa" => $gulaDarahPuasa,
    "gulaDarahPostPrandial" => $gulaDarahPostPrandial,
    "gulaDarahHbA1c" => $gulaDarahHbA1c,
    "fungsiHatiSGOT" => $fungsiHatiSGOT,
    "fungsiHatiSGPT" => $fungsiHatiSGPT,
    "fungsiHatiGamma" => $fungsiHatiGamma,
    "fungsiHatiProtKual" => $fungsiHatiProtKual,
    "fungsiHatiAlbumin" => $fungsiHatiAlbumin,
    "fungsiGinjalCrea" => $fungsiGinjalCrea,
    "fungsiGinjalUreum" => $fungsiGinjalUreum,
    "fungsiGinjalAsam" => $fungsiGinjalAsam,
    "fungsiJantungABI" => $fungsiJantungABI,
    "fungsiJantungEKG" => $fungsiJantungEKG,
    "fungsiJantungEcho" => $fungsiJantungEcho,
    "funduskopi" => $funduskopi,
    "pemeriksaanLain" => $pemeriksaanLain,
    "keterangan" => $keterangan
];
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost('/MCU', $payload);
// $result = testingBPJS_POST("http://localhost/medisafe/controller/admisi/api/getpeserta.php", $payload);
if ($result['code'] != '200') {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $kdMCU = $result['data']['message'];
    $stmt = $koneksi->prepare("INSERT INTO pcare_mcu (
    kdMCU, noKunjungan, kdProvider, tglPelayanan,
    tekananDarahSistole, tekananDarahDiastole,
    radiologiFoto,
    darahRutinHemo, darahRutinLeu, darahRutinErit,
    darahRutinLaju, darahRutinHema, darahRutinTrom,
    lemakDarahHDL, lemakDarahLDL, lemakDarahChol, lemakDarahTrigli,
    gulaDarahSewaktu, gulaDarahPuasa, gulaDarahPostPrandial, gulaDarahHbA1c,
    fungsiHatiSGOT, fungsiHatiSGPT, fungsiHatiGamma, fungsiHatiProtKual, fungsiHatiAlbumin,
    fungsiGinjalCrea, fungsiGinjalUreum, fungsiGinjalAsam,
    fungsiJantungABI, fungsiJantungEKG, fungsiJantungEcho,
    funduskopi, pemeriksaanLain, keterangan
) VALUES (
    ?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?    
)");
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssssss",
        $kdMCU,
        $noKunjungan,
        $kdProvider,
        $DBtglPelayanan,
        $tekananDarahSistole,
        $tekananDarahDiastole,
        $radiologiFoto,
        $darahRutinHemo,
        $darahRutinLeu,
        $darahRutinErit,
        $darahRutinLaju,
        $darahRutinHema,
        $darahRutinTrom,
        $lemakDarahHDL,
        $lemakDarahLDL,
        $lemakDarahChol,
        $lemakDarahTrigli,
        $gulaDarahSewaktu,
        $gulaDarahPuasa,
        $gulaDarahPostPrandial,
        $gulaDarahHbA1c,
        $fungsiHatiSGOT,
        $fungsiHatiSGPT,
        $fungsiHatiGamma,
        $fungsiHatiProtKual,
        $fungsiHatiAlbumin,
        $fungsiGinjalCrea,
        $fungsiGinjalUreum,
        $fungsiGinjalAsam,
        $fungsiJantungABI,
        $fungsiJantungEKG,
        $fungsiJantungEcho,
        $funduskopi,
        $pemeriksaanLain,
        $keterangan
    );
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Membuat MCU",
            'kodeMCU'  => $kdMCU
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Membuat MCU",
        ];
    }
}
echo json_encode($response);
