<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';

$id_customer = $_SESSION['id_customer'] ?? null;
$no          = $_GET['no']  ?? null;
$rm          = $_GET['rm']  ?? null;

/*
 * Helper untuk sanitasi string HTML dan mencegah error 'Passing null to parameter #1'
 */
if (!function_exists('val')) {
    function val(?string $str): string
    {
        $str = trim((string)$str);
        return $str === '' ? '-' : htmlspecialchars($str, ENT_QUOTES);
    }
}

// ============================================================
// QUERY DATA PASIEN + RUANGAN + ICD (Prepared Statements)
// ============================================================
$data = null;

if ($no && $id_customer) {
    $query = "
         SELECT pv.*, mp.patient_name, mp.patient_datebirth,
               mp.patient_gender, mp.patient_address, mp.nomor_rm,
               r.room_name, b.bed_name,
               u.signature_user
        FROM pasien_visit pv
        LEFT JOIN ms_patient       mp  ON pv.id_patient = mp.id_patient
        LEFT JOIN permintaan_ranap pr  ON pv.visit_ID   = pr.visit_ID_inpatient
        LEFT JOIN ms_room           r  ON pr.id_room    = r.id_room
        LEFT JOIN ms_room_bed       b  ON pr.id_bed     = b.id_bed
        LEFT JOIN ms_users          u  ON REPLACE(REPLACE(REPLACE(REPLACE(LOWER(u.fullname), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '') = 
       REPLACE(REPLACE(REPLACE(REPLACE(LOWER(pv.id_doctor), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '')
        WHERE pv.visit_ID = ? AND pv.id_customer = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $no, $id_customer);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $diagnosa = $data['kdDiag1'] ?? $data['diagnosa'] ?? '';
    $diagnosaData = mysqli_query($koneksi, "SELECT * FROM icd_10 WHERE code='$diagnosa'");
    $icd = mysqli_fetch_array($diagnosaData);
}

// ============================================================
// HITUNG USIA
// ============================================================
function hitungUsiaRanap(?string $dob, ?string $visitDate): string
{
    if (empty($dob)) return '-';
    try {
        $dobObj   = new DateTime($dob);
        $visitObj = $visitDate ? new DateTime($visitDate) : new DateTime();
        $diff     = $visitObj->diff($dobObj);

        if ($diff->y > 0) return $diff->y . ' Tahun';
        if ($diff->m > 0) return $diff->m . ' Bulan';
        return $diff->d . ' Hari';
    } catch (Exception $e) {
        return '-';
    }
}

$nama     = val($data['patient_name'] ?? null);
$dokter   = val($data['id_doctor'] ?? null);
$usia     = val(hitungUsiaRanap($data['patient_datebirth'] ?? null, $data['visit_date'] ?? null));




$indikasi = val($data['anamnesa'] ?? null);
$kamar    = val(trim(($data['room_name'] ?? '') . ' - ' . ($data['bed_name'] ?? ''), ' -'));
$terapi   = val($data['tindakan'] ?? null);

$ttdSrc   = !empty($data['signature_user'])
    ? '../../../uploads/ttd_faskes/' . val($data['signature_user'])
    : '../../../uploads/ttd/default.png';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar Rawat Inap</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 15mm 20mm;
        }

        .rinap-body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 0;
            background: white;
            color: #000;
        }

        /* ===== CONTAINER ===== */
        .rinap-container {
            width: 100%;
            max-width: 750px;
            margin: auto;
        }

        /* 
         * ===== INJEKSI CSS KOPSURAT (3 KOLOM) =====
         * Menunggangi (override) gaya dari kopsurat.php untuk memaksanya
         * kembali ke format 3 kolom (Kiri: Logo 1, Tengah: Teks, Kanan: Logo 2).
         * Menggunakan property display: table; karena sangat stabil di DOMPDF.
         */
        .rinap-header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        /* Memaksa elemen anak di dalam header (logo dan teks) menjadi kolom tabel (cell) */
        .rinap-header>div,
        .rinap-header>img {
            display: table-cell;
            vertical-align: middle;
        }

        /* Membidik Logo Kiri (Asumsi ini adalah img pertama) */
        .rinap-header img:first-of-type {
            width: 80px;
            /* Sesuaikan ukuran logo kiri */
            height: auto;
            text-align: left;
        }

        /* Membidik Teks Tengah (Asumsi teks dibungkus dalam tag div) */
        .rinap-header div {
            text-align: center;
            padding: 0 15px;
        }

        /* Membidik Logo Kanan (Asumsi ini adalah img kedua/terakhir) */
        .rinap-header img:last-of-type {
            width: 60px;
            /* Sesuaikan ukuran logo kanan */
            height: auto;
            text-align: right;
        }

        /* Tipografi Kop Surat */
        .rinap-header h1 {
            font-size: 26px;
            /* Disesuaikan agar proporsional */
            margin: 0 0 5px 0;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .rinap-header h2 {
            font-size: 20px;
            margin: 0;
            font-weight: bold;
        }

        .rinap-header .rinap-alamat,
        .rinap-header p {
            font-size: 11pt;
            margin: 2px 0 0 0;
            line-height: 1.3;
        }

        .rinap-hr {
            margin-top: 5px;
            border: 0;
            border-top: 2px solid #000;
        }

        /* ===== JUDUL SURAT ===== */
        .rinap-judul {
            text-align: center;
            margin-top: 20px;
            text-decoration: underline;
            font-size: 16pt;
            font-weight: bold;
        }

        /* ===== ISI ===== */
        .rinap-section {
            margin-top: 20px;
            font-size: 11pt;
        }

        .rinap-section p {
            margin: 5px 0;
        }

        .rinap-data {
            margin-left: 20px;
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 11pt;
            border-collapse: collapse;
        }

        /* Hilangkan Garis Tabel */
        .rinap-data,
        .rinap-data tr,
        .rinap-data td {
            border: none !important;
        }

        .rinap-data td {
            padding: 4px 6px;
            vertical-align: top;
        }

        /* Rapatkan Kolom Label */
        .rinap-data td:first-child {
            width: 150px;
            white-space: nowrap;
        }

        /* ===== PENUTUP ===== */
        .rinap-penutup {
            margin-top: 20px;
            text-align: justify;
            font-size: 11pt;
        }

        /* ===== TTD ===== */
        .rinap-ttd-wrapper {
            width: 100%;
            margin-top: 40px;
        }

        .rinap-ttd-kanan {
            width: 260px;
            margin-left: auto;
            /* Memaksa kotak TTD merapat ke kanan */
            text-align: center;
        }

        .rinap-ttd-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .rinap-ttd-img {
            max-width: 160px;
            max-height: 80px;
            margin: 10px auto;
            display: block;
        }

        .rinap-ttd-box {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 4px;
            font-size: 10pt;
            font-weight: bold;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            font-family: inherit;
            font-size: 11pt;
            cursor: pointer;
            border: 1px solid #ccc;
            background: #f9f9f9;
            border-radius: 4px;
        }

        @media print {
            .rinap-noprint {
                display: none !important;
            }
        }
    </style>
</head>

<body class="rinap-body">

    <div class="rinap-container">

        <!-- 
             Berkas kopsurat.php sekarang akan diformat ulang oleh CSS di atas 
             (diarahkan menjadi tata letak table-cell 3 kolom).
        -->
        <?php include 'kopsurat.php'; ?>

        <div class="rinap-judul">SURAT PERINTAH RAWAT INAP</div>

        <div class="rinap-section">
            <p>Kepada Yth :</p>
            <p>Dokter DPJP : <?= $dokter ?></p>

            <p style="margin-top: 15px;">Mohon Dirawat</p>

            <table class="rinap-data">
                <tr>
                    <td>Nama</td>
                    <td>: <?= $nama ?></td>
                </tr>
                <tr>
                    <td>Umur</td>
                    <td>: <?= $usia ?></td>
                </tr>
                <tr>
                    <td>Diagnosa</td>
                    <td>: <?=
                            !empty($data['kdDiag1'])
                                ? $data['kdDiag1'] . ' - ' . $data['nmDiag1']
                                : (
                                    !empty($data['diagnosa'])
                                    ? $icd['code'] . ' - ' . $icd['icd10']
                                    : '-'
                                )
                            ?></td>
                </tr>
                <tr>
                    <td>Indikasi Dirawat</td>
                    <td>: <?= $indikasi ?></td>
                </tr>
                <tr>
                    <td>Dirawat</td>
                    <td>: <?= $kamar ?></td>
                </tr>
                <tr>
                    <td>Terapi</td>
                    <td>: <?= $terapi ?></td>
                </tr>
            </table>

            <p class="rinap-penutup">
                Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan
                dalam pengajuan klaim biaya rawat inap.
            </p>

            <div class="rinap-ttd-wrapper">
                <div class="rinap-ttd-kanan">
                    <div class="rinap-ttd-title">DOKTER MERAWAT</div>
                    <img src="<?= $ttdSrc ?>" class="rinap-ttd-img" alt="Tanda Tangan Dokter">
                    <div class="rinap-ttd-box"><?= $dokter ?></div>
                </div>
            </div>

            <div class="rinap-noprint" style="text-align:center; margin-top:30px; margin-bottom:20px;">
                <button onclick="window.print()" class="btn-print">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Cetak
                </button>
            </div>
        </div>

    </div>

</body>

</html>