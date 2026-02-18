<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$eduid = $_POST['eduid'] ?? null;
$tglPelayanan = $_POST['tglPelayanan'];
$DBtglPelayanan = date("Y-m-d", strtotime($tglPelayanan));
$kdkegiatan = $_POST['kdKegiatan'];
$kdkelompok = $_POST['kdKelompok'];
$materi = $_POST['materi'];
$pembicara = $_POST['pembicara'];
$lokasi = $_POST['lokasi'];
$biaya = preg_replace('/[^0-9]/', '', $_POST['biaya']);
$keterangan = $_POST['keterangan'];
$kdClpprolanis = $_POST['kdClpprolanis'];

$payload = [
    "eduId" => $eduid,
    "clubId" => $kdClpprolanis,
    "tglPelayanan" => $tglPelayanan,
    "kdKegiatan" => $kdkegiatan,
    "kdKelompok" => $kdkelompok,
    "materi" => $materi,
    "pembicara" => $pembicara,
    "lokasi" => $lokasi,
    "keterangan" => $keterangan,
    "biaya" => $biaya
];
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost('/kelompok/kegiatan', $payload);
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
    $eduId = $result['data']['message'];
    $stmt =  $koneksi->prepare("INSERT INTO pcare_kegiatan (eduId,clubId,tglPelayanan,kdKegiatan,kdKelompok,materi,pembicara,lokasi,keterangan,biaya) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssssi', $eduId, $kdClpprolanis, $DBtglPelayanan, $kdkegiatan, $kdkelompok, $materi, $pembicara, $lokasi, $keterangan, $biaya);
    $hasil = $stmt->execute();
    $stmt->close();
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Membuat Kelompok",
        ];
    } else {
        $response = [
            'success'  => false,
            'message'  => "Gagal Membuat Kelompok",
        ];
    }
}
echo json_encode($response);
