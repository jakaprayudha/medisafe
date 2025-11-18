<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>CATATAN PERKEMBANGAN PASIEN TERINTEGRASI</title>
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
      }

      .signature-area {
         margin-top: 40px;
         width: 100%;
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

      .no-print {
         margin-top: 10px;
         text-align: center;
      }
   </style>
</head>

<body>

   <div class="title">
      PERJALANAN PERKEMBANGAN PENYAKIT, INSTRUKSI DOKTER DAN PENGOBATAN
   </div>

   <!-- ================= HEADER PASIEN ================= -->
   <table>
      <tr class="header">
         <td>Nama :</td>
         <td>No. RM :</td>
         <td>Ruang :</td>
      </tr>
      <tr class="header">
         <td>Umur :</td>
         <td>JK :</td>
         <td>Tanggal :</td>
         <td>Kelas :</td>
      </tr>
   </table>

   <!-- ================= TABEL CPPT ================= -->
   <table>
      <tr class="center" style="font-weight:bold;">
         <td width="15%">Tanggal/Jam</td>
         <td width="45%">Perjalanan Penyakit</td>
         <td width="25%">Permintaan Dokter dan<br>Pengobatan Medik</td>
         <td width="15%">Paraf/Nama</td>
      </tr>
      <tr class="big-cell">
         <td></td>
         <td></td>
         <td></td>
         <td></td>
      </tr>
   </table>


   <!-- ================= BAGIAN BAWAH (SESUAI GAMBAR) ================= -->
   <div class="section-title">Pemeriksaan Fisik</div>
   <div class="box-large"></div>

   <table style="border: none; margin-top:10px;">
      <tr>
         <td style="border:none; width: 80px;">Diagnosa :</td>
         <td style="border:none; border-bottom:1px solid #000;"></td>
      </tr>
   </table>

   <div class="section-title" style="margin-top:15px;">Pengobatan :</div>
   <div class="box-large"></div>

   <table class="signature-area">
      <tr>
         <td width="50%"></td>
         <td width="50%">
            Paraf / Nama Dokter<br><br><br><br>
            _______________________
         </td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>

</html>