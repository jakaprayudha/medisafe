<body>
   <?php include 'kopsurat.php'; ?>
   <div class="form-cppt">
      <style>
         @page {
            size: A4;
            margin: 1.5cm;
         }

         body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            color: #000;
         }

         .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            line-height: 1.4;
         }

         table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
         }

         td,
         th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10pt;
         }

         .header td {
            height: 22px;
         }

         .center {
            text-align: center;
         }

         @media print {
            .no-print {
               display: none;
            }
         }

         .no-print {
            margin-top: 10px;
            text-align: center;
         }
      </style>


      <div class="title">
         CATATAN PERKEMBANGAN PASIEN TERINTEGRASI (CPPT)
      </div>

      <!-- ================= HEADER PASIEN ================= -->
      <table>
         <tr class="header">
            <td>Nama : <span id="p_nama_cppt"></span></td>
            <td>No. RM : <span id="p_rm_cppt"></span></td>
            <td>Ruang : <span id="p_ruang_cppt"></span></td>
         </tr>
         <tr class="header">
            <td>Umur : <span id="p_umur_cppt"></span></td>
            <td>JK : <span id="p_jk_cppt"></span></td>
            <td>Tanggal : <span id="p_tanggal_cppt"></span></td>
            <td>Kelas : <span id="p_cppt"></span></td>
         </tr>
      </table>

      <!-- ================= TABEL CPPT ================= -->
      <table>
         <tr class="center" style="font-weight:bold;">
            <th width="15%">Tanggal/Jam</th>
            <th width="45%">Perkembangan</th>
            <th width="25%">Diagnosa Keperawatan</th>
            <th width="15%">Paraf/Nama</th>
         </tr>
         <tbody id="cppt_body">
            <!-- CPPT akan muncul di sini -->
         </tbody>
      </table>

      <div class="no-print">
         <button onclick="window.print()">🖨 Cetak Halaman</button>
      </div>
   </div>

</body>
<script>
   document.addEventListener("DOMContentLoaded", function() {
      const urlParams = new URLSearchParams(window.location.search);
      const no = urlParams.get("no");
      const rm = urlParams.get("rm");

      if (!no || !rm) return;

      // ======================
      // 1. LOAD DATA PASIEN
      // ======================
      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(data => {
            if (!data) return;

            const birth = new Date(data.patient_datebirth);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();

            document.getElementById("p_nama_cppt").innerText = data.patient_name;
            document.getElementById("p_rm_cppt").innerText = data.nomor_rm;
            document.getElementById("p_ruang_cppt").innerText = data.source_hub || "-";
            document.getElementById("p_umur_cppt").innerText = age + " tahun";
            document.getElementById("p_jk_cppt").innerText = data.patient_gender;
            document.getElementById("p_tanggal_cppt").innerText = today.toISOString().substring(0, 10);
            document.getElementById("p_cppt").innerText = data.patient_status == "1" ? "Reguler" : "-";
         });

      // ======================
      // 2. LOAD DATA CPPT
      // ======================
      fetch(`getcppt.php?visit=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {

            // console.log("CPPT DATA:", resp);

            if (!resp || resp.status !== "success") return;

            const rows = resp.data;
            let html = "";

            rows.forEach(cppt => {
               html += `
                        <tr>
                           <td>${cppt.cppt_date} / ${cppt.cppt_time}</td>
                           <td>
                              <b>S:</b> ${cppt.subjective}<br>
                              <b>O:</b> ${cppt.objective}<br>
                              <b>A:</b> ${cppt.analysis}<br>
                              <b>P:</b> ${cppt.planning}
                           </td>
                           <td>${cppt.instruction}</td>
                           <td>${cppt.users_entry} <br> (${cppt.cppt_profesi})</td>
                        </tr>
                     `;
            });

            document.getElementById("cppt_body").innerHTML = html;
         });

   });
</script>