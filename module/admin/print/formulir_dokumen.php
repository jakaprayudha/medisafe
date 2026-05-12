<?php
require_once '../../../database/connect.php';
$id_customer = $_SESSION['id_customer'] ?? null;
$no          = $_GET['no'] ?? null;
$rm          = $_GET['rm'] ?? null;
// ============================================================
// QUERY DOKUMEN + DATA PASIEN
// ============================================================
$docs = [];
if ($no && $id_customer) {
    $query = "
        SELECT pd.*,
               mp.patient_name, mp.patient_datebirth, mp.patient_address
        FROM pasien_dokumen pd
        LEFT JOIN pasien_visit pv ON pd.visit_ID = pv.visit_ID
        LEFT JOIN ms_patient   mp ON pv.id_patient = mp.id_patient
        WHERE pv.id_customer = ?
          AND pv.visit_ID    = ?
          AND (
                (pd.jenis_dokumen IN ('FOTO_PERAWATAN','FOTO_PASIEN') AND pd.visit_ID = ?)
                OR pd.jenis_dokumen NOT IN ('FOTO_PERAWATAN','FOTO_PASIEN')
              )
    ";

    $stmt = mysqli_prepare($koneksi, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $id_customer, $no, $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $docs = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt);
    }
}

$fotoPerawatan = array_values(array_filter($docs, fn($d) => $d['jenis_dokumen'] === 'FOTO_PERAWATAN'));
$otherDocs     = array_values(array_filter($docs, fn($d) => $d['jenis_dokumen'] !== 'FOTO_PERAWATAN'));

$perPage        = 4;
$perawatanPages = count($fotoPerawatan) > 0 ? (int)ceil(count($fotoPerawatan) / $perPage) : 0;
$totalPages     = count($otherDocs) + $perawatanPages;
$currentPage    = 1;

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseURL  = 'http://' . $httpHost . '/module/verify/';

if (!function_exists('qrImg')) {
    function qrImg(string $url): string
    {
        $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($url);
        return '<img src="' . htmlspecialchars($qrSrc, ENT_QUOTES) . '" class="dokmulti-qr-img" alt="QR Code">';
    }
}

session_start();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getData();
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Method tidak diizinkan.'
        ]);
        break;
}

function getData()
{
    global $koneksi;

    // 🔥 ambil session
    $id_customer = $_SESSION['id_customer'] ?? null;

    if (!$id_customer) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session tidak ditemukan'
        ]);
        exit;
    }

    // 🔥 PREPARE QUERY (WAJIB karena pakai ?)
    $stmt = $koneksi->prepare("SELECT 
         visit_inspection.*,
         pasien_visit.visit_ID,
         pasien_visit.patient_name_pcare,
         pasien_visit.id_doctor,
         ms_patient.nomor_rm,
         ms_patient.patient_gender,
         pasien_visit.id_poli,
         ms_patient.patient_place,
         ms_patient.patient_datebirth,
         pasien_visit.source_hub,
         pasien_visit.visit_status
      FROM visit_inspection 
      LEFT JOIN pasien_visit 
         ON pasien_visit.visit_ID = visit_inspection.id_visit 
      LEFT JOIN ms_patient 
         ON ms_patient.id_patient = pasien_visit.id_patient  
      WHERE pasien_visit.id_customer = ? 
      GROUP BY visit_inspection.id_visit
      ORDER BY visit_inspection.id_inspection ASC
   ");

    if (!$stmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Prepare failed: ' . $koneksi->error
        ]);
        return;
    }

    // 🔥 bind param
    $stmt->bind_param("i", $id_customer);

    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => $data,
    ]);
}

if (!function_exists('fmtTgl')) {

    function fmtTgl(?string $tgl): string
    {
        if (empty($tgl)) return '-';
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $ts = strtotime($tgl);
        return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dokumentasi Perawatan</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        .dokmulti-wrap {
            width: 100%;
            max-width: 780px;
            margin: auto;
        }

        .dokmulti-page {
            border: 1px solid #000;
            padding: 15px;
            background: #fff;
            page-break-after: always;
            min-height: 980px;
            /* Minimal tinggi agar halaman terlihat konsisten */
        }

        .dokmulti-page.last-page {
            page-break-after: auto;
        }

        .dokmulti-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .dokmulti-subtitle {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 15px;
        }

        /* 
         * OPTIMASI GRID FOTO 2x2
         */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid-table td {
            width: 50%;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            height: 380px;
            /* Cukup aman untuk mencegah spillover ke halaman baru */
        }

        .grid-img {
            max-width: 100%;
            max-height: 320px;
            /* Batas tinggi yang dijamin muat untuk 2 baris dalam A4 */
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .grid-date {
            font-size: 10pt;
            color: #333;
            padding-top: 8px;
            font-weight: bold;
        }

        /* 
         * OPTIMASI FOTO TUNGGAL
         */
        .dokmulti-photo-box {
            width: 100%;
            border: 2px solid #000;
            margin: 15px 0;
            text-align: center;
            padding: 4px;
            vertical-align: middle;
            min-height: 480px;
        }

        .dokmulti-photo {
            max-width: 100%;
            max-height: 600px;
            width: auto;
            height: auto;
        }

        .dokmulti-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .dokmulti-table td {
            padding: 4px 3px;
            vertical-align: top;
        }

        .dokmulti-line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 200px;
        }

        /* 
         * FOOTER MENGGUNAKAN TABEL (Bukan absolute position)
         * Menjamin elemen selalu ada di bawah konten utama tanpa overlap.
         */
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
            border: none !important;
            padding: 0;
        }

        .page-num {
            font-size: 10pt;
            color: #555;
        }

        .dokmulti-qr-img {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .sign-table td {
            width: 50%;
            text-align: center;
            padding: 4px;
            vertical-align: bottom;
        }

        .sign-img {
            max-width: 160px;
            max-height: 70px;
            display: block;
            margin: 5px auto;
        }

        .sign-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 4px;
            display: inline-block;
            min-width: 180px;
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
            .dokmulti-noprint {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="dokmulti-wrap">

        <?php if (empty($docs)): ?>
            <div class="dokmulti-page last-page">
                <div style="text-align:center; padding:40px; color:#999; font-style: italic;">
                    Tidak ada data dokumentasi pasien yang tersedia.
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             FOTO PERAWATAN — 4 per halaman (grid 2x2)
             ============================================================ -->
        <?php for ($p = 0; $p < $perawatanPages; $p++):
            $slice      = array_slice($fotoPerawatan, $p * $perPage, $perPage);
            $isLastPage = ($p === $perawatanPages - 1) && empty($otherDocs);
        ?>
            <?php $qrVerify = $baseURL . 'dokumen.php?rm=' . urlencode($rm ?? ''); ?>
            <div class="dokmulti-page <?= $isLastPage ? 'last-page' : '' ?>">

                <div class="dokmulti-title">DOKUMENTASI PERAWATAN</div>
                <div class="dokmulti-subtitle">Semua Foto Dalam Perawatan</div>

                <table class="grid-table">
                    <?php
                    // Membangun baris berisi 2 foto secara berdampingan
                    for ($r = 0; $r < 2; $r++):
                        $col1 = $slice[$r * 2]     ?? null;
                        $col2 = $slice[$r * 2 + 1] ?? null;
                    ?>
                        <tr>
                            <td>
                                <?php if ($col1): ?>
                                    <img class="grid-img" src="../../../<?= htmlspecialchars($col1['foto_path'] ?? '', ENT_QUOTES) ?>" alt="Foto Perawatan">
                                    <div class="grid-date">Tanggal: <?= fmtTgl($col1['rilis'] ?? null) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($col2): ?>
                                    <img class="grid-img" src="../../../<?= htmlspecialchars($col2['foto_path'] ?? '', ENT_QUOTES) ?>" alt="Foto Perawatan">
                                    <div class="grid-date">Tanggal: <?= fmtTgl($col2['rilis'] ?? null) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>

                <table class="footer-table">
                    <tr>
                        <td style="text-align: left;">
                            <div class="page-num">Page <?= $currentPage++ ?> / <?= $totalPages ?></div>
                        </td>
                        <td style="text-align: right;">
                            <?= qrImg($qrVerify) ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endfor; ?>

        <!-- ============================================================
             DOKUMEN LAINNYA — 1 per halaman
             ============================================================ -->
        <?php
        $otherDocsLast = array_key_last($otherDocs);
        foreach ($otherDocs as $idx => $d):
            $qrVerifyDoc = $baseURL . 'verify_dokumen.php?id=' . urlencode($d['id'] ?? '') . '&rm=' . urlencode($rm ?? '') . '&no=' . urlencode($no ?? '');
            $isLastPage  = ($idx === $otherDocsLast);
        ?>
            <div class="dokmulti-page <?= $isLastPage ? 'last-page' : '' ?>">

                <div class="dokmulti-title">DOKUMEN KEPENDUDUKAN</div>
                <div class="dokmulti-subtitle"><?= htmlspecialchars(str_replace('_', ' ', $d['jenis_dokumen'] ?? ''), ENT_QUOTES) ?></div>

                <table class="dokmulti-table">
                    <tr>
                        <td style="width: 32%;">Nama Lengkap</td>
                        <td>: <span class="dokmulti-line"><?= htmlspecialchars($d['patient_name'] ?? '-', ENT_QUOTES) ?></span></td>
                    </tr>
                    <tr>
                        <td>Nomor Dokumen</td>
                        <td>: <span class="dokmulti-line"><?= htmlspecialchars($d['nomor_dokumen'] ?? '-', ENT_QUOTES) ?></span></td>
                    </tr>
                    <tr>
                        <td>Tanggal Lahir</td>
                        <td>: <span class="dokmulti-line"><?= htmlspecialchars(fmtTgl($d['patient_datebirth'] ?? null), ENT_QUOTES) ?></span></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: <span class="dokmulti-line"><?= htmlspecialchars($d['patient_address'] ?? '-', ENT_QUOTES) ?></span></td>
                    </tr>
                    <tr>
                        <td>Tanggal Upload</td>
                        <td>: <span class="dokmulti-line"><?= htmlspecialchars($d['tgl_upload'] ?? '-', ENT_QUOTES) ?></span></td>
                    </tr>
                </table>

                <div class="dokmulti-photo-box">
                    <?php if (!empty($d['foto_path'])): ?>
                        <img class="dokmulti-photo" src="../../../<?= htmlspecialchars($d['foto_path'], ENT_QUOTES) ?>" alt="Dokumen Identitas">
                    <?php else: ?>
                        <div style="height: 400px; line-height: 400px; color: #aaa; font-style: italic;">Tidak ada lampiran foto</div>
                    <?php endif; ?>
                </div>

                <table class="sign-table">
                    <tr>
                        <td>
                            Pemegang Dokumen<br>
                            <?php if (!empty($d['signature_path'])): ?>
                                <img class="sign-img" src="../../../uploads/ttd/<?= htmlspecialchars($d['signature_path'], ENT_QUOTES) ?>" alt="Tanda Tangan Pemegang">
                            <?php else: ?>
                                <br><br><br><br>
                            <?php endif; ?>
                            <div class="sign-line"><?= htmlspecialchars($d['patient_name'] ?? '-', ENT_QUOTES) ?></div>
                        </td>
                        <td>
                            Petugas Klinik<br>
                            <br><br><br><br>
                            <div class="sign-line">Petugas Klinik</div>
                        </td>
                    </tr>
                </table>

                <table class="footer-table">
                    <tr>
                        <td style="text-align: left;">
                            <div class="page-num">Page <?= $currentPage++ ?> / <?= $totalPages ?></div>
                        </td>
                        <td style="text-align: right;">
                            <?= qrImg($qrVerifyDoc) ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="dokmulti-noprint" style="text-align:center; margin-top:30px; margin-bottom: 20px;">
            <button onclick="window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                CETAK SEMUA
            </button>
        </div>

    </div>
</body>

</html>