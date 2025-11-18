<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Surat Pengantar Prothese Gigi Palsu</title>

   <style>
      @page {
         size: A4;
         margin: 20mm 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
         color: #000;
         background: #fff;
      }

      /* ========================= KOP SURAT ========================= */
      .kop-wrapper {
         width: 100%;
         text-align: center;
         position: relative;
         padding-bottom: 10px;
      }

      .kop-logo-left {
         position: absolute;
         left: 0;
         top: 5px;
         width: 70px;
      }

      .kop-logo-right {
         position: absolute;
         right: 0;
         top: 5px;
         width: 70px;
      }

      .kop-title-1 {
         font-size: 32px;
         font-weight: bold;
         letter-spacing: 2px;
         margin-bottom: -6px;
      }

      .kop-title-2 {
         font-size: 26px;
         font-weight: bold;
         margin-top: 2px;
      }

      .kop-alamat {
         font-size: 11pt;
         margin-top: 8px;
         padding-top: 5px;
         border-top: 2px solid #000;
         line-height: 1.4;
      }

      /* ========================= ISI SURAT ========================= */
      .content {
         margin-top: 25px;
      }

      .content p {
         margin: 5px 0;
      }

      .underline {
         border-bottom: 1px dotted #000;
         display: inline-block;
         min-width: 250px;
         padding-bottom: 1px;
      }

      .bio-table {
         width: 100%;
         margin-top: 10px;
      }

      .bio-table td {
         padding: 4px 0;
         vertical-align: top;
         font-size: 12pt;
      }

      /* ========================= POLA GIGI ========================= */
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

      /* ========================= FOOTER TTD ========================= */
      .footer {
         width: 100%;
         margin-top: 40px;
         text-align: right;
         font-size: 12pt;
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

      .no-print {
         text-align: center;
         margin-top: 20px;
      }
   </style>
</head>

<body>

   <!-- ========================= KOP SURAT ========================= -->
   <div class="kop-wrapper">

      <img src="logo_kiri.png" class="kop-logo-left">
      <img src="logo_kanan.png" class="kop-logo-right">

      <div class="kop-title-1">KLINIK</div>
      <div class="kop-title-2">TUTUN SEHATI</div>

      <div class="kop-alamat">
         Jl. Pasar Baru Km. 17 Tanjung Morawa A No. 2<br>
         Telp. 061-7945676 &nbsp;&nbsp; Email: tutunsehati@yahoo.com
      </div>
   </div>

   <!-- ========================= ISI SURAT ========================= -->
   <div class="content">

      <p>No : <span class="underline" style="min-width:180px;">003/KTS/IX/2023</span></p>
      <p>Lamp : -</p>
      <p>Hal : <b>Surat Pengantar Klaim Prothese Gigi Palsu</b></p>

      <br>

      <p>Menerangkan bahwa Peserta yang bernama:</p>

      <table class="bio-table">
         <tr>
            <td width="25%">Nama</td>
            <td>: <span class="underline">CINDY ANUNI</span></td>
         </tr>
         <tr>
            <td>Tanggal Lahir</td>
            <td>: <span class="underline">11 JANUARI 2001</span></td>
         </tr>
         <tr>
            <td>NIK / No. BPJS</td>
            <td>: <span class="underline">0003970786936</span></td>
         </tr>
         <tr>
            <td>Alamat</td>
            <td>: <span class="underline">DUSUN V, TENGA SARI, TANJUNG MORAWA</span></td>
         </tr>
         <tr>
            <td>No. Hp</td>
            <td>: <span class="underline">0812-8133-2170</span></td>
         </tr>
      </table>

      <br>

      <p>
         Benar nama tersebut diatas akan melakukan pengklaiman Prothesa Gigi Palsu dengan pola Gigi sebagai berikut:
      </p>

      <div class="gigi-container">
         <div class="gigi-cross"></div>
         <div style="margin-top:8px; font-size: 14pt;">1 &nbsp;&nbsp; 2 &nbsp;&nbsp; 3</div>
      </div>

      <br><br>

      <p>
         Demikian surat pengantar ini dibuat untuk dapat dipergunakan seperlunya.
      </p>

   </div>

   <!-- ========================= TTD ========================= -->
   <div class="footer">
      T. Morawa, <span class="underline" style="min-width:120px;">09 September 2023</span>

      <div class="ttd-block">
         <div style="height:80px;">(ttd & stempel)</div>
         <div class="sign-line"></div>
         <div>dr. ____________________</div>
      </div>
   </div>

   <div class="no-print">
      <button onclick="window.print()">🖨 CETAK</button>
   </div>

</body>

</html>