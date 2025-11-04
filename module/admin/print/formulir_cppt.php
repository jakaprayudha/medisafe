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
         height: 20px;
      }

      .center {
         text-align: center;
      }

      .big-cell {
         height: 650px;
         /* ruang besar untuk isi catatan dokter */
      }

      .no-border {
         border: none !important;
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
      CATATAN PERKEMBANGAN PASIEN TERINTEGRASI (CPPT)
   </div>

   <table>
      <tr class="header">
         <td>Nama :</td>
         <td>No. RM:</td>
         <td>Ruang:</td>
      </tr>
      <tr class="header">
         <td>Umur :</td>
         <td>JK:</td>
         <td>Tanggal :</td>
         <td>Kelas :</td>
      </tr>
   </table>

   <table>
      <tr class="center" style="font-weight:bold;">
         <td width="15%">Tanggal/Jam</td>
         <td width="45%">Diagnosa Keperawatan</td>
         <td width="25%">Perkembangan</td>
         <td width="15%">Paraf/Nama</td>
      </tr>
      <tr class="big-cell">
         <td></td>
         <td></td>
         <td></td>
         <td></td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>

</html>