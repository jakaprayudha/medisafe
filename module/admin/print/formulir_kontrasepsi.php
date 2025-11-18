<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Form Persetujuan Tindakan Medis KB</title>
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
         /* A4 landscape */
         min-height: 210mm;
         padding: 10mm 15mm;
         box-sizing: border-box;
         margin: 0 auto;
      }

      .two-col {
         display: flex;
         justify-content: space-between;
         gap: 15px;
      }

      .col {
         width: 50%;
         /* lebih lebar */
      }

      .title {
         text-align: center;
         font-size: 17px;
         font-weight: bold;
         margin-bottom: 15px;
      }

      .section-title {
         font-weight: bold;
         border-bottom: 1px solid #000;
         margin-top: 15px;
         margin-bottom: 10px;
         padding-bottom: 3px;
      }

      .center {
         text-align: center;
      }

      .row {
         display: flex;
         margin-bottom: 6px;
      }

      .label {
         width: 180px;
         font-size: 14px;
      }

      .underline {
         border-bottom: 1px solid #000;
         flex: 1;
         height: 16px;
      }

      .underline.short {
         width: 60px;
      }

      .indent {
         padding-left: 10px;
         font-size: 14px;
      }

      .boxgroup {
         letter-spacing: 4px;
         font-size: 16px;
      }

      .longboxgroup {
         letter-spacing: 4px;
         font-size: 16px;
      }

      .checkbox-row label {
         margin-right: 15px;
         font-size: 14px;
      }

      .box {
         display: inline-block;
         width: 14px;
         height: 14px;
         border: 1px solid #000;
         margin-right: 3px;
      }

      .signature {
         display: flex;
         justify-content: space-between;
         margin-top: 40px;
      }

      .sig-box {
         width: 45%;
         text-align: center;
      }

      .sign-line {
         border-bottom: 1px solid #000;
         height: 40px;
      }

      .sig-label {
         margin-top: 5px;
         font-size: 14px;
      }

      .service-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      .service-table th,
      .service-table td {
         border: 1px solid #000;
         padding: 4px;
         font-size: 13px;
      }

      .small {
         font-size: 13px;
         margin-top: 15px;
      }
   </style>
</head>

<body>

   <div class="page">

      <div class="two-col">

         <!-- ==================== KOLOM KIRI ==================== -->
         <div class="col">
            <h2 class="title">
               LEMBAR PERSETUJUAN TINDAKAN MEDIK (INFORMED CONSENT)<br>
               PELAYANAN KONTRASEPSI
            </h2>

            <div class="section-title">IDENTITAS TEMPAT PELAYANAN</div>

            <div class="row">
               <div class="label">Nama Faskes KB / Praktik</div>
               <div class="underline"></div>
            </div>

            <div class="row">
               <div class="label">Nomor Kode Faskes KB</div>
               <div class="boxgroup">[ ][ ][ ] [ ][ ][ ]</div>
            </div>

            <div class="row">
               <div class="label">Nomor Klien / Nomor Sakit</div>
               <div class="longboxgroup">[ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ]</div>
            </div>

            <div class="row">
               <div class="label">Kode Keluarga Indonesia (KKI)</div>
               <div class="longboxgroup">
                  [ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ]
               </div>
            </div>

            <div class="section-title center">PERSETUJUAN KLIEN</div>

            <div class="row">
               <div class="label">Saya yang bertanda tangan di bawah ini</div>
            </div>

            <div class="row indent">
               <div class="label">Nama</div>
               <div class="underline"></div>
            </div>

            <div class="row indent">
               <div class="label">Umur</div>
               <div class="underline short"></div>
               <span style="padding-left:5px">Tahun</span>
            </div>

            <div class="row indent">
               <div class="label">Alamat Lengkap</div>
               <div class="underline"></div>
            </div>

            <div class="row indent">
               Setelah mendapat penjelasan dan MENGERTI SEPENUHNYA PERILAKU
               KONTRASEPSI YANG SAYA PILIH
            </div>

            <div class="checkbox-row indent">
               <label><span class="box"></span> SUNTIKAN</label>
               <label><span class="box"></span> PIL</label>
               <label><span class="box"></span> IUD</label>
               <label><span class="box"></span> IMPLAN</label>
            </div>

            <div class="section-title center">PERSETUJUAN SUAMI / ISTRI KLIEN</div>

            <div class="row indent">
               <div class="label">Nama</div>
               <div class="underline"></div>
            </div>

            <div class="row indent">
               <div class="label">Alamat</div>
               <div class="underline"></div>
            </div>

            <p class="indent small">
               Selaku SUAMI/ISTRI, saya menyetujui tindakan pelayanan KB
               terhadap istri/ suami saya tersebut.
            </p>

            <div class="signature">
               <div class="sig-box">
                  <div class="sign-line"></div>
                  <div class="sig-label">Yang memberi persetujuan</div>
               </div>

               <div class="sig-box">
                  <div class="sign-line"></div>
                  <div class="sig-label">Petugas Pelayanan KB</div>
               </div>
            </div>

         </div>

         <!-- ==================== KOLOM KANAN ==================== -->
         <div class="col">

            <div class="section-title">Nomor Kode Faskes</div>
            <div class="row">
               <div class="boxgroup">[ ][ ][ ] [ ][ ][ ]</div>
            </div>

            <div class="section-title">Nomor Klien</div>
            <div class="row">
               <div class="longboxgroup">[ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ]</div>
            </div>

            <div class="section-title">Kode Keluarga Indonesia (KKI)</div>
            <div class="row">
               <div class="longboxgroup">[ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ][ ]</div>
            </div>

            <div class="row">
               <div class="label">Umur</div>
               <div class="underline short"></div>
            </div>

            <div class="section-title">Daftar Pelyanan</div>

            <table class="service-table">
               <tr>
                  <th>Tindakan</th>
                  <th>Jenis Pelayanan</th>
                  <th>No. Kode</th>
               </tr>
               <tr>
                  <td rowspan="2">Operatif</td>
                  <td>Mini Laparotomi</td>
                  <td>02</td>
               </tr>
               <tr>
                  <td>Vasektomi</td>
                  <td>03</td>
               </tr>

               <tr>
                  <td rowspan="2">Pemasangan</td>
                  <td>Implan</td>
                  <td>11</td>
               </tr>
               <tr>
                  <td>IUD CuT 380A</td>
                  <td>12</td>
               </tr>

               <tr>
                  <td rowspan="2">Tindakan Lain</td>
                  <td>Pemeriksaan Ulang IUD</td>
                  <td>15</td>
               </tr>
               <tr>
                  <td>Pencabutan Implan</td>
                  <td>16</td>
               </tr>

               <tr>
                  <td rowspan="2">Pelayanan</td>
                  <td>Suntikan</td>
                  <td>13</td>
               </tr>
               <tr>
                  <td>Metode Barier</td>
                  <td>14</td>
               </tr>
            </table>

            <p class="small">
               - Isilah kotak tanda √ pada kolom layanan sesuai tindakan.<br>
               - Kembalikan lembar ini ke petugas.
            </p>
         </div>
      </div>

   </div>

</body>

</html>