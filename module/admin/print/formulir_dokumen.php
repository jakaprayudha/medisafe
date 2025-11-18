<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Cetak Dokumen Resmi</title>

   <style>
      @page {
         size: A4;
         margin: 12mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
         background: #fff;
      }

      .page {
         width: 100%;
         min-height: 100%;
         border: 1px solid #000;
         padding: 15px;
         box-sizing: border-box;
         background: white;
      }

      /* Judul besar */
      .document-title {
         text-align: center;
         font-size: 18pt;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 10px;
      }

      /* Subjudul */
      .subtitle {
         text-align: center;
         font-size: 12pt;
         margin-top: -8px;
         margin-bottom: 18px;
      }

      /* Tabel data dokumen */
      table.data {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      table.data td {
         padding: 5px 2px;
         vertical-align: top;
         font-size: 12pt;
      }

      .line {
         border-bottom: 1px dotted #333;
         display: inline-block;
         width: 250px;
         height: 15px;
      }

      /* Area foto dokumen (misal foto KTP, KK, Akta Fisik) */
      .photo-box {
         width: 100%;
         height: 350px;
         border: 2px solid #000;
         display: flex;
         justify-content: center;
         align-items: center;
         margin: 15px 0;
         background: #fafafa;
         font-style: italic;
         color: #666;
         font-size: 14pt;
      }

      /* Cap / tanda tangan */
      .signature-wrapper {
         display: flex;
         justify-content: space-between;
         margin-top: 40px;
      }

      .sign-box {
         width: 45%;
         text-align: center;
         font-size: 12pt;
      }

      .sign-line {
         margin-top: 60px;
         border-top: 1px solid #000;
         padding-top: 5px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }

      .no-print {
         margin-top: 20px;
         text-align: center;
      }
   </style>

</head>

<body>

   <div class="page">

      <!-- Judul Dokumen -->
      <div class="document-title">DOKUMEN KEPENDUDUKAN</div>
      <div class="subtitle">(KTP / KK / AKTA KELAHIRAN / BUKU NIKAH)</div>

      <!-- Data Umum -->
      <table class="data">
         <tr>
            <td width="28%">Nama Lengkap</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>NIK / Nomor Dokumen</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Tempat / Tanggal Lahir</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Alamat</td>
            <td>: <span class="line" style="width: 400px;"></span></td>
         </tr>
         <tr>
            <td>Jenis Dokumen</td>
            <td>: <span class="line"></span></td>
         </tr>
         <tr>
            <td>Tanggal Cetak</td>
            <td>: <span class="line"></span></td>
         </tr>
      </table>

      <!-- Kotak Foto Dokumen (Bisa Foto KTP / Foto KK / Foto Akta) -->
      <div class="photo-box">
         Foto Dokumen (KTP/KK/Akta) – Maksimal A4
      </div>

      <!-- Bagian Tanda Tangan -->
      <div class="signature-wrapper">
         <div class="sign-box">
            Pemegang Dokumen<br><br><br>
            <div class="sign-line">____________________________</div>
         </div>

         <div class="sign-box">
            Petugas / Instansi<br><br><br>
            <div class="sign-line">____________________________</div>
         </div>
      </div>

   </div>

   <!-- Tombol Print -->
   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Dokumen</button>
   </div>

</body>

</html>