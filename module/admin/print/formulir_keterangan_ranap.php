<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Keterangan Rawat Inap - A4</title>
   <style>
      @page {
         size: A4;
         margin: 15mm 20mm;
      }

      body {
         font-family: "Times New Roman", serif;
         margin: 0;
         padding: 0;
         background: white;
      }

      .container {
         width: 100%;
         max-width: 750px;
         margin: auto;
      }

      /* KOP SURAT */
      header {
         text-align: center;
         margin-bottom: 10px;
      }

      header h1 {
         font-size: 32px;
         margin: 0;
         letter-spacing: 2px;
      }

      header h2 {
         font-size: 26px;
         margin: 0;
         font-weight: bold;
         letter-spacing: 1px;
      }

      header .alamat {
         font-size: 12px;
         margin-top: 8px;
         line-height: 1.3;
      }

      hr {
         margin-top: 12px;
         border: 0;
         border-top: 2px solid #000;
      }

      .judul {
         text-align: center;
         margin-top: 15px;
         text-decoration: underline;
         font-size: 20px;
      }

      .nomor {
         text-align: center;
         margin-top: -5px;
         font-size: 14px;
      }

      .section {
         margin-top: 20px;
         font-size: 15px;
      }

      .data {
         margin-left: 20px;
         margin-bottom: 15px;
         font-size: 15px;
      }

      .data td {
         padding: 2px 5px;
         vertical-align: top;
      }

      .penutup {
         margin-top: 15px;
         text-align: justify;
         font-size: 15px;
      }

      /* AREA TTD */
      .ttd-wrapper {
         width: 100%;
         display: flex;
         justify-content: space-between;
         margin-top: 60px;
      }

      .kolom-ttd {
         width: 30%;
         text-align: center;
         font-size: 15px;
      }

      .ttd-box {
         margin-top: 70px;
         border-top: 1px solid #000;
         padding-top: 5px;
         font-size: 15px;
      }
   </style>
</head>

<body>

   <div class="container">

      <header>
         <h1>KLINIK</h1>
         <h2>TUTUN SEHATI</h2>
         <p class="alamat">
            Jl. Pasar Baru Km. 17 Tanjung Morawa A No. 2<br>
            Telp. 061-7945676, Hp 082165281225<br>
            Email: tutsunsehati@yahoo.com
         </p>
         <hr>
      </header>

      <h3 class="judul">KETERANGAN RAWAT INAP</h3>
      <p class="nomor">NOMOR : 01 / RITP / TS / I / 2015</p>

      <div class="section">

         <p>Yang bertanda tangan dibawah ini :</p>

         <table class="data">
            <tr>
               <td>Nama</td>
               <td>: dr. Herison Sinaga</td>
            </tr>
            <tr>
               <td>Jabatan</td>
               <td>: Penanggung Jawab Klinik</td>
            </tr>
         </table>

         <p>Dengan ini menyatakan bahwa pasien :</p>

         <table class="data">
            <tr>
               <td>Nama</td>
               <td>: Churui Ferendy</td>
            </tr>
            <tr>
               <td>Alamat</td>
               <td>: Jl. Saudara</td>
            </tr>
            <tr>
               <td>No. Kartu Peserta BPJS Kesehatan</td>
               <td>: 000149727745</td>
            </tr>
         </table>

         <p>Telah mendapat pelayanan kesehatan rawat inap.</p>

         <table class="data">
            <tr>
               <td>Tempat</td>
               <td>: KLINIK RAWAT INAP TUTUN SEHATI</td>
            </tr>
            <tr>
               <td>Tanggal</td>
               <td>: 28-08-15 s/d 01-09-15</td>
            </tr>
            <tr>
               <td>Diagnosa</td>
               <td>: Gerd</td>
            </tr>
            <tr>
               <td>Dokter yang merawat</td>
               <td>: dr. M. T. Zega</td>
            </tr>
         </table>

         <p class="penutup">
            Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan dalam pengajuan
            klaim biaya rawat inap.
         </p>

      </div>

      <div class="ttd-wrapper">
         <div class="kolom-ttd">
            <p>PESERTA / KELUARGA PESERTA</p>
            <div class="ttd-box">( Friedawang )</div>
         </div>

         <div class="kolom-ttd">
            <p>Dokter yang merawat</p>
            <div class="ttd-box">dr. M. T. Zega</div>
         </div>

         <div class="kolom-ttd">
            <p>Dokter penanggung jawab</p>
            <div class="ttd-box">dr. Herison Sinaga</div>
         </div>
      </div>

   </div>

</body>

</html>