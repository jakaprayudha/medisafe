<?php

$files = [
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
   "formulir_usg.php"
];

echo "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>

.wrapper {
   page-break-after: always;
   page-break-inside: avoid;
}

/* Tinggi IFRAME tergantung orientasi */
.portrait { height: 1350px; border:0; width:100%; }
.landscape { height: 950px; border:0; width:100%; }

</style>
</head>
<body>
";

foreach ($files as $file) {

   if (!file_exists($file)) {
      echo "<p style='color:red'>File tidak ditemukan: $file</p>";
      continue;
   }

   // baca isi file untuk deteksi landscape
   $content = file_get_contents($file);
   $isLandscape = preg_match('/landscape/i', $content);
   $class = $isLandscape ? "landscape" : "portrait";

   echo "
      <div class='wrapper'>
         <iframe class='$class' src='$file'></iframe>
      </div>
   ";
}

echo "</body></html>";
