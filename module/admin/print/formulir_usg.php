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

      /* ============================
         QR CODE — NO CONFLICT MODE
      ============================ */

      /* reset biar ga ketarik style dari file lain */
      .usg-qr-wrap,
      .usg-qr-wrap * {
         all: unset;
      }

      .usg-qr-wrap {
         position: absolute;
         top: 20mm;
         right: 15mm;
         width: 120px;
         height: 120px;
         display: none;
         /* default hidden */
      }

      .usg-qr-img {
         width: 120px;
         height: 120px;
         object-fit: cover;
         display: none;
      }

      /* hide total ketika QR kosong */
      .usg-qr-hide {
         display: none !important;
         visibility: hidden !important;
         opacity: 0 !important;
         width: 0 !important;
         height: 0 !important;
         overflow: hidden !important;
         border: none !important;
         padding: 0 !important;
         margin: 0 !important;
      }

      /* ============================
         KONTEN USG
      ============================ */
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
   </style>

   <!-- QR CODE (Auto Hide) -->
   <div class="usg-qr-wrap usg-qr-hide" id="usg_qr_wrap">
      <img id="usg_qr" class="usg-qr-img">
   </div>

   <!-- KOP -->
   <?php require 'kopsurat.php'; ?>

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
      <img id="usg1_img" class="usg-img">
   </div>

   <!-- FOOTER / TTD -->
   <div class="usg-footer">
      Dokter Pemeriksa:<br><br>
      <img id="usg_ttd" class="usg-ttd">
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

            // IDENTITAS
            set("usg_nama", d.nama_pasien);
            set("usg_rm", d.rm);
            set("usg_usia", d.usia_kandungan);
            set("usg_tanggal", d.tanggal_pemeriksaan);

            // GAMBAR USG
            if (d.usg1) document.getElementById("usg1_img").src = base + d.usg1;

            // TTD
            if (d.ttd_dokter) document.getElementById("usg_ttd").src = base + d.ttd_dokter;

            set("usg_dokter", d.dokter);

            // ============ QR CODE AUTO HIDE ================
            const qrWrap = document.getElementById("usg_qr_wrap");
            const qrImg = document.getElementById("usg_qr");

            if (d.qr_code) {

               qrImg.onload = () => {
                  qrWrap.classList.remove("usg-qr-hide");
                  qrImg.style.display = "block";
               };

               qrImg.onerror = () => {
                  qrWrap.classList.add("usg-qr-hide");
               };

               qrImg.src = base + d.qr_code;

            } else {
               qrWrap.classList.add("usg-qr-hide");
            }

         });
   });
</script>