<?php
$title = "Formulir Resume Medis";
$subtitle = "";
?>

<style>
   .resume-container {
      max-width: 760px;
      margin: auto;
   }

   .resume-header {
      text-align: center;
      margin-bottom: 12px;
   }

   .resume-title {
      font-weight: bold;
      text-transform: uppercase;
      font-size: 13pt;
      margin: 2px 0;
   }

   .resume-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 11pt;
   }

   .resume-table td,
   .resume-table th {
      border: 1px solid #000;
      padding: 6px;
      vertical-align: top;
   }

   .resume-label {
      font-weight: bold;
      width: 22%;
      white-space: nowrap;
   }

   .resume-sign-area {
      margin-top: 35px;
      text-align: right;
   }

   .resume-doc-sign {
      margin-top: 45px;
      display: inline-block;
      text-align: center;
   }

   .resume-doc-line {
      border-top: 1px solid #000;
      width: 220px;
      padding-top: 4px;
      font-weight: bold;
      font-size: 11pt;
   }
</style>

<div class="resume-container">

   <?php include 'kopsurat.php'; ?>

   <div class="resume-header">
      <div class="resume-title">Resume Medis</div>
   </div>

   <table class="resume-table">
      <tr>
         <td class="resume-label">Nama Pasien</td>
         <td id="rm_name"></td>
         <td class="resume-label">Tanggal Masuk</td>
         <td id="rm_tgl_masuk"></td>
      </tr>

      <tr>
         <td class="resume-label">Jenis Kelamin</td>
         <td id="rm_jk"></td>
         <td class="resume-label">Tanggal Keluar</td>
         <td id="rm_tgl_keluar"></td>
      </tr>

      <tr>
         <td class="resume-label">No. Rekam Medis</td>
         <td id="rm_no_rm"></td>
         <td class="resume-label">Ruang Rawat</td>
         <td id="rm_ruang"></td>
      </tr>

      <tr>
         <td class="resume-label">Tanggal Lahir</td>
         <td id="rm_tgl_lahir"></td>
         <td class="resume-label">Kamar / Kelas</td>
         <td id="rm_kelas"></td>
      </tr>

      <tr>
         <td class="resume-label">Cara Bayar</td>
         <td id="rm_cara_bayar"></td>
         <td class="resume-label">DPJP</td>
         <td id="rm_dpjp"></td>
      </tr>
   </table>

   <table class="resume-table">

      <tr>
         <td class="resume-label">Diagnosa Masuk</td>
         <td colspan="3">
            LBP ec spondylosis dd / HNP + HT susp parotitis (L)
         </td>
      </tr>

      <tr>
         <td class="resume-label">Indikasi Rawat Inap</td>
         <td colspan="3">Lemas</td>
      </tr>

      <tr>
         <td class="resume-label">Pemeriksaan Fisik</td>
         <td colspan="3">
            Tanggal & jam : 2025-12-31 12:05<br>
            Kesadaran : Compos mentis<br>
            Tekanan darah : 178 / 106 mmHg<br>
            Nadi : 123 x/menit<br>
            RR : 20 x/menit<br>
            Suhu : 36.1 °C<br>
            Skala nyeri : 8 – 9
         </td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Utama</td>
         <td colspan="3">
            Cerebral infarction due to thrombosis of cerebral arteries
         </td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Sekunder</td>
         <td colspan="3">
            1. Spondylosis
         </td>
      </tr>

      <tr>
         <td class="resume-label">Terapi Selama di Rumah Sakit</td>
         <td colspan="3" style="white-space:pre-line">
            ABBOCATH No.22
            Alkohol swab
            Amitriptyline 25 mg
            Amlodipine 10 mg
            Atorvastatin 20 mg
            Betahistine 6 mg
            Candesartan 8 mg
            Eperisone HCl 50 mg
            Gabapentin 300 mg
            Ketorolac 30 mg inj
            Mecobalamin 500 mcg
            Ringer laktat 500 ml
            Tindakan : pasang infus
         </td>
      </tr>

      <tr>
         <td class="resume-label">Alergi Obat</td>
         <td colspan="3">Tidak ada</td>
      </tr>

      <tr>
         <td class="resume-label">Terapi Pulang</td>
         <td colspan="3" style="white-space:pre-line">
            Natrium diklofenak 50 mg 2x1
            Gabapentin 100 mg 2x1
            Eperisone 50 mg 2x1
            Mecobalamin 500 mg 2x1
            Amitriptyline 25 mg 1x1 malam
            Amlodipine 10 mg 1x1 pagi
            Candesartan 8 mg 1x1 malam
            Paracetamol 3x1
            Betahistine 6 mg 3x1
            Aspilet 80 mg 1x1
         </td>
      </tr>

      <tr>
         <td class="resume-label">Kondisi Pasien Saat Pulang</td>
         <td colspan="3">Membaik</td>
      </tr>

      <tr>
         <td class="resume-label">Cara Keluar RS</td>
         <td colspan="3">Izin Dokter</td>
      </tr>

      <tr>
         <td class="resume-label">Rencana Tindak Lanjut</td>
         <td colspan="3">
            Kontrol ulang ke poliklinik – 09-01-2026
         </td>
      </tr>

   </table>

   <div class="resume-sign-area">
      Lubuk Pakam, 05-01-2026<br>
      Dokter yang Merawat
      <div class="resume-doc-sign">
         <div class="resume-doc-line" id="doctor_name">

         </div>
      </div>
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");

      if (!no || !rm) return;

      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(d => {

            if (!d) return;

            // ========== IDENTITAS PASIEN ==========
            document.getElementById("rm_name").textContent = d.patient_name ?? "";
            document.getElementById("rm_no_rm").textContent = d.nomor_rm ?? "";

            document.getElementById("rm_jk").textContent =
               d.patient_gender ?? "";

            document.getElementById("rm_tgl_lahir").textContent =
               d.patient_datebirth ?? "";

            // ========== KUNJUNGAN ==========
            document.getElementById("rm_tgl_masuk").textContent =
               d.visit_date && d.visit_time ?
               `${d.visit_date} ${d.visit_time}` :
               (d.visit_date ?? "");

            document.getElementById("rm_tgl_keluar").textContent =
               d.visit_out ?? "";

            document.getElementById("rm_ruang").textContent =
               d.source_hub ?? ""; // karena di JSON tidak ada ruang_rawat

            document.getElementById("rm_kelas").textContent =
               ""; // tidak ada field kelas di JSON

            document.getElementById("rm_cara_bayar").textContent =
               d.patient_bpjs ? "BPJS" : "";

            // ========== DOKTER ==========
            document.getElementById("rm_dpjp").textContent =
               d.doctor_name ?? "";

            document.getElementById("doctor_name").textContent =
               d.doctor_name ?? "";

         })
         .catch(err => console.error(err));

   });
</script>