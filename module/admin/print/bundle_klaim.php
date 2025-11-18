<?php
// daftar file HTML yang ingin digabung
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
   "formulir_lab.php"

];

// mulai file HTML
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Gabungan Cetakan</title></head><body>";

// loop semua file
foreach ($files as $file) {

   if (file_exists($file)) {
      echo "<div style='page-break-after: always;'>"; // supaya setiap file terpisah halaman saat print PDF
      echo file_get_contents($file);
      echo "</div>";
   } else {
      echo "<p style='color:red;'>File tidak ditemukan: $file</p>";
   }
}

// tutup HTML
echo "</body></html>";
