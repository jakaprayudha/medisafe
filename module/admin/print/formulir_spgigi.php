<!-- ========================= KOP SURAT ========================= -->
<div class="form-spgigi">
   <style>
      @page {
         size: A4;
         margin: 20mm 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         background: #fff;
         margin: 0;
         padding: 0;
      }

      .content {
         margin-top: 25px;
      }

      .underline {
         border-bottom: 1px dotted #000;
         padding-bottom: 1px;
         min-width: 200px;
         display: inline-block;
      }

      .bio-table {
         width: 100%;
         margin-top: 10px;
      }

      .bio-table td {
         padding: 4px 0;
         font-size: 12pt;
      }

      .gigi-container {
         text-align: center;
         margin-top: 25px;
      }

      .gigi-cross {
         width: 250px;
         height: 160px;
         margin: auto;
         position: relative;
      }

      .gigi-cross:before {
         content: "";
         position: absolute;
         top: 50%;
         left: 0;
         right: 0;
         border-top: 2px solid #000;
      }

      .gigi-cross:after {
         content: "";
         position: absolute;
         left: 50%;
         top: 0;
         bottom: 0;
         border-left: 2px solid #000;
      }

      .footer {
         text-align: right;
         margin-top: 40px;
      }

      .ttd-block {
         margin-top: 60px;
         text-align: center;
         display: inline-block;
      }

      .sign-line {
         margin-top: 60px;
         border-top: 1px solid #000;
         width: 200px;
         margin-left: auto;
         margin-right: auto;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</div>

<!-- ========================= ISI SURAT ========================= -->
<div class="content">

   <?php require('kopsurat.php') ?>

   <p>No : <span class="underline" id="sp_no_surat"></span></p>
   <p>Lamp : -</p>
   <p>Hal : <b>Surat Pengantar Klaim Prothese Gigi Palsu</b></p>

   <br>

   <p>Menerangkan bahwa Peserta yang bernama:</p>

   <table class="bio-table">
      <tr>
         <td width="25%">Nama</td>
         <td>: <span class="underline" id="sp_nama"></span></td>
      </tr>
      <tr>
         <td>Tanggal Lahir</td>
         <td>: <span class="underline" id="sp_tgllahir"></span></td>
      </tr>
      <tr>
         <td>NIK / No. BPJS</td>
         <td>: <span class="underline" id="sp_nikbpjs"></span></td>
      </tr>
      <tr>
         <td>Alamat</td>
         <td>: <span class="underline" id="sp_alamat"></span></td>
      </tr>
      <tr>
         <td>No. Hp</td>
         <td>: <span class="underline" id="sp_nohp"></span></td>
      </tr>
   </table>

   <br>

   <p>Benar nama tersebut diatas akan melakukan pengklaiman Prothesa Gigi Palsu dengan pola gigi sebagai berikut:</p>

   <div class="gigi-container">
      <div class="gigi-cross"></div>
      <div id="sp_pola" style="margin-top:8px; font-size: 14pt;"></div>
   </div>

   <br><br>

   <p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan seperlunya.</p>

</div>

<!-- ========================= TTD ========================= -->
<div class="footer">
   T. Morawa, <span class="underline" id="sp_tgl_surat"></span>

   <div class="ttd-block">
      <div style="height:80px;">(ttd & stempel)</div>
      <div class="sign-line"></div>
      <div id="sp_dokter">dr. ____________________</div>
   </div>
</div>

<div class="no-print">
   <button onclick="window.print()">🖨 CETAK</button>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("get_sp_gigi.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               document.getElementById(id).innerText = val ?? "-";
            };

            set("sp_no_surat", d.nomor_surat);
            set("sp_nama", d.nama_pasien);
            set("sp_tgllahir", d.tgl_lahir);
            set("sp_nikbpjs", d.nik_bpjs);
            set("sp_alamat", d.alamat);
            set("sp_nohp", d.no_hp);
            set("sp_dokter", d.dokter);
            set("sp_tgl_surat", d.tanggal_surat);

            // Tampilkan pola gigi (contoh: "1, 2, 3")
            set("sp_pola", d.pola_gigi);
         });

   });
</script>