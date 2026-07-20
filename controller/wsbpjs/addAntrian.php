<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
header('Content-Type: application/json');
$nomorkartu     = $_POST['noKartu'] ?? '';
$nik            = $_POST['noNik'] ?? '';
$nohp           = $_POST['noHp'] ?? '';
$kodepoli       = $_POST['kdPoli'] ?? '';
$namapoli       = $_POST['nmPoli'] ?? '';
$norm           = $_POST['norm'] ?? '';
$tanggalperiksa = $_POST['tglDaftar'] ?? '';
$kodedokter     = $_POST['kdDokter'] ?? '';
$namadokter     = $_POST['nmDokter'] ?? '';
$jampraktek     = $_POST['jampraktek'] ?? '';

if (empty($nohp)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nomor HP wajib diisi.'
    ]);
    exit;
}

$koneksi->begin_transaction();
try {
    $visit_ID = generateVisitID($koneksi, $idcustomer);
    $resultAntrian = createAntrian($koneksi, $kodepoli, $idcustomer, $visit_ID, $kodedokter, $tanggalperiksa, $jampraktek);
    $nomorantrean = $resultAntrian['display'];
    $angkaantrean = $resultAntrian['nomor'];
    $kodeAntri       = $resultAntrian['kode'];
    $payload = [
        "nomorkartu"      => $nomorkartu,
        "nik"             => $nik,
        "nohp"            => $nohp,
        "kodepoli"        => $kodepoli,
        "namapoli"        => $namapoli,
        "norm"            => $norm,
        "tanggalperiksa"  => $tanggalperiksa,
        "kodedokter"      => $kodedokter,
        "namadokter"      => $namadokter,
        "jampraktek"      => $jampraktek,
        "nomorantrean"    => $nomorantrean,
        "angkaantrean"    => $angkaantrean,
        "keterangan"      => ""
    ];
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    // $result = testingBPJS_POST("http://localhost/medisafe/controller/admisi/api/getantrian.php", $payload);
    if ($status_antrol) {
        $result = bpjsPost("/antrean/add", $payload);
    } else {
        $result = [
            "code"    => 200,
            "message" => "OK"
        ];
    }
    if ($result['code'] != '200') {
        $msg = $result['message'];
        if ($msg == null) {
            $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        }
        $response = [
            'success' => false,
            'message' => $msg,
            'result' => $result
        ];
        $koneksi->rollback();
    } else {
        $koneksi->commit();
        $response = [
            'success'  => true,
            'message'  => "Berhasil Mendaftar Pasien",
            'visitID' => $visit_ID,
            'antian' => $nomorantrean,
            'kdAntri' => $kodeAntri,
            'noAntrian' => $angkaantrean
        ];
    }
    echo json_encode($response);
} catch (Exception $e) {
    $koneksi->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek){
    $cekantrian = $koneksi->prepare("SELECT 
        COALESCE(MAX(a.nomor), 0) AS last,
        (
            SELECT d.doctor_antrean
            FROM ms_doctor d
            WHERE d.doctor_code = ?
            AND d.id_customer = ?
            LIMIT 1
        ) AS kode_antrian
    FROM antrian_poli a
    WHERE a.poli = ?
    AND a.tanggal = ?
    AND a.id_customer = ?
    AND a.kode_antri = (
        SELECT d.doctor_antrean
        FROM ms_doctor d
        WHERE d.doctor_code = ?
        AND d.id_customer = ?
        LIMIT 1
    )
    FOR UPDATE");
    $cekantrian->bind_param("sssssss", $kdDokter, $idcustomer, $kdPoli, $tglDaftarDB, $idcustomer, $kdDokter, $idcustomer);
    $cekantrian->execute();
    $rowantrian = $cekantrian->get_result()->fetch_assoc();
    $next = (int)$rowantrian['last'] + 1;
    $kode_antrian = $rowantrian['kode_antrian'];
    $createantrian = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit,id_dokter, kode_antri, jampraktek)VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $createantrian->bind_param("isssssss", $next, $kdPoli, $tglDaftarDB, $idcustomer, $visit_ID, $kdDokter, $kode_antrian, $jampraktek);
    $createantrian->execute();
    return [
        'nomor' => $next,
        'kode' => $kode_antrian,
        'display' => $kode_antrian . $next
    ];
}
function generateVisitID($koneksi, $idcustomer){
    do {
        $date = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $visitID = "VIS-" . $idcustomer . "-" . $date . "-" . $random;
        $count = '';
        $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
        $check->bind_param("s", $visitID);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
    } while ($count > 0);

    return $visitID;
}