<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>FORM RESUME MEDIS PASIEN</title>
   <style>
      @page {
         size: A4;
         margin: 1.5cm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
         line-height: 1.5;
      }

      .title {
         text-align: center;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 15px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         border: none;
         margin-bottom: 8px;
      }

      td {
         padding: 4px 6px;
         vertical-align: top;
      }

      .underline {
         border-bottom: 1px dotted #666;
         height: 18px;
      }

      .section {
         margin-top: 10px;
      }

      .section-title {
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 4px;
         border-bottom: 1px solid #000;
         padding-bottom: 2px;
      }

      .box {
         border: 1px solid #000;
         min-height: 100px;
         padding: 6px;
      }

      .signature {
         margin-top: 20px;
         width: 100%;
         display: flex;
         justify-content: space-between;
      }

      .signature .col {
         width: 45%;
         text-align: center;
      }

      .spacer {
         height: 20px;
      }

      .no-print {
         text-align: center;
         margin-top: 15px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <div class="title">
      FORM RESUME MEDIS PASIEN
   </div>

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

   <div class="section">
      <div class="section-title">Diagnosa</div>
      <div class="box" style="height: 100px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Tindakan / Terapi</div>
      <div class="box" style="height: 100px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Hasil Pemeriksaan Penunjang</div>
      <div class="box" style="height: 100px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Obat yang Diberikan</div>
      <div class="box" style="height: 80px;"></div>
   </div>

   <div class="section">
      <div class="section-title">Instruksi / Anjuran Lanjutan</div>
      <div class="box" style="height: 80px;"></div>
   </div>

   <div class="signature">
      <div class="col">
         <div>______________________________</div>
         <div style="margin-top:5px;">Petugas / Perawat</div>
      </div>
      <div class="col">
         <div>______________________________</div>
         <div style="margin-top:5px;">Dokter Penanggung Jawab</div>
      </div>
   </div>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Form</button>
   </div>

</body>

</html>