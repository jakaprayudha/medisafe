<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$noKartu = $_GET['nokartu'];
// $result = bpjsGet("/kunjungan/peserta/" . $noKartu);
$stmt = $koneksi->prepare("SELECT pk.*, p.patient_name, pp.kdTkp, pp.kunjSakit FROM pcare_kunjungan AS pk INNER JOIN ms_patient AS p ON pk.noKartu = p.patient_bpjs INNER JOIN pcare_pendaftaran AS pp ON pk.noKartu = pp.noKartu AND pk.tglDaftar = pp.tanggal_daftar WHERE pk.noKartu = ?");
$stmt->bind_param("s", $noKartu);
$stmt->execute();
$result1 = $stmt->get_result();
$data = [];
while ($row = $result1->fetch_assoc()){
    $data = $row;
}

echo json_encode([
    "list" => $data,
]);
