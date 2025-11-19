<div class="form-usg-print">

   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body.form-usg-print {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
      }

      .usg-kop {
         text-align: center;
         margin-bottom: 5px;
      }

      .usg-kop h1 {
         font-size: 26pt;
         margin: 0;
         font-weight: bold;
      }

      .usg-kop h2 {
         font-size: 18pt;
         margin: -3px 0 0 0;
         font-weight: bold;
      }

      .usg-alamat {
         font-size: 11pt;
         margin-top: 3px;
      }

      .usg-hr {
         border: none;
         border-top: 2px solid #000;
         margin: 10px 0 20px 0;
      }

      .usg-title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         margin-bottom: 10px;
      }

      table.usg-identitas td {
         padding: 4px 3px;
         font-size: 12pt;
      }

      .usg-line {
         border-bottom: 1px dotted #666;
         display: inline-block;
         width: 260px;
         height: 16px;
      }

      /* BOX USG */
      .usg-box {
         width: 100%;
         height: 450px;
         border: 2px solid #000;
         margin-bottom: 15px;
         display: flex;
         align-items: center;
         justify-content: center;
         background: #f7f7f7;
      }

      .usg-img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      /* FOOTER */
      .usg-footer {
         text-align: right;
         margin-top: 20px;
      }

      .usg-ttd {
         width: 180px;
         height: 80px;
         object-fit: contain;
         margin-bottom: -10px;
      }

      /* QR CODE */
      .usg-qr-wrap {
         width: 120px;
         height: 120px;
         position: absolute;
         top: 20mm;
         right: 15mm;
      }

      .usg-qr-img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
   </style>


   <!-- QR CODE -->
   <div class="usg-qr-wrap">
      <img id="usg_qr" class="usg-qr-img" src="">
   </div>

   <!-- KOP SURAT -->
   <?php
   require 'kopsurat.php';
   ?>

   <hr class="usg-hr">

   <div class="usg-title">HASIL PEMERIKSAAN USG</div>

   <!-- IDENTITAS -->
   <table class="usg-identitas">
      <tr>
         <td>Nama Pasien</td>
         <td>: <span id="usg_nama" class="usg-line"></span></td>
      </tr>
      <tr>
         <td>No. Rekam Medis</td>
         <td>: <span id="usg_rm" class="usg-line"></span></td>
      </tr>
      <tr>
         <td>Usia Kandungan</td>
         <td>: <span id="usg_usia" class="usg-line"></span></td>
      </tr>
      <tr>
         <td>Tanggal Pemeriksaan</td>
         <td>: <span id="usg_tanggal" class="usg-line"></span></td>
      </tr>
   </table>

   <!-- FOTO USG -->
   <div class="usg-box">
      <img id="usg1_img" class="usg-img" src="" alt="USG 1">
   </div>

   <!-- FOOTER / TTD -->
   <div class="usg-footer">
      Dokter Pemeriksa:<br><br>
      <img id="usg_ttd" class="usg-ttd" src="">
      <br>
      <b id="usg_dokter"></b>
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      const base = window.location.origin + "/medisafe/";

      fetch("get_usg.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.textContent = val ?? "";
            };

            // IDENTITAS PASIEN
            set("usg_nama", d.nama_pasien);
            set("usg_rm", d.rm);
            set("usg_usia", d.usia_kandungan);
            set("usg_tanggal", d.tanggal_pemeriksaan);

            // GAMBAR USG (IMG)
            if (d.usg1) {
               document.getElementById("usg1_img").src = base + d.usg1;
            }

            // TTD
            if (d.ttd_dokter) {
               document.getElementById("usg_ttd").src = base + d.ttd_dokter;
            }

            // Dokter
            set("usg_dokter", d.dokter);

            // QR CODE
            if (d.qr_code) {
               document.getElementById("usg_qr").src = base + d.qr_code;
            }
         });

   });
</script>