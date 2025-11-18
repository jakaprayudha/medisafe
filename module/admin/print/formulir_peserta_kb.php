<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Kartu Peserta KB</title>

   <style>
      @page {
         size: A4 landscape;
         margin: 10mm;
      }

      body {
         font-family: Arial, sans-serif;
         margin: 0;
         padding: 0;
      }

      .page {
         width: 297mm;
         min-height: 210mm;
         margin: 0 auto;
         padding: 10mm;
         box-sizing: border-box;
      }

      .two-col {
         display: flex;
         justify-content: space-between;
         gap: 15px;
      }

      .col {
         width: 50%;
         border: 1px solid #333;
         padding: 15px;
         box-sizing: border-box;
         height: 100%;
      }

      .title {
         font-size: 20px;
         font-weight: bold;
         text-align: center;
         margin-bottom: 10px;
      }

      .sub-title {
         font-size: 13px;
         text-align: center;
         font-weight: bold;
         margin-bottom: 10px;
      }

      .row {
         margin-bottom: 6px;
         font-size: 13px;
      }

      .label {
         display: inline-block;
         width: 140px;
      }

      .underline {
         display: inline-block;
         border-bottom: 1px solid #000;
         width: 200px;
         height: 14px;
      }

      .small-box {
         width: 12px;
         height: 12px;
         border: 1px solid #000;
         display: inline-block;
         margin-right: 5px;
      }

      .section-title {
         font-weight: bold;
         margin-top: 10px;
         margin-bottom: 5px;
         font-size: 14px;
         border-bottom: 1px solid #000;
         padding-bottom: 3px;
      }

      /* Right card */
      table.jadwal {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
         font-size: 13px;
      }

      table.jadwal th,
      table.jadwal td {
         border: 1px solid #000;
         padding: 5px;
         height: 22px;
      }

      .boxgroup {
         letter-spacing: 6px;
         font-size: 14px;
      }
   </style>
</head>

<body>

   <div class="page">

      <div class="two-col">

         <!-- ================= KARTU PESERTA KB ================= -->
         <div class="col">

            <div class="title">KARTU PESERTA KB</div>

            <div class="row">
               <span class="label">Nama Peserta KB</span>
               <span class="underline"></span>
            </div>

            <div class="row">
               <span class="label">Nama Suami/Istri</span>
               <span class="underline"></span>
            </div>

            <div class="row">
               <span class="label">Tgl Lahir / Umur</span>
               <span class="underline"></span>
            </div>

            <div class="row">
               <span class="label">Alamat Peserta KB</span>
               <span class="underline"></span>
            </div>

            <div class="section-title">Tahapan KB</div>

            <div class="row">
               <span class="small-box"></span> Peserta Baru<br>
               <span class="small-box"></span> Peserta Lama<br>
               <span class="small-box"></span> Pindahan
            </div>

            <div class="section-title">Status Peserta JKN</div>
            <div class="row">
               <span class="small-box"></span> Peserta JKN<br>
               <span class="small-box"></span> Bukan Peserta JKN
            </div>

            <div class="row">
               <span class="label">Nomor Sakit</span>
               <span class="underline"></span>
            </div>

            <div class="row">
               <span class="label">Nama Faskes KB</span>
               <span class="underline"></span>
            </div>

            <div class="row">
               <span class="label">Nomor Kode Faskes</span>
               <span class="boxgroup">[ ][ ][ ] [ ][ ][ ]</span>
            </div>

            <br><br>

            <div class="row">Petugas Pelayanan KB:</div>
            <div class="underline" style="width: 80%;"></div>

         </div>

         <!-- ================= BAGIAN KANAN ================= -->
         <div class="col">

            <div class="section-title">Metode Kontrasepsi</div>
            <div class="row">
               <span class="underline" style="width: 150px;"></span>
            </div>

            <div class="row">
               Tgl/Bln/Thn Mulai Dipakai :
               <span class="boxgroup">[ ][ ] [ ][ ] [ ][ ][ ]</span>
            </div>

            <div class="row">
               Tgl/Bln/Thn Dilepas/Diganti :
               <span class="boxgroup">[ ][ ] [ ][ ] [ ][ ][ ]</span>
            </div>

            <div class="row">
               (Bila IUD Implan/Kondom)
            </div>

            <div class="section-title">Catatan Pelayanan</div>

            <table class="jadwal">
               <tr>
                  <th width="40%">DIPESAN KEMBALI</th>
                  <th>KETERANGAN</th>
               </tr>

               <!-- Repeat 12 rows -->
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
               <tr>
                  <td></td>
                  <td></td>
               </tr>
            </table>

         </div>

      </div>

   </div>

</body>

</html>