<?php
session_start();
require '../../database/connect.php';
$no = $_GET['no'];
$rm = $_GET['rm'];

$klinik = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1"));
$pasien = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pasien_visit 
    JOIN ms_pasien ON ms_pasien.nomor_rm = pasien_visit.nomor_rm 
    WHERE pasien_visit.nomor_visit='$no' AND pasien_visit.nomor_rm='$rm'"));

$obat = mysqli_query($koneksi, "SELECT * FROM permintaan_farmasi WHERE nomor_visit='$no' AND nomor_rm='$rm'");
$billing = mysqli_query($koneksi, "SELECT * FROM pasien_billing WHERE nomor_visit='$no' AND nomor_rm='$rm'");

// Hitung total
$total = 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Rincian Pembayaran</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         width: 210mm;
         margin: 0 auto;
         font-size: 12px;
      }

      .container {
         padding: 20px;
      }

      h2,
      h3 {
         text-align: center;
         margin: 5px 0;
      }

      .clinic-info {
         text-align: center;
         font-size: 11px;
      }

      .section {
         margin-top: 20px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      table,
      th,
      td {
         border: 1px solid #000;
      }

      th,
      td {
         padding: 5px;
         text-align: left;
      }

      .right {
         text-align: right;
      }

      .footer {
         margin-top: 30px;
         display: flex;
         justify-content: space-between;
      }

      .signature {
         text-align: center;
         margin-top: 50px;
      }

      @media print {
         @page {
            size: A4;
            margin: 20mm;
         }
      }
   </style>
</head>

<body onload="window.print()">
   <div class="container">
      <h2><?= $klinik['clinic_name'] ?></h2>
      <div class="clinic-info">
         <?= $klinik['alamat'] ?><br>
         Telp: <?= $klinik['telepon'] ?>
      </div>

      <div class="section">
         <strong>Data Pasien:</strong>
         <table>
            <tr>
               <td>Nama</td>
               <td><?= $pasien['nama_pasien'] ?></td>
               <td>No. RM</td>
               <td><?= $pasien['nomor_rm'] ?></td>
            </tr>
            <tr>
               <td>Tanggal Lahir</td>
               <td><?= $pasien['tanggal_lahir'] ?></td>
               <td>Jenis Kelamin</td>
               <td><?= $pasien['gender'] ?></td>
            </tr>
            <tr>
               <td>Tanggal Kunjungan</td>
               <td colspan="3"><?= date('d-m-Y', strtotime($pasien['tanggal'])) ?> <?= $pasien['waktu'] ?></td>
            </tr>
         </table>
      </div>

      <div class="section">
         <strong>Rincian Pembayaran:</strong>
         <table>
            <thead>
               <tr>
                  <th>No</th>
                  <th>Deskripsi</th>
                  <th>Qty</th>
                  <th>Harga Satuan</th>
                  <th>Diskon</th>
                  <th>Total</th>
               </tr>
            </thead>
            <tbody>
               <?php $i = 1; ?>
               <?php while ($row = mysqli_fetch_assoc($obat)) :
                  $sub = $row['qty'] * $row['harga'];
                  $total += $sub;
               ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $row['item'] ?> (Obat)</td>
                     <td><?= $row['qty'] ?></td>
                     <td class="right"><?= number_format($row['harga']) ?></td>
                     <td class="right">0</td>
                     <td class="right"><?= number_format($sub) ?></td>
                  </tr>
               <?php endwhile; ?>

               <?php while ($row = mysqli_fetch_assoc($billing)) :
                  $sub = ($row['qty'] * $row['harga']) - $row['diskon'];
                  $total += $sub;
               ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $row['item'] ?></td>
                     <td><?= $row['qty'] ?></td>
                     <td class="right"><?= number_format($row['harga']) ?></td>
                     <td class="right"><?= number_format($row['diskon']) ?></td>
                     <td class="right"><?= number_format($sub) ?></td>
                  </tr>
               <?php endwhile; ?>
               <tr>
                  <td colspan="5" class="right"><strong>Total Bayar</strong></td>
                  <td class="right"><strong>Rp <?= number_format($total) ?></strong></td>
               </tr>
            </tbody>
         </table>
      </div>

      <div class="footer">
         <div class="signature">
            <p>Penerima</p>
            <br><br>
            <p><?= $pasien['nama_pasien'] ?></p>
         </div>
         <div class="signature">
            <p>Petugas</p>
            <br><br>
            <p><?= $_SESSION['fullname'] ?></p>
         </div>
      </div>
   </div>
</body>

</html>