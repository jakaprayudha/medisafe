<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$id = $_GET['id'];
$status = '1';
$stmt = $koneksi->prepare("SELECT pp.*, p.patient_name, p.patient_datebirth FROM pcare_pendaftaran AS pp INNER JOIN ms_patient AS p ON pp.noKartu = p.patient_bpjs WHERE pp.kunjSakit != ? AND pp.idKlp = ? LIMIT 50;");
$stmt->bind_param('ss', $status, $id);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()){
    $data[] = $row;
}
echo json_encode([
    "list" => $data,
]);