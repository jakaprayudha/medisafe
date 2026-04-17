<?php
$title = "Rincian Pembayaran";
$subtitle = "Invoice Tagihan Perawatan Pasien";
session_start();
require '../../database/connect.php';
$id_customer = $_SESSION['id_customer'];
$no = $_GET['no'];
require '../admin/getdataclinic.php';
$pasien = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pasien_visit 
    JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient 
    WHERE pasien_visit.visit_ID='$no'"));

$obat = mysqli_query($koneksi, "
  SELECT 
    mp.id_pharmacy,
    mp.pharmacy_name_generic,
    mp.pharmacy_name_trade,
    SUM(pd.qty) as total_qty,
    pd.harga,
    SUM(pd.qty * pd.harga) as total_harga
  FROM permintaan_pharmacy_details pd
  INNER JOIN permintaan_pharmacy p 
    ON p.id_permintaan_farmasi = pd.id_permintaan_farmasi
  INNER JOIN ms_pharmacy mp 
    ON mp.id_pharmacy = pd.id_pharmacy
  WHERE p.id_visit = '$no'
  AND p.id_customer = '$id_customer'
  GROUP BY mp.id_pharmacy, pd.harga
");

$billing = mysqli_query($koneksi, "SELECT * FROM pasien_billing WHERE id_visit='$no' AND id_customer='$id_customer' ");

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
      <?php
      require 'kop-surat.php';
      ?>
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
               <?php while ($row = mysqli_fetch_assoc($obat)) :
                  $sub = $row['total_qty'] * $row['harga'];
                  $total += $sub;
               ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $row['pharmacy_name_generic'] ?>/ <?= $row['pharmacy_name_trade'] ?>(Obat)</td>
                     <td><?= $row['total_qty'] ?></td>
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