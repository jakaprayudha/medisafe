<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Hasil Pemeriksaan EKG</title>

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
         text-transform: uppercase;
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

      /* ===== KOTAK EKG ===== */
      .ekg-box {
         width: 100%;
         height: 300px;
         border: 2px solid #000;
         background: #fafafa;
         margin-bottom: 20px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 13pt;
         font-style: italic;
         color: #555;
      }

      /* ===== CATATAN DOKTER ===== */
      .note-title {
         font-weight: bold;
         margin-bottom: 5px;
         margin-top: 10px;
      }

      .note-box {
         width: 100%;
         height: 120px;
         border: 1px solid #000;
         padding: 8px;
         background: #fff;
         font-size: 12pt;
      }

      /* ===== TTD ===== */
      .footer {
         text-align: right;
         margin-top: 20px;
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
      <div class="title">HASIL PEMERIKSAAN EKG</div>

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
            <td>Usia</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Tanggal Pemeriksaan</td>
            <td>: <span class="line"></span></td>
         </tr>
      </table>

      <!-- ===== KOTAK EKG (2 gambar besar) ===== -->
      <div class="ekg-box">Gambar EKG 1 (kosong)</div>
      <div class="ekg-box">Gambar EKG 2 (kosong)</div>

      <!-- ===== CATATAN DOKTER ===== -->
      <div class="note-title">Interpretasi/ Catatan Dokter:</div>
      <div class="note-box"></div>

      <!-- ===== TTD ===== -->
      <div class="footer">
         Dokter Pemeriksa:<br><br><br>
         ____________________________
      </div>

   </div>

</body>

</html>