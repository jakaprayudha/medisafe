<?php
$title    = "Formulir Catatan Pemberian Obat (CPO)";
$subtitle = "";
require_once '../../../database/connect.php';
require_once __DIR__ . '/qr_local.php';

$no          = $_GET['no'] ?? null;
$rm          = $_GET['rm'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

/*
 * Pengecekan eksistensi fungsi diterapkan untuk mencegah 
 * fatal error 'Cannot redeclare' saat berkas dibundel.
 */
if (!function_exists('val')) {
    function val(?string $str): string
    {
        $str = trim((string)$str);
        return $str === '' ? '-' : htmlspecialchars($str, ENT_QUOTES);
    }
}

// ============================================================
// QUERY RUANGAN + DIAGNOSA
// ============================================================
$dataruangan = [];
if ($no && $id_customer) {
    $qRoom = "
        SELECT ms_room.room_name, ms_room_bed.bed_name,
               pasien_visit.diagnosa, icd_10.icd10
        FROM pasien_visit
        LEFT JOIN permintaan_ranap ON pasien_visit.visit_ID = permintaan_ranap.visit_ID_inpatient
        LEFT JOIN ms_room          ON permintaan_ranap.id_room = ms_room.id_room
        LEFT JOIN ms_room_bed      ON permintaan_ranap.id_bed  = ms_room_bed.id_bed
        LEFT JOIN icd_10           ON icd_10.code = pasien_visit.diagnosa
        WHERE pasien_visit.visit_ID = ? AND pasien_visit.id_customer = ?
    ";
    $stmtRoom = mysqli_prepare($koneksi, $qRoom);
    if ($stmtRoom) {
        mysqli_stmt_bind_param($stmtRoom, "si", $no, $id_customer);
        mysqli_stmt_execute($stmtRoom);
        $dataruangan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRoom)) ?? [];
        mysqli_stmt_close($stmtRoom);
    }
}

// ============================================================
// QUERY PETUGAS APOTEKER
// ============================================================
$datapetugas = [];
if ($id_customer) {
    $qApo = "
        SELECT fullname, signature_user
        FROM ms_users
        WHERE id_customer = ? AND roles = 'apoteker'
        LIMIT 1
    ";
    $stmtApo = mysqli_prepare($koneksi, $qApo);
    if ($stmtApo) {
        mysqli_stmt_bind_param($stmtApo, "i", $id_customer);
        mysqli_stmt_execute($stmtApo);
        $datapetugas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtApo)) ?? [];
        mysqli_stmt_close($stmtApo);
    }
}

// ============================================================
// QUERY DATA CPO 
// ============================================================
$cpoRows = [];
if ($no && $id_customer) {
    $qCpo = "
        SELECT pc.*, 
               u.signature_user AS sig_user,
               pv.signature_path
        FROM pasien_cpo pc
        LEFT JOIN ms_users u ON pc.petugas = u.id_user
        LEFT JOIN pasien_visit pv ON pc.visit_ID = pv.visit_ID AND pc.id_customer = pv.id_customer
        WHERE pc.visit_ID = ? AND pc.id_customer = ?
        ORDER BY pc.tanggal ASC
    ";
    $stmtCpo = mysqli_prepare($koneksi, $qCpo);
    if ($stmtCpo) {
        mysqli_stmt_bind_param($stmtCpo, "si", $no, $id_customer);
        mysqli_stmt_execute($stmtCpo);
        $resultCpo = mysqli_stmt_get_result($stmtCpo);
        while ($row = mysqli_fetch_assoc($resultCpo)) {
            $cpoRows[] = $row;
        }
        mysqli_stmt_close($stmtCpo);
    }
}

// Pengelompokkan data berdasarkan tanggal
$grouped = [];
foreach ($cpoRows as $row) {
    $tgl = $row['tanggal'] ?? '-';
    $grouped[$tgl][] = $row;
}

// ============================================================
// GENERATE QR CODE URL
// ============================================================
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$verifyUrl = $protocol . $domain . "/module/verify/cpo.php?no=" . urlencode((string)$no) . "&rm=" . urlencode((string)$rm);
$qrApiUrl  = qrDataUri($verifyUrl, 120);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 15mm 15mm;
        }

        body.cpo-body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        .cpo-container {
            width: 100%;
            max-width: 100%;
            margin: auto;
        }

        .cpo-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0 15px;
        }

        .cpo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .cpo-table th,
        .cpo-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }

        .cpo-table th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        .cpo-table tbody tr {
            page-break-inside: avoid;
        }

        /* 
         * KELAS VISUAL ROWSPAN
         * Memanipulasi border-top dan border-bottom untuk memberikan 
         * ilusi sel yang menyatu (rowspan), padahal tabel tetap utuh.
         */
        .cpo-table td.date-single {
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
        }

        .cpo-table td.date-first {
            border-top: 1px solid #000 !important;
            border-bottom: none !important;
        }

        .cpo-table td.date-middle {
            border-top: none !important;
            border-bottom: none !important;
        }

        .cpo-table td.date-last {
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }

        .cpo-info {
            table-layout: auto;
            margin-bottom: 5px;
        }

        .cpo-info td {
            border: none !important;
            padding: 3px 6px;
            font-size: 10pt;
        }

        .cpo-center {
            text-align: center;
        }

        table.cpo-footer-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
            border: none !important;
            page-break-inside: avoid;
        }

        table.cpo-footer-table td {
            border: none !important;
            padding: 0;
            vertical-align: bottom;
        }

        .qr-wrapper {
            width: 140px;
            text-align: center;
        }

        .qr-wrapper img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto 5px auto;
        }

        .ttd-wrapper {
            width: 280px;
            text-align: center;
            display: inline-block;
            font-size: 10pt;
        }

        .ttd-wrapper img {
            max-height: 80px;
            max-width: 100%;
            display: block;
            margin: 10px auto;
        }

        .baseline-align-text {
            border-top: 1px solid #000;
            margin-top: 5px;
            padding-top: 3px;
            font-weight: bold;
            font-size: 10pt;
        }

        .baseline-align-qr {
            border-top: 1px solid transparent;
            margin-top: 5px;
            padding-top: 3px;
            font-weight: normal;
            font-size: 9pt;
        }

        .cppt-ttd-small img {
            max-height: 35px;
            max-width: 100%;
            display: block;
            margin: 0 auto;
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
            .cpo-noprint {
                display: none !important;
            }
        }
    </style>
</head>

<body class="cpo-body">

    <div class="cpo-container">

        <?php require 'kop-surat.php'; ?>

        <table class="cpo-table cpo-info">
            <tr>
                <td style="width: 12%;">Ruangan</td>
                <td style="width: 38%;">
                    : <?= val($dataruangan['room_name'] ?? null) ?>
                    Bed <?= val($dataruangan['bed_name'] ?? null) ?>
                </td>
                <td style="width: 12%;">Diagnosa</td>
                <td style="width: 38%;">
                    : <?= val($dataruangan['diagnosa'] ?? null) ?>
                    - <?= val($dataruangan['icd10'] ?? null) ?>
                </td>
            </tr>
        </table>

        <table class="cpo-table">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 18%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 6%;">
                <col style="width: 6%;">
                <col style="width: 6%;">
                <col style="width: 6%;">
                <col style="width: 15%;">
                <col style="width: 15%;">
            </colgroup>

            <thead>
                <tr>
                    <th rowspan="2">Tanggal</th>
                    <th rowspan="2">Nama Obat / Injeksi</th>
                    <th rowspan="2">Dosis</th>
                    <th rowspan="2">Sign</th>
                    <th colspan="4">Jam Pemberian</th>
                    <th rowspan="2">Paraf Keluarga</th>
                    <th rowspan="2">Paraf Petugas</th>
                </tr>
                <tr>
                    <th>Pagi</th>
                    <th>Siang</th>
                    <th>Sore</th>
                    <th>Malam</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($grouped)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:#999; padding: 20px 0;">
                            Tidak ada data CPO
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $firstGroupKey = array_key_first($grouped);
                    $lastGroupKey  = array_key_last($grouped);
                    $isSplit       = false;

                    foreach ($grouped as $tgl => $items):
                        $isLastGroup = ($tgl === $lastGroupKey);
                        $totalItems  = count($items);
                        $lastItemKey = array_key_last($items);

                        foreach ($items as $idx => $o):
                            $isAbsoluteFirst = ($tgl === $firstGroupKey && $idx === 0);
                            $isAbsoluteLast  = ($isLastGroup && $idx === $lastItemKey);

                            /* 
                             * TRIK TABLE SPLIT 
                             * Memotong tabel tepat sebelum baris paling akhir untuk dibungkus 
                             * ke dalam satu block <div> bersama area tanda tangan (menghindari TTD terpisah).
                             * Trik ini dilewati jika total data keseluruhannya hanya ada 1 baris.
                             */
                            if ($isAbsoluteLast && !$isAbsoluteFirst):
                                $isSplit = true;
                    ?>
            </tbody>
        </table>
        <div style="page-break-inside: avoid;">
            <table class="cpo-table" style="margin-top: -1px;">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 18%;">
                    <col style="width: 8%;">
                    <col style="width: 10%;">
                    <col style="width: 6%;">
                    <col style="width: 6%;">
                    <col style="width: 6%;">
                    <col style="width: 6%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                </colgroup>
                <tbody>
                <?php endif; ?>

                <?php
                            $dateClass = '';
                            if ($totalItems == 1) {
                                $dateClass = 'date-single';
                            } elseif ($idx === 0) {
                                $dateClass = 'date-first';
                            } elseif ($idx === $totalItems - 1) {
                                $dateClass = 'date-last';
                            } else {
                                $dateClass = 'date-middle';
                            }
                ?>
                <tr>
                    <td class="cpo-center <?= $dateClass ?>">
                        <?= $idx === 0 ? val($tgl) : '' ?>
                    </td>
                    <td><?= val($o['nama_item'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['dosis'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['signature'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['jam_pagi'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['jam_siang'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['jam_sore'] ?? null) ?></td>
                    <td class="cpo-center"><?= val($o['jam_malam'] ?? null) ?></td>
                    <td class="cpo-center cppt-ttd-small">
                        <?php if (!empty($o['signature_path'])): ?>
                            <img src="../../../uploads/ttd/<?= val($o['signature_path']) ?>" alt="Paraf Keluarga">
                        <?php endif; ?>
                    </td>
                    <td class="cpo-center cppt-ttd-small">
                        <?php if (!empty($o['sig_user'])): ?>
                            <img src="../../../uploads/ttd_faskes/<?= val($o['sig_user']) ?>" alt="Paraf Petugas">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
                </tbody>
            </table>

            <!-- ================= FOOTER TTD & QR ================= -->
            <!-- Blok ini akan langsung masuk ke dalam div pelindung jika tabel di-split -->
            <table class="cpo-footer-table">
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <div class="qr-wrapper">
                            <img src="<?= $qrApiUrl ?>" alt="QR Code Verifikasi">
                            <div class="baseline-align-qr">
                                Scan untuk verifikasi
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div class="ttd-wrapper">
                            Pengisi Data<br>
                            <?php if (!empty($datapetugas['signature_user'])): ?>
                                <img src="../../../uploads/ttd_faskes/<?= val($datapetugas['signature_user']) ?>" alt="Tanda Tangan" onerror="this.src='../../../uploads/ttd/default.png';">
                            <?php else: ?>
                                <br><br><br><br>
                            <?php endif; ?>
                            <div class="baseline-align-text">
                                <?= val($datapetugas['fullname'] ?? null) ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <?php if (!empty($grouped) && isset($isSplit) && $isSplit): ?>
        </div> <!-- Menutup div pelindung page-break -->
    <?php endif; ?>

    <!-- ================= PRINT BUTTON ================= -->
    <div class="cpo-noprint" style="text-align:center; margin-top:20px; margin-bottom:20px;">
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

</body>

</html>