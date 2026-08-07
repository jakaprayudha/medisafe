<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
$nomor_visit = $_POST['novisit'];
$sql = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT pv.noKartu, pv.visit_date, p.kdPoli FROM pasien_visit AS pv INNER JOIN master_poli AS p ON p.nmPoli = pv.id_poli WHERE visit_ID = '$nomor_visit'"));
$tanggalperiksa = $sql['visit_date'];
$kdPoli = $sql['kdPoli'];
$nomorkartu = $sql['noKartu'];
$alasan = $_POST['alasan'];
if ($status_antrol) {
    $payload = [
        "tanggalperiksa"  => $tanggalperiksa,
        "kodepoli" => $kdPoli,
        "nomorkartu" => $nomorkartu,
        "alasan"      => $alasan
    ];
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    $result = bpjsPost("/antrean/batal", $payload);
    if ($result['code'] != '200') {
        $msg = $result['message'];
        if ($msg == null) {
            $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        }
        if ($msg == "Data tidak ditemukan"){
            $response = [
            'success' => true,
            'message' => "",
        ];
        }
        $response = [
            'success' => false,
            'message' => $msg,
            'result' => $result
        ];
    } else {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Batal Pasien",
        ];
    }
} else {
    // Klinik tidak terdaftar di setting_antrol, maka lewati BPJS,
    // di deletePendaftaran.php yang mengubah pasien_visit.
    $response = [
        'success'  => true,
        'message'  => "Berhasil Batal Pasien",
    ];
}
echo json_encode($response);
