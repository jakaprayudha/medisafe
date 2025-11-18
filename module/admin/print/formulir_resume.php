<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>FORM RESUME MEDIS PASIEN</title>

   <style>
      @page {
         size: A4;
         margin: 1.2cm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
         margin: 0;
         padding: 0;
         line-height: 1.4;
      }

      .title {
         text-align: center;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 12px;
         font-size: 14pt;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 6px;
      }

      td {
         padding: 3px 4px;
         vertical-align: top;
      }

      .underline {
         border-bottom: 1px dotted #555;
         height: 16px;
      }

      .section {
         margin-top: 8px;
      }

      .section-title {
         font-weight: bold;
         text-transform: uppercase;
         font-size: 11.5pt;
         border-bottom: 1px solid #000;
         margin-bottom: 4px;
         padding-bottom: 2px;
      }

      .box {
         border: 1px solid #000;
         padding: 5px;
         min-height: 60px;
         /* Tinggi disesuaikan biar muat 1 halaman */
      }

      .signature {
         margin-top: 20px;
         display: flex;
         justify-content: space-between;
      }

      .signature .col {
         width: 48%;
         text-align: center;
         font-size: 11pt;
      }

      .sign-line {
         margin-top: 50px;
         border-top: 1px solid #000;
         padding-top: 3px;
      }

      /* Tombol cetak hilang saat print */
      .no-print {
         margin-top: 15px;
         text-align: center;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <div class="title">FORM RESUME MEDIS PASIEN</div>

   <!-- IDENTITAS PASIEN -->
   <table>
      <tr>
         <td width="20%">Nama Pasien</td>
         <td class="underline" width="30%"></td>
         <td width="20%">No. Rekam Medis</td>
         <td class="underline" width="30%"></td>
      </tr>
      <tr>
         <td>Umur</td>
         <td class="underline"></td>
         <td>Jenis Kelamin</td>
         <td class="underline"></td>
      </tr>
      <tr>
         <td>Alamat</td>
         <td class="underline" colspan="3"></td>
      </tr>
      <tr>
         <td>Ruang / Kelas</td>
         <td class="underline"></td>
         <td>Tanggal Masuk</td>
         <td class="underline"></td>
      </tr>
      <tr>
         <td>Tanggal Keluar</td>
         <td class="underline"></td>
         <td>DPJP</td>
         <td class="underline"></td>
      </tr>
   </table>

   <!-- CONTENT BOXES -->
   <div class="section">
      <div class="section-title">Diagnosa</div>
      <div class="box" style="min-height: 65px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Tindakan / Terapi</div>
      <div class="box" style="min-height: 65px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Hasil Pemeriksaan Penunjang</div>
      <div class="box" style="min-height: 65px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Obat yang Diberikan</div>
      <div class="box" style="min-height: 60px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Instruksi / Anjuran Lanjutan</div>
      <div class="box" style="min-height: 60px;"></div>
   </div>

   <!-- SIGNATURE -->
   <div class="signature">
      <div class="col">
         <div class="sign-line">Petugas / Perawat</div>
      </div>
      <div class="col">
         <div class="sign-line">Dokter Penanggung Jawab</div>
      </div>
   </div>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Form</button>
   </div>

</body>

</html>