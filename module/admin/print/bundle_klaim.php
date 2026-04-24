<?php
require '../../../vendor/autoload.php';
require '../../../database/connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

// === TEMP DIR ===
$tempDir = __DIR__ . '/temp_pdf/';
if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
if (!is_writable($tempDir)) die('Folder temp tidak bisa ditulis: ' . $tempDir);

$fontDir = $tempDir . 'fonts/';
if (!is_dir($fontDir)) mkdir($fontDir, 0777, true);

// === CEK EKG & TRIASE ===
$checkekg = mysqli_query($koneksi, "SELECT id_ekg FROM ekg_results 
    WHERE visit_ID='$visit' AND nomor_rm='$rm' ORDER BY id_ekg DESC LIMIT 1");
$dataekg = mysqli_fetch_array($checkekg);

$checktriase = mysqli_query($koneksi, "SELECT * FROM pasien_triase 
    WHERE visit_ID='$visit' AND nomor_rm='$rm' ORDER BY id_triase DESC LIMIT 1");
$datatriase = mysqli_fetch_array($checktriase);

$files = [
    "formulir_dokumen.php",
    "formulir_pasien.php",
    "formulir_triase.php",
    "formulir_pengantar_ranap.php",
    "formulir_cpo.php",
    "formulir_cppt.php",
    "formulir_lab.php",
    "formulir_resume_v2.php",
    "formulir_sep.php",
    "formulir_fkpp.php"
];

if (!$dataekg)    $files = array_filter($files, fn($f) => $f !== "formulir_ekg.php");
if (!$datatriase) $files = array_filter($files, fn($f) => $f !== "formulir_triase.php");

$tempFiles = [];

// === STEP 1: Render tiap formulir jadi PDF terpisah ===
foreach ($files as $file) {
    if (!file_exists($file)) {
        error_log("File tidak ditemukan: $file");
        continue;
    }

    ob_start();
    include $file;
    $htmlSatu = ob_get_clean();

    $wrappedHtml = "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
@page { size: A4; margin: 15mm 20mm; }
html, body { margin: 0; padding: 0; font-family: 'Times New Roman', serif; }
table { border-collapse: collapse; width: 100%; }
td, th { padding: 4px; }
</style>
</head>
<body>
$htmlSatu
</body>
</html>";

    try {
        $opt = new Options();
        $opt->set('isHtml5ParserEnabled', true);
        $opt->set('isRemoteEnabled', true);
        $opt->set('defaultFont', 'times');
        $opt->set('fontDir', $fontDir);
        $opt->set('fontCache', $fontDir);
        $opt->set('tempDir', $tempDir);

        $dom = new Dompdf($opt);
        $dom->loadHtml($wrappedHtml);
        $dom->setPaper('A4', 'portrait');
        $dom->render();

        $tempPath = $tempDir . 'form_' . pathinfo($file, PATHINFO_FILENAME) . '_' . uniqid() . '.pdf';
        $written  = file_put_contents($tempPath, $dom->output());

        if ($written === false) {
            error_log("Gagal tulis: $tempPath");
            continue;
        }

        $tempFiles[] = $tempPath;

    } catch (Exception $e) {
        error_log("SKIP render $file: " . $e->getMessage());
        continue;
    }
}

// === STEP 2: Scan PDF dokumen dari uploads/dokumen berdasarkan visit ID ===
$uploadDir = __DIR__ . '/../../../uploads/dokumen/';
if (is_dir($uploadDir)) {
    $allFiles = scandir($uploadDir);
    foreach ($allFiles as $filename) {
        if (
            strpos($filename, $visit) !== false &&
            pathinfo($filename, PATHINFO_EXTENSION) === 'pdf'
        ) {
            $fullPath = $uploadDir . $filename;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                $tempFiles[] = $fullPath;
            }
        }
    }
}

// === STEP 3: Merge semua PDF via FPDI ===
$pdf = new Fpdi();

foreach ($tempFiles as $filePath) {
    try {
        $pageCount = $pdf->setSourceFile($filePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }
    } catch (Exception $e) {
        error_log("Gagal merge: " . basename($filePath) . " - " . $e->getMessage());
        continue;
    }
}

// === STEP 4: Output ke browser ===
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Bundle_' . $visit . '.pdf"');
echo $pdf->Output('S');

// === STEP 5: Bersihkan file temp formulir ===
foreach ($tempFiles as $filePath) {
    if (strpos($filePath, $tempDir) === 0 && file_exists($filePath)) {
        unlink($filePath);
    }
}

exit;