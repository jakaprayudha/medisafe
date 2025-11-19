<body class="perjalanan-body">
   <?php include 'kopsurat.php'; ?>
   <style>
      @page {
         size: A4;
         margin: 1.5cm;
      }

      .perjalanan-body {
         font-family: "Times New Roman", serif;
         font-size: 10pt;
         color: #000;
      }

      .perjalanan-title {
         text-align: center;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 10px;
         line-height: 1.4;
      }

      .perjalanan-table {
         width: 100%;
         border-collapse: collapse;
         border: 1px solid #000;
      }

      .perjalanan-table td,
      .perjalanan-table th {
         border: 1px solid #000;
         padding: 4px 6px;
         vertical-align: top;
         font-size: 10pt;
      }

      .perjalanan-header td {
         height: 20px;
      }

      .perjalanan-center {
         text-align: center;
      }

      .perjalanan-big-cell {
         height: 650px;
      }

      .no-print {
         margin-top: 10px;
         text-align: center;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <div class="perjalanan-title">
      PERJALANAN PERKEMBANGAN PENYAKIT, INSTRUKSI DOKTER DAN PENGOBATAN
   </div>

   <!-- IDENTITAS PASIEN -->
   <table class="perjalanan-table">
      <tr class="perjalanan-header">
         <td>Nama : <span id="p_nama_pasien"></span></td>
         <td>No. RM: <span id="p_nomor_rm"></span></td>
         <td>Ruang: <span id="p_ruang_inap"></span></td>
      </tr>
      <tr class="perjalanan-header">
         <td>Umur : <span id="p_usia"></span></td>
         <td>JK: <span id="p_gender"></span></td>
         <td>Tanggal : <span id="p_tanggal_visit"></span></td>
         <td>Kelas : <span id="p_kelas_visit"></span></td>
      </tr>
   </table>

   <!-- PERJALANAN -->
   <table class="perjalanan-table">
      <tr class="perjalanan-center" style="font-weight:bold;">
         <td width="15%">Tanggal/Jam</td>
         <td width="45%">Perjalanan Penyakit</td>
         <td width="25%">Permintaan Dokter & Pengobatan</td>
         <td width="15%">Tanda Tangan</td>
      </tr>
      <tbody id="tbl_body"></tbody>
   </table>

   <!-- K/U -->
   <table class="perjalanan-table">
      <tr>
         <th>K/U</th>
      </tr>
      <tr class="perjalanan-big-cell">
         <td id="ku_body"></td>
      </tr>
   </table>

   <!-- PEMERIKSAAN FISIK -->
   <table class="perjalanan-table">
      <tr>
         <th>Pemeriksaan Fisik</th>
      </tr>
      <tr class="perjalanan-big-cell">
         <td id="fisik_body"></td>
      </tr>
   </table>

   <!-- DIAGNOSA & PENGOBATAN -->
   <table class="perjalanan-table">
      <tr>
         <th>Diagnosa & Pengobatan</th>
      </tr>
      <tr class="perjalanan-big-cell">
         <td id="diag_body"></td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>
<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const visit = url.get("no");
      const rm = url.get("rm");

      if (!visit || !rm) return;

      /* =============================
         LOAD IDENTITAS PASIEN
      ==============================*/
      fetch(`getpasien.php?no=${visit}&rm=${rm}`)
         .then(res => res.json())
         .then(data => {

            if (!data) return;

            const birth = new Date(data.patient_datebirth);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();

            document.getElementById("p_nama_pasien").innerText = data.patient_name;
            document.getElementById("p_nomor_rm").innerText = data.nomor_rm;
            document.getElementById("p_ruang_inap").innerText = data.source_hub ?? "-";
            document.getElementById("p_usia").innerText = age + " tahun";
            document.getElementById("p_gender").innerText = data.patient_gender;
            document.getElementById("p_tanggal_visit").innerText = today.toISOString().substring(0, 10);
            document.getElementById("p_kelas_visit").innerText =
               data.patient_status == "1" ? "Reguler" : "-";
         });


      /* =============================
         LOAD PERJALANAN PENYAKIT
      ==============================*/
      fetch(`getperjalanan.php?visit=${visit}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {

            if (!resp || resp.status !== "success") return;

            let tbody = "";
            let ku = "";
            let fisik = "";
            let diag = "";
            let obat = "";

            resp.data.forEach(d => {

               tbody += `
                  <tr>
                     <td>${d.cppt_date} ${d.cppt_time}</td>
                     <td>${d.perjalanan}</td>
                     <td>${d.tindakan}</td>
                     <td>${d.tanda_tangan}</td>
                  </tr>
               `;

               ku += d.ku + "<br><br>";
               fisik += d.pemeriksaan_fisik + "<br><br>";
               diag += d.diagnosa + "<br><br>";
               obat += d.pengobatan + "<br><br>";
            });

            document.getElementById("tbl_body").innerHTML = tbody;
            document.getElementById("ku_body").innerHTML = ku;
            document.getElementById("fisik_body").innerHTML = fisik;
            document.getElementById("diag_body").innerHTML = diag + obat;
         });

   });
</script>