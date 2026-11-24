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
    // pasien diambil lewat id_patient (dulu lewat no. BPJS: bisa cocok ke pasien faskes lain / kosong bila no. kartu berubah)
    $stmt = $koneksi->prepare("SELECT (noKunjung IS NOT NULL AND noKunjung != '') AS status_kunjungan FROM pasien_visit WHERE visit_ID = ? AND id_customer = ?");
    $stmt->bind_param('ss', $nomor_visit, $idcustomer);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $status = $data['status_kunjungan'] ?? '0';
    if ($status == '1') {
        $stmt = $koneksi->prepare("SELECT pv.visit_ID,p.patient_nik,pv.desAlMakan, pv.desAlUdara, pv.desAlObat, pv.id_patient,pv.visit_notes,pv.saturasi,pv.tindakan,p.patient_datebirth,CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur, pk.* FROM pasien_visit AS pv INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN ms_patient AS p ON p.id_patient = pv.id_patient WHERE pv.visit_ID = ? AND pv.id_customer = ?");
        $stmt->bind_param('ss', $nomor_visit, $idcustomer);
        $stmt->execute();
        $hasil = $stmt->get_result();
    } else {
        $stmt = $koneksi->prepare("SELECT 
    pp.tanggal_daftar,
    pp.noKartu,
    pp.kdPoli,

    pv.id_visit,
    pp.kdTkp,
    pp.kunjSakit,
    pv.id_patient,
    pv.id_doctor,
    pv.code_doctor,

    pv.anamnesa AS keluhan,
    pv.catatan_screening,

    COALESCE(NULLIF(pv.tinggi_badan, 0), pp.tinggiBadan) AS tinggiBadan,
    COALESCE(NULLIF(pv.berat_badan, 0), pp.beratBadan) AS beratBadan,
    COALESCE(NULLIF(pv.lingkar_perut, 0), pp.lingkarPerut) AS lingkarPerut,

    COALESCE(NULLIF(pv.sistole, 0), pp.sistole) AS sistole,
    COALESCE(NULLIF(pv.diastole, 0), pp.diastole) AS diastole,
    COALESCE(NULLIF(pv.suhu, 0), pp.suhu) AS suhu,
    COALESCE(NULLIF(pv.saturasi, 0), pp.saturasi) AS saturasi,

    COALESCE(NULLIF(pv.nadi, 0), pp.heartRate) AS heartRate,
    COALESCE(NULLIF(pv.respirasi, 0), pp.respRate) AS respRate,

    p.patient_nik,

    CONCAT(
        TIMESTAMPDIFF(
            YEAR,
            p.patient_datebirth,
            CURDATE()
        ),
        ' Tahun ',
        TIMESTAMPDIFF(
            MONTH,
            p.patient_datebirth,
            CURDATE()
        ) % 12,
        ' Bulan ',
        DATEDIFF(
            CURDATE(),
            DATE_ADD(
                DATE_ADD(
                    p.patient_datebirth,
                    INTERVAL TIMESTAMPDIFF(
                        YEAR,
                        p.patient_datebirth,
                        CURDATE()
                    ) YEAR
                ),
                INTERVAL (
                    TIMESTAMPDIFF(
                        MONTH,
                        p.patient_datebirth,
                        CURDATE()
                    ) % 12
                ) MONTH
            )
        ),
        ' Hari'
    ) AS umur

FROM pcare_pendaftaran AS pp

INNER JOIN pasien_visit AS pv 
    ON pp.nomor_visit = pv.visit_ID

INNER JOIN ms_patient AS p 
    ON p.id_patient = pv.id_patient

WHERE pp.nomor_visit = ?
  AND pv.id_customer = ?");
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
    $stmt = $koneksi->prepare("SELECT pv.*,p.patient_nik,p.patient_bpjs,p.patient_datebirth, CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur FROM pasien_visit pv INNER JOIN ms_patient p ON p.id_patient = pv.id_patient WHERE pv.id_customer = ? AND pv.visit_ID = ?");
    $stmt->bind_param('ss', $idcustomer, $nomor_visit);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_assoc();
    if (!$hasil) {
        // kunjungan tidak ditemukan untuk faskes ini (dulu tetap "success" dengan isi null + warning PHP)
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Kunjungan tidak ditemukan untuk faskes ini.", "data" => []]);
        exit;
    }
    $response = [
        "status" => "success",
        "pasienStatus" => "UMUM",
        "data" => [
            [
                "visit_ID" => ($hasil['visit_ID'] ?? null),
                "id_patient" => ($hasil['id_patient'] ?? null),
                "saturasi" => ($hasil['saturasi'] ?? null),
                "tindakan" => ($hasil['tindakan'] ?? null),
                "sistole" => ($hasil['sistole'] ?? null),
                "diastole" => ($hasil['diastole'] ?? null),
                "resp_rate" => ($hasil['respirasi'] ?? null),
                "heart_rate" => ($hasil['nadi'] ?? null),
                "suhu" => ($hasil['suhu'] ?? null),
                "berat_badan" => ($hasil['berat_badan'] ?? null),
                "tinggi_badan" => ($hasil['tinggi_badan'] ?? null),
                "lingkar_perut" => ($hasil['lingkar_perut'] ?? null),
                "tglDaftar" => ($hasil['visit_date'] ?? null),
                "nmPoli" => ($hasil['id_poli'] ?? null),
                "keluhan" => ($hasil['keluhan_penyerta'] ?? null),
                "anamnesa" => ($hasil['anamnesa'] ?? null),
                "kdDiag1" => ($hasil['kdDiag1'] ?? null),
                "kdDiag2" => ($hasil['kdDiag2'] ?? null),
                "kdDiag3" => ($hasil['kdDiag3'] ?? null),
                "nmDiag1" => ($hasil['nmDiag1'] ?? null),
                "nmDiag2" => ($hasil['nmDiag2'] ?? null),
                "nmDiag3" => ($hasil['nmDiag3'] ?? null),
                "kdDokter" => ($hasil['code_doctor'] ?? null),
                "nmDokter" => ($hasil['id_doctor'] ?? null),
                "kdStatusPulang" => ($hasil['status_pulang'] ?? null),
                "alergiMakan" => ($hasil['alergiMakan'] ?? null),
                "alergiUdara" => ($hasil['alergiUdara'] ?? null),
                "alergiObat" => ($hasil['alergiObat'] ?? null),
                "umur" => ($hasil['umur'] ?? null),
                "patient_nik" => ($hasil['patient_nik'] ?? null),
                "noKartu" => ($hasil['patient_bpjs'] ?? null),
                "deskripsiAlergiMakan" => ($hasil['desAlMakan'] ?? null),
                "deskripsiAlergiUdara" => ($hasil['desAlUdara'] ?? null),
                "deskripsiAlergiObat" => ($hasil['desAlObat'] ?? null)
            ]
        ]
    ];
    echo json_encode($response);
}
