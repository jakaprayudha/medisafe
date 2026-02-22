<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$no_kunjungan = $_GET['no_kunjungan'];
$stmt = $koneksi->prepare("SELECT * FROM pcare_obat WHERE noKunjungan = ?");
$stmt->bind_param('s', $no_kunjungan);
$stmt->execute();
$hasil = $stmt->get_result();
$data = [];
while ($row = $hasil->fetch_assoc()){
    $data[] = $row;
}
echo json_encode($data);