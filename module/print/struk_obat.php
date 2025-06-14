<?php
require '../../database/connect.php';
require '../../controller/view.php';

$no = $_GET['no'];
$rm = $_GET['rm'];

$checkklinik = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataklinik = mysqli_fetch_array($checkklinik);

$checkpasien = mysqli_query($koneksi, "SELECT * FROM pasien_visit 
   INNER JOIN ms_pasien ON ms_pasien.nomor_rm = pasien_visit.nomor_rm 
   WHERE pasien_visit.nomor_visit='$no' AND pasien_visit.nomor_rm='$rm'");
$datapasien = mysqli_fetch_array($checkpasien);

$checkobat = tampildata("SELECT * FROM permintaan_farmasi WHERE nomor_visit='$no' AND nomor_rm='$rm'");

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
         <?= $dataklinik['alamat'] ?><br>
         Telp/Wa: <?= $dataklinik['telepon'] ?>
      </div>

      <div class="info">
         <div>Pasien: <strong><?= $datapasien['nama_pasien'] ?></strong></div>
         <div>No. RM: <?= $datapasien['nomor_rm'] ?></div>
         <div>Tgl: <?= date('d/m/Y') ?></div>
         <div>Dokter: <?= $datapasien['dokter'] ?></div>
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
                  <td class="wrap"><?= $obat['item'] ?></td>
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