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
         margin-top: 60px;
      }

      .rinap-ttd-kanan {
         width: 35%;
         margin-left: auto;
         text-align: center;
         font-size: 15px;
      }

      .rinap-ttd-box {
         margin-top: 70px;
         border-top: 1px solid #000;
         padding-top: 5px;
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
            .then(data => {

               if (!data) return;

               document.getElementById("rinap_nama").innerText = data.patient_name || "-";
               document.getElementById("rinap_dokter").innerText = data.doctor_name || "-";
               document.getElementById("rinap_dokter2").innerText = data.doctor_name || "-";
            });
      });
   </script>

   <!-- ================== SURAT ================== -->
   <div class="rinap-container">

      <?php include 'kopsurat.php'; ?>

      <h3 class="rinap-judul">SURAT PENGANTAR DIRAWAT</h3>

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
               <td>: 29 Tahun</td>
            </tr>
            <tr>
               <td>Diagnosa</td>
               <td>: Febris + HEG</td>
            </tr>
            <tr>
               <td>Indikasi Dirawat</td>
               <td>: Nyeri perut (+), pusing (+)</td>
            </tr>
            <tr>
               <td>Dirawat</td>
               <td>: Kamar 01 B</td>
            </tr>
            <tr>
               <td>Terapi</td>
               <td>: IVFD RL 20 gtt/i</td>
            </tr>
         </table>

         <p class="rinap-penutup">
            Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan dalam pengajuan klaim biaya rawat inap.
         </p>

         <!-- ===== TTD (DALAM CONTAINER) ===== -->
         <div class="rinap-ttd-wrapper">
            <div class="rinap-ttd-kanan">
               <p>DOKTER MERAWAT</p>
               <div class="rinap-ttd-box">
                  <span id="rinap_dokter2"></span>
               </div>
            </div>
         </div>

      </div>
   </div>

</body>