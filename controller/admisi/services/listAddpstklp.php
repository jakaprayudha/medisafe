<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$tgl = date("Y-m-d", strtotime($_GET['tgl']));
$status = "1";
$stmt = $koneksi->prepare("SELECT pp.*, p.patient_name, p.patient_datebirth FROM pcare_pendaftaran AS pp INNER JOIN ms_patient AS p ON pp.noKartu = p.patient_bpjs WHERE pp.tanggal_daftar = ? AND pp.kunjSakit != ? LIMIT 35;");
$stmt->bind_param('ss', $tgl, $status);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()){
    $data[] = $row;
}
echo json_encode([
    "list" => $data,
]);