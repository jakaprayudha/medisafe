<?php
$title    = "FORMULIR TRIASE KEGAWATDARURATAN";
$subtitle = "Assesmen Medis Awal Pasien IGD";
require_once '../../../database/connect.php';

$id_customer = $_SESSION['id_customer'] ?? null;
$no          = $_GET['no'] ?? null;

$triase  = null;
$terapii = "-";

if ($no && $id_customer) {
    $queryTriase = "
        SELECT
            t.*,
            v.riwayat_penyakit_sekarang,
            v.riwayat_pengobatan,
            v.riwayat_penyakit_pribadi,
            v.riwayat_alergi,
            v.diagnosa_sekunder,
            v.id_doctor,
            u.signature_user,
            i.code  AS icd_code,
            i.icd10 AS icd10
        FROM pasien_triase t
        LEFT JOIN pasien_visit v  ON t.visit_ID = v.visit_ID AND v.id_customer = ?
        LEFT JOIN icd_10       i  ON i.code = v.diagnosa
        LEFT JOIN ms_users     u  ON u.fullname = v.id_doctor
        WHERE t.visit_ID = ?
        ORDER BY t.id_triase DESC
        LIMIT 1
    ";

    $stmt = mysqli_prepare($koneksi, $queryTriase);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "is", $id_customer, $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $triase = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    $queryTindakan = "SELECT tindakan FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1";
    $stmtTindakan  = mysqli_prepare($koneksi, $queryTindakan);
    if ($stmtTindakan) {
        mysqli_stmt_bind_param($stmtTindakan, "ss", $no, $id_customer);
        mysqli_stmt_execute($stmtTindakan);
        $resultTindakan = mysqli_stmt_get_result($stmtTindakan);
        $rowTindakan    = mysqli_fetch_assoc($resultTindakan);

        if ($rowTindakan && !empty($rowTindakan['tindakan'])) {
            $terapii = $rowTindakan['tindakan'];
        }
        mysqli_stmt_close($stmtTindakan);
    }
}

$ref = [];
if (!empty($triase['referensi_triase'])) {
    $decoded = json_decode($triase['referensi_triase'], true);
    if (is_array($decoded)) {
        $ref = array_map(fn($v) => strtolower(trim($v)), $decoded);
    }
}

/*
 * Helper: Merender kotak centang dalam bentuk tabel mikro untuk stabilitas PDF.
 */
if (!function_exists('renderCheckbox')) {
    function renderCheckbox(string $label, array $ref): string
    {
        $chk = in_array(strtolower(trim($label)), $ref) ? 'checked="checked"' : '';
        return '
        <table class="micro-layout-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="chk-cell">
                    <input type="checkbox" ' . $chk . ' style="margin: 0;">
                </td>
                <td class="lbl-cell">
                    ' . htmlspecialchars($label) . '
                </td>
            </tr>
        </table>';
    }
}

/*
 * Helper: Mengembalikan tanda hubung jika string kosong.
 */
if (!function_exists('val')) {
    function val(?string $str): string
    {
        $str = trim((string)$str);
        return $str === '' ? '-' : htmlspecialchars($str, ENT_QUOTES);
    }
}

/*
 * Helper: SVG wajah dalam format Base64 untuk memastikan keamanan render pada PDF.
 */
if (!function_exists('getPainFaceSvg')) {
    function getPainFaceSvg(int $level): string
    {
        $svg = '';
        if ($level <= 2) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><circle cx="12" cy="12" r="10" fill="#fde047" stroke="#ca8a04" stroke-width="1"/><circle cx="8" cy="9" r="1.5" fill="#000"/><circle cx="16" cy="9" r="1.5" fill="#000"/><path d="M7 14s2 2.5 5 2.5 5-2.5 5-2.5" stroke="#000" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>';
        } elseif ($level <= 4) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><circle cx="12" cy="12" r="10" fill="#fef08a" stroke="#ca8a04" stroke-width="1"/><circle cx="8" cy="9" r="1.5" fill="#000"/><circle cx="16" cy="9" r="1.5" fill="#000"/><line x1="8" y1="15" x2="16" y2="15" stroke="#000" stroke-width="1.5" stroke-linecap="round"/></svg>';
        } elseif ($level <= 6) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><circle cx="12" cy="12" r="10" fill="#fdba74" stroke="#c2410c" stroke-width="1"/><circle cx="8" cy="10" r="1.5" fill="#000"/><circle cx="16" cy="10" r="1.5" fill="#000"/><path d="M8 16s2-2.5 5-2.5 5 2.5 5 2.5" stroke="#000" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>';
        } else {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><circle cx="12" cy="12" r="10" fill="#fca5a5" stroke="#b91c1c" stroke-width="1"/><path d="M7 10l2 2m0-2l-2 2M17 10l-2 2m0-2l2 2" stroke="#000" stroke-width="1.5" stroke-linecap="round"/><path d="M8 16s2-2.5 5-2.5 5 2.5 5 2.5" stroke="#000" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>';
        }

        $base64 = base64_encode($svg);
        return '<img src="data:image/svg+xml;base64,' . $base64 . '" width="32" height="32" style="display:block; margin: 0 auto;">';
    }
}

$atsText  = strtoupper(trim($triase['triase'] ?? ''));
$atsClass = match ($atsText) {
    'ATS 1' => 'ats1',
    'ATS 2' => 'ats2',
    'ATS 3' => 'ats3',
    'ATS 4' => 'ats4',
    'ATS 5' => 'ats5',
    default => '',
};

$anamnesa = strtolower($triase['anamnesa_choice'] ?? '');
$chkAuto  = str_contains($anamnesa, 'auto') ? 'checked="checked"' : '';
$chkAllo  = str_contains($anamnesa, 'allo') ? 'checked="checked"' : '';

$skalaNyeri = (int)($triase['skala_nyeri'] ?? 0);

$MAP = [
    'airway'      => ["Sumbatan Jalan Nafas", "Tidak ada sumbatan"],
    'breathing'   => ["Henti Nafas", "RR < 10 x/menit, distress napas berat", "Takipnea / distress sedang", "Dipsnea ringan"],
    'circulation' => ["Henti Jantung", "Sistolik < 80 mmHg", "Gangguan sirkulasi (akral dingin, nadi <50 atau>150)", "Muntah / diare tanda dehidrasi ringan"],
    'disability'  => ["Nyeri sedang", "Nyeri berat tidak respon obat", "Cedera kepala ringan"],
    'exposure'    => ["Kejang berkelanjutan", "Nyeri dada tipikal", "Luka kecil", "Multiple trauma", "Defisit neurologis", "Nyeri hebat"],
    'psikiatri'   => ["Gangguan perilaku berat mengancam diri & orang lain", "Datang dengan restrain", "Agresif fisik", "Ancaman bunuh diri", "Keluhan minor"],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>

    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .triase-noprint {
                display: none;
            }
        }

        body.triase-body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            margin: 0;
            background: #fff;
            color: #000;
        }

        .triase-container {
            max-width: 760px;
            margin: auto;
        }

        .triase-header-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }

        .triase-table,
        .igd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        /* Mencegah row tabel terpotong di tengah halaman */
        .triase-table tr,
        .igd-table tr {
            page-break-inside: avoid;
        }

        .triase-table th,
        .triase-table td,
        .igd-table th,
        .igd-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .igd-table th {
            background: #f2f2f2;
            text-align: center;
        }

        table.micro-layout-table,
        table.micro-layout-table th,
        table.micro-layout-table td {
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        table.micro-layout-table {
            width: 100%;
            margin-bottom: 6px !important;
        }

        table.micro-layout-table td.chk-cell {
            width: 18px;
            vertical-align: top;
        }

        table.micro-layout-table td.lbl-cell {
            vertical-align: top;
            padding-left: 4px !important;
            line-height: 1.3;
        }

        .badge-triase {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 14px;
            font-weight: bold;
            color: #fff;
            font-size: 12px;
            vertical-align: middle;
        }

        .ats1 {
            background: #d9534f;
        }

        .ats2 {
            background: #f0ad4e;
            color: #000;
        }

        .ats3 {
            background: #5cb85c;
        }

        .ats4 {
            background: #000;
        }

        .ats5 {
            background: #999;
        }

        table.pain-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            border: none !important;
            table-layout: fixed;
        }

        table.pain-table td {
            border: none !important;
            padding: 0 !important;
            text-align: center;
            vertical-align: top;
        }

        .pain-box {
            display: inline-block;
            width: 46px;
            margin: 0 auto;
            border: 2px solid transparent;
            border-radius: 6px;
            padding: 8px 0;
            box-sizing: border-box;
        }

        .pain-selected {
            border: 2px solid #ef4444;
            background: #fef2f2;
        }

        .pain-selected span {
            color: #b91c1c !important;
            font-weight: bold;
        }

        .triase-section {
            margin-top: 14px;
            font-weight: bold;
        }

        .igd-ttd {
            width: 300px;
            margin-left: auto;
            margin-top: 30px;
            text-align: center;
        }

        .igd-ttd img {
            height: 90px;
            object-fit: contain;
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
    </style>
</head>

<body class="triase-body">

    <div class="triase-container">

        <?php include 'kop-surat.php'; ?>

        <table class="triase-header-table">
            <tr>
                <td style="width: 180px; vertical-align: middle;">
                    <?php if (!empty($triase['visit_ID'])): ?>
                        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= urlencode($triase['visit_ID']) ?>&code=Code128" height="40" alt="Barcode Visit ID" style="display: block;">
                    <?php endif; ?>
                </td>
                <td style="vertical-align: middle; padding-left: 25px;">
                    <span style="font-size: 11pt; vertical-align: middle;">Kategori ATS :</span>
                    <span class="badge-triase <?= $atsClass ?>" style="margin-left: 6px;">
                        <?= val($atsText) ?>
                    </span>
                </td>
            </tr>
        </table>

        <table class="igd-table">
            <tr>
                <th>Airway</th>
                <th>Breathing</th>
                <th>Circulation</th>
                <th>Disability</th>
                <th>Exposure</th>
                <th>Psikiatri</th>
                <th>GCS</th>
                <th>Vital Sign</th>
            </tr>
            <tr>
                <td><?php foreach ($MAP['airway']      as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td><?php foreach ($MAP['breathing']   as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td><?php foreach ($MAP['circulation'] as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td><?php foreach ($MAP['disability']  as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td><?php foreach ($MAP['exposure']    as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td><?php foreach ($MAP['psikiatri']   as $opt) echo renderCheckbox($opt, $ref); ?></td>
                <td>
                    GCS E: <?= val($triase['gcs_e'] ?? null) ?><br>
                    GCS V: <?= val($triase['gcs_v'] ?? null) ?><br>
                    GCS M: <?= val($triase['gcs_m'] ?? null) ?>
                </td>
                <td>
                    TD: <?= val($triase['tekanan_darah'] ?? null) ?> mmHg<br>
                    Nadi: <?= val($triase['nadi'] ?? null) ?> x/menit<br>
                    RR: <?= val($triase['rr'] ?? null) ?> x/menit<br>
                    Suhu: <?= val($triase['suhu'] ?? null) ?> °C<br>
                    SpO2: <?= val($triase['spo2'] ?? null) ?> %
                </td>
            </tr>
        </table>

        <table class="igd-table">
            <tr>
                <th style="width:20%">ANAMNESIS</th>
                <td>
                    <table class="micro-layout-table" style="width: auto;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="chk-cell"><input type="checkbox" <?= $chkAuto ?>></td>
                            <td class="lbl-cell" style="padding-right: 20px !important;">Auto Anamnesa</td>
                            <td class="chk-cell"><input type="checkbox" <?= $chkAllo ?>></td>
                            <td class="lbl-cell">Allo Anamnesa</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="igd-table">
            <tr>
                <td style="width:28%">Keluhan Utama</td>
                <td>: <?= val($triase['keluhan_utama'] ?? null) ?></td>
            </tr>
            <tr>
                <td>Riwayat Penyakit Sekarang</td>
                <td>: <?= val($triase['riwayat_penyakit_sekarang'] ?? null) ?></td>
            </tr>
            <tr>
                <td>Riwayat Penyakit Dahulu</td>
                <td>: <?= val($triase['riwayat_penyakit_pribadi'] ?? null) ?></td>
            </tr>
            <tr>
                <td>Riwayat Pengobatan</td>
                <td>: <?= val($triase['riwayat_pengobatan'] ?? null) ?></td>
            </tr>
            <tr>
                <td>Riwayat Alergi</td>
                <td>: <?= val($triase['riwayat_alergi'] ?? null) ?></td>
            </tr>
        </table>

        <table class="igd-table">
            <tr>
                <th colspan="2">DIAGNOSA</th>
            </tr>
            <tr>
                <td style="width:25%">Diagnosa Kerja</td>
                <td>
                    <?php
                    $diagKerja = trim(($triase['icd_code'] ?? '') . ' - ' . ($triase['icd10'] ?? ''), ' -');
                    echo val($diagKerja);
                    ?>
                </td>
            </tr>
            <tr>
                <td>Diagnosa Banding</td>
                <td><?= val($triase['diagnosa_sekunder'] ?? null) ?></td>
            </tr>
        </table>

        <!-- 
          Trik Table Split: Membungkus blok Skala Nyeri agar 
          Judul, Wajah, dan Nilainya tidak terpisahkan ke halaman berbeda.
        -->
        <div style="page-break-inside: avoid;">
            <div class="triase-section">Skala Nyeri</div>
            <table class="pain-table">
                <tr>
                    <?php for ($i = 0; $i <= 10; $i++): ?>
                        <td>
                            <div class="pain-box <?= $i === $skalaNyeri ? 'pain-selected' : '' ?>">
                                <?= getPainFaceSvg($i) ?>
                                <span style="display: block; text-align: center; font-size: 10pt; margin-top: 8px; color: #555;"><?= $i ?></span>
                            </div>
                        </td>
                    <?php endfor; ?>
                </tr>
            </table>

            <table class="triase-table" style="margin-top:0;">
                <tr>
                    <td>Nilai Nyeri : <b><?= $skalaNyeri ?></b> / 10</td>
                </tr>
            </table>
        </div>

        <table class="igd-table">
            <tr>
                <th style="width:20%">Terapi</th>
                <td><?= nl2br(val($terapii)) ?></td>
            </tr>
        </table>

        <table class="igd-table">
            <tr>
                <th style="width:20%">Perawatan Lanjutan</th>
                <td>
                    <table class="micro-layout-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="chk-cell"><input type="checkbox" checked="checked"></td>
                            <td class="lbl-cell">Rawat Inap</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 
          Trik Table Split: Membungkus elemen blok terakhir (Catatan Tambahan & TTD) 
          ke dalam div page-break-inside avoid agar tidak terpisahkan ke halaman baru sendirian.
        -->
        <div style="page-break-inside: avoid;">
            <div class="triase-section">Catatan Tambahan</div>
            <table class="triase-table">
                <tr>
                    <td style="height: 60px; vertical-align: top;">
                        <?= val($triase['catatan'] ?? null) ?>
                    </td>
                </tr>
            </table>

            <div class="igd-ttd">
                Dokter<br><br>
                <?php
                $ttdFile = $triase['signature_user'] ?? '';
                $ttdPath = '../../../uploads/ttd_faskes/' . $ttdFile;
                $imgSrc  = (!empty($ttdFile) && file_exists($ttdPath)) ? $ttdPath : '../../../uploads/ttd/default.png';
                ?>
                <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>" alt="Tanda Tangan"><br>
                <b><?= val($triase['id_doctor'] ?? null) ?></b>
            </div>
        </div>

        <div class="triase-noprint" style="text-align:center; margin-top:30px; margin-bottom:20px;">
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