<body class="rinap-body">

   <style>
      @page {
         size: A4;
         margin: 15mm 20mm;
      }

      .rinap-body {
         font-family: "Times New Roman", serif;
         margin: 0;
         padding: 0;
         background: white;
      }

      /* ===== CONTAINER ===== */
      .rinap-container {
         width: 100%;
         max-width: 750px;
         margin: auto;
      }

      /* ===== HEADER ===== */
      .rinap-header {
         text-align: center;
         margin-bottom: 10px;
      }

      .rinap-header h1 {
         font-size: 32px;
         margin: 0;
         letter-spacing: 2px;
      }

      .rinap-header h2 {
         font-size: 26px;
         margin: 0;
         font-weight: bold;
      }

      .rinap-header .rinap-alamat {
         font-size: 12px;
         margin-top: 8px;
         line-height: 1.3;
      }

      .rinap-hr {
         margin-top: 12px;
         border: 0;
         border-top: 2px solid #000;
      }

      /* ===== JUDUL ===== */
      .rinap-judul {
         text-align: center;
         margin-top: 15px;
         text-decoration: underline;
         font-size: 20px;
      }

      /* ===== ISI ===== */
      .rinap-section {
         margin-top: 20px;
         font-size: 15px;
      }

      .rinap-data {
         margin-left: 20px;
         margin-bottom: 15px;
         font-size: 15px;
         border-collapse: collapse;
      }

      /* HILANGKAN GARIS TABEL */
      .rinap-data,
      .rinap-data tr,
      .rinap-data td {
         border: none !important;
      }

      .rinap-data td {
         padding: 4px 6px;
         vertical-align: top;
      }

      /* RAPATKAN KOLOM LABEL */
      .rinap-data td:first-child {
         width: 140px;
      }

      /* ===== PENUTUP ===== */
      .rinap-penutup {
         margin-top: 15px;
         text-align: justify;
         font-size: 15px;
      }

      /* ===== TTD ===== */
      .rinap-ttd-wrapper {
         width: 100%;
         margin-top: 40px;
      }

      .rinap-ttd-kanan {
         width: 220px;
         margin-left: auto;
         /* KUNCI: tetap di kanan */
         text-align: center;
      }

      .rinap-ttd-title {
         font-size: 11pt;
         font-weight: bold;
         margin-bottom: 6px;
      }

      .rinap-ttd-img {
         width: 90px;
         /* TTD diperkecil */
         height: auto;
         margin: 4px auto;
         display: block;
      }

      .rinap-ttd-box {
         border-top: 1px solid #000;
         margin-top: 6px;
         padding-top: 4px;
         font-size: 10pt;
         font-weight: bold;
      }
   </style>

   <script>
      document.addEventListener("DOMContentLoaded", function() {

         const url = new URLSearchParams(window.location.search);
         const no = url.get("no");
         const rm = url.get("rm");

         if (!no || !rm) return;

         fetch(`getpasien.php?no=${no}&rm=${rm}`)
            .then(res => res.json())
            .then(res => {

               const d = res.data || res; // 🔥 fleksibel

               // ===============================
               // NAMA
               // ===============================
               document.getElementById("rinap_nama").innerText =
                  d.patient_name_pcare || d.patient_name || "-";

               // ===============================
               // DOKTER
               // ===============================
               document.getElementById("rinap_dokter").innerText =
                  d.id_doctor || "-";

               document.getElementById("rinap_dokter2").innerText =
                  d.id_doctor || "-";

               // ===============================
               // UMUR (FIX EXCEL DATE BUG 🔥)
               // ===============================
               let umur = "-";

               if (d.patient_datebirth) {

                  let dob;

                  // 🔥 kalau format excel number
                  if (!isNaN(d.patient_datebirth)) {
                     dob = new Date((d.patient_datebirth - 25569) * 86400 * 1000);
                  } else {
                     dob = new Date(d.patient_datebirth);
                  }

                  let today = new Date(d.visit_date || new Date());
                  let diff = today - dob;

                  let years = Math.floor(diff / (1000 * 60 * 60 * 24 * 365));

                  umur = years + " Tahun";
               }

               document.getElementById("rinap_umur").innerText = umur;

               // ===============================
               // DIAGNOSA (UTAMA + SEKUNDER)
               // ===============================
               let diagnosa = d.diagnosa || "-";

               if (d.diagnosa_sekunder) {
                  diagnosa += " + " + d.diagnosa_sekunder;
               }

               document.getElementById("rinap_diagnosa").innerText = diagnosa;

               // ===============================
               // INDIKASI (ambil dari anamnesa)
               // ===============================
               document.getElementById("rinap_indikasi").innerText =
                  d.anamnesa || "-";

               document.getElementById("kamar").innerText =
                  d.room_name + " - " + d.bed_name || "-";

               // ===============================
               // TTD DOKTER
               // ===============================
               const ttd = document.getElementById("rinap_ttd");

               if (d.signature_path) {
                  ttd.src = `../../../uploads/ttd/${d.signature_path}`;
               }

            });
      });
   </script>

   <!-- ================== SURAT ================== -->
   <div class="rinap-container">

      <?php include 'kopsurat.php'; ?>

      <h3 class="rinap-judul">SURAT PERINTAH RAWAT INAP</h3>

      <div class="rinap-section">
         <p>
            Kepada Yth : <br>
            Dokter DPJP : <span id="rinap_dokter"></span>
         </p>

         <p>Mohon Dirawat</p>

         <table class="rinap-data">
            <tr>
               <td>Nama</td>
               <td>: <span id="rinap_nama"></span></td>
            </tr>
            <tr>
               <td>Umur</td>
               <td>: <span id="rinap_umur">-</span></td>
            </tr>
            <tr>
               <td>Diagnosa</td>
               <td>: <span id="rinap_diagnosa">-</span></td>
            </tr>
            <tr>
               <td>Indikasi Dirawat</td>
               <td>: <span id="rinap_indikasi">-</span></td>
            </tr>
            <tr>
               <td>Dirawat</td>
               <td>: <span id="kamar">-</span></td>
            </tr>
            <tr>
               <td>Terapi</td>
               <td>: <span id="terapi">-</span></td>
            </tr>
         </table>

         <p class="rinap-penutup">
            Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan dalam pengajuan klaim biaya rawat inap.
         </p>

         <!-- ===== TTD (DALAM CONTAINER) ===== -->
         <div class="rinap-ttd-wrapper">
            <div class="rinap-ttd-kanan">
               <p class="rinap-ttd-title">DOKTER MERAWAT</p>
               <img src="../../../uploads/ttd/drdevi.png"
                  class="rinap-ttd-img"
                  alt="TTD Dokter">

               <div class="rinap-ttd-box">
                  <span id="rinap_dokter2"></span>
               </div>
            </div>
         </div>

      </div>
   </div>

</body>