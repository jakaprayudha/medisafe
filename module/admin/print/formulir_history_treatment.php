<div class="form-history-treatment">
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

      .big-cell {
         height: 350px;
      }

      .section-title {
         font-weight: bold;
         margin: 10px 0 5px 0;
      }

      .box-large {
         width: 100%;
         border: 1px solid #000;
         height: 160px;
         margin-bottom: 10px;
         padding: 5px;
         box-sizing: border-box;
      }

      .signature-area td {
         border: none !important;
         padding-top: 10px;
         text-align: center;
         font-size: 10pt;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <?php require 'kopsurat.php' ?>

   <h3 style="text-align:center;">PERJALANAN PERKEMBANGAN PENYAKIT, INSTRUKSI DOKTER DAN PENGOBATAN</h3>

   <!-- ================= HEADER PASIEN ================= -->
   <table>
      <tr class="header">
         <td>Nama : <span id="h_nama"></span></td>
         <td>No. RM : <span id="h_rm"></span></td>
         <td>Ruang : <span id="h_ruang"></span></td>
      </tr>
      <tr class="header">
         <td>Umur : <span id="h_umur"></span></td>
         <td>JK : <span id="h_jk"></span></td>
         <td>Tanggal : <span id="h_tanggal"></span></td>
         <td>Kelas : <span id="h_kelas"></span></td>
      </tr>
   </table>

   <!-- ================= TABEL CPPT ================= -->
   <table>
      <tr class="center" style="font-weight:bold;">
         <td width="15%">Tanggal/Jam</td>
         <td width="45%">Perjalanan Penyakit</td>
         <td width="25%">Permintaan Dokter & Pengobatan</td>
         <td width="15%">Paraf/Nama</td>
      </tr>

      <tr class="big-cell">
         <td id="cppt_datetime"></td>
         <td id="cppt_perjalanan"></td>
         <td id="cppt_tindakan"></td>
         <td id="cppt_paraf"></td>
      </tr>
   </table>

   <!-- ================= PEMERIKSAAN FISIK ================= -->
   <div class="section-title">Pemeriksaan Fisik</div>
   <div class="box-large" id="cppt_fisik"></div>

   <!-- ================= DIAGNOSA ================= -->
   <table style="border:none; margin-top:10px;">
      <tr>
         <td style="border:none; width: 80px;">Diagnosa :</td>
         <td style="border:none; border-bottom:1px solid #000;">
            <span id="cppt_diagnosa"></span>
         </td>
      </tr>
   </table>

   <!-- ================= PENGOBATAN ================= -->
   <div class="section-title" style="margin-top:15px;">Pengobatan :</div>
   <div class="box-large" id="cppt_pengobatan"></div>

   <!-- ================= PARAF ================= -->
   <table class="signature-area">
      <tr>
         <td width="50%"></td>
         <td width="50%">
            Paraf / Nama Dokter:<br><br><br><br>
            <span id="cppt_paraf2"></span><br>
            _______________________
         </td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("getcppttindakan.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {
            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               let el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            // ========== HEADER PASIEN ==========
            set("h_nama", d.nama_pasien ?? "");
            set("h_rm", d.nomor_rm ?? "");
            set("h_ruang", d.ruang ?? "-");
            set("h_umur", d.umur ?? "-");
            set("h_jk", d.jk ?? "-");
            set("h_tanggal", d.cppt_date ?? "-");
            set("h_kelas", d.kelas ?? "-");

            // ========== CPPT MAIN TABLE ==========
            set("cppt_datetime", `${d.cppt_date} / ${d.cppt_time}`);
            set("cppt_perjalanan", d.perjalanan);
            set("cppt_tindakan", d.tindakan);
            set("cppt_paraf", d.tanda_tangan);

            // ========== PEMERIKSAAN FISIK ==========
            set("cppt_fisik", d.pemeriksaan_fisik);

            // ========== DIAGNOSA ==========
            set("cppt_diagnosa", d.diagnosa);

            // ========== PENGOBATAN ==========
            set("cppt_pengobatan", d.pengobatan);

            // ========== PARAF BAWAH ==========
            set("cppt_paraf2", d.tanda_tangan);
         });
   });
</script>