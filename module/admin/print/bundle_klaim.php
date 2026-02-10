<?php
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$checkekg = mysqli_query($koneksi, "SELECT id_ekg FROM ekg_results WHERE visit_ID='$visit' AND nomor_rm='$rm' ORDER BY id_ekg DESC LIMIT 1");
$dataekg = mysqli_fetch_array($checkekg);

$checktriase = mysqli_query($koneksi, "SELECT * FROM pasien_triase WHERE visit_ID='$visit' AND nomor_rm='$rm' ORDER BY id_triase DESC LIMIT 1");
$datatriase = mysqli_fetch_array($checktriase);

$files = [
   "formulir_dokumen.php",
   "formulir_triase.php",
   "formulir_pengantar_ranap.php",
   // "formulir_inout_ranap.php",
   "formulir_cpo.php",
   "formulir_cppt.php",
   "formulir_lab.php",
   // "formulir_resume.php",
   "formulir_resume_v2.php",
   // "formulir_lbp.php",
   "formulir_sep.php",
   "formulir_fkpp.php"
];

if (!$dataekg) {
   $files = array_filter($files, fn($f) => $f !== "formulir_ekg.php");
}
if (!$datatriase) {
   $files = array_filter($files, fn($f) => $f !== "formulir_triase.php");
}

echo "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<link rel='shortcut icon' href='../../../assets/images/logos/icon_medisafe.png'>

<style>
/* ===== PRINT RESET ===== */
@page {
   size: A4;
   margin: 0; /* BIARKAN 0 */
}

html, body {
   margin: 0;
   padding: 0;
   font-family: 'Times New Roman', serif;
}

/* ===== INI KUNCI UTAMA ===== */
.page-wrapper {
   padding: 15mm 20mm;  /* TOP-BOTTOM | LEFT-RIGHT */
   box-sizing: border-box;
}

/* ===== PAGE BREAK ===== */
.page-break {
   page-break-after: always;
}
.page-break:last-child {
   page-break-after: auto;
}
</style>
</head>
<body>
";

foreach ($files as $file) {

   if (!file_exists($file)) {
      echo "<p style='color:red'>File tidak ditemukan: $file</p>";
      continue;
   }

   echo "<div class='page-wrapper page-break'>";
   include $file;
   echo "</div>";
}

echo "</body></html>";
