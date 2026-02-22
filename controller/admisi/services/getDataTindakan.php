<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$nokunjung = $_GET['no_kunjungan'];


// $result = bpjsGet("/tindakan/kunjungan/0032B0370226Y000005");
// echo json_encode($result);

$stmt = $koneksi->prepare("SELECT * FROM pcare_tindakan WHERE noKunjungan = ?");
$stmt->bind_param('s', $nokunjung);
$stmt->execute();
$hasil = $stmt->get_result();
$data = [];
while ($row = $hasil->fetch_assoc()){
    $data[] = $row;
}
echo json_encode($data);