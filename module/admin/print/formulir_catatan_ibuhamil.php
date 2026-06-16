<div class="ibu-form-catatan">

   <style>
      @page {
         size: A4 potrait;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .ibu-page {
         display: flex;
         justify-content: space-between;
         gap: 15px;
      }

      .ibu-col {
         width: 49%;
      }

      .ibu-title {
         text-align: center;
         font-weight: bold;
         font-size: 14pt;
         text-transform: uppercase;
         margin-bottom: 5px;
      }

      .ibu-subtitle {
         text-align: center;
         font-size: 11pt;
         margin-bottom: 10px;
      }

      .ibu-info {
         font-size: 11pt;
         margin-bottom: 10px;
         line-height: 1.3;
      }

      table.ibu-table {
         width: 100%;
         border-collapse: collapse;
         font-size: 10pt;
      }

      .ibu-table th,
      .ibu-table td {
         border: 1px solid #000;
         padding: 4px;
         text-align: center;
      }

      .ibu-table th {
         font-weight: bold;
         background: #f7f7f7;
      }

      .ibu-left {
         text-align: left;
      }
   </style>

   <?php require 'kopsurat.php' ?>

   <div class="ibu-page">

      <!-- ======================== KOLOM KIRI ======================== -->
      <div class="ibu-col">
         <div class="ibu-title">CATATAN KESEHATAN IBU HAMIL</div>
         <div class="ibu-subtitle">(DIISI OLEH PETUGAS KESEHATAN)</div>

         <div class="ibu-info">
            Hari Pertama Haid Terakhir (HPHT): <span id="hpht"></span><br>
            Taksiran Persalinan (HPL): <span id="hpl"></span><br>
            Tinggi Badan: <span id="tinggi_badan"></span> cm &nbsp;&nbsp;&nbsp;
            Berat Badan Sebelum Kehamilan: <span id="bb_sebelum"></span> kg<br>
            Tekanan Darah Sebelum Hamil: <span id="tensi_sebelum"></span><br>
            Riwayat Penyakit yang diderita Ibu: <span id="riwayat_penyakit"></span><br>
            Riwayat Alergi: <span id="riwayat_alergi"></span>
         </div>

         <table class="ibu-table">
            <thead>
               <tr>
                  <th>Tgl</th>
                  <th class="ibu-left">Keluhan Sekarang</th>
                  <th>Tekanan Darah<br>(mmHg)</th>
                  <th>Berat Badan<br>(kg)</th>
                  <th>Umur Kehamilan<br>(minggu)</th>
                  <th>Tinggi Fundus<br>(cm)</th>
                  <th>Letak Janin</th>
                  <th>Denyut Jantung<br>Janin / Menit</th>
               </tr>
            </thead>

            <tbody id="ibu_table_left"></tbody>
         </table>
      </div>

      <!-- ======================== KOLOM KANAN ======================== -->
      <div class="ibu-col">
         <div class="ibu-title">CATATAN KESEHATAN IBU HAMIL</div>

         <div class="ibu-info">
            Hamil Ke: <span id="hamil_ke"></span><br>
            Jumlah Persalinan: <span id="jumlah_persalinan"></span><br>
            Jumlah Anak Hidup: <span id="jumlah_anak_hidup"></span><br>
            Jumlah Keguguran: <span id="jumlah_keguguran"></span><br>
            Jarak Anak Lahir Terakhir – Sekarang: <span id="jarak"></span><br>
            Status Imunisasi TT: <span id="status_tt"></span><br>
            Golongan Darah Ibu: <span id="gol_darah"></span><br>
            Riwayat Persalinan Terdahulu: <span id="riwayat_persalinan"></span><br>
            Riwayat Kehamilan Terdahulu: <span id="riwayat_kehamilan"></span>
         </div>

         <table class="ibu-table">
            <thead>
               <tr>
                  <th>Bengkak</th>
                  <th>Hasil Pemeriksaan Lab</th>
                  <th>Tindakan / Nasihat</th>
                  <th>Imunisasi TT</th>
                  <th>Keterangan Pemeriksa</th>
               </tr>
            </thead>

            <tbody id="ibu_table_right"></tbody>
         </table>

      </div>
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("get_ibu_hamil.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const h = res.header;
            const d = res.detail;

            // ================== SET HEADER LEFT ==================
            document.getElementById("hpht").innerText = h.hpht;
            document.getElementById("hpl").innerText = h.hpl;
            document.getElementById("tinggi_badan").innerText = h.tinggi_badan;
            document.getElementById("bb_sebelum").innerText = h.bb_sebelum;
            document.getElementById("tensi_sebelum").innerText = h.tensi_sebelum;
            document.getElementById("riwayat_penyakit").innerText = h.riwayat_penyakit;
            document.getElementById("riwayat_alergi").innerText = h.riwayat_alergi;

            // ================== SET HEADER RIGHT ==================
            document.getElementById("hamil_ke").innerText = h.hamil_ke;
            document.getElementById("jumlah_persalinan").innerText = h.jumlah_persalinan;
            document.getElementById("jumlah_anak_hidup").innerText = h.jumlah_anak_hidup;
            document.getElementById("jumlah_keguguran").innerText = h.jumlah_keguguran;
            document.getElementById("jarak").innerText = h.jarak_anak_terakhir;
            document.getElementById("status_tt").innerText = h.status_tt;
            document.getElementById("gol_darah").innerText = h.gol_darah;
            document.getElementById("riwayat_persalinan").innerText = h.riwayat_persalinan;
            document.getElementById("riwayat_kehamilan").innerText = h.riwayat_kehamilan;

            // ================== GENERATE TABLE LEFT ==================
            const leftTable = document.getElementById("ibu_table_left");
            leftTable.innerHTML = "";

            d.forEach(row => {
               leftTable.innerHTML += `
               <tr>
                  <td>${row.tanggal}</td>
                  <td class="ibu-left">${row.keluhan}</td>
                  <td>${row.tekanan_darah}</td>
                  <td>${row.berat_badan}</td>
                  <td>${row.umur_kehamilan}</td>
                  <td>${row.tinggi_fundus}</td>
                  <td>${row.letak_janin}</td>
                  <td>${row.denyut_janin}</td>
               </tr>
            `;
            });

            // ================== GENERATE TABLE RIGHT ==================
            const rightTable = document.getElementById("ibu_table_right");
            rightTable.innerHTML = "";

            d.forEach(row => {
               rightTable.innerHTML += `
               <tr>
                  <td>${row.bengkak}</td>
                  <td>${row.hasil_lab}</td>
                  <td>${row.tindakan}</td>
                  <td>${row.imunisasi_tt}</td>
                  <td>${row.keterangan}</td>
               </tr>
            `;
            });

         });
   });
</script>