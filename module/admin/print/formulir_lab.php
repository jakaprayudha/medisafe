<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';

$id_customer = $_SESSION['id_customer'] ?? null;
$no          = $_GET['no'] ?? null;
$rm          = $_GET['rm'] ?? null;

/*
 * Helper terpusat untuk sanitasi string HTML
 */
if (!function_exists('val')) {
    function val(?string $str): string
    {
        $str = trim((string)$str);
        return $str === '' ? '-' : htmlspecialchars($str, ENT_QUOTES);
    }
}

// ============================================================
// QUERY DATA PASIEN
// ============================================================
$pasien = [];
$puser  = [];

if ($no && $id_customer) {
    $qPasien = "
        SELECT mp.*, pv.visit_date
        FROM pasien_visit pv
        LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
        WHERE pv.visit_ID = ? AND pv.id_customer = ?
        LIMIT 1
    ";
    $stmtPasien = mysqli_prepare($koneksi, $qPasien);
    mysqli_stmt_bind_param($stmtPasien, "si", $no, $id_customer);
    mysqli_stmt_execute($stmtPasien);
    $pasien = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPasien)) ?? [];
    mysqli_stmt_close($stmtPasien);



    $qUser = "
        SELECT fullname, signature_user
        FROM ms_users
        WHERE id_customer = ? AND roles = 'analislab'
        LIMIT 1
    ";
    $stmtUser = mysqli_prepare($koneksi, $qUser);
    mysqli_stmt_bind_param($stmtUser, "i", $id_customer);
    mysqli_stmt_execute($stmtUser);
    $puser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUser)) ?? [];
    mysqli_stmt_close($stmtUser);
}

// ============================================================
// QUERY HASIL LAB
// ============================================================
$grupHasil = [];

if ($no && $id_customer) {
    $qIns = "
        SELECT * FROM visit_inspection
        WHERE id_visit = ? AND id_customer = ?
        ORDER BY id_inspection DESC
    ";
    $stmtIns = mysqli_prepare($koneksi, $qIns);
    mysqli_stmt_bind_param($stmtIns, "si", $no, $id_customer);
    mysqli_stmt_execute($stmtIns);
    $resIns = mysqli_stmt_get_result($stmtIns);

    while ($ins = mysqli_fetch_assoc($resIns)) {
        $id_inspection = $ins['id_inspection'];
        $assemen       = $ins['inspection_name'];

        $qLab = "
            SELECT
                ld.assemen AS group_name,
                li.assemen AS nama,
                li.satuan,
                li.minimum,
                li.maksimum,
                lr.hasil
            FROM laboratorium_detail ld
            JOIN laboratorium_item li ON li.kode = ld.kode
            LEFT JOIN laboratorium_result lr
                ON lr.id_item = li.id AND lr.id_inspection = ?
            WHERE ld.assemen = ?
            ORDER BY li.urutan ASC
        ";
        $stmtLab = mysqli_prepare($koneksi, $qLab);
        mysqli_stmt_bind_param($stmtLab, "ss", $id_inspection, $assemen);
        mysqli_stmt_execute($stmtLab);
        $resLab = mysqli_stmt_get_result($stmtLab);

        while ($row = mysqli_fetch_assoc($resLab)) {
            $tanggal = date('Y-m-d', strtotime($ins['inspection_date']));
            $grupHasil[$tanggal][$row['group_name']][] = [

                'nama'   => $row['nama'],
                'hasil'  => $row['hasil'],
                'satuan' => $row['satuan'],
                'normal' => ($row['minimum'] ?? '-') . ' - ' . ($row['maksimum'] ?? '-'),
            ];
        }
        mysqli_stmt_close($stmtLab);
    }
    mysqli_stmt_close($stmtIns);
    // 🔥 SORT TANGGAL ASC (AWAL → AKHIR)
    if (!empty($grupHasil)) {
        ksort($grupHasil);
    }
}

// ============================================================
// TTD PETUGAS & TGL PERIKSA
// ============================================================
$ttdLabSrc = !empty($puser['signature_user'])
    ? '../../../uploads/ttd_faskes/' . val($puser['signature_user'])
    : '../../../uploads/ttd/default.png';

$tglPeriksa = !empty($pasien['visit_date'])
    ? date('d-m-Y', strtotime($pasien['visit_date']))
    : '-';

// ============================================================
// GENERATE QR CODE URL
// ============================================================
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$verifyUrl = $protocol . $domain . "/module/verify/lab.php?no=" . urlencode((string)$no) . "&rm=" . urlencode((string)$rm);
$qrApiUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($verifyUrl);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Laboratorium</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 15mm 20mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        .lab-container {
            width: 100%;
            max-width: 760px;
            margin: auto;
        }

        .lab-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 15px;
        }

        /* =========================
           INFO PASIEN
        ========================= */
        .lab-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .lab-info td {
            border: none !important;
            padding: 3px 6px;
            font-size: 11pt;
            vertical-align: top;
        }

        .lab-info .label {
            font-weight: bold;
            white-space: nowrap;
            width: 1%;
        }

        .lab-info .val {
            width: auto;
        }

        .lab-info .wrap {
            word-break: break-word;
            white-space: normal;
        }

        /* =========================
           TABEL UTAMA LAB
        ========================= */
        .lab-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .lab-table th,
        .lab-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 11pt;
        }

        .lab-table th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .lab-section td {
            background: #f4f4f4;
            font-weight: bold;
        }

        .lab-abnormal {
            color: #c00;
            font-weight: bold;
        }

        .lab-center {
            text-align: center;
        }

        /* =========================
           FOOTER (Keluar dari tabel utama)
        ========================= */
        table.lab-footer-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
            border: none !important;
        }

        table.lab-footer-table td {
            border: none !important;
            padding: 0;
            vertical-align: bottom;
        }

        .lab-qr {
            text-align: center;
            width: 140px;
            font-size: 9pt;
        }

        .lab-qr img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto 5px auto;
        }

        .lab-ttd {
            display: inline-block;
            width: 220px;
            text-align: center;
            font-size: 10pt;
        }

        .lab-ttd img {
            width: 100px;
            height: auto;
            max-height: 80px;
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
            .lab-noprint {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="lab-container">

        <?php include 'kopsurat.php'; ?>

        <div class="lab-title">HASIL LABORATORIUM</div>

        <table class="lab-info">
            <tr>
                <td class="label">Nama</td>
                <td class="val">: <?= val($pasien['patient_name'] ?? null) ?></td>

                <!-- <td class="label">Tgl Periksa</td>
                <td class="val">: <?= val($tglPeriksa) ?></td> -->
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="val">: <?= val($pasien['patient_gender'] ?? null) ?></td>

                <td class="label align-top">Alamat</td>
                <td class="val wrap">: <?= val($pasien['patient_address'] ?? null) ?></td>
            </tr>
        </table>

        <!-- ================= TABEL HASIL LAB ================= -->
        <table class="lab-table" id="table-main">
            <!-- Menentukan persentase kolom pasti agar tabel split tetap selaras -->
            <colgroup>
                <col style="width: 45%;">
                <col style="width: 25%;">
                <col style="width: 30%;">
            </colgroup>
            <thead>
                <tr>
                    <th>PEMERIKSAAN</th>
                    <th>HASIL</th>
                    <th>NILAI NORMAL</th>
                </tr>
            </thead>

            <tbody>

                <?php if (empty($grupHasil)): ?>

                    <tr>
                        <td colspan="3" style="text-align:center; color:#999; padding:20px;">
                            Tidak ada data laboratorium
                        </td>
                    </tr>

                <?php else: ?>

                    <?php
                    $totalTanggal = count($grupHasil);
                    $indexTanggal = 0;
                    ?>

                    <?php foreach ($grupHasil as $tanggal => $groups): ?>
                        <?php $indexTanggal++; ?>

                        <!-- 🔥 UPDATE TANGGAL SAJA -->
                        <tr>
                            <td colspan="3" style="border:none; font-weight:bold;">
                                Tanggal Pemeriksaan: <?= date('d-m-Y', strtotime($tanggal)) ?>
                            </td>
                        </tr>

                        <?php foreach ($groups as $grupNama => $items): ?>

                            <tr class="lab-section">
                                <td colspan="3"><?= val($grupNama) ?></td>
                            </tr>

                            <?php foreach ($items as $item): ?>

                                <?php
                                $valHasil = (float)($item['hasil'] ?? 0);
                                $parts = explode('-', $item['normal']);
                                $min = is_numeric(trim($parts[0] ?? null)) ? (float)$parts[0] : null;
                                $max = is_numeric(trim($parts[1] ?? null)) ? (float)$parts[1] : null;

                                $abnormal = ($min !== null && $max !== null && $item['hasil'] !== null)
                                    ? ($valHasil < $min || $valHasil > $max)
                                    : false;
                                ?>

                                <tr>
                                    <td><?= val($item['nama']) ?></td>
                                    <td class="lab-center <?= $abnormal ? 'lab-abnormal' : '' ?>">
                                        <?= val($item['hasil']) ?> <?= val($item['satuan']) ?>
                                    </td>
                                    <td class="lab-center"><?= val($item['normal']) ?></td>
                                </tr>

                            <?php endforeach; ?>
                        <?php endforeach; ?>

                        <?php if ($indexTanggal < $totalTanggal): ?>
                            <tr>
                                <td colspan="3" style="border:none; padding:0;">
                                    <div style="page-break-after: always;"></div>
                                </td>
                            </tr>
                        <?php endif; ?>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>
        <div style="page-break-inside: avoid; margin-top: 10px;">
            <!-- ================= FOOTER TTD & QR ================= -->
            <table class="lab-footer-table">
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <div class="lab-qr">
                            <img src="<?= $qrApiUrl ?>" alt="QR Code Verifikasi">
                            <div class="baseline-align-qr">
                                Scan untuk verifikasi hasil
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div class="lab-ttd">
                            Pengisi Data<br>
                            <img src="<?= $ttdLabSrc ?>" alt="Tanda Tangan">
                            <div class="baseline-align-text">
                                <?= val($puser['fullname'] ?? 'Petugas Laboratorium') ?>
                            </div>
                            <span style="font-weight: normal; font-size: 10pt;">Petugas Laboratorium</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ================= PRINT BUTTON ================= -->
        <div class="lab-noprint" style="text-align:center; margin-top:20px; margin-bottom:20px;">
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