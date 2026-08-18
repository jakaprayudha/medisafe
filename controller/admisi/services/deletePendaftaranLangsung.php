<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nomor_kartu = $_POST['nomor_kartu'];
$tanggal = $_POST['tanggal'];
$noUrut = $_POST['noUrut'];
$kdpoli = $_POST['kdpoli'];
$result = bpjsDelete('/pendaftaran/peserta/' . $nomor_kartu . '/tglDaftar/' . $tanggal . '/noUrut/' . $noUrut . '/kdPoli/' . $kdpoli);
    if ($result['code'] != "200") {
        $msg = $result['message'];
        if ($msg == null) {
            $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        }
        $msg = ". [Pcare Error]: " . $msg;
    }else{
        echo "berhasil";
    }

