<div class="dokmulti-wrap">
   <style>
      @page {
         size: A4;
         margin: 12mm;
      }

      /* =========================
     BODY KHUSUS DOKMULTI
     ========================= */
      body.dokmulti-body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
         background: #fff;
         color: #000;
      }

      /* =========================
     WRAPPER
     ========================= */
      .dokmulti-wrap {
         width: 100%;
         max-width: 780px;
         margin: auto;
      }

      /* =========================
     PAGE
     ========================= */
      .dokmulti-page {
         border: 1px solid #000;
         padding: 18px;
         min-height: 100%;
         background: #fff;
         position: relative;
         page-break-after: always;
      }

      /* =========================
     WATERMARK
     ========================= */
      .dokmulti-watermark {
         position: absolute;
         top: 40%;
         left: 50%;
         transform: translate(-50%, -50%);
         font-size: 80pt;
         color: rgba(0, 0, 0, 0.06);
         font-weight: bold;
         pointer-events: none;
         user-select: none;
         white-space: nowrap;
      }

      /* =========================
     TITLE
     ========================= */
      .dokmulti-title {
         text-align: center;
         font-size: 18pt;
         font-weight: bold;
         margin-bottom: 4px;
      }

      .dokmulti-subtitle {
         text-align: center;
         font-size: 12pt;
         margin-bottom: 10px;
      }

      /* =========================
     QR CODE
     ========================= */
      .dokmulti-qr-img {
         position: absolute;
         top: 15px;
         right: 15px;
         width: 110px;
         height: 110px;
         border: 1px solid #000;
         background: #fff;
         object-fit: cover;
      }

      /* =========================
     TABLE
     ========================= */
      .dokmulti-table {
         width: 100%;
         margin-top: 10px;
         border-collapse: collapse;
      }

      .dokmulti-table td {
         padding: 4px 3px;
         vertical-align: top;
      }

      /* =========================
     LINE
     ========================= */
      .dokmulti-line {
         border-bottom: 1px dotted #333;
         display: inline-block;
         width: 260px;
         height: 15px;
      }

      /* =========================
     PHOTO
     ========================= */
      .dokmulti-photo-box {
         width: 100%;
         height: 350px;
         border: 2px solid #000;
         margin: 18px 0;
         background: #f0f0f0;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      .dokmulti-photo {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      .dokmulti-photo-date {
         text-align: center;
         font-size: 10pt;
         margin-top: 4px;
         color: #333;
      }

      /* =========================
     SIGNATURE
     ========================= */
      .dokmulti-sign {
         display: flex;
         justify-content: space-between;
         margin-top: 25px;
      }

      .dokmulti-sign-box {
         width: 45%;
         text-align: center;
      }

      .dokmulti-sign-img {
         width: 150px;
         height: 70px;
         object-fit: contain;
      }

      .dokmulti-sign-line {
         border-top: 1px solid #000;
         padding-top: 5px;
         margin-top: 5px;
      }

      /* =========================
     PAGE NUMBER
     ========================= */
      .dokmulti-page-number {
         position: absolute;
         bottom: 8px;
         right: 15px;
         font-size: 10pt;
         color: #444;
      }

      /* =========================
     GRID FOTO PERAWATAN
     ========================= */
      .dokmulti-grid {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 12px;
         margin-top: 20px;
      }

      .dokmulti-grid-item {
         width: 100%;
         height: 300px;
         overflow: hidden;
         border: 1px solid #ccc;
         background: #fafafa;
         display: flex;
         justify-content: center;
         align-items: center;
         flex-direction: column;
      }

      .dokmulti-grid-item img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }

      /* =========================
     PRINT CONTROL
     ========================= */
      @media print {
         .dokmulti-noprint {
            display: none !important;
         }
      }

      .dokmulti-noprint {
         text-align: center;
         margin-top: 20px;
      }
   </style>

   <div id="dokmulti_container"></div>

   <div class="dokmulti-noprint">
      <button onclick="window.print()">🖨 CETAK SEMUA</button>
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const rm = url.get("rm");
      const no = url.get("no");

      if (!rm) {
         alert("RM tidak ditemukan!");
         return;
      }

      const baseURL = window.location.origin + "/medisafe/";
      const container = document.getElementById("dokmulti_container");

      fetch("get_dokumen.php?rm=" + rm + "&no=" + no)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const list = res.data;

            let totalPages =
               list.filter(x => x.jenis_dokumen !== "FOTO_PERAWATAN").length +
               Math.ceil(list.filter(x => x.jenis_dokumen === "FOTO_PERAWATAN").length / 4);

            let currentPage = 1;

            // ==========================================================
            // 2) FOTO PERAWATAN — MULTI PAGE 2 KOLOM x 2 BARIS
            // ==========================================================
            const perawatan = list.filter(x => x.jenis_dokumen === "FOTO_PERAWATAN");

            let perPage = 4;
            let pages = Math.ceil(perawatan.length / perPage);

            for (let i = 0; i < pages; i++) {

               let pPage = document.createElement("div");
               pPage.className = "dokmulti-page";

               let verifyURL = baseURL + "verify_dokumen_perawatan.php?rm=" + rm;
               let qrURL =
                  "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" +
                  encodeURIComponent(verifyURL);

               let slice = perawatan.slice(i * perPage, i * perPage + perPage);
               let gridHTML = slice
                  .map(p => `
                     <div class="dokmulti-grid-item">
                        <div style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;overflow:hidden;">
                           <img src="${baseURL + p.foto_path}" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                       <div class="dokmulti-photo-date">
                        Tanggal: ${(p.tgl_upload || '-').split(' ')[0]}
                        </div>
                     </div>
                  `)
                  .join("");
               pPage.innerHTML = `
                  <div class="dokmulti-watermark">FOTO PERAWATAN</div>
                  <img class="dokmulti-qr-img" src="${qrURL}">

                  <div class='dokmulti-title'>DOKUMENTASI PERAWATAN</div>
                  <div class='dokmulti-subtitle'>Semua Foto Dalam Perawatan</div>
                  <div class="dokmulti-grid">${gridHTML}</div>

                  <div class="dokmulti-page-number">Page ${currentPage} / ${totalPages}</div>
               `;

               container.appendChild(pPage);
               currentPage++;
            }

            // ==========================================================
            // 1) CETAK DOKUMEN BIASA
            // ==========================================================
            list.filter(d => d.jenis_dokumen !== "FOTO_PERAWATAN").forEach((d) => {

               let page = document.createElement("div");
               page.className = "dokmulti-page";

               let verifyURL = baseURL + "verify_dokumen.php?id=" + d.id + "&rm=" + rm;
               let qrURL =
                  "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" +
                  encodeURIComponent(verifyURL);

               page.innerHTML = `
                  <div class="dokmulti-watermark">KLINIK TUTUN SEHATI</div>
                  <img class="dokmulti-qr-img" src="${qrURL}">

                  <div class='dokmulti-title'>DOKUMEN KEPENDUDUKAN</div>
                  <div class='dokmulti-subtitle'>${d.jenis_dokumen.replace(/_/g, " ")}</div>

                  <table class='dokmulti-table'>
                     <tr><td width='30%'>Nama Lengkap</td><td>: <span class='dokmulti-line'>${d.patient_name ?? "-"}</span></td></tr>
                     <tr><td>Nomor Dokumen</td><td>: <span class='dokmulti-line'>${d.nomor_dokumen ?? "-"}</span></td></tr>
                     <tr><td>Tempat / Tanggal Lahir</td><td>: <span class='dokmulti-line'>${(d.patient_place ?? "")}, ${(d.patient_datebirth ?? "")}</span></td></tr>
                     <tr><td>Alamat</td><td>: <span class='dokmulti-line' style='width:400px;'>${d.patient_address ?? "-"}</span></td></tr>
                     <tr><td>Tanggal Upload</td><td>: <span class='dokmulti-line'>${d.tgl_upload ?? "-"}</span></td></tr>
                  </table>

                  <div class='dokmulti-photo-box'>
                     <img class='dokmulti-photo' src='${baseURL + d.foto_path}'>
                  </div>

                  <div class='dokmulti-sign'>
                    <div class='dokmulti-sign-box'>
                        Pemegang Dokumen<br>

                        <img class="dokmulti-sign-img" 
                              src="${d.signature_path ? baseURL + 'uploads/ttd/' + d.signature_path : baseURL + 'assets/img/no-sign.png'}">
                        <div class='dokmulti-sign-line'>${d.patient_name}</div>
                        </div>

                     <div class='dokmulti-sign-box'>
                        Petugas Klinik<br>
                        <img class="dokmulti-sign-img" src="${baseURL}uploads/ttd/ttd_petugas.png">
                        <div class='dokmulti-sign-line'>Petugas Klinik</div>
                     </div>
                  </div>

                  <div class="dokmulti-page-number">Page ${currentPage} / ${totalPages}</div>
               `;

               container.appendChild(page);
               currentPage++;
            });



         });

   });
</script>