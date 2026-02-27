<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$consid = $_POST['id'];
$nomor_kartu = $_POST['nokartu'];
$tglDB = $_POST['tanggal'];
$tanggal = date("d-m-Y", strtotime($tglDB));
$noUrut = $_POST['noUrut'];
$kdpoli = $_POST['kdpoli'];
$result = bpjsDelete('/pendaftaran/peserta/' . $nomor_kartu . '/tglDaftar/' . $tanggal . '/noUrut/' . $noUrut . '/kdPoli/' . $kdpoli, 'DELETE');
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
    $response = $result;
}
echo json_encode($response);
