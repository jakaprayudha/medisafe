<?php
require '../../database/connect.php';
require '../../controller/view.php';

$no = $_GET['no'];
$rm = $_GET['rm'];
$id = $_GET['id'];

$checkklinik = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataklinik = mysqli_fetch_array($checkklinik);

$checkpasien = mysqli_query($koneksi, "SELECT * FROM pasien_visit 
   LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   WHERE pasien_visit.visit_ID='$no'");
$datapasien = mysqli_fetch_array($checkpasien);

$checkobat = tampildata("SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$id' ");

$total = 0;
foreach ($checkobat as $obat) {
   $total += $obat['qty'] * $obat['harga'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Cetak Obat</title>
   <style>
      @media print {
         body {
            width: 80mm;
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 10px;
         }

         .container {
            padding: 5px;
         }

         h2,
         h3 {
            text-align: center;
            margin: 2px 0;
         }

         .info {
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
         }

         .info div {
            margin-bottom: 2px;
         }

         table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 5px;
         }

         th,
         td {
            padding: 2px 0;
            border-bottom: 1px solid #000;
            text-align: left;
            vertical-align: top;
         }

         td.wrap {
            display: block;
            word-break: break-word;
            white-space: normal;
            max-width: 35mm;
         }

         .right {
            text-align: right;
         }

         .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
         }

         @page {
            size: 80mm auto;
            margin: 0;
         }
      }

      /* Tombol cetak hanya tampil saat tidak dalam mode print */
      .print-btn {
         margin: 10px auto;
         display: block;
         padding: 5px 10px;
         font-size: 12px;
      }

      @media print {
         .print-btn {
            display: none;
         }
      }
   </style>
</head>

<body>
   <button class="print-btn" onclick="handlePrint()">🖨️ Cetak & Tutup</button>

   <div class="container">
      <h2><?= $dataklinik['clinic_name'] ?></h2>
      <div style="text-align:center; font-size: 9px;">
         <?= $dataklinik['address'] ?><br>
         Telp/Wa: <?= $dataklinik['phone_number'] ?>
      </div>

      <div class="info">
         <div>Pasien: <strong><?= $datapasien['patient_name'] ?></strong></div>
         <div>No. RM: <?= $datapasien['nomor_rm'] ?></div>
         <div>Tgl: <?= date('d/m/Y') ?></div>
         <div>Dokter: <?= $datapasien['id_doctor'] ?></div>
      </div>

      <table>
         <thead>
            <tr>
               <th style="width:5mm;">No</th>
               <th style="width:35mm;">Obat</th>
               <th style="width:10mm;">Qty</th>
               <th style="width:15mm;">Harga</th>
               <th class="right" style="width:15mm;">Total</th>
            </tr>
         </thead>
         <tbody>
            <?php $i = 1;
            foreach ($checkobat as $obat): ?>
               <tr>
                  <td><?= $i++ ?></td>
                  <td class="wrap"><?= $obat['pharmacy_name_trade'] ?>/<?= $obat['pharmacy_name_generic'] ?></td>
                  <td><?= $obat['qty'] ?></td>
                  <td class="right"><?= number_format($obat['harga']) ?></td>
                  <td class="right"><?= number_format($obat['qty'] * $obat['harga']) ?></td>
               </tr>
            <?php endforeach ?>
         </tbody>
      </table>

      <div class="right" style="font-weight:bold;">
         TOTAL: Rp <?= number_format($total) ?>
      </div>

      <div class="footer">
         Terima kasih atas kunjungan Anda<br>
         <?= date('d/m/Y H:i:s') ?>
      </div>
   </div>

   <script>
      function handlePrint() {
         window.print();
      }

      window.onafterprint = () => {
         window.close();
      };
   </script>
</body>

</html>