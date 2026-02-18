<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$noKartu = $_POST['pesertaId'];
$noKlp = $_POST['idKlp'];
$status = "0";
$tgl = $_POST['tgl'];
$payload = [
    "eduId" => $noKlp,
    "noKartu" => $noKartu
];
$result = bpjsPost('/kelompok/peserta', $payload);
if ($result['code'] != "200") {
    $msg = $result['message'];
    if ($result['message'] = "PRECONDITION_REQUIRED"){
        $msg = "Pasien Sudah Masuk Kelompok Ini";
    }
    $response = [
        'success' => false,
        'message' => $msg
    ];
} else {
    $stmt = $koneksi->prepare("INSERT INTO pcare_pstKelompok (`tgl_kegiatan`, `idKelompok`, `noKartu`, `status`) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $tgl, $noKlp, $noKartu, $status);
    $stmt->execute();
    $stmt1 = $koneksi->prepare("UPDATE pcare_pendaftaran SET idTkp = ? WHERE noKartu = ? AND tanggal_daftar = ?");
    $stmt1->bind_param("sss", $noKlp, $noKartu, $tgl);
    $stmt1->execute();
    if ($stmt && $stmt1) {
        $response = [
            'success' => true,
        ];
    } else {
        $response = [
            'success' => false,
            'message' => mysqli_error($koneksi)
        ];
    }
}
echo json_encode($response);
