<div class="sepprint-wrapper">

   <style>
      @page {
         margin: 0;
         size: A4;
      }

      /* ================= RESET AMAN ================= */
      .sepprint-wrapper img,
      .sepprint-wrapper iframe {
         border: none !important;
         outline: none !important;
         box-shadow: none !important;
      }

      /* Hilangkan gambar kosong */
      .sepprint-wrapper img[src=""],
      .sepprint-wrapper img:not([src]) {
         display: none !important;
      }

      /* ================= LAYOUT CETAK ================= */

      .sepprint-wrapper {
         width: 210mm;
         height: 297mm;
         background: #fff;
         margin: 0 auto;
         overflow: hidden;
      }

      .sepprint-page {
         width: 100%;
         height: 100%;
         position: relative;
         overflow: hidden;
         background: #fff;
      }

      .sepprint-content-box {
         width: 100%;
         height: 100%;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      /* ===== GAMBAR ===== */
      .sepprint-photo {
         width: 100%;
         height: 100%;
         object-fit: contain;
         display: none;
      }

      /* ===== PDF ===== */
      .sepprint-pdf {
         width: 100%;
         height: 100%;
         border: none !important;
         display: none;
      }

      /* ===== ALERT ===== */
      .sepprint-alert {
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

   <div class="sepprint-page">

      <div id="sepprint_alert" class="sepprint-alert">Loading...</div>

      <div class="sepprint-content-box">
         <img id="sepprint_img" class="sepprint-photo">
         <iframe id="sepprint_pdf" class="sepprint-pdf"></iframe>
      </div>

   </div>
</div>


<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      const alertBox = document.getElementById("sepprint_alert");
      const img = document.getElementById("sepprint_img");
      const pdf = document.getElementById("sepprint_pdf");

      const showAlert = (msg) => {
         alertBox.style.display = "block";
         alertBox.textContent = msg;
         img.style.display = "none";
         pdf.style.display = "none";
      };

      if (!no || !rm) {
         return showAlert("Parameter tidak lengkap.");
      }

      fetch(`getsep.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(result => {

            if (result.status !== "success" || result.data.length === 0) {
               return showAlert("Data SEP belum diupload.");
            }

            const sep = result.data[0];
            const file = sep.sep_file;

            if (!file) return showAlert("File SEP tidak ditemukan.");

            const ext = file.split('.').pop().toLowerCase();
            const path = "../../../uploads/sep/" + file;

            // ================== PDF MODE ==================
            if (ext === "pdf") {
               pdf.src = path;
               pdf.style.display = "block";
               alertBox.style.display = "none";
               img.style.display = "none";
               return;
            }

            // ================== IMAGE MODE ==================
            img.onerror = () => showAlert("File SEP rusak atau tidak ditemukan.");

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