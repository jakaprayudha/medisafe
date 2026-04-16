<?php
require_once __DIR__ . '/../view.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../servicebpjs.php';

header('Content-Type: application/json');

$tanggal = $_GET['tglDaftar'] ?? '';
$nomor_visit = $_GET['nomor_visit'] ?? '';

$stmt = $koneksi->prepare("SELECT *, (noKunjung IS NOT NULL AND noKunjung != '') AS status_kunjungan FROM pasien_visit WHERE visit_ID = ?");
$stmt->bind_param('s', $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$status = $data['status_kunjungan'];
if ($status == '1') {
    $stmt = $koneksi->prepare("SELECT pv.visit_ID,pv.id_patient,pv.visit_notes,pv.saturasi,pv.tindakan,p.patient_datebirth,CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur, pk.* FROM pasien_visit AS pv INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN ms_patient AS p ON p.patient_bpjs = pv.noKartu WHERE pv.visit_ID = ? AND pv.id_customer = ?");
    $stmt->bind_param('ss', $nomor_visit, $idcustomer);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $stmt = $koneksi->prepare("SELECT pp.*, pv.id_patient, pv.id_doctor, pv.code_doctor FROM pcare_pendaftaran AS pp INNER JOIN pasien_visit AS pv ON pp.nomor_visit = pv.visit_ID WHERE nomor_visit = ? AND pv.id_customer = ?");
    $stmt->bind_param('ss', $nomor_visit, $idcustomer);
    $stmt->execute();
    $hasil = $stmt->get_result();
}

$data = [];
while ($row = $hasil->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode([
    "status" => "success",
    "data" => $data,
    'kunjung' => $status
]);
exit; // penting