<?php
session_start();
require '../../database/connect.php';
$no = $_GET['no'];
require '../admin/getdataclinic.php';
$pasien = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pasien_visit 
    JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient 
    WHERE pasien_visit.visit_ID='$no'"));


$checkid = mysqli_query($koneksi, "SELECT id_permintaan_farmasi FROM permintaan_pharmacy WHERE id_visit='$no'");
$idfarmasi = mysqli_fetch_array($checkid)['id_permintaan_farmasi'];

$obat = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE permintaan_pharmacy_details.id_permintaan_farmasi='$idfarmasi' ");
$billing = mysqli_query($koneksi, "SELECT * FROM pasien_billing WHERE id_visit='$no' ");

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

      .logo {
         display: block;
         margin: 0 auto 10px auto;
         height: 100px;
         object-fit: contain;
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

         #action-buttons {
            display: none;
         }
      }
   </style>
</head>

<body onload="window.print()">
   <div class="container">
      <!-- Logo Klinik -->
      <img src="../../uploads/<?= $datafaskes['image_clinic'] ?>" alt="Logo Klinik" class="logo">

      <h2><?= $datafaskes['clinic_name'] ?></h2>
      <div class="clinic-info">
         <?= $datafaskes['faskes_address'] ?><br>
         Telp: <?= $datafaskes['faskes_phone'] ?>
      </div>

      <div class="section">
         <strong>Data Pasien:</strong>
         <table>
            <tr>
               <td>Nama</td>
               <td><?= $pasien['patient_name'] ?></td>
               <td>No. RM</td>
               <td><?= $pasien['nomor_rm'] ?></td>
            </tr>
            <tr>
               <!-- <td>Tanggal Lahir</td>
               <td><?= $pasien['patient_place'] ?>/<?= $pasien['patient_datebirth'] ?></td> -->
               <td>Jenis Kelamin</td>
               <td colspan="3"><?= $pasien['patient_gender'] ?></td>
            </tr>
            <tr>
               <td>Tanggal Kunjungan</td>
               <td colspan="3"><?= date('d-m-Y', strtotime($pasien['visit_date'])) ?> <?= $pasien['visit_time'] ?></td>
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
                  $sub = $row['harga'] * $row['qty'];
                  $total += $sub;
               ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $row['pharmacy_name_generic'] ?>/ <?= $row['pharmacy_name_trade'] ?>(Obat)</td>
                     <td><?= $row['qty'] ?></td>
                     <td class="right"><?= number_format($row['harga']) ?></td>
                     <td class="right">0</td>
                     <td class="right"><?= number_format($sub) ?></td>
                  </tr>
               <?php endwhile; ?>

               <?php while ($row = mysqli_fetch_assoc($billing)) :
                  $sub = ($row['billing_qty'] * $row['billing_price']) - $row['billing_discount'];
                  $total += $sub;
               ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $row['billing_item'] ?></td>
                     <td><?= $row['billing_qty'] ?></td>
                     <td class="right"><?= number_format($row['billing_price']) ?></td>
                     <td class="right"><?= number_format($row['billing_discount']) ?></td>
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
            <p><?= $pasien['patient_name'] ?></p>
         </div>
         <div class="signature">
            <p>Petugas</p>
            <br><br>
            <p><?= @$_SESSION['fullname'] ?></p>
         </div>
      </div>
   </div>

   <div style="text-align: right; margin-bottom: 15px;" id="action-buttons">
      <button onclick="printAndClose()" style="padding: 6px 12px;">🖨️ Cetak</button>
      <button onclick="window.close()" style="padding: 6px 12px;">❌ Batal</button>
   </div>
</body>

<script>
   function printAndClose() {
      window.print();
      setTimeout(() => {
         window.close();
      }, 1000);
   }

   window.onbeforeprint = () => {
      document.getElementById('action-buttons').style.display = 'none';
   };
   window.onafterprint = () => {
      document.getElementById('action-buttons').style.display = 'block';
   };
</script>

</html>