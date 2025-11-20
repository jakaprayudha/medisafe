<?php
$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$files = [
   "formulir_sep.php",
   "formulir_triase.php",
   "formulir_cppt.php",
   "formulir_inout_ranap.php",
   "formulir_instruksi.php",
   "formulir_keterangan_ranap.php",
   "formulir_lbp.php",
   "formulir_pernyataan.php",
   "formulir_persalinan.php",
   "formulir_resume.php",
   "formulir_surat_persetujuan.php",
   "formulir_status_kb.php",
   "formulir_kontrasepsi.php",
   "formulir_peserta_kb.php",
   "formulir_history_treatment.php",
   "formulir_lab.php",
   "formulir_cpo.php",
   "formulir_spgigi.php",
   "formulir_catatan_ibuhamil.php",
   "formulir_trisemester3.php",
   "formulir_catatan_persalinan.php",
   "formulir_partograf.php",
   "formulir_skrining_hipotiroid.php",
   "formulir_skl.php",
   "formulir_usg.php",
   "formulir_ekg.php",
   "formulir_dokumen.php"
];

echo "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<link rel='shortcut icon' type='image/png' href='../../../assets/images/logos/icon_medisafe.png' />
<style>
@page { margin: 0; size: A4; }
body { margin:0; padding:0; }
.page-break { page-break-after: always; }
</style>
</head>
<body>
";

foreach ($files as $file) {

   if (!file_exists($file)) {
      echo "<p style='color:red'>File tidak ditemukan: $file</p>";
      continue;
   }

   echo "<div class='page-break'>";
   include $file;  // <-- INI KUNCI TANPA IFRAME
   echo "</div>";
}

echo "</body></html>";
