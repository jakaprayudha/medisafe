<body>

   <style>
      @page {
         size: A4;
         margin: 1.2cm;
      }

      .rmprint-wrapper {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
         width: 100%;
      }

      .rmprint-title {
         text-align: center;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 12px;
         font-size: 16pt;
      }

      .rmprint-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 6px;
      }

      .rmprint-td {
         padding: 3px 4px;
         vertical-align: top;
      }

      .rmprint-underline {
         border-bottom: 1px dotted #555;
         height: 16px;
      }

      .rmprint-section {
         margin-top: 10px;
      }

      .rmprint-section-title {
         font-weight: bold;
         text-transform: uppercase;
         font-size: 11.5pt;
         border-bottom: 1px solid #000;
         margin-bottom: 4px;
         padding-bottom: 2px;
      }

      .rmprint-box {
         border: 1px solid #000;
         padding: 5px;
         min-height: 60px;
         white-space: pre-line;
      }

      .rmprint-signature {
         margin-top: 20px;
         display: flex;
         justify-content: space-between;
      }

      .rmprint-sign-col {
         width: 48%;
         text-align: center;
         font-size: 11pt;
      }

      .rmprint-sign-line {
         margin-top: 50px;
         border-top: 1px solid #000;
         padding-top: 3px;
      }

      @media print {
         .rmprint-noprint {
            display: none;
         }
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <div class="rmprint-wrapper">
      <div class="rmprint-title">RESUME MEDIS</div>

      <!-- IDENTITAS PASIEN -->
      <table class="rmprint-table">
         <tr>
            <td class="rmprint-td" width="20%">Nama Pasien</td>
            <td class="rmprint-underline rmprint-td" width="30%" id="r_nama"></td>
            <td class="rmprint-td" width="20%">No. Rekam Medis</td>
            <td class="rmprint-underline rmprint-td" width="30%" id="r_rm"></td>
         </tr>

         <tr>
            <td class="rmprint-td">Umur</td>
            <td class="rmprint-underline rmprint-td" id="r_umur"></td>
            <td class="rmprint-td">Jenis Kelamin</td>
            <td class="rmprint-underline rmprint-td" id="r_jk"></td>
         </tr>

         <tr>
            <td class="rmprint-td">Alamat</td>
            <td class="rmprint-underline rmprint-td" colspan="3" id="r_alamat"></td>
         </tr>

         <tr>
            <td class="rmprint-td">Ruang / Kelas</td>
            <td class="rmprint-underline rmprint-td" id="r_ruang"></td>
            <td class="rmprint-td">Tanggal Masuk</td>
            <td class="rmprint-underline rmprint-td" id="r_masuk"></td>
         </tr>

         <tr>
            <td class="rmprint-td">Tanggal Keluar</td>
            <td class="rmprint-underline rmprint-td" id="r_keluar"></td>
            <td class="rmprint-td">DPJP</td>
            <td class="rmprint-underline rmprint-td" id="r_dpjp"></td>
         </tr>
      </table>

      <!-- CONTENT SECTIONS -->
      <div class="rmprint-section">
         <div class="rmprint-section-title">Diagnosa</div>
         <div class="rmprint-box" id="r_diagnosa"></div>
      </div>

      <div class="rmprint-section">
         <div class="rmprint-section-title">Tindakan / Terapi</div>
         <div class="rmprint-box" id="r_tindakan"></div>
      </div>

      <div class="rmprint-section">
         <div class="rmprint-section-title">Hasil Pemeriksaan Penunjang</div>
         <div class="rmprint-box" id="r_penunjang"></div>
      </div>

      <div class="rmprint-section">
         <div class="rmprint-section-title">Obat yang Diberikan</div>
         <div class="rmprint-box" id="r_obat"></div>
      </div>

      <div class="rmprint-section">
         <div class="rmprint-section-title">Instruksi / Anjuran Lanjutan</div>
         <div class="rmprint-box" id="r_instruksi"></div>
      </div>

      <!-- SIGNATURE -->
      <div class="rmprint-signature">
         <div class="rmprint-sign-col">
            <img src="../../../uploads/ttd/farmasi.png" style="height:125px;" alt="">
            <div class="rmprint-sign-line" id="r_perawat">Petugas / Perawat</div>
         </div>
         <div class="rmprint-sign-col">
            <img src="../../../uploads/ttd/drdevi.png" style="height:125px;" alt="">
            <div class="rmprint-sign-line" id="r_dokter">Dokter Penanggung Jawab</div>
         </div>
      </div>

      <div class="rmprint-noprint">
         <button onclick="window.print()">🖨 Cetak Form</button>
      </div>
   </div>

</body>

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const p = new URLSearchParams(window.location.search);
      const no = p.get("no");
      const rm = p.get("rm");
      if (!no || !rm) return;

      // === LOAD DATA PASIEN ===
      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(d => {
            if (!d) return;

            let umur = "";
            if (d.patient_datebirth) {
               const b = new Date(d.patient_datebirth);
               umur = (new Date().getFullYear() - b.getFullYear()) + " Tahun";
            }

            r_nama.innerText = d.patient_name;
            r_rm.innerText = d.nomor_rm;
            r_jk.innerText = d.patient_gender;
            r_umur.innerText = umur;
            r_alamat.innerText = d.patient_address;
            r_ruang.innerText = d.source_hub || "-";
            r_masuk.innerText = d.visit_date || "-";
            r_keluar.innerText = d.visit_out || "-";
            r_dpjp.innerText = d.doctor_name || "-";
         });

      // === LOAD RESUME ===
      fetch(`getresume.php?visit=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(res => {
            if (!res || res.status !== "success") return;
            const x = res.data;

            r_diagnosa.innerText = x.diagnosa || "";
            r_tindakan.innerText = x.tindakan || "";
            r_penunjang.innerText = x.pemeriksaan_penunjang || "";
            r_obat.innerText = x.obat || "";
            r_instruksi.innerText = x.instruksi || "";

            r_perawat.innerText = x.petugas || "Perawat";
            r_dokter.innerText = x.dokter || "Dokter DPJP";
         });

   });
</script>