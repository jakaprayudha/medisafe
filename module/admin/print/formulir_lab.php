<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Hasil Laboratorium</title>

   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      /* ================= HEADER ================= */

      .header {
         text-align: center;
         margin-bottom: 10px;
      }

      .header-table {
         width: 100%;
         border-collapse: collapse;
      }

      .header-table td {
         vertical-align: middle;
      }

      .logo-box {
         width: 90px;
         height: 90px;
         border: 1px solid #000;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 10px;
      }

      .title-klinik {
         font-size: 22px;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 2px;
      }

      .title-sub {
         font-size: 20px;
         font-weight: bold;
         margin-top: -5px;
      }

      .alamat {
         font-size: 11px;
         margin-top: 5px;
      }

      hr {
         border: none;
         border-top: 2px solid #000;
         margin: 10px 0;
      }

      /* ================= CONTENT ================= */

      .judul-section {
         text-align: center;
         font-size: 16px;
         font-weight: bold;
         margin-top: 10px;
         text-transform: uppercase;
      }

      .info-table {
         width: 100%;
         margin-top: 10px;
         font-size: 11pt;
      }

      .info-table td {
         padding: 4px 2px;
      }

      /* ================= TABLE ================= */

      table.lab-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      .lab-table th,
      .lab-table td {
         border: 1px solid #000;
         padding: 5px 6px;
         font-size: 11pt;
      }

      .lab-table th {
         text-align: center;
         font-weight: bold;
      }

      .section-header {
         font-weight: bold;
         background: #f4f4f4;
      }

      .bold {
         font-weight: bold;
      }

      /* ================= SIGNATURE ================= */

      .signature {
         width: 200px;
         text-align: center;
         float: right;
         margin-top: 30px;
      }

      .signature .paraf {
         height: 80px;
         margin-bottom: 5px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <!-- ================= HEADER ================= -->

   <table class="header-table">
      <tr>
         <td width="100px">
            <div class="logo-box">LOGO</div>
         </td>
         <td class="header">
            <div class="title-klinik">KLINIK PRATAMA RAWAT INAP</div>
            <div class="title-sub">TUTUN SEHATI</div>
            <div class="alamat">
               Jl. Pasar Baru KM 16,5 Tanjung Morawa A - No. 2
               ☎ 082165281225
               Email: tutunsehati@yahoo.com
            </div>
         </td>
         <td width="100px">
            <div class="logo-box">LOGO</div>
         </td>
      </tr>
   </table>

   <hr>

   <!-- ================= JUDUL ================= -->

   <div class="judul-section">HASIL LABORATORIUM</div>

   <!-- ================= DATA PASIEN ================= -->

   <table class="info-table">
      <tr>
         <td width="160px">Nama</td>
         <td>: Chairul Effendy</td>
         <td width="180px">Tanggal Pemeriksaan</td>
         <td>: 28 Agustus 2025</td>
      </tr>

      <tr>
         <td>Tanggal Lahir</td>
         <td>: 31-12-1957 (67 thn)</td>
      </tr>

      <tr>
         <td>Alamat</td>
         <td>: Jl. Saudara Kel T. Deli Medan</td>
      </tr>

      <tr>
         <td>Jenis Kelamin</td>
         <td>: Laki-laki</td>
      </tr>
   </table>

   <!-- ================= TABEL LAB ================= -->

   <table class="lab-table">
      <tr>
         <th>PEMERIKSAAN</th>
         <th>HASIL</th>
         <th>NILAI NORMAL</th>
      </tr>

      <tr class="section-header">
         <td colspan="3">Hematologi</td>
      </tr>

      <tr>
         <td>Hemoglobin (Hb)</td>
         <td class="bold">12.1</td>
         <td>11.0 - 16.0 - 17.5 g/dL</td>
      </tr>

      <tr>
         <td>Leukosit (WBC)</td>
         <td class="bold">6.28</td>
         <td>4.0 - 10.10³ μL</td>
      </tr>

      <tr>
         <td>Eritrosit (RBC)</td>
         <td>3.81</td>
         <td>3.5 - 5.5 × 10¹²/L</td>
      </tr>

      <tr>
         <td>Trombosit (PLT)</td>
         <td>260</td>
         <td>100 - 300.10³ mil/μL</td>
      </tr>

      <tr>
         <td>Hematokrit (HCT)</td>
         <td>36.3</td>
         <td>37 - 50 %</td>
      </tr>

      <tr>
         <td>MCV</td>
         <td>79.9</td>
         <td>82 - 95 fL</td>
      </tr>

      <tr>
         <td>MCH</td>
         <td>31.7</td>
         <td>27 - 31 pg</td>
      </tr>

      <tr>
         <td>MCHC</td>
         <td>39.7</td>
         <td>32 - 36 g/dL</td>
      </tr>

      <tr>
         <td>LYM</td>
         <td>21.9</td>
         <td>23.420 - 40 %</td>
      </tr>

      <!-- ================= SALMONELLA ================= -->

      <tr>
         <td>Salmonella Typhi (O)</td>
         <td>1/40</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi A – O</td>
         <td>1/40</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi B – O</td>
         <td>1/80</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi C – O</td>
         <td class="bold">1/160</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Typhi (H)</td>
         <td>1/80</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi A – H</td>
         <td>1/40</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi B – H</td>
         <td>1/40</td>
         <td>1/40</td>
      </tr>

      <tr>
         <td>Salmonella Paratyphi C – H</td>
         <td class="bold">1/160</td>
         <td>1/40</td>
      </tr>

   </table>

   <!-- ================= TTD ================= -->

   <div class="signature">
      <div class="paraf">[PARAF]</div>
      Rizky Syafitri Amd.Kes<br>
      <span style="font-size:10pt;">Paraf Petugas</span>
   </div>

</body>

</html>