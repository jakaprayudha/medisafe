<div class="ekgprint-wrapper">
   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body.ekgprint-body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
         background: #fff;
      }

      /* ============================
         QR CODE (Clean No Conflict)
      ============================ */
      /* Reset total biar ga ketarik style lain */
      .ekgprint-qr-wrap,
      .ekgprint-qr-wrap * {
         all: unset;
      }

      .ekgprint-qr-wrap {
         position: absolute;
         top: 20mm;
         right: 15mm;
         width: 120px;
         height: 120px;
         display: none;
      }

      .ekgprint-qr-img {
         width: 120px;
         height: 120px;
         object-fit: cover;
         display: none;
      }

      /* kalau hide → hilang total */
      .ekgprint-q-hide {
         display: none !important;
         visibility: hidden !important;
         opacity: 0 !important;
         width: 0 !important;
         height: 0 !important;
         overflow: hidden !important;
         margin: 0 !important;
         padding: 0 !important;
         border: none !important;
      }

      /* ============================
         KONTEN EKG
      ============================ */
      .ekgprint-kop {
         text-align: center;
         margin-bottom: 5px;
      }

      .ekgprint-kop h1 {
         font-size: 26pt;
         font-weight: bold;
         margin: 0;
      }

      .ekgprint-kop h2 {
         font-size: 18pt;
         margin-top: -3px;
         font-weight: bold;
      }

      .ekgprint-alamat {
         font-size: 11pt;
         margin-top: 3px;
      }

      .ekgprint-hr {
         border: none;
         border-top: 2px solid #000;
         margin: 10px 0 20px 0;
      }

      .ekgprint-title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 15px;
      }

      table.ekgprint-identitas {
         width: 100%;
         margin-bottom: 15px;
         font-size: 12pt;
      }

      table.ekgprint-identitas td {
         padding: 4px 3px;
      }

      .ekgprint-line {
         border-bottom: 1px dotted #666;
         display: inline-block;
         width: 260px;
         height: 16px;
      }

      .ekgprint-box {
         width: 100%;
         height: 350px;
         border: 2px solid #000;
         background: #f7f7f7;
         margin-bottom: 15px;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      .ekgprint-img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      .ekgprint-note-title {
         font-weight: bold;
         margin-bottom: 5px;
      }

      .ekgprint-note {
         width: 100%;
         height: 120px;
         border: 1px solid #000;
         padding: 8px;
         font-size: 12pt;
         white-space: pre-line;
      }

      .ekgprint-footer {
         text-align: right;
         margin-top: 30px;
      }

      .ekgprint-ttd {
         width: 180px;
         height: 80px;
         object-fit: contain;
         margin-bottom: -10px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <!-- QR Code (Auto Hide) -->
   <div id="ekgprint_qr_wrap" class="ekgprint-qr-wrap ekgprint-q-hide">
      <img id="ekgprint_qr" class="ekgprint-qr-img">
   </div>

   <!-- KOP -->
   <?php require 'kopsurat.php'; ?>

   <hr class="ekgprint-hr">

   <div class="ekgprint-title">HASIL PEMERIKSAAN EKG</div>

   <!-- IDENTITAS PASIEN -->
   <table class="ekgprint-identitas">
      <tr>
         <td width="28%">Nama Pasien</td>
         <td>: <span id="ekgprint_nama" class="ekgprint-line"></span></td>
      </tr>
      <tr>
         <td>No. Rekam Medis</td>
         <td>: <span id="ekgprint_rm" class="ekgprint-line"></span></td>
      </tr>
      <tr>
         <td>Usia</td>
         <td>: <span id="ekgprint_usia" class="ekgprint-line"></span></td>
      </tr>
      <tr>
         <td>Tanggal Pemeriksaan</td>
         <td>: <span id="ekgprint_tanggal" class="ekgprint-line"></span></td>
      </tr>
   </table>

   <!-- GAMBAR EKG -->
   <div class="ekgprint-box">
      <img id="ekgprint_img1" class="ekgprint-img">
   </div>

   <div class="ekgprint-box">
      <img id="ekgprint_img2" class="ekgprint-img">
   </div>

   <!-- CATATAN -->
   <div class="ekgprint-note-title">Interpretasi / Catatan Dokter:</div>
   <div id="ekgprint_catatan" class="ekgprint-note"></div>

   <!-- FOOTER -->
   <div class="ekgprint-footer">
      Dokter Pemeriksa:<br><br>
      <img id="ekgprint_ttd" class="ekgprint-ttd">
      <br>
      <b id="ekgprint_dokter"></b>
   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      const base = window.location.origin + "/medisafe/";

      fetch("get_ekg.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.textContent = val ?? "";
            };

            // IDENTITAS
            set("ekgprint_nama", d.patient_name);
            set("ekgprint_rm", d.nomor_rm);
            set("ekgprint_usia", d.usia);
            set("ekgprint_tanggal", d.tanggal_pemeriksaan);

            // GAMBAR EKG 1
            if (d.ekg1) {
               document.getElementById("ekgprint_img1").src = base + d.ekg1;
            } else {
               document.getElementById("ekgprint_img1").closest(".ekgprint-box").style.display = "none";
            }

            // GAMBAR EKG 2 — Auto Hide Jika Tidak Ada
            if (d.ekg2) {
               document.getElementById("ekgprint_img2").src = base + d.ekg2;
            } else {
               document.getElementById("ekgprint_img2").closest(".ekgprint-box").style.display = "none";
            }
            // CATATAN
            document.getElementById("ekgprint_catatan").textContent = d.interpretasi ?? "";

            // TTD
            if (d.ttd_dokter) document.getElementById("ekgprint_ttd").src = base + d.ttd_dokter;

            set("ekgprint_dokter", d.dokter);

            // QR CODE FIX
            const qrWrap = document.getElementById("ekgprint_qr_wrap");
            const qrImg = document.getElementById("ekgprint_qr");

            if (d.qr_code) {

               qrImg.onload = () => {
                  qrWrap.classList.remove("ekgprint-q-hide");
                  qrImg.style.display = "block";
               };

               qrImg.onerror = () => {
                  qrWrap.classList.add("ekgprint-q-hide");
               };

               qrImg.src = base + d.qr_code;

            } else {
               qrWrap.classList.add("ekgprint-q-hide");
            }

         });
   });
</script>