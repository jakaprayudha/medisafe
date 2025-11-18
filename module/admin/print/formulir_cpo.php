<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Catatan Pemberian Obat</title>
   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         color: #000;
      }

      .header {
         text-align: center;
         margin-bottom: 5px;
      }

      .header h1 {
         margin: 0;
         font-size: 28px;
         font-weight: bold;
      }

      .header h2 {
         margin: 0;
         margin-top: -5px;
         font-size: 24px;
         font-weight: bold;
      }

      .alamat {
         margin-top: 3px;
         font-size: 11pt;
      }

      .title {
         margin-top: 10px;
         text-align: center;
         font-weight: bold;
         font-size: 16pt;
         text-decoration: underline;
      }

      table.info {
         width: 100%;
         margin-top: 15px;
         font-size: 12pt;
      }

      table.info td {
         padding: 3px 0;
      }

      table.data {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
         font-size: 11pt;
      }

      table.data th,
      table.data td {
         border: 1px solid #000;
         padding: 4px 5px;
         vertical-align: top;
      }

      .center {
         text-align: center;
      }

      .small-header {
         font-size: 10pt;
         text-align: center;
      }
   </style>
</head>

<body>

   <!-- HEADER -->
   <div class="header">
      <h1>KLINIK PRATAMA RAWAT INAP</h1>
      <h2>TUTUN SEHATI</h2>
      <div class="alamat">
         Jl. Pasar Baru KM 16,5 Tanjung Morawa - A No. 2 &nbsp; 📞 082165281225 <br>
         Email: tutunsehati@yahoo.com
      </div>
   </div>

   <div class="title">CATATAN PEMBERIAN OBAT</div>

   <!-- INFO PASIEN -->
   <table class="info">
      <tr>
         <td width="20%">NAMA PASIEN</td>
         <td>: .......................................................</td>
         <td width="20%">NOMOR RM</td>
         <td>: ................................</td>
      </tr>
      <tr>
         <td>TANGGAL LAHIR</td>
         <td>: .......................................................</td>
         <td>RUANGAN</td>
         <td>: ................................</td>
      </tr>
      <tr>
         <td>DIAGNOSA</td>
         <td>: .......................................................</td>
         <td></td>
         <td></td>
      </tr>
   </table>

   <!-- TABEL OBAT -->
   <table class="data">
      <tr>
         <th width="70px">Tanggal</th>
         <th width="200px">Nama Obat Oral<br>dan Injeksi</th>
         <th width="70px">Dosis</th>
         <th width="90px">Signature</th>
         <th colspan="4" class="center">Jadwal dan Jam Pemberian Obat</th>
         <th width="80px">Paraf<br>Keluarga</th>
         <th width="80px">Paraf<br>Petugas</th>
      </tr>

      <tr class="small-header">
         <th></th>
         <th></th>
         <th></th>
         <th></th>
         <th width="60px">Pagi</th>
         <th width="60px">Siang</th>
         <th width="60px">Sore</th>
         <th width="60px">Malam</th>
         <th></th>
         <th></th>
      </tr>

      <!-- Template Baris Kosong (duplikasi sesuai kebutuhan) -->
      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td></td>
         <td></td>
      </tr>

      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td></td>
         <td></td>
      </tr>

      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td class="center"></td>
         <td></td>
         <td></td>
      </tr>

   </table>

</body>

</html>