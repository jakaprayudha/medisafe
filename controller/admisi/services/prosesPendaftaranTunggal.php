<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../wsbpjs/serviceantrian.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$typePatient    = $_POST['typePatient'] ?? '';
$kunjSakit      = (isset($_POST['kunjSakit']) && $_POST['kunjSakit'] === 'true') ? true : false;

$nomorkartu     = $_POST['noKartu'] ?? '';
$nik            = $_POST['noNik'] ?? '';
$nohp           = $_POST['noHp'] ?? '';
$kodepoli       = $_POST['kdPoli'] ?? '';
$namapoli       = $_POST['nmPoli'] ?? '';
$norm           = $_POST['norm'] ?? '';
$tanggalperiksa = $_POST['tglDaftar'] ?? '';
$kodedokter     = $_POST['kdDokter'] ?? null;
$namadokter     = $_POST['nmDokter'] ?? null;
$namadokterPcare     = $_POST['nmDokterPcare'] ?? null;
$jampraktek     = $_POST['jampraktek'] ?? '';
$id_patient     = $_POST['id_patient'] ?? '';
$nama           = $_POST['nama'] ?? '';

$kdProviderPeserta = $_POST['kdProviderPeserta'] ?? '';
$keluhan        = !empty($_POST['keluhan']) ? $_POST['keluhan'] : null;
$sistole        = (int)($_POST['sistole'] ?? 0);
$diastole       = (int)($_POST['diastole'] ?? 0);
$beratBadan     = (int)($_POST['beratBadan'] ?? 0);
$tinggiBadan    = (int)($_POST['tinggiBadan'] ?? 0);
$respRate       = (int)($_POST['respRate'] ?? 0);
$lingkarPerut   = (int)($_POST['lingkarPerut'] ?? 0);
$heartRate      = (int)($_POST['heartRate'] ?? 0);
$kdTkp          = $_POST['kdTkp'] ?? '';
$suhu           = $_POST['suhu'] ?? '';
$saturasi       = $_POST['saturasiOksigen'] ?? '';
$kdProv         = $kunjSakit ? ($_POST['kdProv'] ?? '') : '1';
$SProlanis      = $_POST['SProlanis'] ?? '';
$SPRB           = $_POST['SPRB'] ?? '';
$bmi            = $_POST['bmi'] ?? '';
$bmiKet         = $_POST['bmiKet'] ?? '';
$rujukbalik     = 0;

$tglDaftarFormat = date("d-m-Y", strtotime($tanggalperiksa));

if (empty($nohp) && $typePatient == 'BPJS') {
    echo json_encode(['success' => false, 'message' => 'Nomor HP wajib diisi.']);
    exit;
}
if (empty($kodedokter) && $kunjSakit) {
    echo json_encode(['success' => false, 'message' => 'Dokter harus diisi']);
    exit;
}

$koneksi->begin_transaction();
try {
    if ($typePatient != 'BPJS') {
        $visit_ID = generateVisitID($koneksi, $idcustomer);
        $resultAntrian = createAntrian($koneksi, $kodepoli, $idcustomer, $visit_ID, $kodedokter, $tanggalperiksa, $jampraktek);

        $nomorantrean = $resultAntrian['display'];
        $created_user = "Onsite";
        $source_hub = "Poliklinik";
        $visit_time = date('H:i:s');
        $status_antrian = 0;
        $td = $sistole . "/" . $diastole;
        $stmt = $koneksi->prepare("INSERT INTO pasien_visit (id_patient, visit_ID, visit_date, id_poli, source_hub, created_user, visit_antrian, status_antrian, id_customer, id_doctor, visit_time, keluhan_penyerta, tekanan_darah, nadi, respirasi, tinggi_badan, berat_badan, patient_name_pcare, suhu, saturasi, bmi, bmi_keterangan, code_doctor, id_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssssssssss", $id_patient, $visit_ID, $tanggalperiksa, $namapoli, $source_hub, $created_user, $nomorantrean, $status_antrian, $idcustomer, $namadokter, $visit_time, $keluhan, $td, $heartRate, $respRate, $tinggiBadan, $beratBadan, $nama, $suhu, $saturasi, $bmi, $bmiKet, $kodedokter, $kdProv);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data kunjungan lokal.");
        }
        $koneksi->commit();
        echo json_encode(['success' => true, 'message' => 'Berhasil Mendaftar Pasien UMUM', 'type' => 'UMUM']);
        exit;
    }

    $visit_ID = generateVisitID($koneksi, $idcustomer);
    $resultAntrian = createAntrian($koneksi, $kodepoli, $idcustomer, $visit_ID, $kodedokter, $tanggalperiksa, $jampraktek);
    $nomorantrean = $resultAntrian['display'];
    $angkaantrean = $resultAntrian['nomor'];
    $kodeAntri    = $resultAntrian['kode'];
    global $status_antrol;
    if ($status_antrol) {
        $payloadAntrean = [
            "nomorkartu" => $nomorkartu,
            "nik" => $nik,
            "nohp" => $nohp,
            "kodepoli" => $kodepoli,
            "namapoli" => $namapoli,
            "norm" => $norm,
            "tanggalperiksa"  => $tanggalperiksa,
            "kodedokter" => $kodedokter,
            "namadokter" => $namadokterPcare,
            "jampraktek" => $jampraktek,
            "nomorantrean"    => $nomorantrean,
            "angkaantrean" => $angkaantrean,
            "keterangan" => ""
        ];
        $resAntrean = antrolPost("/antrean/add", $payloadAntrean);
        if ($resAntrean['code'] != '200') {
            throw new Exception($resAntrean['message'] ?? "Gagal mengambil antrean BPJS.");
        }
    }
    $payloadPendaftaran = [
        "kdProviderPeserta" => $kdProviderPeserta,
        "tglDaftar" => $tglDaftarFormat,
        "noKartu" => $nomorkartu,
        "kdPoli" => $kodepoli,
        "keluhan" => $keluhan,
        "kunjSakit" => $kunjSakit,
        "sistole" => $sistole,
        "diastole" => $diastole,
        "beratBadan" => $beratBadan,
        "tinggiBadan" => $tinggiBadan,
        "respRate" => $respRate,
        "lingkarPerut" => $lingkarPerut,
        "heartRate" => $heartRate,
        "rujukBalik" => $rujukbalik,
        "kdTkp" => $kdTkp
    ];
    $resPendaftaran = bpjsPost("/pendaftaran", $payloadPendaftaran);
    if ($resPendaftaran['code'] != '200') {
        $errorMsg = $resPendaftaran['metadata'] ?? "Gagal pendaftaran BPJS.";
        $payloadBatal = [
            "tanggalperiksa" => $tanggalperiksa,
            "kodepoli"       => $kodepoli,
            "nomorkartu"     => $nomorkartu,
            "alasan"         => "Dibatalkan sistem (Pendaftaran gagal: " . $errorMsg . ")"
        ];

        $statusBatalText = "";
        try {
            $resBatal = antrolPost("/antrean/batal", $payloadBatal);
            if (isset($resBatal['code']) && $resBatal['code'] == '200') {
                $statusBatalText = " [Antrean BPJS berhasil dibatalkan otomatis].";
            } else {
                $batalMsg = $resBatal['message'] ?? 'Gagal menghubungi server';
                $statusBatalText = " [PERHATIAN: Antrean BPJS gagal dibatalkan otomatis: " . $batalMsg . "].";
            }
        } catch (Exception $exBatal) {
            $statusBatalText = " [PERHATIAN: Error exception saat batal antrean: " . $exBatal->getMessage() . "].";
        }
        throw new Exception($errorMsg . $statusBatalText);
    }
    $noUrut = (string)$resPendaftaran['data']['message'];
    $created_user = $kunjSakit ? "JKNOnsite" : "JKNSehat";
    $antrianBPJS = $kunjSakit ? $nomorantrean : $noUrut;
    $stmtPCare = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`, `saturasi`, `suhu`, `jampraktek`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmtPCare->bind_param("ssssssiiiiiiisssssss", $tanggalperiksa, $nomorkartu, $kodepoli, $namapoli, $keluhan, $kunjSakit, $sistole, $diastole, $beratBadan, $tinggiBadan, $respRate, $lingkarPerut, $heartRate, $rujukbalik, $kdTkp, $noUrut, $visit_ID, $saturasi, $suhu, $jampraktek);
    if (!$stmtPCare->execute()) throw new Exception("Gagal simpan pcare_pendaftaran.");
    $stmtPCare->close();
    $source_hub = "Poliklinik";
    $visit_time = date('H:i:s');
    $td = $sistole . "/" . $diastole;
    $status_antrian = 0;
    $stmtVisit = $koneksi->prepare("INSERT INTO pasien_visit (id_patient, visit_ID, visit_date, id_poli, source_hub, created_user, visit_antrian, status_antrian, id_customer, id_doctor, noKartu, visit_time, anamnesa, tekanan_darah, nadi, respirasi, tinggi_badan, berat_badan, patient_name_pcare, suhu, saturasi, bmi, bmi_keterangan, code_doctor, id_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtVisit->bind_param("sssssssssssssssssssssssss", $id_patient, $visit_ID, $tanggalperiksa, $namapoli, $source_hub, $created_user, $antrianBPJS, $status_antrian, $idcustomer, $namadokter, $nomorkartu, $visit_time, $keluhan, $td, $heartRate, $respRate, $tinggiBadan, $beratBadan, $nama, $suhu, $saturasi, $bmi, $bmiKet, $kodedokter, $kdProv);
    if (!$stmtVisit->execute()) throw new Exception("Gagal simpan pasien_visit.");
    $stmtVisit->close();
    $stmtUpdate = $koneksi->prepare("UPDATE ms_patient SET Sprolanis = ?, SPRB = ? WHERE id_patient = ?");
    $stmtUpdate->bind_param("sss", $SProlanis, $SPRB, $id_patient);
    if (!$stmtUpdate->execute()) throw new Exception("Gagal update ms_patient.");
    $stmtUpdate->close();
    $koneksi->commit();
    echo json_encode([
        'success'  => true,
        'message'  => "Berhasil Mendaftar Pasien BPJS",
        'visitID'  => $visit_ID,
        'antian'   => $nomorantrean,
        'type'     => "BPJS"
    ]);
} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generateVisitID($koneksi, $idcustomer)
{
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

function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek)
{
    $cekantrian = $koneksi->prepare("SELECT COALESCE(MAX(a.nomor), 0) AS last, (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) AS kode_antrian FROM antrian_poli a WHERE a.poli = ? AND a.tanggal = ? AND a.id_customer = ? AND a.kode_antri = (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) FOR UPDATE");
    $cekantrian->bind_param("sssssss", $kdDokter, $idcustomer, $kdPoli, $tglDaftarDB, $idcustomer, $kdDokter, $idcustomer);
    $cekantrian->execute();
    $rowantrian = $cekantrian->get_result()->fetch_assoc();
    $next = (int)$rowantrian['last'] + 1;
    $kode_antrian = $rowantrian['kode_antrian'];
    $createantrian = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit,id_dokter, kode_antri, jampraktek)VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $createantrian->bind_param("isssssss", $next, $kdPoli, $tglDaftarDB, $idcustomer, $visit_ID, $kdDokter, $kode_antrian, $jampraktek);
    $createantrian->execute();
    return ['nomor' => $next, 'kode' => $kode_antrian, 'display' => $kode_antrian . $next];
}
