<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$tanggal = $_POST['tanggal'] ?? '';

$stmt = $koneksi->prepare("SELECT pm.*, pk.noKartu, p.patient_name FROM `pcare_mcu` AS pm INNER JOIN pcare_kunjungan AS pk ON pk.noKunjungan = pm.noKunjungan INNER JOIN ms_patient AS p ON p.patient_bpjs = pk.noKartu WHERE pm.tglPelayanan = ?");
$stmt->bind_param('s', $tanggal);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $totalData,
    "recordsFiltered" => $totalFiltered,
    "data"            => $data
]);