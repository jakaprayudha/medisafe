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

      .rinap-container {
         width: 100%;
         max-width: 750px;
         margin: auto;
      }

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
         letter-spacing: 1px;
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

      .rinap-judul {
         text-align: center;
         margin-top: 15px;
         text-decoration: underline;
         font-size: 20px;
      }

      .rinap-nomor {
         text-align: center;
         margin-top: -5px;
         font-size: 14px;
      }

      .rinap-section {
         margin-top: 20px;
         font-size: 15px;
      }

      .rinap-data {
         margin-left: 20px;
         margin-bottom: 15px;
         font-size: 15px;
      }

      .rinap-data td {
         padding: 2px 5px;
         vertical-align: top;
      }

      .rinap-penutup {
         margin-top: 15px;
         text-align: justify;
         font-size: 15px;
      }

      .rinap-ttd-wrapper {
         width: 100%;
         display: flex;
         justify-content: space-between;
         margin-top: 60px;
      }

      .rinap-kolom-ttd {
         width: 30%;
         text-align: center;
         font-size: 15px;
      }

      .rinap-ttd-box {
         margin-top: 70px;
         border-top: 1px solid #000;
         padding-top: 5px;
         font-size: 15px;
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

               // Nama, alamat, bpjs
               document.getElementById("patient_name").innerText = data.patient_name;
               document.getElementById("rinap_alamat").innerText = data.patient_address;
               document.getElementById("rinap_bpjs").innerText = data.patient_bpjs ? data.patient_bpjs : "-";

               // Diagnosa
               document.getElementById("rinap_diagnosa").innerText = data.visit_notes ? data.visit_notes : "-";

               // Tanggal rawat
               let masuk = data.visit_date ? data.visit_date : "-";
               let keluar = data.visit_out ? data.visit_out : "-";
               document.getElementById("rinap_tgl_rawat").innerText = `${masuk} s/d ${keluar}`;

               // Dokter
               document.getElementById("rinap_dokter").innerText = data.doctor_name ? data.doctor_name : "-";
               document.getElementById("rinap_dokter2").innerText = data.doctor_name ? data.doctor_name : "-";
            });
      });
   </script>

   <div class="rinap-container">

      <?php include 'kopsurat.php'; ?>

      <h3 class="rinap-judul">KETERANGAN RAWAT INAP</h3>
      <p class="rinap-nomor">NOMOR : 02 / RITP / TS / XI / 2025</p>

      <div class="rinap-section">

         <p>Yang bertanda tangan dibawah ini :</p>

         <table class="rinap-data">
            <tr>
               <td>Nama</td>
               <td>: dr. Henson Sinaga</td>
            </tr>
            <tr>
               <td>Jabatan</td>
               <td>: Penanggung Jawab Klinik</td>
            </tr>
         </table>

         <p>Dengan ini menyatakan bahwa pasien :</p>

         <table class="rinap-data">
            <tr>
               <td>Nama</td>
               <td>: <span id="patient_name"></span></td>
            </tr>
            <tr>
               <td>Alamat</td>
               <td>: <span id="rinap_alamat"></span></td>
            </tr>
            <tr>
               <td>No. BPJS</td>
               <td>: <span id="rinap_bpjs"></span></td>
            </tr>
         </table>

         <p>Telah mendapat pelayanan kesehatan rawat inap.</p>

         <table class="rinap-data">
            <tr>
               <td>Tempat</td>
               <td>: KLINIK RAWAT INAP TUTUN SEHATI</td>
            </tr>
            <tr>
               <td>Tanggal</td>
               <td>: <span id="rinap_tgl_rawat"></span>01-11-2025</td>
            </tr>
            <tr>
               <td>Diagnosa</td>
               <td>: <span id="rinap_diagnosa">Febis + HEG</span></td>
            </tr>
            <tr>
               <td>Dokter Merawat</td>
               <td>: <span id="rinap_dokter"></span></td>
            </tr>
         </table>

         <p class="rinap-penutup">
            Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan dalam pengajuan klaim biaya rawat inap.
         </p>

      </div>

      <div class="rinap-ttd-wrapper">

         <div class="rinap-kolom-ttd">
            <p>PESERTA / KELUARGA</p>
            <img src="../../../uploads/ttd/regina.png" alt="">
            <div class="rinap-ttd-box">Regina Pardede</div>
         </div>

         <div class="rinap-kolom-ttd">
            <p>DOKTER MERAWAT</p>
            <img src="../../../uploads/ttd/drdevi.png" alt="">
            <div class="rinap-ttd-box"><span id="rinap_dokter2"></span></div>
         </div>

         <div class="rinap-kolom-ttd">
            <p>DOKTER PENANGGUNG JAWAB</p>
            <img src="../../../uploads/ttd/henson.png" style="height:125px;" alt="">
            <div class="rinap-ttd-box">dr. Henson Sinaga</div>
         </div>

      </div>

   </div>

</body>