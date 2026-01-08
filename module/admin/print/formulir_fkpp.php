<div class="fkppprint-wrapper">

   <style>
      @page {
         margin: 0;
         size: A4;
      }

      /* ================= RESET AMAN ================= */
      .fkppprint-wrapper img,
      .fkppprint-wrapper iframe {
         border: none !important;
         outline: none !important;
         box-shadow: none !important;
      }

      /* Hilangkan gambar kosong */
      .fkppprint-wrapper img[src=""],
      .fkppprint-wrapper img:not([src]) {
         display: none !important;
      }

      /* ================= LAYOUT CETAK ================= */

      .fkppprint-wrapper {
         width: 210mm;
         height: 297mm;
         background: #fff;
         margin: 0 auto;
         overflow: hidden;
      }

      .fkppprint-page {
         width: 100%;
         height: 100%;
         position: relative;
         overflow: hidden;
         background: #fff;
      }

      .fkppprint-content-box {
         width: 100%;
         height: 100%;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      /* ===== GAMBAR ===== */
      .fkppprint-photo {
         width: 100%;
         height: 100%;
         object-fit: contain;
         display: none;
      }

      /* ===== PDF ===== */
      .fkppprint-pdf {
         width: 100%;
         height: 100%;
         border: none !important;
         display: none;
      }

      /* ===== ALERT ===== */
      .fkppprint-alert {
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         background: #fff3cd;
         border: 1px solid #ffecb5;
         padding: 16px 20px;
         font-size: 14pt;
         font-weight: bold;
         color: #7a5a2b;
         border-radius: 5px;
         width: 70%;
         text-align: center;
         display: none;
         z-index: 99;
      }
   </style>

   <div class="fkppprint-page">

      <div id="fkppprint_alert" class="fkppprint-alert">Loading...</div>

      <div class="fkppprint-content-box">
         <img id="fkppprint_img" class="fkppprint-photo">
         <iframe id="fkppprint_pdf" class="fkppprint-pdf"></iframe>
      </div>

   </div>
</div>


<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      const alertBox = document.getElementById("fkppprint_alert");
      const img = document.getElementById("fkppprint_img");
      const pdf = document.getElementById("fkppprint_pdf");

      const showAlert = (msg) => {
         alertBox.style.display = "block";
         alertBox.textContent = msg;
         img.style.display = "none";
         pdf.style.display = "none";
      };

      if (!no || !rm) {
         return showAlert("Parameter tidak lengkap.");
      }

      fetch(`getfkpp.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(result => {

            if (result.status !== "success" || result.data.length === 0) {
               return showAlert("Data FKPP belum diupload.");
            }

            const fkpp = result.data[0];
            const file = fkpp.fkpp_file;

            if (!file) return showAlert("File fkpp tidak ditemukan.");

            const ext = file.split('.').pop().toLowerCase();
            const path = "../../../uploads/dokumen/" + file;

            // ================== PDF MODE ==================
            if (ext === "pdf") {
               pdf.src = path;
               pdf.style.display = "block";
               alertBox.style.display = "none";
               img.style.display = "none";
               return;
            }

            // ================== IMAGE MODE ==================
            img.onerror = () => showAlert("File fkpp rusak atau tidak ditemukan.");

            img.onload = () => {
               img.style.display = "block";
               alertBox.style.display = "none";
               pdf.style.display = "none";
            };

            img.src = path;

         })
         .catch(() => showAlert("Gagal mengambil data dari server."));
   });
</script>