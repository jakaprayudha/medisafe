<?php
$title    = "Catatan Perkembangan Pasien Terintegrasi (CPPT)";
$subtitle = "";
require_once '../../../database/connect.php';

$id_customer = $_SESSION['id_customer'] ?? null;
$no          = $_GET['no'] ?? null;

/*
 * Helper terpusat untuk sanitasi string HTML dan mencegah error 'Passing null to parameter #1'
 */
if (!function_exists('val')) {
    function val(?string $str): string
    {
        $str = trim((string)$str);
        return $str === '' ? '-' : htmlspecialchars($str, ENT_QUOTES);
    }
}

// ============================================================
// QUERY DATA CPPT (Optimasi Keamanan dengan Prepared Statements)
// ============================================================
$cpptRows = [];

if ($no && $id_customer) {
    $query = "
        SELECT vc.*, u.fullname, u.roles, u.signature_user
        FROM visit_cppt vc
        LEFT JOIN ms_users u ON vc.users_entry = u.id_user
        WHERE vc.visit_ID = ? AND vc.id_customer = ?
        ORDER BY vc.cppt_date ASC, vc.cppt_time ASC
    ";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "si", $no, $id_customer);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $cpptRows[] = $row;
    }
    mysqli_stmt_close($stmt);
}
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
            margin: 15mm 20mm;
        }

        /* =========================
           BODY KHUSUS
        ========================= */
        body.cpo-body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        /* =========================
           CONTAINER
        ========================= */
        .cpo-container {
            width: 100%;
            max-width: 760px;
            margin: auto;
        }

        /* =========================
           TITLE
        ========================= */
        .cpo-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 15px;
        }

        /* =========================
           TABLE GENERAL
        ========================= */
        .cpo-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            /* Mencegah kolom melar bebas di DOMPDF */
            word-wrap: break-word;
        }

        .cpo-table th,
        .cpo-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.3;
        }

        .cpo-table th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        /* 
         * Mencegah baris tabel terpotong separuh saat berganti halaman. 
         */
        .cpo-table tbody tr {
            page-break-inside: avoid;
        }

        /* =========================
           HELPER
        ========================= */
        .cpo-center {
            text-align: center;
        }

        /* =========================
           STYLING KHUSUS AREA TTD
        ========================= */
        .cppt-ttd-cell {
            text-align: center;
            /* Memastikan DOMPDF membaca orientasi sel ke atas (karena middle sering gagal pada baris anti-potong) */
            vertical-align: top !important;
            /* Bantalan atas 15px menjamin paraf tidak akan pernah menabrak garis batas atas sel */
            padding: 15px 6px 10px 6px !important;
        }

        .cppt-ttd-img {
            height: 45px;
            width: auto;
            max-width: 100px;
            display: block;
            /* Margin atas dinolkan karena jarak sudah ditangani oleh padding-top sel */
            margin: 0 auto 10px auto;
        }

        .cppt-ttd-name {
            display: block;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .cppt-ttd-role {
            display: block;
            font-size: 9pt;
            color: #444;
        }

        .cppt-ttd-error {
            display: block;
            color: #d32f2f;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        /* Optimasi tombol cetak */
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

        /* =========================
           PRINT
        ========================= */
        @media print {
            .cpo-noprint {
                display: none !important;
            }
        }
    </style>
</head>

<body class="cpo-body">

    <div class="cpo-container">

        <?php include 'kop-surat.php'; ?>

        <div class="cpo-title">
            <?= strtoupper(htmlspecialchars($title)) ?>
        </div>

        <!-- ================= TABEL CPPT ================= -->
        <table class="cpo-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal / Jam</th>
                    <th style="width: 45%;">Perkembangan (SOAP)</th>
                    <th style="width: 25%;">Instruksi</th>
                    <th style="width: 15%;">Paraf / Nama</th>
                </tr>
            </thead>
            <tbody id="cppt_body">
                <?php if (empty($cpptRows)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#999; padding: 20px 0;">
                            Tidak ada data CPPT
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cpptRows as $cppt): ?>
                        <tr>
                            <td class="cpo-center">
                                <?= val($cppt['cppt_date'] ?? null) ?><br>
                                <?= val($cppt['cppt_time'] ?? null) ?>
                            </td>
                            <td>
                                <b>S:</b> <?= val($cppt['subjective'] ?? null) ?><br>
                                <b>O:</b> <?= val($cppt['objective'] ?? null) ?><br>
                                <b>A:</b> <?= val($cppt['analysis'] ?? null) ?><br>
                                <b>P:</b> <?= val($cppt['planning'] ?? null) ?>
                            </td>
                            <td><?= nl2br(val($cppt['instruction'] ?? null)) ?></td>

                            <!-- Implementasi TTD Cell yang Tahan Banting di DOMPDF -->
                            <td class="cppt-ttd-cell">
                                <?php if (!empty($cppt['signature_user'])): ?>
                                    <img
                                        src="../../../uploads/ttd_faskes/<?= htmlspecialchars($cppt['signature_user'], ENT_QUOTES) ?>"
                                        class="cppt-ttd-img"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                        alt="TTD">
                                    <span class="cppt-ttd-error" style="display:none;">User belum<br>upload TTD</span>
                                <?php else: ?>
                                    <span class="cppt-ttd-error">User belum<br>TTD di master</span>
                                <?php endif; ?>

                                <span class="cppt-ttd-name"><?= val($cppt['fullname'] ?? null) ?></span>
                                <span class="cppt-ttd-role">(<?= val($cppt['roles'] ?? null) ?>)</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ================= TOMBOL CETAK ================= -->
        <div class="cpo-noprint" style="text-align:center; margin-top:20px; margin-bottom:20px;">
            <button onclick="window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Halaman
            </button>
        </div>

    </div>

</body>

</html>