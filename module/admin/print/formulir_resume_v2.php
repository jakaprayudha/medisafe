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
         <td colspan="3" id="rs_diagnosa_masuk">

         </td>
      </tr>

      <tr>
         <td class="resume-label">Indikasi Rawat Inap</td>
         <td colspan="3" id="rs_indikasi"></td>
      </tr>

      <tr>
         <td class="resume-label">Pemeriksaan Fisik</td>
         <td colspan="3" id="rs_fisik">
         </td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Utama</td>
         <td colspan="3" id="rs_diagnosa_utama">

         </td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Sekunder</td>
         <td colspan="3" id="rs_diagnosa_sekunder">
         </td>
      </tr>

      <tr>
         <td class="resume-label">Terapi Selama di Rumah Sakit</td>
         <td colspan="3" style="white-space:pre-line" id="rs_terapi_rs">
         </td>
      </tr>

      <tr>
         <td class="resume-label">Alergi Obat</td>
         <td colspan="3" id="rs_alergi"></td>
      </tr>

      <tr>
         <td class="resume-label">Terapi Pulang</td>
         <td colspan="3" style="white-space:pre-line" id="rs_terapi_pulang">

         </td>
      </tr>

      <tr>
         <td class="resume-label">Kondisi Pasien Saat Pulang</td>
         <td colspan="3" id="rs_kondisi_pulang"></td>
      </tr>

      <tr>
         <td class="resume-label">Cara Keluar RS</td>
         <td colspan="3" id="rs_cara_keluar">Izin Dokter</td>
      </tr>

      <tr>
         <td class="resume-label">Rencana Tindak Lanjut</td>
         <td colspan="3" id="rs_rencana">
         </td>
      </tr>

   </table>

   <div class="resume-sign-area">
      Tanjung Morawa, 11-12-2025<br>
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

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");

      if (!no || !rm) return;

      /* =============================
         DATA PASIEN
      ============================= */

      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(d => {

            if (!d) return;

            document.getElementById("rm_name").textContent = d.patient_name ?? "";
            document.getElementById("rm_no_rm").textContent = d.nomor_rm ?? "";
            document.getElementById("rm_jk").textContent = d.patient_gender ?? "";
            document.getElementById("rm_tgl_lahir").textContent = d.patient_datebirth ?? "";

            document.getElementById("rm_tgl_masuk").textContent =
               d.visit_date && d.visit_time ?
               `${d.visit_date} ${d.visit_time}` :
               (d.visit_date ?? "");

            document.getElementById("rm_tgl_keluar").textContent = d.visit_out ?? "";
            document.getElementById("rm_ruang").textContent = d.source_hub ?? "";
            document.getElementById("rm_kelas").textContent = "";
            document.getElementById("rm_cara_bayar").textContent = d.patient_bpjs ? "BPJS" : "";
            document.getElementById("rm_dpjp").textContent = d.doctor_name ?? "";
            document.getElementById("doctor_name").textContent = d.doctor_name ?? "";

         });

      /* =============================
         DATA RESUME MEDIS
      ============================= */

      fetch(`getresume.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(resp => {

            if (!resp || resp.status !== "success") return;

            const r = resp.data;

            document.getElementById("rs_diagnosa_utama").textContent =
               r.diagnosa ?? "";

            document.getElementById("rs_indikasi").textContent =
               r.indikasi_dirawat ?? "";

            document.getElementById("rs_fisik").textContent =
               r.pemeriksaan_fisik ?? "";

            document.getElementById("rs_diagnosa_sekunder").textContent =
               r.diagnosa_sekunder ?? "";

            document.getElementById("rs_terapi_rs").textContent =
               r.terapi_rs ?? "";

            document.getElementById("rs_terapi_pulang").textContent =
               r.terapi_pulang ?? "";

            document.getElementById("rs_kondisi_pulang").textContent =
               r.kondisi_pulang ?? "";

            document.getElementById("rs_cara_keluar").textContent =
               r.cara_keluar_rs ?? "";

            document.getElementById("rs_rencana").textContent =
               r.rencana_tindak_lanjut ?? "";

         });

   });
</script>