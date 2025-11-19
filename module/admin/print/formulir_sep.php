<div class="sepprint-wrapper">

   <style>
      @page {
         size: A4;
         margin: 12mm;
      }

      body.sepprint-body {
         font-family: "Times New Roman", serif;
         margin: 0;
         font-size: 12pt;
         background: #fff;
      }

      .sepprint-wrapper {
         width: 210mm;
         min-height: 297mm;
         margin: 0 auto;
      }

      .sepprint-page {
         width: 100%;
         min-height: 100%;
         border: 1px solid #000;
         padding: 10px;
         background: #fff;
         display: flex;
         justify-content: center;
         align-items: center;
         position: relative;
         box-sizing: border-box;
      }

      /* Kotak Gambar */
      .sepprint-photo-box {
         width: 100%;
         height: 100%;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      /* Hilangkan border error image */
      .sepprint-photo {
         max-width: 100%;
         max-height: 100%;
         object-fit: contain;
         border: none !important;
         outline: none !important;
      }

      /* ALERT */
      .sepprint-alert {
         position: absolute;
         top: 40%;
         left: 50%;
         transform: translate(-50%, -50%);
         background: #fff3cd;
         padding: 15px 20px;
         border: 1px solid #ffecb5;
         font-size: 14pt;
         color: #8a6d3b;
         font-weight: bold;
         text-align: center;
         border-radius: 4px;
         display: none;
         z-index: 10;
         width: 80%;
      }
   </style>

   <div class="sepprint-page">
      <div id="sepprint_alert" class="sepprint-alert">
         Data SEP belum diupload / dibuat.
      </div>

      <div class="sepprint-photo-box">
         <img id="sepprint_img" class="sepprint-photo" src="" alt="">
      </div>
   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      const img = document.getElementById("sepprint_img");
      const alertBox = document.getElementById("sepprint_alert");

      if (!no || !rm) {
         alertBox.style.display = "block";
         alertBox.innerText = "Parameter tidak lengkap.";
         return;
      }

      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(data => {

            if (!data || !data.sep_file) {
               alertBox.style.display = "block";
               alertBox.innerText = "Data SEP belum diupload / belum dibuat.";
               img.style.display = "none";
               return;
            }

            const imgPath = "../../../uploads/sep/" + data.sep_file;

            // Jika gambar error → hide supaya tidak ada kotak aneh
            img.onerror = function() {
               img.style.display = "none";
               alertBox.style.display = "block";
               alertBox.innerText = "Gambar SEP tidak dapat dimuat.";
            };

            img.onload = function() {
               img.style.display = "block";
            };

            img.src = imgPath;
         })
         .catch(err => {
            alertBox.style.display = "block";
            alertBox.innerText = "Terjadi kesalahan mengambil data.";
            img.style.display = "none";
         });

   });
</script>