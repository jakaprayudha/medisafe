<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';
?>
<!-- ================== STYLE KHUSUS FORM INI ================== -->
<style>
   .form-pernyataan {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      font-family: "Times New Roman", Arial, sans-serif;
   }

   .form-pernyataan .kop {
      text-align: center;
      margin-bottom: 10px;
      position: relative;
   }

   .form-pernyataan h3 {
      text-align: center;
      margin-top: 25px;
      font-size: 18px;
      text-decoration: underline;
   }

   .form-pernyataan .row {
      margin: 10px 0;
      font-size: 16px;
   }

   .form-pernyataan .label {
      width: 180px;
      display: inline-block;
      font-weight: bold;
   }

   .form-pernyataan .dots {
      border-bottom: 1px dotted #000;
      display: inline-block;
      width: 320px;
      height: 16px;
   }

   .form-pernyataan .text-area {
      margin-top: 15px;
      font-size: 16px;
      text-align: justify;
      line-height: 1.4;
   }

   .form-pernyataan .ttd {
      margin-top: 50px;
      width: 100%;
      display: flex;
      justify-content: flex-end;
   }

   .form-pernyataan .ttd-box {
      width: 260px;
      text-align: center;
      font-size: 16px;
   }

   .form-pernyataan .sign-line {
      margin-top: 60px;
      border-bottom: 1px solid #000;
      height: 0;
      width: 100%;
   }
</style>


<!-- ================== FORM ================== -->
<div class="form-pernyataan">

   <div class="page">

      <!-- Kop Surat -->
      <?php include 'kopsurat.php'; ?>

      <h3>FORMULIR PERNYATAAN PESERTA</h3>

      <div class="row">
         <span class="label">Nama</span>
         <span class="dots" id="fp_nama"></span>
      </div>

      <div class="row">
         <span class="label">Tempat/Tanggal Lahir</span>
         <span class="dots" id="fp_ttl"></span>
      </div>

      <div class="row">
         <span class="label">Jenis Kelamin</span>
         <span class="dots" id="fp_jk"></span>
      </div>

      <div class="row">
         <span class="label">NIK / No BPJS</span>
         <span class="dots" id="fp_nik"></span>
      </div>

      <div class="row">
         <span class="label">No. Telepon</span>
         <span class="dots" id="fp_phone"></span>
      </div>

      <div class="text-area">
         Dengan sadar saya menyatakan bahwa data medis saya dapat digunakan sesuai kebutuhan pelayanan BPJS Kesehatan. dengan ini menyatakan : <br>
         "Kesediaan atas data medis (Rekam Medis) diri saya untuk dipergunakan oleh Dokter/Faskes BPJS Kesehatan Sesuai dengan kepentingan". <br>
      </div>

      <div class="ttd">
         <div class="ttd-box">
            <p id="ttd_tanggal"></p>
            Yang Membuat Pernyataan<br>
            <img id="ttd_img" src="" alt="TTD" style="max-height:80px;">
            <p id="ttd_nama"></p>
            <!-- <div class="sign-line" id="fp_nama"></div> -->
         </div>
      </div>

   </div>

</div>


<!-- ================== SCRIPT GET PASIEN ================== -->
<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(data => {

            if (!data) return;

            // Format TTL
            let ttl = "";
            if (data.patient_place && data.patient_datebirth) {
               ttl = data.patient_place + " / " + data.patient_datebirth;
            }

            // 🔥 SET TANGGAL TTD
            if (data.visit_date) {
               let tanggal = formatTanggal(data.visit_date);

               document.getElementById("ttd_tanggal").innerText =
                  "<?= $datafaskes['faskes_district'] ?>, " + tanggal;
            }

            // Isi field
            document.getElementById("fp_nama").innerText = data.patient_name ?? "";
            document.getElementById("fp_ttl").innerText = ttl;
            document.getElementById("fp_jk").innerText = data.patient_gender ?? "";
            document.getElementById("fp_nik").innerText = data.patient_nik ?? "";
            document.getElementById("fp_phone").innerText = data.patient_phone ?? "";
            // 🔥 SET TTD DINAMIS
            if (data.signature_path) {
               document.getElementById("ttd_img").src =
                  `../../../uploads/ttd/${data.signature_path}`;
            } else {
               document.getElementById("ttd_img").style.display = "none";
            }
            // 🔥 NAMA TTD
            document.getElementById("ttd_nama").innerText =
               data.nama_ttd ?? data.patient_name ?? "";

         });
   });

   // 🔥 FORMAT TANGGAL INDONESIA
   function formatTanggal(tgl) {
      const bulan = [
         "Januari", "Februari", "Maret", "April", "Mei", "Juni",
         "Juli", "Agustus", "September", "Oktober", "November", "Desember"
      ];

      let d = new Date(tgl);
      let hari = d.getDate();
      let bln = bulan[d.getMonth()];
      let thn = d.getFullYear();

      return `${hari} ${bln} ${thn}`;
   }
</script>