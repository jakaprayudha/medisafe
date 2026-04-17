<div class="sepprint-wrapper">

   <style>
      .sepprint-content-box {
         display: flex;
         justify-content: center;
         align-items: flex-start;
      }

      /* 🔥 WRAPPER SCALE */
      .pdf-wrapper {
         width: 210mm;
         height: 297mm;
         overflow: hidden;

         display: flex;
         justify-content: center;
      }

      /* 🔥 SCALE DI DALAM */
      .sepprint-pdf {
         width: 210mm;
         height: 297mm;
         border: none;
         display: none;

         transform: scale(0.85);
         /* 🔥 TURUNKAN LAGI */
         transform-origin: top center;
      }

      @media print {
         .sepprint-pdf {
            transform: scale(0.85) !important;
         }
      }
   </style>
   <div class="sepprint-page">

      <div class="sepprint-alert">Loading...</div>

      <div class="sepprint-content-box">
         <img class="sepprint-photo">

         <div class="pdf-wrapper">
            <iframe class="sepprint-pdf"></iframe>
         </div>
      </div>

   </div>
</div>

<script>
   (function() {

      const isBundle = <?= defined('IS_BUNDLE') ? 'true' : 'false' ?>;

      const wrapper = document.currentScript.previousElementSibling;

      const alertBox = wrapper.querySelector(".sepprint-alert");
      const img = wrapper.querySelector(".sepprint-photo");
      const pdf = wrapper.querySelector(".sepprint-pdf");

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      const base = window.location.origin + "/medisafe/";

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

            const file = result.data[0].file_spp;
            if (!file) return showAlert("File SEP tidak ditemukan.");

            const ext = file.split('.').pop().toLowerCase();
            const path = base + "uploads/dokumen/" + file;

            if (ext === "pdf") {

               if (isBundle) {
                  // ✅ bundle → embed
                  pdf.src = path + "#zoom=90";
                  pdf.style.display = "block";
               } else {
                  // ✅ standalone → direct open
                  window.location.href = path;
               }

               return;
            }

            img.onload = () => {
               img.style.display = "block";
               alertBox.style.display = "none";
            };

            img.onerror = () => showAlert("File rusak");

            img.src = path;

         })
         .catch(() => showAlert("Gagal load data"));

   })();
</script>