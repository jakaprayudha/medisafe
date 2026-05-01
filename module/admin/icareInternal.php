<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>I-Care JKN - Riwayat Pelayanan</title>

   <style>
      @page {
         size: A4;
         margin: 20mm;
      }

      body {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 12px;
         color: #333;
         background: #e9ecef;
         margin: 0;
      }

      /* CONTAINER PAGE */
      .page {
         width: 210mm;
         min-height: 297mm;
         background: #fff;
         margin: 10px auto;
         padding: 20px;
         position: relative;
         box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      }

      /* WATERMARK */
      .page::before {
         content: "BPJS KESEHATAN • I-CARE JKN";
         position: absolute;
         top: 45%;
         left: 10%;
         font-size: 40px;
         color: rgba(0, 0, 0, 0.05);
         transform: rotate(-30deg);
         white-space: nowrap;
      }

      /* HEADER */
      .header {
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .header img {
         height: 45px;
      }

      /* TITLE */
      .title-center {
         text-align: center;
         font-weight: bold;
         margin: 20px 0 10px;
         font-size: 14px;
      }

      /* SECTION */
      .section {
         margin-top: 15px;
      }

      .section-title {
         font-weight: bold;
         margin-bottom: 5px;
      }

      /* TABLE */
      .table {
         width: 100%;
         border-collapse: collapse;
         font-size: 11px;
      }

      .table th,
      .table td {
         border: 1px solid #ccc;
         padding: 4px;
         vertical-align: top;
      }

      .table th {
         background: #f5f5f5;
         text-align: center;
      }

      /* SMALL LABEL */
      .label {
         font-weight: bold;
      }

      /* RME BOX */
      .rme {
         border: 1px solid #ccc;
         margin-top: 8px;
      }

      .rme-header {
         background: #f5f5f5;
         padding: 4px;
         font-weight: bold;
      }

      .rme-row {
         display: flex;
         border-top: 1px solid #ddd;
      }

      .rme-col-label {
         width: 200px;
         padding: 5px;
         border-right: 1px solid #ddd;
         background: #fafafa;
      }

      .rme-col-value {
         flex: 1;
         padding: 5px;
      }

      /* FOOTER */
      .footer {
         margin-top: 20px;
         font-size: 10px;
         color: #666;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }
   </style>
</head>

<body>

   <div class="page">

      <!-- HEADER -->
      <div class="header">
         <img src="logo-icare.png">
         <img src="logo-bpjs.png">
      </div>

      <!-- NAMA -->
      <div style="margin-top:10px;">
         <strong>Nama: NURLI BR PURBA</strong>
      </div>

      <!-- ALERGI -->
      <div class="section">
         <div class="section-title">Riwayat Alergi</div>
         <table class="table">
            <tr>
               <th style="width:40px;">No</th>
               <th>Jenis Alergi</th>
               <th>Alergi</th>
            </tr>
            <tr>
               <td colspan="3" style="text-align:center;">Data tidak ditemukan</td>
            </tr>
         </table>
      </div>

      <!-- FKTP -->
      <div class="title-center">Riwayat Pelayanan FKTP</div>

      <table class="table">
         <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Faskes</th>
            <th>Poli</th>
            <th>Keluhan</th>
            <th>Diagnosa</th>
            <th>Terapi Obat</th>
            <th>Terapi Non Obat</th>
         </tr>

         <tr>
            <td>1</td>
            <td>04-04-2026</td>
            <td>MEDAN JOHOR</td>
            <td>POLI UMUM</td>
            <td>KAKI BENGKAK</td>
            <td>Diabetes Mellitus</td>
            <td>TIDAK ADA OBAT</td>
            <td>ADA TERAPI NON OBAT</td>
         </tr>

         <tr>
            <td>2</td>
            <td>22-03-2026</td>
            <td>KLINIK ELVI</td>
            <td>UGD</td>
            <td>PUSING</td>
            <td>Typhoid fever</td>
            <td>Paracetamol</td>
            <td>Istirahat</td>
         </tr>

      </table>

      <!-- FKRTL -->
      <div class="title-center">Riwayat Pelayanan FKRTL</div>

      <table class="table">
         <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>JenPel</th>
            <th>Faskes</th>
            <th>Poli</th>
            <th>Diagnosa</th>
            <th>Obat</th>
         </tr>

         <tr>
            <td>1</td>
            <td>30-04-2026</td>
            <td>RJTL</td>
            <td>RS MITRA SEJATI</td>
            <td>PENYAKIT DALAM</td>
            <td>E11.7 Diabetes Mellitus</td>
            <td>-</td>
         </tr>
      </table>

      <!-- RME DETAIL -->
      <div class="rme">
         <div class="rme-header">REKAM MEDIS ELEKTRONIK</div>

         <div class="rme-row">
            <div class="rme-col-label">Composition</div>
            <div class="rme-col-value">-</div>
         </div>

         <div class="rme-row">
            <div class="rme-col-label">Medication of Discharge</div>
            <div class="rme-col-value">-</div>
         </div>

         <div class="rme-row">
            <div class="rme-col-label">Laboratory</div>
            <div class="rme-col-value">-</div>
         </div>

         <div class="rme-row">
            <div class="rme-col-label">Diagnostic Radiology</div>
            <div class="rme-col-value">-</div>
         </div>

         <div class="rme-row">
            <div class="rme-col-label">Diagnostic Exam</div>
            <div class="rme-col-value">-</div>
         </div>

         <div class="rme-row">
            <div class="rme-col-label">Vital Signs</div>
            <div class="rme-col-value">-</div>
         </div>

      </div>

      <!-- FOOTER -->
      <div class="footer">
         <div>
            Data ini bersifat pribadi dan tidak diperkenankan untuk penyebaran.
            Data rekam medis berasal dari fasilitas pelayanan kesehatan.
         </div>
         <div>
            <strong>BPJS Kesehatan</strong>
         </div>
      </div>

   </div>

</body>

</html>