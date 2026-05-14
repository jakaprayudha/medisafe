<!-- ========================= -->
<!-- CONTAINER -->
<!-- ========================= -->
<div id="dokumenLainnyaContainer"></div>

<style>
   .fkpp-a4-wrapper {
      width: 210mm;
      height: 297mm;
      margin: 0 auto 15px auto;
      background: #fff;
      overflow: hidden;
      position: relative;
      border: 1px solid #ddd;
      page-break-after: always;
   }

   .pdf-frame {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
      background: white;
   }

   @media print {

      html,
      body {
         margin: 0 !important;
         padding: 0 !important;
         background: white !important;
      }

      .fkpp-a4-wrapper {
         width: 210mm !important;
         height: 297mm !important;
         margin: 0 auto !important;
         border: none !important;
         page-break-after: always;
      }

      .pdf-frame {
         width: 100% !important;
         height: 100% !important;
      }
   }
</style>

<script>
   (async function() {

      console.clear();

      console.log("START LOAD DOKUMEN");

      // =========================
      // CONTAINER
      // =========================
      const container = document.getElementById(
         'dokumenLainnyaContainer'
      );

      if (!container) {

         console.log("CONTAINER TIDAK ADA");

         return;
      }

      // =========================
      // URL PARAM
      // =========================
      const params = new URLSearchParams(
         window.location.search
      );

      const no = params.get('no');

      const rm = params.get('rm');

      console.log("NO:", no);
      console.log("RM:", rm);

      // =========================
      // BASE URL
      // =========================
      const base =
         window.location.origin +
         window.location.pathname.split('/module')[0] +
         "/";

      console.log("BASE:", base);

      try {

         // =========================
         // FETCH API
         // =========================
         const api =
            base +
            `controller/ranap/dokumenKlaimLainnya?no=${no}`;

         console.log("API:", api);

         const response = await fetch(api);

         console.log("STATUS:", response.status);

         // =========================
         // AMBIL RAW TEXT
         // =========================
         const raw = await response.text();

         console.log("RAW RESPONSE:");
         console.log(raw);

         // =========================
         // JSON PARSE
         // =========================
         let result;

         try {

            result = JSON.parse(raw);

         } catch (jsonErr) {

            console.log("JSON ERROR:", jsonErr);

            container.innerHTML = `
            <div style="
               padding:20px;
               background:#ffebee;
               color:red;
               border:1px solid red;
            ">
               RESPONSE BUKAN JSON VALID
            </div>
         `;

            return;
         }

         console.log("RESULT:", result);

         // =========================
         // VALIDASI
         // =========================
         if (
            !result ||
            result.status !== 'success'
         ) {

            container.innerHTML = `
            <div style="
               padding:20px;
               background:#fff3cd;
               color:#856404;
               border:1px solid #ffeeba;
            ">
               Dokumen tidak ditemukan
            </div>
         `;

            return;
         }

         // =========================
         // ARRAY DATA
         // =========================
         const files = result.data || [];

         console.log("FILES:", files);

         if (!files.length) {

            container.innerHTML = `
            <div style="
               padding:20px;
               background:#fff3cd;
               color:#856404;
               border:1px solid #ffeeba;
            ">
               Tidak ada dokumen lainnya
            </div>
         `;

            return;
         }

         // =========================
         // RENDER FILE
         // =========================
         files.forEach((item, index) => {

            console.log("ITEM:", item);

            if (!item.dokumen_path) return;

            const pdfPath = base + item.dokumen_path;

            console.log("PDF PATH:", pdfPath);

            const html = `
            <div class="fkpp-a4-wrapper">

               <iframe
                  class="pdf-frame"
                  src="${pdfPath}#toolbar=0&navpanes=0&scrollbar=0&zoom=55">
               </iframe>

            </div>
         `;

            container.insertAdjacentHTML(
               'beforeend',
               html
            );

         });

         console.log("SELESAI RENDER PDF");

      } catch (err) {

         console.log("FETCH ERROR:", err);

         container.innerHTML = `
         <div style="
            padding:20px;
            background:#ffebee;
            color:red;
            border:1px solid red;
         ">
            Gagal memuat dokumen lainnya
         </div>
      `;
      }

   })();
</script>