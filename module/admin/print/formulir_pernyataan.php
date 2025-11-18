<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Formulir Pernyataan Peserta</title>

   <style>
      @page {
         size: A4 portrait;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", Arial, sans-serif;
         margin: 0;
         padding: 0;
      }

      .page {
         width: 210mm;
         min-height: 297mm;
         margin: 0 auto;
      }

      .kop {
         text-align: center;
         margin-bottom: 10px;
         position: relative;
      }

      .kop img.logo {
         width: 80px;
         position: absolute;
         top: 0;
      }

      .kop .left {
         left: 10px;
      }

      .kop .right {
         right: 10px;
      }

      .kop .title1 {
         font-size: 32px;
         font-weight: bold;
         margin-top: 5px;
      }

      .kop .title2 {
         font-size: 34px;
         font-weight: bold;
         margin-top: -10px;
      }

      .address {
         font-size: 13px;
         margin-top: -5px;
      }

      h3 {
         text-align: center;
         margin-top: 25px;
         font-size: 18px;
         text-decoration: underline;
      }

      .row {
         margin: 10px 0;
         font-size: 16px;
      }

      .label {
         width: 180px;
         display: inline-block;
      }

      .dots {
         border-bottom: 1px dotted #000;
         display: inline-block;
         width: 320px;
         height: 16px;
      }

      .text-area {
         margin-top: 15px;
         font-size: 16px;
         text-align: justify;
         line-height: 1.4;
      }

      .ttd {
         margin-top: 50px;
         width: 100%;
         display: flex;
         justify-content: flex-end;
      }

      .ttd-box {
         width: 260px;
         text-align: center;
         font-size: 16px;
      }

      .sign-line {
         margin-top: 60px;
         border-bottom: 1px solid #000;
         height: 0;
         width: 100%;
      }
   </style>
</head>

<body>

   <div class="page">

      <!-- =================== KOP SURAT =================== -->
      <div class="kop">
         <img src="logo_kiri.png" class="logo left">
         <img src="logo_kanan.png" class="logo right">

         <div class="title1">KLINIK</div>
         <div class="title2">TUTUN SEHATI</div>

         <div class="address">
            Jl. Pasar Baru Km. 17 Tanjung Morawa A No. 7 Telp. 061-7945676, HP 082165281225<br>
            Email: tutunsehati@yahoo.com
         </div>
      </div>

      <!-- =================== JUDUL =================== -->
      <h3>FORMULIR PERNYATAAN PESERTA</h3>

      <!-- =================== ISI DATA =================== -->
      <div class="row">
         Saya yang bertanda tangan dibawah ini :
      </div>

      <div class="row">
         <span class="label">Nama</span>
         <span class="dots"></span>
      </div>

      <div class="row">
         <span class="label">Tempat/Tanggal Lahir</span>
         <span class="dots"></span>
      </div>

      <div class="row">
         <span class="label">Jenis Kelamin</span>
         <span class="dots"></span>
      </div>

      <div class="row">
         <span class="label">NIK/No. Kartu BPJS</span>
         <span class="dots"></span>
      </div>

      <div class="row">
         <span class="label">Nomor Telepon</span>
         <span class="dots"></span>
      </div>

      <!-- =================== PARAGRAF =================== -->
      <div class="text-area">
         Dengan Sadar, terkait pemanfaatan jaminan pelayanan kesehatan BPJS Kesehatan, dengan ini menyatakan :
         <br><br>
         “Kesesuaian atas data medis (Rekam Medis) diri saya untuk dipergunakan oleh Dokter / Rumah Sakit / BPJS Kesehatan sesuai dengan kepentingan."
      </div>

      <!-- =================== TANDA TANGAN =================== -->
      <div class="ttd">
         <div class="ttd-box">
            <div>Tj. Morawa, .......................</div>
            <br>
            Yang Membuat Pernyataan<br><br><br><br>
            <div class="sign-line"></div>
         </div>
      </div>

   </div>

</body>

</html>