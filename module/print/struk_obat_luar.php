<?php
require "../../database/connect.php";
$checkklinik = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataklinik = mysqli_fetch_array($checkklinik);

$no = $_GET['no'] ?? '';

$query = "SELECT *
          FROM resep_luar  
          INNER JOIN pasien_visit ON pasien_visit.visit_ID = resep_luar.id_visit 
          INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient 
          INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
          WHERE resep_luar.id_visit = '$no' ";
$result = mysqli_query($koneksi, $query);
$info = mysqli_fetch_assoc($result) ?? [];
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Cetak Resep Obat</title>
   <style>
      body {
         font-family: "Times New Roman", serif;
         margin: 20mm;
         /* biar pas di A4 */
         font-size: 14pt;
         line-height: 1.4;
      }

      .header {
         text-align: center;
         border-bottom: 2px solid #000;
         padding-bottom: 5px;
         margin-bottom: 20px;
      }

      .header h2 {
         margin: 0;
         font-size: 20pt;
      }

      .info {
         margin-bottom: 15px;
         font-size: 12pt;
         width: 100%;
      }

      .info td {
         padding: 3px 6px;
         vertical-align: top;
      }

      .resep {
         margin-top: 15px;
         font-style: italic;
         /* isi resep miring */
         font-size: 13pt;
      }

      .rx {
         font-size: 36pt;
         /* lebih besar agar mirip cetakan resep */
         font-weight: bold;
         font-style: italic;
         margin-bottom: 15px;
      }

      .footer {
         margin-top: 50px;
         text-align: right;
         font-size: 12pt;
      }

      @media print {
         @page {
            size: A4 portrait;
            /* pastikan A4 */
            margin: 20mm;
            /* margin default */
         }

         body {
            margin: 0;
         }

         .no-print {
            display: none;
         }

         /* Pastikan tidak ada page break */
         .header,
         .info,
         .resep,
         .footer {
            page-break-inside: avoid;
         }
      }
   </style>
</head>

<body>

   <div class="header">
      <h2><?= $dataklinik['clinic_name'] ?></h2>
      <p>Alamat : <?= $dataklinik['address'] ?>, Phone : <?= $dataklinik['phone_number'] ?> </p>
   </div>

   <table class="info">
      <tr>
         <td><b>No. Resep</b></td>
         <td>: <?= $info['resep_number'] ?? '-' ?></td>
         <td><b>Tgl. Kunjungan</b></td>
         <td>: <?= $info['visit_date'] ?? '-' ?></td>
      </tr>
      <tr>
         <td><b>No. RM</b></td>
         <td>: <?= $info['nomor_rm'] ?? '-' ?></td>
         <td><b>Dokter</b></td>
         <td>: <?= $info['doctor_name'] ?? '-' ?></td>
      </tr>
      <tr>
         <td><b>Nama Pasien</b></td>
         <td>: <?= $info['patient_name'] ?? '-' ?></td>
         <td><b>Tgl. Lahir</b></td>
         <td>: <?= $info['patient_datebirth'] ?? '-' ?></td>
      </tr>
   </table>

   <div class="resep">
      <div class="rx">℞</div>
      <p><b>Prescriptio:</b><br><?= nl2br($info['prescriptio'] ?? '-') ?></p>
      <p><b>Signatura (Aturan Pakai):</b><br><?= nl2br($info['signatura'] ?? '-') ?></p>
      <p><b>Subscriptio (Instruksi Tambahan):</b><br><?= nl2br($info['subscriptio'] ?? '-') ?></p>
      <p><b>Pro:</b><br><?= nl2br($info['pro'] ?? '-') ?></p>
   </div>

   <div class="footer">
      Medan, <?= date("d-m-Y") ?><br><br><br>
      <b>( <?= $info['doctor_name'] ?? '________________' ?> )</b><br>
      Dokter
   </div>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Resep</button>
   </div>

</body>

</html>