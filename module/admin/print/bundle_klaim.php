<?php

/**
 * Bundle Klaim PDF
 *
 * Menggabungkan semua formulir klaim + file PDF upload menjadi satu PDF
 * yang ditampilkan inline di browser.
 */

require '../../../vendor/autoload.php';
require_once '../../../database/connect.php';
require_once '../../admin/getdataclinic.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

// =============================================================================
// PARAMETER & VALIDASI
// =============================================================================

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

if (empty($visit) || empty($rm)) {
    die('Parameter tidak lengkap — tambahkan ?no=VISIT_ID&rm=NOMOR_RM');
}

// =============================================================================
// TEMP DIRECTORY
// =============================================================================

$tempBase = '/tmp';
if (!is_dir($tempBase) || !is_writable($tempBase)) {
    $tempBase = rtrim(sys_get_temp_dir(), '/');
}
if (!is_dir($tempBase) || !is_writable($tempBase)) {
    $tempBase = rtrim(__DIR__ . '/../../../storage/tmp', '/');
}
$tempDir = $tempBase . '/medisafe_pdf_' . md5(__DIR__) . '/';
$fontDir = $tempDir . 'fonts/';

if (!is_dir($tempDir)) {
    if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
        die('Gagal membuat folder temporary sistem.');
    }
}
if (!is_dir($fontDir)) {
    if (!mkdir($fontDir, 0755, true) && !is_dir($fontDir)) {
        die('Gagal membuat folder font temporary.');
    }
}

$tempPaths = [];
register_shutdown_function(function () use (&$tempPaths, $tempDir) {
    foreach ($tempPaths as $path) {
        if (is_file($path)) @unlink($path);
    }
    foreach (glob($tempDir . '*.pdf') as $stale) {
        if (is_file($stale) && filemtime($stale) < time() - 3600) {
            @unlink($stale);
        }
    }
});

// =============================================================================
// CEK DATA KONDISIONAL
// =============================================================================

$dataekg = mysqli_fetch_array(mysqli_query(
    $koneksi,
    "SELECT id_ekg FROM ekg_results
     WHERE visit_ID='$visit' AND nomor_rm='$rm'
     ORDER BY id_ekg DESC LIMIT 1"
));

$datatriase = mysqli_fetch_array(mysqli_query(
    $koneksi,
    "SELECT id_triase FROM pasien_triase
     WHERE visit_ID='$visit' AND nomor_rm='$rm'
     ORDER BY id_triase DESC LIMIT 1"
));

$visitInfo = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT id_patient, file_spp, file_fkpp FROM pasien_visit WHERE visit_ID='$visit' AND id_customer='$id_customer' LIMIT 1"
));

$debugMode  = !empty($_GET['debug']);
$fpdiErrors = [];

// =============================================================================
// HELPER: Flatten PDF ke format kompatibel FPDI free parser
// Coba ghostscript dulu, fallback ke Imagick (PDF → PNG per halaman → PDF baru)
// =============================================================================

function flattenPdfForFpdi(string $inputPath, string $tempDir, array &$tempPaths): string
{
    $outputPath = $tempDir . 'flat_' . md5($inputPath) . '.pdf';
    if (is_file($outputPath)) {
        return $outputPath;
    }

    // Method 1: Ghostscript
    $canExec = function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    if ($canExec) {
        $gsBin = '';
        foreach (['/opt/homebrew/bin/gs', '/usr/local/bin/gs', '/usr/bin/gs'] as $c) {
            if (@is_executable($c)) { $gsBin = $c; break; }
        }
        if ($gsBin) {
            exec($gsBin . ' -dBATCH -dNOPAUSE -dQUIET -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
                . ' -dPrinted=false'
                . ' -sOutputFile=' . escapeshellarg($outputPath)
                . ' ' . escapeshellarg($inputPath) . ' 2>/dev/null', $out, $rc);
            if ($rc === 0 && is_readable($outputPath)) {
                $tempPaths[] = $outputPath;
                return $outputPath;
            }
        }
    }

    // Method 2: Imagick (fallback ketika gs tidak tersedia)
    if (extension_loaded('imagick')) {
        try {
            /** @phpstan-ignore-next-line */
            $im = new \Imagick();
            $im->setResolution(200, 200);
            $im->readImage($inputPath);
            $fpdf      = new \setasign\Fpdi\Fpdi();
            $pageCount = $im->getNumberImages();
            for ($i = 0; $i < $pageCount; $i++) {
                $im->setIteratorIndex($i);
                $page = $im->getImage();
                $page->setImageFormat('png');
                $geo  = $page->getImageGeometry();
                $wMm  = $geo['width']  * 25.4 / 200;
                $hMm  = $geo['height'] * 25.4 / 200;
                $imgPath = $tempDir . 'img_' . md5($inputPath) . '_' . $i . '.png';
                $page->writeImage($imgPath);
                $tempPaths[] = $imgPath;
                $page->destroy();
                $fpdf->AddPage($wMm > $hMm ? 'L' : 'P', [$wMm, $hMm]);
                $fpdf->Image($imgPath, 0, 0, $wMm, $hMm);
            }
            $im->clear();
            $im->destroy();
            if (file_put_contents($outputPath, $fpdf->Output('S')) !== false) {
                $tempPaths[] = $outputPath;
                return $outputPath;
            }
        } catch (Exception $e) {
            error_log('[bundle_klaim] Imagick flatten gagal: ' . $e->getMessage());
        }
    }

    return $inputPath;
}

// =============================================================================
// HELPER: Render formulir PHP → PDF sementara via dompdf
// =============================================================================

function renderFormulirToPdf(string $file, string $tempDir, string $fontDir, string $siteRoot): ?string
{
    extract($GLOBALS);

    if (!file_exists($file)) {
        error_log("[bundle_klaim] File tidak ditemukan: $file");
        return null;
    }

    ob_start();
    include $file;
    $html = ob_get_clean();

    preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches);
    $extractedStyles = implode("\n", $matches[1] ?? []);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

    $pdfOnlyCss = '
        button, .btn, input[type="button"], input[type="submit"],
        .cpo-noprint, .noprint, .no-print, .dokmulti-noprint, .resume-noprint, .lab-noprint,
        .cetak-semua, .print-btn, svg { display: none !important; }
        iframe { display: none !important; }
    ';

    $fileUri = rtrim($siteRoot, '/') . '/';
    $html = preg_replace('/(src|href)=["\']\.\.\/\.\.\/\.\.\/(.*?)["\']/i', '$1="' . $fileUri . '$2"', $html);
    $html = preg_replace('/(src|href)=["\']\.\.\/\.\.\/(.*?)["\']/i', '$1="' . $fileUri . 'module/$2"', $html);
    $html = preg_replace('/(src|href)=["\']\.\.\/(.*?)["\']/i', '$1="' . $fileUri . 'module/admin/$2"', $html);

    if (stripos($html, '<!DOCTYPE') === false && stripos($html, '<html') === false) {
        $html = preg_replace('/<body(.*?)>/i', '<div$1>', $html);
        $html = str_ireplace('</body>', '</div>', $html);

        $html = "<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<style>
@page { size: A4; margin: 15mm 20mm; }
html, body { margin: 0; padding: 0; font-family: 'Times New Roman', serif; font-size: 11pt; }
table { border-collapse: collapse; width: 100%; }
$extractedStyles
$pdfOnlyCss
</style>
</head>
<body>$html</body>
</html>";
    } else {
        $html = str_ireplace('</head>', "<style>$extractedStyles \n $pdfOnlyCss</style></head>", $html);
    }

    $html = preg_replace('/window\s*\.\s*print\s*\(\s*\)\s*;?/', '', $html);

    try {
        $opt = new Options();
        $opt->set('isHtml5ParserEnabled', true);
        $opt->set('isRemoteEnabled', true);
        $opt->set('defaultFont', 'times');
        $opt->set('tempDir', $tempDir);
        $opt->set('fontDir', $fontDir);
        $opt->set('fontCache', $fontDir);
        $opt->set('chroot', $siteRoot);

        $dom = new Dompdf($opt);
        $dom->setBasePath($siteRoot);
        $dom->loadHtml($html);
        $dom->setPaper('A4', 'portrait');
        $dom->render();

        $tempPath = $tempDir . 'form_' . pathinfo($file, PATHINFO_FILENAME) . '_' . uniqid() . '.pdf';
        file_put_contents($tempPath, $dom->output());

        return $tempPath;
    } catch (Exception $e) {
        error_log("[bundle_klaim] Gagal render $file: " . $e->getMessage());
        return null;
    }
}

// =============================================================================
// SUSUN DAFTAR FORMULIR PHP
// =============================================================================

if ($visitInfo) {
    $_GET['id_patient'] = $visitInfo['id_patient'];
}

$phpForms = [
    'formulir_dokumen.php',
    'formulir_pasien.php',
];

if ($datatriase) {
    $phpForms[] = 'formulir_triase.php';
}

$phpForms = array_merge($phpForms, [
    'formulir_pengantar_ranap.php',
    'formulir_cpo.php',
    'formulir_cppt.php',
    'formulir_lab.php',
    'formulir_resume_v2.php',
]);

if ($dataekg) {
    $phpForms[] = 'formulir_ekg.php';
}

// =============================================================================
// RENDER FORMULIR → PDF SEMENTARA
// =============================================================================

$siteRoot = realpath(__DIR__ . '/../../../') . '/';
$allPdfs = [];

foreach ($phpForms as $file) {
    $path = renderFormulirToPdf($file, $tempDir, $fontDir, $siteRoot);
    if ($path) {
        $allPdfs[]   = $path;
        $tempPaths[] = $path;
    }
}

// =============================================================================
// TAMBAHKAN SEP & FKPP (file_spp / file_fkpp dari pasien_visit)
// =============================================================================

$uploadDir = realpath(__DIR__ . '/../../../uploads/dokumen/') . '/';

foreach (['file_spp' => 'SEP', 'file_fkpp' => 'FKPP'] as $col => $label) {
    $filename = $visitInfo[$col] ?? '';
    if (!empty($filename)) {
        $fullPath = $uploadDir . $filename;
        if (is_readable($fullPath)) {
            $allPdfs[] = flattenPdfForFpdi($fullPath, $tempDir, $tempPaths);
        } else {
            error_log("[bundle_klaim] $label tidak ditemukan: $fullPath");
        }
    }
}

// =============================================================================
// MERGE SEMUA PDF MENGGUNAKAN FPDI
// =============================================================================

if (empty($allPdfs)) {
    die('Tidak ada dokumen yang dapat digabungkan untuk visit: ' . htmlspecialchars($visit));
}

$pdf = new Fpdi();

foreach ($allPdfs as $filePath) {
    try {
        $pageCount = $pdf->setSourceFile($filePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }
    } catch (Exception $e) {
        $msg = basename($filePath) . ': ' . $e->getMessage();
        error_log("[bundle_klaim] Gagal merge $msg");
        $fpdiErrors[] = $msg;
    }
}

// =============================================================================
// OUTPUT PDF KE BROWSER
// =============================================================================

if ($debugMode) {
    echo '<pre style="font-family:monospace;font-size:13px;padding:20px">';
    echo "=== BUNDLE KLAIM DEBUG ===\n\n";
    echo "visit       : $visit\n";
    echo "rm          : $rm\n";
    echo "id_customer : $id_customer\n\n";
    echo "--- visitInfo ---\n"; print_r($visitInfo);
    echo "\nfile_spp    : " . ($visitInfo['file_spp']  ?? '(null/kosong)') . "\n";
    echo "file_fkpp   : " . ($visitInfo['file_fkpp'] ?? '(null/kosong)') . "\n";
    echo "\nuploadDir   : $uploadDir\n";
    $gsCheck = '';
    foreach (['/opt/homebrew/bin/gs', '/usr/local/bin/gs', '/usr/bin/gs'] as $g) {
        if (is_executable($g)) { $gsCheck = $g; break; }
    }
    echo "\ngs binary   : " . ($gsCheck ?: '(tidak ditemukan)') . "\n";
    echo "\n--- allPdfs (" . count($allPdfs) . ") ---\n";
    foreach ($allPdfs as $p) echo "  $p\n";
    echo "\n--- FPDI errors (" . count($fpdiErrors) . ") ---\n";
    echo $fpdiErrors ? implode("\n", $fpdiErrors) . "\n" : "(tidak ada error)\n";
    echo '</pre>';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Bundle_Klaim_' . $rm . '_' . $visit . '.pdf"');
echo $pdf->Output('S');
exit;
