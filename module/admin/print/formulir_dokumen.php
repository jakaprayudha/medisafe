<div class="dokmulti-wrap">

   <style>
      @page {
         size: A4;
         margin: 12mm;
      }

      body.dokmulti-body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
      }

      .dokmulti-page {
         border: 1px solid #000;
         padding: 18px;
         min-height: 100%;
         background: #fff;
         position: relative;
         page-break-after: always;
      }

      /* WATERMARK */
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
      }

      .dokmulti-title {
         text-align: center;
         font-size: 18pt;
         font-weight: bold;
         margin-bottom: 5px;
      }

      .dokmulti-subtitle {
         text-align: center;
         font-size: 12pt;
         margin-bottom: 10px;
      }

      /* QR CODE IMG (bukan CSS background) */
      .dokmulti-qr-img {
         position: absolute;
         top: 15px;
         right: 15px;
         width: 110px;
         height: 110px;
         border: 1px solid #000;
         object-fit: cover;
         background: #fff;
      }

      table.dokmulti-table {
         width: 100%;
         margin-top: 10px;
      }

      table.dokmulti-table td {
         padding: 4px 3px;
      }

      .dokmulti-line {
         border-bottom: 1px dotted #333;
         display: inline-block;
         width: 260px;
         height: 15px;
      }

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

      /* SIGNATURE */
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

      /* PAGE NUMBER */
      .dokmulti-page-number {
         position: absolute;
         bottom: 8px;
         right: 15px;
         font-size: 10pt;
         color: #444;
      }

      /* FOTO PERAWATAN GRID 2 KOLOM */
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
      }

      .dokmulti-grid-item img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }

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

      if (!rm) {
         alert("RM tidak ditemukan!");
         return;
      }

      const baseURL = window.location.origin + "/medisafe/";
      const container = document.getElementById("dokmulti_container");

      fetch("get_dokumen.php?rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const list = res.data;

            let totalPages =
               list.filter(x => x.jenis_dokumen !== "FOTO_PERAWATAN").length +
               Math.ceil(list.filter(x => x.jenis_dokumen === "FOTO_PERAWATAN").length / 4);

            let currentPage = 1;

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
                     <tr><td width='30%'>Nama Lengkap</td><td>: <span class='dokmulti-line'>${d.nama_lengkap ?? "-"}</span></td></tr>
                     <tr><td>Nomor Dokumen</td><td>: <span class='dokmulti-line'>${d.nomor_dokumen ?? "-"}</span></td></tr>
                     <tr><td>Tempat / Tanggal Lahir</td><td>: <span class='dokmulti-line'>${(d.tempat_lahir ?? "")}, ${(d.tanggal_lahir ?? "")}</span></td></tr>
                     <tr><td>Alamat</td><td>: <span class='dokmulti-line' style='width:400px;'>${d.alamat ?? "-"}</span></td></tr>
                     <tr><td>Tanggal Upload</td><td>: <span class='dokmulti-line'>${d.tgl_upload ?? "-"}</span></td></tr>
                  </table>

                  <div class='dokmulti-photo-box'>
                     <img class='dokmulti-photo' src='${baseURL + d.foto_path}'>
                  </div>

                  <div class='dokmulti-sign'>
                     <div class='dokmulti-sign-box'>
                        Pemegang Dokumen<br>
                        <img class="dokmulti-sign-img" src="${baseURL}uploads/ttd/ttd_user.png">
                        <div class='dokmulti-sign-line'>${d.nama_lengkap}</div>
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
                        <img src="${baseURL + p.foto_path}">
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

         });

   });
</script>