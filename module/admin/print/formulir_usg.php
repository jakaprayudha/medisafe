<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Hasil Pemeriksaan USG</title>

   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
      }

      .container {
         width: 100%;
         max-width: 900px;
         margin: auto;
      }

      /* ===== KOP SURAT ===== */
      .kop {
         text-align: center;
         margin-bottom: 5px;
      }

      .kop h1 {
         font-size: 26pt;
         font-weight: bold;
         margin: 0;
         letter-spacing: 1px;
      }

      .kop h2 {
         font-size: 18pt;
         margin: -3px 0 0 0;
         font-weight: bold;
      }

      .kop .alamat {
         font-size: 11pt;
         margin-top: 3px;
      }

      hr {
         border: none;
         border-top: 2px solid #000;
         margin-top: 10px;
         margin-bottom: 20px;
      }

      /* ===== TITLE ===== */
      .title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         margin-bottom: 10px;
      }

      /* ===== IDENTITAS PASIEN ===== */
      table.identitas {
         width: 100%;
         margin-bottom: 15px;
         border-collapse: collapse;
      }

      table.identitas td {
         padding: 4px 3px;
         font-size: 12pt;
      }

      .line {
         border-bottom: 1px dotted #666;
         display: inline-block;
         width: 260px;
         height: 16px;
      }

      /* ===== KOTAK USG ===== */
      .usg-box {
         width: 100%;
         height: 230px;
         /* agar persis muat 3 box */
         border: 2px solid #000;
         background: #fbfbfb;
         margin-bottom: 15px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 13pt;
         font-style: italic;
         color: #444;
      }

      /* ===== TTD ===== */
      .footer {
         text-align: right;
         margin-top: 10px;
         font-size: 12pt;
      }
   </style>
</head>

<body>

   <div class="container">

      <!-- ===== KOP SURAT ===== -->
      <div class="kop">
         <h1>KLINIK TUTUN SEHATI</h1>
         <h2>TANJUNG MORAWA</h2>
         <div class="alamat">
            Jl. Medan - Lubuk Pakam / Pasar Baru Gg. Serasi No. 2 Tanjung Morawa <br>
            Telp: 061-7945676 &nbsp;&bull;&nbsp; HP: 0812-6322-6990
         </div>
      </div>

      <hr>

      <!-- ===== TITLE ===== -->
      <div class="title">HASIL PEMERIKSAAN USG</div>

      <!-- ===== IDENTITAS PASIEN ===== -->
      <table class="identitas">
         <tr>
            <td width="28%">Nama Pasien</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>No. Rekam Medis</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Usia Kandungan</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Tanggal Pemeriksaan</td>
            <td>: <span class="line"></span></td>
         </tr>
      </table>

      <!-- ===== 3 KOTAK USG ===== -->
      <div class="usg-box">Gambar USG 1 (kosong)</div>
      <div class="usg-box">Gambar USG 2 (kosong)</div>
      <div class="usg-box">Gambar USG 3 (kosong)</div>

      <!-- ===== TTD ===== -->
      <div class="footer">
         Dokter Pemeriksa:<br><br><br>
         ____________________________
      </div>

   </div>

</body>

</html>