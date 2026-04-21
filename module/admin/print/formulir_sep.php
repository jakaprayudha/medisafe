<div class="pdf-a4-wrapper">

   <style>
      /* ===== A4 CANVAS ===== */
      .pdf-a4-wrapper {
         width: 210mm;
         height: 297mm;
         margin: 0 auto;
         background: #fff;
         overflow: hidden;
         position: relative;
      }

      .pdf-a4-wrapper {
         background: white !important;
      }

      .pdf-frame {
         background: white !important;
      }

      .pdf-frame {
         display: block;
      }

      /* ===== IFRAME FULL ===== */
      .pdf-frame {
         width: 100%;
         height: 100%;
         border: none;
      }

      /* ===== PRINT FIX ===== */
      @media print {

         html,
         body {
            margin: 0 !important;
            padding: 0 !important;
         }

         .pdf-a4-wrapper {
            width: 210mm !important;
            height: 297mm !important;
            page-break-after: always;
         }

         .pdf-frame {
            width: 100% !important;
            height: 100% !important;
         }
      }
   </style>

   <iframe class="pdf-frame"></iframe>

</div>

<script>
   (function() {

      const wrapper = document.currentScript.previousElementSibling;
      const iframe = wrapper.querySelector(".pdf-frame");

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      const base = window.location.origin + window.location.pathname.split('/module')[0] + "/";

      fetch(`getsep.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(res => {

            if (!res.data || !res.data[0]) return;

            const file = res.data[0].file_spp;
            if (!file) return;

            const path = base + "uploads/dokumen/" + file;

            // 🔥 KUNCI: pakai zoom fit width
            iframe.src = path + "#zoom=55";

         });

   })();
</script>