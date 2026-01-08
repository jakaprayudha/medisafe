<div class="labprint-wrapper">

   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body.labprint-body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      /* TITLE */
      .labprint-title {
         text-align: center;
         font-size: 16px;
         font-weight: bold;
         margin-top: 10px;
         text-transform: uppercase;
      }

      /* INFO TABLE */
      .labprint-info-table {
         width: 100%;
         margin-top: 10px;
         font-size: 11pt;
      }

      .labprint-info-table td {
         padding: 4px 2px;
      }

      /* LAB TABLE */
      table.labprint-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      .labprint-table th,
      .labprint-table td {
         border: 1px solid #000;
         padding: 5px 6px;
         font-size: 11pt;
      }

      .labprint-table th {
         text-align: center;
         font-weight: bold;
      }

      .labprint-section-header {
         font-weight: bold;
         background: #f4f4f4;
      }

      /* NILAI ABNORMAL */
      .labprint-abnormal {
         color: #c00;
         font-weight: bold;
      }

      /* FOOTER FLEX */
      .labprint-footer {
         width: 100%;
         display: flex;
         justify-content: space-between;
         margin-top: 40px;
         padding-top: 10px;
      }

      /* QR AREA */
      .labprint-qr-sec {
         text-align: center;
         width: 140px;
      }

      .labprint-qr-sec img {
         width: 120px;
         height: 120px;
         margin-bottom: 5px;
      }

      .labprint-qr-text {
         font-size: 10pt;
      }

      /* TTD AREA */
      .labprint-ttd-sec {
         text-align: center;
         width: 250px;
         margin-right: 30px;
      }

      .labprint-ttd-line {
         margin: 60px auto 5px auto;
         border-bottom: 1px solid #000;
         width: 180px;
      }

      .labprint-ttd-name {
         font-weight: bold;
         font-size: 11pt;
      }

      .labprint-ttd-role {
         font-size: 10pt;
         margin-top: 2px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <?php require 'kopsurat.php' ?>

   <div class="labprint-title">HASIL LABORATORIUM</div>

   <!-- ================= DATA PASIEN ================= -->
   <table class="labprint-info-table">
      <tr>
         <td width="160px">Nama</td>
         <td>: <span id="lab_nama"></span></td>
         <td width="180px">Tanggal Pemeriksaan</td>
         <td>: <span id="lab_tgl_periksa"></span></td>
      </tr>

      <tr>
         <td>Tanggal Lahir</td>
         <td>: <span id="lab_tgllahir"></span> (<span id="lab_umur"></span> thn)</td>
      </tr>

      <tr>
         <td>Alamat</td>
         <td>: <span id="lab_alamat"></span></td>
      </tr>

      <tr>
         <td>Jenis Kelamin</td>
         <td>: <span id="lab_jk"></span></td>
      </tr>
   </table>

   <!-- ================= TABEL LAB ================= -->
   <table class="labprint-table">
      <tr>
         <th>PEMERIKSAAN</th>
         <th>HASIL</th>
         <th>NILAI NORMAL</th>
      </tr>

      <tr class="labprint-section-header">
         <td colspan="3">Hematologi</td>
      </tr>

      <tr>
         <td>Hemoglobin (Hb)</td>
         <td id="lab_hb"></td>
         <td>11.0 - 17.5 g/dL</td>
      </tr>

      <tr>
         <td>Leukosit (WBC)</td>
         <td id="lab_wbc"></td>
         <td>4.0 - 10.1 ×10³ /μL</td>
      </tr>

      <tr>
         <td>Eritrosit (RBC)</td>
         <td id="lab_rbc"></td>
         <td>3.5 - 5.5 ×10¹² /L</td>
      </tr>

      <tr>
         <td>Trombosit (PLT)</td>
         <td id="lab_plt"></td>
         <td>100 - 300 ×10³ /μL</td>
      </tr>

      <tr>
         <td>Hematokrit (HCT)</td>
         <td id="lab_hct"></td>
         <td>37 - 50 %</td>
      </tr>

      <tr>
         <td>MCV</td>
         <td id="lab_mcv"></td>
         <td>82 - 95 fL</td>
      </tr>

      <tr>
         <td>MCH</td>
         <td id="lab_mch"></td>
         <td>27 - 31 pg</td>
      </tr>

      <tr>
         <td>MCHC</td>
         <td id="lab_mchc"></td>
         <td>32 - 36 g/dL</td>
      </tr>

      <tr>
         <td>LYM</td>
         <td id="lab_lym"></td>
         <td>23.4 - 40 %</td>
      </tr>

      <tr class="labprint-section-header">
         <td colspan="3">Widal / Salmonella</td>
      </tr>

      <tr>
         <td>Salmonella Typhi (O)</td>
         <td id="lab_sto"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi A – O</td>
         <td id="lab_spa_o"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi B – O</td>
         <td id="lab_spb_o"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi C – O</td>
         <td id="lab_spc_o"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Typhi (H)</td>
         <td id="lab_sth"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi A – H</td>
         <td id="lab_spa_h"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi B – H</td>
         <td id="lab_spb_h"></td>
         <td>≤ 1/40</td>
      </tr>
      <tr>
         <td>Salmonella Paratyphi C – H</td>
         <td id="lab_spc_h"></td>
         <td>≤ 1/40</td>
      </tr>
   </table>

   <!-- ================= FOOTER ================= -->
   <div class="labprint-footer">

      <div class="labprint-qr-sec">
         <div id="lab_qr"></div>
         <div class="labprint-qr-text">Scan untuk verifikasi hasil</div>
      </div>

      <div class="labprint-ttd-sec">
         <div style="height:60px;">Pengisi Data</div>
         <img src="../../../uploads/ttd/lab.png" alt="">
         <div class="labprint-ttd-line"></div>
         <div class="labprint-ttd-name" id="lab_petugas"></div>
         <div class="labprint-ttd-role">Petugas Laboratorium</div>
      </div>

   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const urlParams = new URLSearchParams(window.location.search);
      const no = urlParams.get("no");
      const rm = urlParams.get("rm");

      if (!no || !rm) return;

      fetch("getlab.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data || {};

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            // DATA PASIEN
            set("lab_nama", d.nama_pasien);
            set("lab_tgl_periksa", d.tgl_pemeriksaan);
            set("lab_tgllahir", d.tgl_lahir);
            set("lab_umur", d.umur);
            set("lab_alamat", d.alamat);
            set("lab_jk", d.jk);

            // HEMATOLOGI
            set("lab_hb", d.hb);
            set("lab_wbc", d.wbc);
            set("lab_rbc", d.rbc);
            set("lab_plt", d.plt);
            set("lab_hct", d.hct);
            set("lab_mcv", d.mcv);
            set("lab_mch", d.mch);
            set("lab_mchc", d.mchc);
            set("lab_lym", d.lym);

            // WIDAL
            set("lab_sto", d.sto);
            set("lab_spa_o", d.spa_o);
            set("lab_spb_o", d.spb_o);
            set("lab_spc_o", d.spc_o);
            set("lab_sth", d.sth);
            set("lab_spa_h", d.spa_h);
            set("lab_spb_h", d.spb_h);
            set("lab_spc_h", d.spc_h);

            // PETUGAS
            set("lab_petugas", d.petugas);

            // QR
            const verifyUrl = window.location.origin +
               "/verify_lab.php?no=" + encodeURIComponent(no) +
               "&rm=" + encodeURIComponent(rm);

            const qrContainer = document.getElementById("lab_qr");
            if (qrContainer) {
               qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(verifyUrl)}">`;
            }

            // ABNORMAL COLORING
            const abnormal = (id, low, high) => {
               const el = document.getElementById(id);
               if (!el) return;
               let v = parseFloat((el.innerText || "").replace(",", "."));
               if (!isNaN(v) && (v < low || v > high)) el.classList.add("labprint-abnormal");
            };

            abnormal("lab_hb", 11.0, 17.5);
            abnormal("lab_wbc", 4.0, 10.1);
            abnormal("lab_rbc", 3.5, 5.5);
            abnormal("lab_plt", 100, 300);
            abnormal("lab_hct", 37, 50);
            abnormal("lab_mcv", 82, 95);
            abnormal("lab_mch", 27, 31);
            abnormal("lab_mchc", 32, 36);
            abnormal("lab_lym", 23.4, 40);

         });

   });
</script>