<?php
require_once __DIR__ . '/../view.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../servicebpjs.php';

header('Content-Type: application/json');

$tanggal = $_GET['tglDaftar'] ?? '';
$nomor_visit = $_GET['nomor_visit'] ?? '';

$stmt = $koneksi->prepare("SELECT pk.kdPrognosa,pk.kdSadar,pk.alergiObat,pk.alergiMakan,pk.alergiUdara, pk.sistole, pk.lingkarPerut, pk.diastole, pk.respRate, pk.heartRate, pv.saturasi, pv.bmi,pv.bmi_keterangan, pv.id_patient, pv.noKartu, pv.visit_date, pv.catatan_screening, pv.tinggi_badan, pv.berat_badan, pv.suhu, pv.kondisi_masuk, pv.anamnesa, pv.keluhan_penyerta, pv.code_doctor, pv.id_doctor, pv.riwayat_alergi, pv.riwayat_penyakit_pribadi, pv.riwayat_penyakit_sekarang, pv.riwayat_pengobatan, pv.tindakan, pv.edukasi, pv.visit_ID, pv.id_customer,ms_poli.poli_code,ms_poli.poli_name, pk.noKunjungan, pk.nmDiag1 , pk.nmDiag2, pk.nmDiag3, pk.kdDiag1, pk.kdDiag2, pk.kdDiag3, pk.kdStatusPulang FROM pasien_visit AS pv INNER JOIN pcare_pendaftaran AS pp ON pv.visit_ID = pp.nomor_visit INNER JOIN ms_poli ON ms_poli.poli_name = pv.id_poli LEFT JOIN pcare_kunjungan AS pk ON pk.noKunjungan = pv.noKunjung WHERE pv.id_customer = ? AND ms_poli.id_customer = ? AND pv.visit_ID = ?");

$stmt->bind_param('sss', $idcustomer, $idcustomer, $nomor_visit);
$stmt->execute();

$hasil = $stmt->get_result();
$data = [];

while ($row = $hasil->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
exit; // penting