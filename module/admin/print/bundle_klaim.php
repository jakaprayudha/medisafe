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
   "formulir_ekg.php",
   "formulir_pernyataan.php",
   "formulir_pengantar_ranap.php",
   "formulir_keterangan_ranap.php",
   "formulir_surat_persetujuan.php",
   "formulir_inout_ranap.php",
   // "formulir_history_treatment.php",
   // "formulir_instruksi.php",
   "formulir_cpo.php",
   "formulir_cppt.php",
   "formulir_lab.php",
   "formulir_resume.php",
   "formulir_lbp.php",
   "formulir_sep.php",
   "formulir_fkpp.php"
   // "formulir_persalinan.php",
   // "formulir_status_kb.php",
   // "formulir_kontrasepsi.php",
   // "formulir_peserta_kb.php",
   // "formulir_spgigi.php",
   // "formulir_catatan_ibuhamil.php",
   // "formulir_trisemester3.php",
   // "formulir_catatan_persalinan.php",
   // "formulir_partograf.php",
   // "formulir_skrining_hipotiroid.php",
   // "formulir_skl.php",
   // "formulir_usg.php",
];


// JIKA TIDAK ADA DATA EKG → HAPUS FILE EKG
if (!$dataekg) {
   $files = array_filter($files, function ($file) {
      return $file !== "formulir_ekg.php";
   });
}
if (!$datatriase) {
   $files = array_filter($files, function ($file) {
      return $file !== "formulir_triase.php";
   });
}

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
