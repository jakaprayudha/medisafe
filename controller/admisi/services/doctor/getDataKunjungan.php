<?php
require_once __DIR__ . '/../view.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../servicebpjs.php';

header('Content-Type: application/json');

$tanggal = $_GET['tglDaftar'] ?? '';
$nomor_visit = $_GET['nomor_visit'] ?? '';

$stmt = $koneksi->prepare("SELECT 1 FROM pcare_pendaftaran WHERE nomor_visit = ?");
$stmt->bind_param("s", $nomor_visit);
$stmt->execute();
$result = $stmt->get_result();
$exist = $result->fetch_assoc();

$result->free();
$stmt->close();
if ($exist) {
    $stmt = $koneksi->prepare("SELECT *, (noKunjung IS NOT NULL AND noKunjung != '') AS status_kunjungan FROM pasien_visit WHERE visit_ID = ?");
    $stmt->bind_param('s', $nomor_visit);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $status = $data['status_kunjungan'];
    if ($status == '1') {
        $stmt = $koneksi->prepare("SELECT pv.visit_ID,p.patient_nik,pv.id_patient,pv.visit_notes,pv.saturasi,pv.tindakan,p.patient_datebirth,CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur, pk.* FROM pasien_visit AS pv INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN ms_patient AS p ON p.patient_bpjs = pv.noKartu WHERE pv.visit_ID = ? AND pv.id_customer = ?");
        $stmt->bind_param('ss', $nomor_visit, $idcustomer);
        $stmt->execute();
        $hasil = $stmt->get_result();
    } else {
        $stmt = $koneksi->prepare("SELECT pp.*, p.patient_nik, pv.id_patient, pv.id_doctor, pv.code_doctor, CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur FROM pcare_pendaftaran AS pp INNER JOIN pasien_visit AS pv ON pp.nomor_visit = pv.visit_ID INNER JOIN ms_patient AS p ON p.patient_bpjs = pv.noKartu  WHERE nomor_visit = ? AND pv.id_customer = ?");
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
    ]);
} else {
    $stmt = $koneksi->prepare("SELECT pv.*,p.patient_datebirth, CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur FROM pasien_visit pv INNER JOIN ms_patient p ON p.id_patient = pv.id_patient WHERE pv.id_customer = ? AND pv.visit_ID = ?");
    $stmt->bind_param('ss', $idcustomer, $nomor_visit);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    $response = [
        "status" => "success",
        "pasienStatus" => "UMUM",
        "data" => [
            [
                "visit_ID" => $hasil['visit_ID'],
                "id_patient" => $hasil['id_patient'],
                "saturasi" => $hasil['saturasi'],
                "tindakan" => $hasil['tindakan'],
                "sistole" => $hasil['sistole'],
                "diastole" => $hasil['diastole'],
                "resp_rate" => $hasil['nadi'],
                "heart_rate" => $hasil['respirasi'],
                "suhu" => $hasil['suhu'],
                "berat_badan" => $hasil['berat_badan'],
                "tinggi_badan" => $hasil['tinggi_badan'],
                "lingkar_perut" => $hasil['lingkar_perut'],
                "tglDaftar" => $hasil['visit_data'],
                "nmPoli" => $hasil['id_poli'],
                "keluhan" => $hasil['keluhan_penyerta'],
                "anamnesa" => $hasil['anamnesa'],
                "kdDiag1" => $hasil['kdDiag1'],
                "kdDiag2" => $hasil['kdDiag2'],
                "kdDiag3" => $hasil['kdDiag3'],
                "nmDiag1" => $hasil['nmDiag1'],
                "nmDiag2" => $hasil['nmDiag2'],
                "nmDiag3" => $hasil['nmDiag3'],
                "kdDokter" => $hasil['code_doctor'],
                "nmDokter" => $hasil['id_doctor'],
                "kdStatusPulang" => $hasil['status_pulang'],
                "alergiMakan" => $hasil['alergiMakan'],
                "alergiUdara" => $hasil['alergiUdara'],
                "alergiObat" => $hasil['alergiObat'],
                "umur" => $hasil['umur'],
            ]
        ]
    ];
    echo json_encode($response);
}
