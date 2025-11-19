<body>

   <style>
      @page {
         size: A4;
         margin: 1.2cm;
      }

      .form-resume {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
         width: 100%;
      }

      .form-resume .title {
         text-align: center;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 12px;
         font-size: 16pt;
      }

      .form-resume table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 6px;
      }

      .form-resume td {
         padding: 3px 4px;
         vertical-align: top;
      }

      .form-resume .underline {
         border-bottom: 1px dotted #555;
         height: 16px;
      }

      .form-resume .section {
         margin-top: 10px;
      }

      .form-resume .section-title {
         font-weight: bold;
         text-transform: uppercase;
         font-size: 11.5pt;
         border-bottom: 1px solid #000;
         margin-bottom: 4px;
         padding-bottom: 2px;
      }

      .form-resume .box {
         border: 1px solid #000;
         padding: 5px;
         min-height: 60px;
      }

      .form-resume .signature {
         margin-top: 20px;
         display: flex;
         justify-content: space-between;
      }

      .form-resume .signature .col {
         width: 48%;
         text-align: center;
         font-size: 11pt;
      }

      .form-resume .sign-line {
         margin-top: 50px;
         border-top: 1px solid #000;
         padding-top: 3px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <div class="form-resume">
      <div class="title">RESUME MEDIS</div>

      <!-- IDENTITAS PASIEN -->
      <table>
         <tr>
            <td width="20%">Nama Pasien</td>
            <td class="underline" width="30%" id="r_nama"></td>
            <td width="20%">No. Rekam Medis</td>
            <td class="underline" width="30%" id="r_rm"></td>
         </tr>

         <tr>
            <td>Umur</td>
            <td class="underline" id="r_umur"></td>
            <td>Jenis Kelamin</td>
            <td class="underline" id="r_jk"></td>
         </tr>

         <tr>
            <td>Alamat</td>
            <td class="underline" colspan="3" id="r_alamat"></td>
         </tr>

         <tr>
            <td>Ruang / Kelas</td>
            <td class="underline" id="r_ruang"></td>
            <td>Tanggal Masuk</td>
            <td class="underline" id="r_masuk"></td>
         </tr>

         <tr>
            <td>Tanggal Keluar</td>
            <td class="underline" id="r_keluar"></td>
            <td>DPJP</td>
            <td class="underline" id="r_dpjp"></td>
         </tr>
      </table>

      <!-- CONTENT BOXES -->
      <div class="section">
         <div class="section-title">Diagnosa</div>
         <div class="box" id="r_diagnosa"></div>
      </div>

      <div class="section">
         <div class="section-title">Tindakan / Terapi</div>
         <div class="box" id="r_tindakan"></div>
      </div>

      <div class="section">
         <div class="section-title">Hasil Pemeriksaan Penunjang</div>
         <div class="box" id="r_penunjang"></div>
      </div>

      <div class="section">
         <div class="section-title">Obat yang Diberikan</div>
         <div class="box" id="r_obat"></div>
      </div>

      <div class="section">
         <div class="section-title">Instruksi / Anjuran Lanjutan</div>
         <div class="box" id="r_instruksi"></div>
      </div>

      <div class="signature">
         <div class="col">
            <div class="sign-line" id="r_perawat">Petugas / Perawat</div>
         </div>
         <div class="col">
            <div class="sign-line" id="r_dokter">Dokter Penanggung Jawab</div>
         </div>
      </div>

      <div class="no-print">
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

      // ===== LOAD PASIEN =====
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
            r_masuk.innerText = d.tanggal_masuk || "-";
            r_keluar.innerText = d.tanggal_keluar || "-";
            r_dpjp.innerText = d.dpjp || "-";
         });

      // ===== LOAD RESUME =====
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