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
if ($kunjSakit === false) {
    $typePatient = 'BPJS';
}

$nomorkartu     = $_POST['noKartu'] ?? '';
$nik            = $_POST['noNik'] ?? '';
$nohp           = $_POST['noHp'] ?? '';
$kodepoli       = $_POST['kdPoli'] ?? '';
$namapoli       = $_POST['nmPoli'] ?? '';
$norm           = $_POST['norm'] ?? '';
$tanggalperiksa = $_POST['tglDaftar'] ?? '';
$kodedokter     = $_POST['kdDokter'] ?? null;
$namadokter     = $_POST['nmDokter'] ?? null;
$namadokterPcare = $_POST['nmDokterPcare'] ?? null;
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

$dt_visit = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
$visit_time = $dt_visit->format('H:i:s');

$tglDaftarFormat = date("d-m-Y", strtotime($tanggalperiksa));

if (empty($nohp) && $typePatient == 'BPJS') {
    echo json_encode(['success' => false, 'message' => 'Nomor HP wajib diisi.']);
    exit;
}
if (empty($kodedokter) && $kunjSakit) {
    echo json_encode(['success' => false, 'message' => 'Dokter harus diisi']);
    exit;
}
if (empty($idcustomer)) {
    echo json_encode(['success' => false, 'message' => 'ID Customer wajib diisi / ditemukan.']);
    exit;
}
if (empty($kdProv)) {
    echo json_encode(['success' => false, 'message' => 'Kode Provider (kdProv) wajib diisi.']);
    exit;
}
if (!isset($_POST['kunjSakit'])) {
    echo json_encode(['success' => false, 'message' => 'Status Kunjungan (kunjSakit) wajib dikirim.']);
    exit;
}

// --- validasi tambahan (dulu tidak dicek) ---
if (empty($kodepoli) || empty($namapoli)) {
    echo json_encode(['success' => false, 'message' => 'Poli tujuan wajib dipilih.']);
    exit;
}
// if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggalperiksa) || $tanggalperiksa > date('Y-m-d') || $tanggalperiksa < date('Y-m-d', strtotime('-7 days'))) {
//     echo json_encode(['success' => false, 'message' => 'Tanggal kunjungan harus hari ini atau s.d. 7 hari ke belakang.']);
//     exit;
// }
// tanda vital opsional; bila diisi harus dalam rentang wajar
$vitalRentang = ['sistole' => ['Sistole', 50, 300], 'diastole' => ['Diastole', 20, 200], 'heartRate' => ['Nadi', 20, 250], 'respRate' => ['Respirasi', 4, 80],
    'suhu' => ['Suhu', 30, 45], 'saturasiOksigen' => ['SpO2', 50, 100], 'tinggiBadan' => ['Tinggi badan', 30, 250], 'beratBadan' => ['Berat badan', 0.5, 350],
    'lingkarPerut' => ['Lingkar perut', 20, 250]];
$vitalSalah = [];
foreach ($vitalRentang as $k => [$nm, $min, $max]) {
    $v = str_replace(',', '.', trim((string) ($_POST[$k] ?? '')));
    if ($v !== '' && (!is_numeric($v) || (float) $v < $min || (float) $v > $max)) $vitalSalah[] = $nm;
}
if ($vitalSalah) {
    echo json_encode(['success' => false, 'message' => 'Nilai tanda vital tidak wajar: ' . implode(', ', $vitalSalah) . '.']);
    exit;
}
// pasien wajib milik faskes ini
$cekPasien = $koneksi->prepare("SELECT id_patient FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1");
$idPasienInt = (int) $id_patient;
$idcInt = (int) $idcustomer;
$cekPasien->bind_param("ii", $idPasienInt, $idcInt);
$cekPasien->execute();
$adaPasien = (bool) $cekPasien->get_result()->fetch_row();
$cekPasien->close();
if (!$adaPasien) {
    echo json_encode(['success' => false, 'message' => 'Data pasien tidak ditemukan di faskes ini. Pilih ulang pasien.']);
    exit;
}
// cegah daftar dobel (mis. tombol simpan ditekan dua kali): pasien sama, tanggal sama, poli sama, belum batal
if (($_POST['paksa'] ?? '') !== '1') {
    $cekDobel = $koneksi->prepare("SELECT visit_ID, visit_antrian, id_doctor FROM pasien_visit WHERE id_patient = ? AND id_customer = ? AND visit_date = ? AND id_poli = ? AND visit_status <> 99 LIMIT 1");
    $idPasienStr = (string) $idPasienInt;
    $cekDobel->bind_param("siss", $idPasienStr, $idcInt, $tanggalperiksa, $namapoli);
    $cekDobel->execute();
    $dobel = $cekDobel->get_result()->fetch_assoc();
    $cekDobel->close();
    if ($dobel) {
        echo json_encode(['success' => false, 'dobel' => true, 'message' => "Pasien sudah terdaftar di $namapoli pada tanggal ini (antrian " . ($dobel['visit_antrian'] ?: '-') . ", " . ($dobel['id_doctor'] ?: '-') . ")."]);
        exit;
    }
}
// nilai untuk penyimpanan lokal: kosong / 0 = tidak diisi (dulu tersimpan "0/0" sehingga terbaca sudah diperiksa)
$lokal = function ($v) {
    $v = str_replace(',', '.', trim((string) $v));
    return ($v === '' || !is_numeric($v) || (float) $v <= 0) ? null : (string) (0 + $v);
};
$tdLokal = ($sistole > 0 && $diastole > 0) ? $sistole . "/" . $diastole : null;
$sistoleLokal = $lokal($sistole);
$diastoleLokal = $lokal($diastole);
$nadiLokal = $lokal($heartRate);
$respLokal = $lokal($respRate);
$tbLokal = $lokal($tinggiBadan);
$bbLokal = $lokal($beratBadan);
$lpLokal = $lokal($lingkarPerut);
$suhuLokal = $lokal($suhu);
$satLokal = $lokal($saturasi);
$bmiLokal = $lokal($bmi);
$bmiKetLokal = $bmiLokal === null ? null : ($bmiKet !== '' ? $bmiKet : null);

$safe_idcustomer = (int)$idcustomer; 
$safe_kdProv     = (int)$kdProv;
$safe_kunjSakit  = $kunjSakit ? "1" : "0";
$koneksi->begin_transaction();
try {
    if ($typePatient != 'BPJS') {
        $visit_ID = generateVisitID($koneksi, $idcustomer);
        $resultAntrian = createAntrian($koneksi, $kodepoli, $idcustomer, $visit_ID, $kodedokter, $tanggalperiksa, $jampraktek);
        $nomorantrean = $resultAntrian['display'];
        $created_user = "Onsite";
        $source_hub = "Poliklinik";
        $status_antrian = 0;
        // keluhan -> anamnesa (dulu keluhan_penyerta, sehingga keluhan pasien umum tidak tampil di vital sign / detail)
        $stmt = $koneksi->prepare("INSERT INTO pasien_visit (id_patient, visit_ID, visit_date, id_poli, source_hub, created_user, visit_antrian, status_antrian, id_customer, id_doctor, visit_time, anamnesa, tekanan_darah, sistole, diastole, nadi, respirasi, tinggi_badan, berat_badan, lingkar_perut, patient_name_pcare, suhu, saturasi, bmi, bmi_keterangan, code_doctor, id_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssssssssssssssssss", $id_patient, $visit_ID, $tanggalperiksa, $namapoli, $source_hub, $created_user, $nomorantrean, $status_antrian, $safe_idcustomer, $namadokter, $visit_time, $keluhan, $tdLokal, $sistoleLokal, $diastoleLokal, $nadiLokal, $respLokal, $tbLokal, $bbLokal, $lpLokal, $nama, $suhuLokal, $satLokal, $bmiLokal, $bmiKetLokal, $kodedokter, $safe_kdProv);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data kunjungan lokal UMUM: " . $stmt->error);
        }
        $koneksi->commit();
        echo json_encode(['success' => true, 'message' => 'Berhasil Mendaftar Pasien UMUM', 'type' => 'UMUM', 'visitID' => $visit_ID, 'antian' => $nomorantrean]);
        exit;
    }

    $visit_ID = generateVisitID($koneksi, $idcustomer);
    $resultAntrian = createAntrian($koneksi, $kodepoli, $idcustomer, $visit_ID, $kodedokter, $tanggalperiksa, $jampraktek);
    $nomorantrean = $resultAntrian['display'];
    $angkaantrean = $resultAntrian['nomor'];
    $kodeAntri    = $resultAntrian['kode'];
    global $status_antrol;
    
    if ($status_antrol && $kunjSakit) {
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
            }
        } catch (Exception $exBatal) {}
        throw new Exception($errorMsg . $statusBatalText);
    }

    $bpjs_berhasil = true;

    $noUrut = (string)$resPendaftaran['data']['message'];
    $created_user = $kunjSakit ? "JKNOnsite" : "JKNSehat";
    $antrianBPJS = $kunjSakit ? $nomorantrean : $noUrut;
    
    $stmtPCare = $koneksi->prepare("INSERT INTO `pcare_pendaftaran` (`tanggal_daftar`, `noKartu`, `kdPoli`, `nmPoli`, `keluhan`, `kunjSakit`, `sistole`, `diastole`, `beratBadan`, `tinggiBadan`, `respRate`, `lingkarPerut`, `heartRate`, `rujukBalik`, `kdTkp`, `noUrut`, `nomor_visit`, `saturasi`, `suhu`, `jampraktek`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmtPCare->bind_param("ssssssssssssssssssss", $tanggalperiksa, $nomorkartu, $kodepoli, $namapoli, $keluhan, $safe_kunjSakit, $sistole, $diastole, $beratBadan, $tinggiBadan, $respRate, $lingkarPerut, $heartRate, $rujukbalik, $kdTkp, $noUrut, $visit_ID, $saturasi, $suhu, $jampraktek);
    if (!$stmtPCare->execute()) throw new Exception("Gagal simpan pcare_pendaftaran: " . $stmtPCare->error);
    $stmtPCare->close();

    $source_hub = "Poliklinik";
    $status_antrian = 0;
    
    $stmtVisit = $koneksi->prepare("INSERT INTO pasien_visit (id_patient, visit_ID, visit_date, id_poli, source_hub, created_user, visit_antrian, status_antrian, id_customer, id_doctor, noKartu, visit_time, anamnesa, tekanan_darah, sistole, diastole, nadi, respirasi, tinggi_badan, berat_badan, lingkar_perut, patient_name_pcare, suhu, saturasi, bmi, bmi_keterangan, code_doctor, id_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // $safe_idcustomer dan $safe_kdProv dimasukkan agar kolom INT tidak dimasuki string ""
    $stmtVisit->bind_param("ssssssssssssssssssssssssssss", $id_patient, $visit_ID, $tanggalperiksa, $namapoli, $source_hub, $created_user, $antrianBPJS, $status_antrian, $safe_idcustomer, $namadokter, $nomorkartu, $visit_time, $keluhan, $tdLokal, $sistoleLokal, $diastoleLokal, $nadiLokal, $respLokal, $tbLokal, $bbLokal, $lpLokal, $nama, $suhuLokal, $satLokal, $bmiLokal, $bmiKetLokal, $kodedokter, $safe_kdProv);
    if (!$stmtVisit->execute()) throw new Exception("Gagal simpan pasien_visit: " . $stmtVisit->error);
    $stmtVisit->close();

    $stmtUpdate = $koneksi->prepare("UPDATE ms_patient SET Sprolanis = ?, SPRB = ? WHERE id_patient = ?");
    $stmtUpdate->bind_param("sss", $SProlanis, $SPRB, $id_patient);
    if (!$stmtUpdate->execute()) throw new Exception("Gagal update ms_patient: " . $stmtUpdate->error);
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
    // DB Lokal Gagal -> Rollback MySQL
    $koneksi->rollback();
    $errorMessage = $e->getMessage();

    // AUTO-KOMPENSASI BPJS:
    // Jika API pendaftaran BPJS sudah keburu masuk, tapi MySQL lokal gagal, batalkan di server BPJS!
    if (isset($bpjs_berhasil) && $bpjs_berhasil === true) {
        $payloadBatalKompensasi = [
            "tanggalperiksa" => $tanggalperiksa,
            "kodepoli"       => $kodepoli,
            "nomorkartu"     => $nomorkartu,
            "alasan"         => "DB Lokal Error: " . substr($errorMessage, 0, 45)
        ];
        try {
            antrolPost("/antrean/batal", $payloadBatalKompensasi);
        } catch (Exception $ex) {}
    }

    echo json_encode([
        'success' => false,
        'message' => $errorMessage // SEKARANG AKAN MUNCUL PESAN MYSQL YANG ASLI: misal "Column cannot be null" dll.
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