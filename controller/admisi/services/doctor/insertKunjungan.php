<?php
require_once __DIR__ . '/../view.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../servicebpjs.php';
header('Content-Type: application/json');

// rawat jalan
$noKunjungan = $_POST['noKunjungan'] ?? null;
$noKartu = $_POST['noKartu'];
$DBtglDatar = $_POST['tglDaftar'];
// $DBtglEstRujuk = $_POST['tglRujukan'] ?? NULL;
$DBtglEstRujuk = !empty($_POST['tglRujukan']) ? $_POST['tglRujukan'] : NULL;
$DBtglPulang = $_POST['tglDaftar'];
$tglDaftar = date("d-m-Y", strtotime($DBtglDatar));
$tglEstRujuk = date("d-m-Y", strtotime($DBtglEstRujuk));
$tglPulang = date("d-m-Y", strtotime($DBtglPulang));
$kdPoli = $_POST['kdPoli'];
$nmPoli = $_POST['nmPoli'];
$keluhan = $_POST['keluhan_penyerta'];
$kdSadar = $_POST['kdSadar'];
$sistole = (int) $_POST['sistole'];
$diastole = (int) $_POST['diastole'];
$tekanandarah = $sistole . '/' . $diastole;
$beratBadan = (int) $_POST['beratBadan'];
$tinggiBadan = (int) $_POST['tinggiBadan'];
$respRate = (int) $_POST['respRate'];
$lingkarPerut = (int) $_POST['lingkarPerut'];
$heartRate = (int) $_POST['heartRate'];
$kdStatusPulang = $_POST['kdStatusPulang'];
$kdDokter = $_POST['kdDokter'];
$nmDokter = $_POST['nmDokter'] ?? null;
$kdPoliRujukInternal = $_POST['kdPoliRujukInternal'] ?? null;
$kdppk = $_POST['kdppk'] ?? null;
$kdSubSpesialis1 = $_POST['kdSubSpesialis1'] ?? null;
// $kdspesialiskhusus = $_POST['kdSubSpesialiskhusus'] ?? null;
$kdSarana = $_POST['kdSarana'] ?? null;
$kdkategori = $_POST['kdKategori'] ?? null;
$kdTacc = $_POST['kdTacc'] ?? '0';
$alasanTacc = $_POST['alasanTacc'] ?? null;
$anamnesa = $_POST['keluhan_utama'];
$alergiMakan = $_POST['alergiMakan'];
$alergiUdara = $_POST['alergiUdara'];
$alergiObat = $_POST['alergiObat'];
$kdPrognosa = $_POST['kondisi_masuk'];
$terapiObat = $_POST['terapiObat'];
$terapiNonObat = $_POST['terapiNonObat'];
$bmhp = $_POST['bmhp'];
$suhu = $_POST['suhu'];
$diag1 = $_POST['diag1'] ?? null;
$diag2 = $_POST['diag2'] ?? null;
$diag3 = $_POST['diag3'] ?? null;
$diagnosa_sekunder = $diag2 . ',' . $diag3;
$nmdiag1 = $_POST['nmDiag1'] ?? null;
$nmdiag2 = $_POST['nmDiag2'] ?? null;
$nmdiag3 = $_POST['nmDiag3'] ?? null;
$catatan = $_POST['catatan'] ?? null;
$nomorLP = $_POST['nomorLp'] ?? null;
$typeRujukan = $_POST['typeRujukan'];
$bmi = $_POST['bmi'] ?? null;
$bmi_keterangan = $_POST['bmi_keterangan'] ?? null;
$riwayat_alergi = $_POST['riwayat_alergi'] ?? null;
$riwayat_penyakit_pribadi = $_POST['riwayat_penyakit_pribadi'] ?? null;
$riwayat_penyakit_sekarang = $_POST['riwayat_penyakit_sekarang'] ?? null;
$riwayat_pengobatan = $_POST['riwayat_pengobatan'] ?? null;
$tindakan = $_POST['tindakan'] ?? null;
$edukasi = $_POST['edukasi'] ?? null;
$saturasi = $_POST['saturasi'] ?? null;
$nomor_visit = $_POST['nomor_visit'];
$id_patient = $_POST['id_patient'];
$payload = [
    "noKunjungan" => $noKunjungan,
    "noKartu" => $noKartu,
    "tglDaftar" => $tglDaftar,
    "kdPoli" => $kdPoli,
    "keluhan" => $keluhan,
    "kdSadar" => $kdSadar,
    "sistole" => $sistole,
    "diastole" => $diastole,
    "beratBadan" => $beratBadan,
    "tinggiBadan" => $tinggiBadan,
    "respRate" => $respRate,
    "heartRate" => $heartRate,
    "lingkarPerut" => $lingkarPerut,
    "kdStatusPulang" => $kdStatusPulang,
    "tglPulang" => $tglPulang,
    "kdDokter" => $kdDokter,
    "kdDiag1" => $diag1,
    "kdDiag2" => $diag2,
    "kdDiag3" => $diag3,
    "kdPoliRujukInternal" => $kdPoliRujukInternal,
    "rujukLanjut" => null,
    "kdTacc" => $kdTacc,
    "alasanTacc" => $alasanTacc,
    "anamnesa" => $anamnesa,
    "alergiMakan" => $alergiMakan,
    "alergiUdara" => $alergiUdara,
    "alergiObat" => $alergiObat,
    "kdPrognosa" => $kdPrognosa,
    "terapiObat" => $terapiObat,
    "terapiNonObat" => $terapiNonObat,
    "bmhp" => $bmhp,
    "suhu" => $suhu
];
switch ($typeRujukan) {
    case 'normal':
        $payload['rujukLanjut'] = null;
        break;
    case 'spesialis':
        $payload['rujukLanjut'] = [
            "kdppk" => $kdppk,
            "tglEstRujuk" => $tglEstRujuk,
            "subSpesialis" => [
                "kdSubSpesialis1" => $kdSubSpesialis1,
                "kdSarana" => $kdSarana
            ],
            "khusus" => null
        ];
        break;
    case 'khusus':
        $payload['rujukLanjut'] = [
            "kdppk" => $kdppk,
            "tglEstRujuk" => $tglEstRujuk,
            "subSpesialis" => null,
            "khusus" => [
                "kdKhusus" => $kdkategori,
                "kdSubSpesialis" => null,
                "catatan" => $catatan
            ]
        ];
        break;
}
if ($typeRujukan == 'spesialis') {
    $kdspesialiskhusus = "";
} else if ($typeRujukan == 'khusus') {
    $kdspesialiskhusus = $kdkategori;
    $kdkategori = null;
}
$method = "POST";
if ($noKunjungan != null) {
    $method = "PUT";
}
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/kunjungan", $payload, $method);
// $result = testingBPJS_POST("https://app.medisafe.id/controller/admisi/api/getpeserta.php", $payload);
if ($result['code'] != "200") {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    if ($method == "POST") {
        $message = 'Berhasil Membuat Kunjungan';
        $noKunjungan = $result['data'][0]['message'];
        $stmt = $koneksi->prepare("INSERT INTO pcare_kunjungan (
            noKunjungan, noKartu, tglDaftar, kdPoli,nmPoli, keluhan, kdSadar,
            sistole, diastole, beratBadan, tinggiBadan, respRate, heartRate,
            lingkarPerut, kdStatusPulang, tglPulang, kdDokter,nmDokter,
            kdDiag1, kdDiag2, kdDiag3, nmDiag1, nmDiag2, nmDiag3, kdPoliRujukInternal,
            tglEstRujuk, kdppk, subSpesialis, kdsarana, kdKhusus, kdkhSpesialis,
            catatan, kdTacc, alasanTacc, anamnesa,
            alergiMakan, alergiUdara, alergiObat,
            kdPrognosa, terapiObat, terapiNonObat,
            bmhp, suhu, noLP
            ) VALUES (
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,
            ?,?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssssssssssssssss",
            $noKunjungan,
            $noKartu,
            $DBtglDatar,
            $kdPoli,
            $nmPoli,
            $keluhan,
            $kdSadar,
            $sistole,
            $diastole,
            $beratBadan,
            $tinggiBadan,
            $respRate,
            $heartRate,
            $lingkarPerut,
            $kdStatusPulang,
            $DBtglPulang,
            $kdDokter,
            $nmDokter,
            $diag1,
            $diag2,
            $diag3,
            $nmdiag1,
            $nmdiag2,
            $nmdiag3,
            $kdPoliRujukInternal,
            $DBtglEstRujuk,
            $kdppk,
            $kdSubSpesialis1,
            $kdSarana,
            $kdkategori,
            $kdspesialiskhusus,
            $catatan,
            $kdTacc,
            $alasanTacc,
            $anamnesa,
            $alergiMakan,
            $alergiUdara,
            $alergiObat,
            $kdPrognosa,
            $terapiObat,
            $terapiNonObat,
            $bmhp,
            $suhu,
            $nomorLP
        );
        $stmt1 = $koneksi->prepare("UPDATE pasien_visit SET
                kondisi_masuk = ?,
                tekanan_darah = ?,
                suhu = ?,
                nadi = ?,
                respirasi = ?,
                tinggi_badan = ?,
                berat_badan = ?,
                bmi = ?,
                bmi_keterangan = ?,

                anamnesa = ?,
                keluhan_penyerta = ?,
                riwayat_alergi = ?,
                riwayat_penyakit_pribadi = ?,
                riwayat_penyakit_sekarang = ?,
                riwayat_pengobatan = ?,
                diagnosa = ?,
                tindakan = ?,
                edukasi = ?,
                visit_out = ?, 
                kondisi_keluar = ?,
                saturasi = ?,
                diagnosa_sekunder = ?

            WHERE visit_ID = ? AND id_patient = ?
        ");

        $stmt1->bind_param(
            "ssssssssssssssssssssssss",
            $kdPrognosa,
            $tekanandarah,
            $suhu,
            $heartRate,
            $respRate,
            $tinggiBadan,
            $berat,
            $bmi,
            $bmi_keterangan,
            $anamnesa, // masuk ke anamnesa
            $keluhan,
            $riwayat_alergi,
            $riwayat_penyakit_pribadi,
            $riwayat_penyakit_sekarang,
            $riwayat_pengobatan,
            $diag1,
            $tindakan,
            $edukasi,
            $kdStatusPulang,
            $kdStatusPulang,
            $saturasi,
            $diagnosa_sekunder,
            $nomor_visit,
            $id_patient
        );
    } else {
        $message = "Berhasil Update Kunjungan";
        $stmt = $koneksi->prepare("UPDATE pcare_kunjungan SET
            noKartu = ?,
            tglDaftar = ?,
            kdPoli = ?,
            nmPoli = ?,
            keluhan = ?,
            kdSadar = ?,
            sistole = ?,
            diastole = ?,
            beratBadan = ?,
            tinggiBadan = ?,
            respRate = ?,
            heartRate = ?,
            lingkarPerut = ?,
            kdStatusPulang = ?,
            tglPulang = ?,
            kdDokter = ?,
            nmDokter = ?,
            kdDiag1 = ?,
            kdDiag2 = ?,
            kdDiag3 = ?,
            nmDiag1 = ?,
            nmDiag2 = ?,
            nmDiag3 = ?,
            kdPoliRujukInternal = ?,
            tglEstRujuk = ?,
            kdppk = ?,
            subSpesialis = ?,
            kdsarana = ?,
            kdKhusus = ?,
            kdkhSpesialis = ?,
            catatan = ?,
            kdTacc = ?,
            alasanTacc = ?,
            anamnesa = ?,
            alergiMakan = ?,
            alergiUdara = ?,
            alergiObat = ?,
            kdPrognosa = ?,
            terapiObat = ?,
            terapiNonObat = ?,
            bmhp = ?,
            suhu = ?,
            noLP = ?
        WHERE noKunjungan = ?
        ");
        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssssssssssssssss",
            $noKartu,
            $DBtglDatar,
            $kdPoli,
            $nmPoli,
            $keluhan,
            $kdSadar,
            $sistole,
            $diastole,
            $beratBadan,
            $tinggiBadan,
            $respRate,
            $heartRate,
            $lingkarPerut,
            $kdStatusPulang,
            $DBtglPulang,
            $kdDokter,
            $nmDokter,
            $diag1,
            $diag2,
            $diag3,
            $nmdiag1,
            $nmdiag2,
            $nmdiag3,
            $kdPoliRujukInternal,
            $DBtglEstRujuk,
            $kdppk,
            $kdSubSpesialis1,
            $kdSarana,
            $kdkategori,
            $kdspesialiskhusus,
            $catatan,
            $kdTacc,
            $alasanTacc,
            $anamnesa,
            $alergiMakan,
            $alergiUdara,
            $alergiObat,
            $kdPrognosa,
            $terapiObat,
            $terapiNonObat,
            $bmhp,
            $suhu,
            $nomorLP,
            $noKunjungan
        );
    }
    $simpan = $stmt->execute();
    $simpan1 = $stmt1->execute();
    $stmt->close();
    $stmt1->close();
    if ($simpan and $simpan1) {
        $response = [
            'success'  => true,
            'message'  => $message,
            'result' => $result,
            'noKunjung' => $noKunjungan
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal " . mysqli_error($koneksi),
        ];
    }
}
echo json_encode($response);
