<?php
require '../../../database/connect.php';

$id_patient = $_GET['id_patient'] ?? null;

if (!$id_patient) {
    die("ID patient tidak ditemukan");
}

/*
 * Menerapkan Prepared Statements untuk mencegah celah SQL Injection.
 */
$query = "SELECT patient_ktp_file, patient_kk_file, patient_bpjs_file FROM ms_patient WHERE id_patient = ?";
$stmt  = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param($stmt, "s", $id_patient);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($result);

/*
 * Menangani resolusi path file dan kondisi nilai kosong.
 */
function getFilePath(?string $file): ?string
{
    if (empty($file) || $file === 'null') {
        return null;
    }
    return "../../../uploads/patient/" . $file;
}

$kkSrc   = getFilePath($data['patient_kk_file'] ?? null);
$ktpSrc  = getFilePath($data['patient_ktp_file'] ?? null);
$bpjsSrc = getFilePath($data['patient_bpjs_file'] ?? null);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dokumen Identitas Pasien</title>

    <style>
        * {
            box-sizing: border-box;
        }

        /* 
         * Kertas A4 Landscape dengan margin 8mm menyisakan ruang vertikal (usable height) sekitar 194mm.
         */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        .page {
            width: 100%;
            position: relative;
            page-break-inside: avoid;
        }

        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 65pt;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: -1;
        }

        .title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 11pt;
            margin: 0 0 10px 0;
        }

        /* 
         * AREA KARTU KELUARGA 
         * Padding kontainer dihapus. 
         */
        .kk-box {
            border: 1px solid #000;
            margin-bottom: 8px;
        }

        .kk-img {
            width: 100%;
            height: 120mm;
            /* Alokasi 120mm memastikan tidak melebihi kertas */
            position: relative;
            /* Menjadi acuan bagi absolute image di dalamnya */
            overflow: hidden;
            border-bottom: 1px solid #000;
            background-color: #fafafa;
        }

        /* Penempatan sentral absolut untuk seluruh gambar (mencegah ghosting height) */
        .kk-img img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 100%;
            max-height: 100%;
        }

        .rotate-left-lg {
            /* Rotasi dikombinasikan dengan translate agar titik pusat rotasi akurat */
            transform: translate(-50%, -50%) rotate(-90deg) !important;
            max-width: 120mm !important;
            /* Batas tinggi visual saat landscape */
            max-height: 270mm !important;
            /* Batas lebar visual maksimal */
        }

        /* 
         * AREA KTP DAN BPJS
         */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bottom-table td {
            width: 50%;
            border: 1px solid #000;
            vertical-align: top;
            /* Padding kontainer dihapus */
        }

        .img-box {
            width: 100%;
            height: 35mm;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid #000;
            background-color: #fafafa;
        }

        .img-box img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 100%;
            max-height: 100%;
        }

        .rotate-left-sm {
            transform: translate(-50%, -50%) rotate(-90deg) !important;
            max-width: 35mm !important;
            max-height: 130mm !important;
        }

        /* 
         * TYPOGRAPHY & STATES
         */
        .label {
            font-size: 10pt;
            font-weight: bold;
            text-align: left;
            display: block;
            padding: 3px 6px;
            /* Padding dipindahkan ke sini agar teks tidak menempel ke garis */
            background: #fff;
        }

        .empty {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #d32f2f;
            font-size: 10pt;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="page">
        <div class="watermark">KLINIK</div>

        <div class="title">DOKUMEN IDENTITAS PASIEN</div>
        <div class="subtitle">Kartu Keluarga, KTP dan BPJS</div>

        <!-- Area Kartu Keluarga -->
        <div class="kk-box">
            <div class="kk-img">
                <?php if ($kkSrc): ?>
                    <img src="<?= htmlspecialchars($kkSrc) ?>" alt="Kartu Keluarga" class="rotate-left-lg">
                <?php else: ?>
                    <span class="empty">KK belum diupload</span>
                <?php endif; ?>
            </div>
            <span class="label">KARTU KELUARGA</span>
        </div>

        <!-- Area KTP & BPJS -->
        <table class="bottom-table">
            <tr>
                <td>
                    <div class="img-box">
                        <?php if ($ktpSrc): ?>
                            <img src="<?= htmlspecialchars($ktpSrc) ?>" alt="KTP" class="rotate-left-sm">
                        <?php else: ?>
                            <span class="empty">KTP belum diupload</span>
                        <?php endif; ?>
                    </div>
                    <span class="label">KTP</span>
                </td>
                <td>
                    <div class="img-box">
                        <?php if ($bpjsSrc): ?>
                            <img src="<?= htmlspecialchars($bpjsSrc) ?>" alt="BPJS" class="rotate-left-sm">
                        <?php else: ?>
                            <span class="empty">BPJS belum diupload</span>
                        <?php endif; ?>
                    </div>
                    <span class="label">BPJS</span>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>