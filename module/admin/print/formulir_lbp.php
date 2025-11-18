<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>LBP BPJS – Klaim RITP</title>
   <style>
      @page {
         size: A4;
         margin: 1.5cm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .header {
         text-align: center;
         margin-bottom: 10px;
      }

      .header img {
         width: 120px;
         margin-bottom: 5px;
      }

      .title {
         font-weight: bold;
         text-transform: uppercase;
         margin-top: 5px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
      }

      th,
      td {
         border: 1px solid #000;
         vertical-align: top;
         padding: 6px;
      }

      th {
         text-align: center;
         font-weight: bold;
      }

      /* Kolom */
      .col-tgl {
         width: 12%;
      }

      .col-uraian {
         width: 68%;
      }

      .col-ttdpasien {
         width: 20%;
         text-align: center;
      }

      .signature-area {
         margin-top: 40px;
         width: 100%;
         text-align: right;
      }

      .doctor-sign {
         margin-top: 60px;
         display: inline-block;
         text-align: center;
      }

      .doctor-line {
         border-top: 1px solid #000;
         width: 180px;
         margin: 3px auto 0 auto;
         padding-top: 2px;
      }

      .no-print {
         text-align: center;
         margin-top: 20px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <div class="header">
      <img src="https://seeklogo.com/images/B/bpjs-kesehatan-logo-496D1735E1-seeklogo.com.png" alt="BPJS Logo">
      <div class="title">LEMbar Bukti Pelayanan (LBP)</div>
      <div class="title">KLAIM RITP</div>
      <div class="title">BPJS Kesehatan Cabang Lubuk Pakam</div>
   </div>

   <table>
      <tr>
         <th class="col-tgl">TGL</th>
         <th class="col-uraian">URAIAN PELAYANAN</th>
         <th class="col-ttdpasien">TANDA TANGAN PASIEN</th>
      </tr>

      <!-- Baris contoh, bisa loop dari database -->
      <tr>
         <td>28/08</td>
         <td>
            K/D OS datang dengan keluhan lemas (+), pusing, nyeri ulu hati (+),
            mual muntah (+)<br>
            Dx: GERD<br><br>
            ▪ IVFD RL 20 tth<br>
            ▪ Ranitidine 1 amp<br>
            ▪ Ondansetron 1 amp<br>
            ▪ Antasida 3x1<br>
            ▪ Vit B-complex 2x1
         </td>
         <td>✒️</td>
      </tr>

      <tr>
         <td>29/08</td>
         <td>
            S/ : pusing (+), nafsu makan berkurang (+)<br>
            O/TD 120/60, T 36.2<br>
            A/ Dyspepsia syndrome<br><br>
            ▪ Ranitidine 1 amp<br>
            ▪ Ondansetron 1 amp<br>
            ▪ Antasida 3x1<br>
            ▪ Omeprazole 1 kaps 2x1
         </td>
         <td>✒️</td>
      </tr>

      <tr>
         <td>30/08</td>
         <td>
            S/Mual (+) lemas (+)<br>
            O/TD 116/70, T 36.4<br>
            A/ Dyspepsia syndrome<br><br>
            ▪ IVFD RL 20 tth<br>
            ▪ Ranitidine 1 amp<br>
            ▪ Ondansetron 1 amp<br>
            ▪ Antasida 3x1<br>
            ▪ Omeprazole 2x1<br>
            ▪ Domperidone 1x1
         </td>
         <td>✒️</td>
      </tr>

   </table>

   <div class="signature-area">
      <div>Dokter yang merawat</div>
      <div class="doctor-sign">
         <div style="height:60px;">(ttd)</div>
         <div class="doctor-line">dr. .......................</div>
      </div>
   </div>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>

</html>