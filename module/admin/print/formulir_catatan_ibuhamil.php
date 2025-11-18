<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Catatan Kesehatan Ibu Hamil</title>

   <style>
      @page {
         size: A4 landscape;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .page {
         display: flex;
         justify-content: space-between;
         gap: 15px;
      }

      .col {
         width: 49%;
      }

      .title {
         text-align: center;
         font-weight: bold;
         font-size: 14pt;
         text-transform: uppercase;
         margin-bottom: 5px;
      }

      .subtitle {
         text-align: center;
         font-size: 11pt;
         margin-bottom: 10px;
      }

      .info {
         font-size: 11pt;
         margin-bottom: 10px;
         line-height: 1.3;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         font-size: 10pt;
      }

      th,
      td {
         border: 1px solid #000;
         padding: 4px;
         text-align: center;
      }

      th {
         font-weight: bold;
         background: #f7f7f7;
      }

      .left {
         text-align: left;
      }

      .big-space {
         height: 280px;
      }
   </style>
</head>

<body>

   <div class="page">

      <!-- =================== KOLOM KIRI =================== -->
      <div class="col">
         <div class="title">CATATAN KESEHATAN IBU HAMIL</div>
         <div class="subtitle">(DIISI OLEH PETUGAS KESEHATAN)</div>

         <div class="info">
            Hari Pertama Haid Terakhir (HPHT): ___________________________ <br>
            Taksiran Persalinan (HPL): __________________________________ <br>
            Tinggi Badan: ______ cm &nbsp;&nbsp;&nbsp; Berat Badan Sebelum Kehamilan: ______ kg <br>
            Tekanan Darah Sebelum Hamil: _____________ <br>
            Riwayat Penyakit yang diderita Ibu: __________________________ <br>
            Riwayat Alergi: ____________________________________________
         </div>

         <table>
            <thead>
               <tr>
                  <th>Tgl</th>
                  <th class="left">Keluhan Sekarang</th>
                  <th>Tekanan Darah<br>(mmHg)</th>
                  <th>Berat Badan<br>(kg)</th>
                  <th>Umur Kehamilan<br>(minggu)</th>
                  <th>Tinggi Fundus<br>(cm)</th>
                  <th>Letak Janin</th>
                  <th>Denyut Jantung<br>Janin / Menit</th>
               </tr>
            </thead>

            <tbody>
               <tr>
                  <td colspan="8" class="big-space"></td>
               </tr>
            </tbody>
         </table>
      </div>

      <!-- =================== KOLOM KANAN =================== -->
      <div class="col">
         <div class="title">CATATAN KESEHATAN IBU HAMIL</div>

         <div class="info">
            Hamil Ke: ___________________________ <br>
            Jumlah Persalinan: __________________ <br>
            Jumlah Anak Hidup: _________________ <br>
            Jumlah Keguguran: _________________ <br>
            Jarak Anak Lahir Terakhir – Sekarang: ______________________ <br>
            Status Imunisasi TT: ________________ <br>
            Golongan Darah Ibu: ________________ <br>
            Riwayat Persalinan Terdahulu: _____________________________ <br>
            Riwayat Kehamilan Terdahulu: _____________________________
         </div>

         <table>
            <thead>
               <tr>
                  <th>Bengkak</th>
                  <th>Hasil Pemeriksaan Laboratorium</th>
                  <th>Tindakan / Nasihat</th>
                  <th>Imunisasi TT</th>
                  <th>Keterangan<br>Pemeriksa</th>
               </tr>
            </thead>

            <tbody>
               <tr>
                  <td colspan="5" class="big-space"></td>
               </tr>
            </tbody>
         </table>

      </div>

   </div>

</body>

</html>