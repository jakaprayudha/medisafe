<?php
include '../../../database/connect.php';
$title = 'Permintan Farmasi';
$subtitle = 'Tiket Permintaan Obat Pasien';
$id_visit = $_GET['no'] ?? null;

if (!$id_visit) {
   die("ID tidak ditemukan");
}

// ============================
// AMBIL SEMUA TIKET
// ============================
$tickets = mysqli_query($koneksi, "
  SELECT *
  FROM permintaan_pharmacy
  WHERE id_visit = '$id_visit'
  ORDER BY created_at ASC
");
?>
<!DOCTYPE html>
<html>

<head>
   <title>Laporan Obat</title>
   <style>
      @page {
         size: A4;
         margin: 1.2cm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      h1 {
         text-align: center;
         font-size: 16pt;
         margin-bottom: 5px;
      }

      .subtitle {
         text-align: center;
         font-size: 11pt;
         margin-bottom: 15px;
      }

      .ticket {
         page-break-after: always;
         margin-bottom: 20px;
      }

      .header-box {
         border: 1px solid #000;
         padding: 8px;
         margin-bottom: 10px;
      }

      .row {
         display: flex;
         justify-content: space-between;
      }

      .col {
         width: 48%;
      }

      .label {
         font-weight: bold;
      }

      .badge {
         padding: 3px 8px;
         border-radius: 4px;
         font-size: 10px;
         font-weight: bold;
      }

      .success {
         background: #28a745;
         color: #fff;
      }

      .warning {
         background: #ffc107;
         color: #000;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 8px;
      }

      th,
      td {
         border: 1px solid #000;
         padding: 6px;
      }

      th {
         background: #f2f2f2;
         text-align: center;
      }

      .text-center {
         text-align: center;
      }

      .box {
         border: 1px solid #000;
         padding: 6px;
         margin-top: 8px;
      }

      .ttd {
         width: 250px;
         margin-left: auto;
         margin-top: 40px;
         text-align: center;
      }

      .footer {
         margin-top: 20px;
         font-size: 10pt;
      }

      @media print {
         body {
            margin: 0;
         }
      }
   </style>
</head>

<body onload="window.print()">
   <?php require 'kop-surat.php'; ?>
   <div class="subtitle"></div>

   <?php
   if (mysqli_num_rows($tickets) == 0) {
      echo "<h3>Tidak ada data obat</h3>";
   }

   $no_tiket = 1;

   while ($header = mysqli_fetch_assoc($tickets)) {

      $status = ($header['status_permintaan'] == 1) ? "Diproses" : "Menunggu";

      // ============================
      // DETAIL
      // ============================
      $details = mysqli_query($koneksi, "
    SELECT *
    FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy
    WHERE id_permintaan_farmasi = '" . $header['id_permintaan_farmasi'] . "'
  ");
   ?>

      <div class="ticket">

         <!-- ================= HEADER ================= -->
         <div class="header-box">
            <div class="row">
               <div class="col">
                  <div><span class="label">Tiket:</span> #<?= $no_tiket++ ?></div>
                  <div><span class="label">Nomor:</span> <?= $header['permintaan_number'] ?? '-' ?></div>
                  <div><span class="label">Tanggal:</span> <?= $header['created_at'] ?></div>
               </div>
               <div class="col" style="text-align:right">
                  <span class="badge <?= $header['status_permintaan'] == 1 ? 'success' : 'warning' ?>">
                     <?= $status ?>
                  </span>
               </div>
            </div>
         </div>

         <!-- ================= CATATAN ================= -->
         <div class="box">
            <b>Catatan Permintaan:</b><br>
            <?= nl2br(htmlspecialchars($header['catatan_permintaan'] ?? '-')) ?>
         </div>

         <!-- ================= RACIKAN ================= -->
         <?php if (!empty($header['rck_jumlah'])) { ?>
            <div class="box">
               <b>Racikan:</b><br>
               Jumlah: <?= $header['rck_jumlah'] ?> <?= $header['rck_satuan'] ?><br>
               Signa: <?= $header['rck_signa'] ?>
            </div>
         <?php } ?>

         <!-- ================= TABLE OBAT ================= -->
         <table>
            <thead>
               <tr>
                  <th style="width:5%">No</th>
                  <th>Nama Obat</th>
                  <th style="width:10%">Qty</th>
                  <th style="width:20%">Signa</th>
                  <th>Catatan</th>
               </tr>
            </thead>
            <tbody>

               <?php
               $no = 1;
               while ($d = mysqli_fetch_assoc($details)) { ?>
                  <tr>
                     <td class="text-center"><?= $no++ ?></td>
                     <td><?= $d['pharmacy_name_trade'] ?></td>
                     <td class="text-center"><?= $d['qty'] ?></td>
                     <td><?= $d['signa'] ?></td>
                     <td><?= $d['catatan'] ?: '-' ?></td>
                  </tr>
               <?php } ?>

            </tbody>
         </table>

         <!-- ================= FOOTER ================= -->
         <div class="footer">
            <div class="ttd">
               Petugas Farmasi<br><br><br><br>
               ______________________
            </div>
         </div>

      </div>

   <?php } ?>

</body>

</html>