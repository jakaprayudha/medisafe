<?php
require '../../database/connect.php';
$no = $_GET['no'] ?? '';

$checkklinik = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataklinik = mysqli_fetch_array($checkklinik);

$order = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pharmacy_order_buy WHERE order_number='$no' LIMIT 1"));

$details = mysqli_query($koneksi, "SELECT * FROM pharmacy_buy_detail WHERE order_number='$no'");
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Cetak Pembelian - <?= $order['order_number'] ?></title>
   <style>
      body {
         font-family: Arial, sans-serif;
         font-size: 12px;
         color: #000;
      }

      .container {
         width: 210mm;
         /* A4 width */
         margin: auto;
         padding: 20px;
      }

      h2,
      h4 {
         text-align: center;
         margin: 4px 0;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
      }

      table,
      th,
      td {
         border: 1px solid #000;
      }

      th,
      td {
         padding: 6px;
         text-align: left;
      }

      th {
         background: #f0f0f0;
      }

      .text-center {
         text-align: center;
      }

      .text-right {
         text-align: right;
      }

      .no-border td {
         border: none;
         padding: 4px;
      }

      .signature {
         margin-top: 40px;
         width: 100%;
      }

      .signature td {
         height: 60px;
         vertical-align: bottom;
         text-align: center;
      }
   </style>
</head>

<body onload="window.print()">
   <div class="container">
      <h2><?= $dataklinik['clinic_name'] ?></h2>
      <p style="text-align: center;">Alamat : <?= $dataklinik['address'] ?>, Phone : <?= $dataklinik['phone_number'] ?> </p>
      <h4>Rincian Pembelian Barang</h4>
      <hr>

      <table class="no-border">
         <tr>
            <td><strong>No Order</strong></td>
            <td>: <?= $order['order_number'] ?></td>
            <td><strong>Tanggal</strong></td>
            <td>: <?= date('d-m-Y', strtotime($order['order_date'])) ?></td>
         </tr>
         <tr>
            <td><strong>Supplier</strong></td>
            <td>: <?= $order['order_market'] ?></td>
            <td><strong>Catatan Ke Supplier</strong></td>
            <td>: <?= $order['order_description'] ?></td>
         </tr>
      </table>

      <table>
         <thead>
            <tr>
               <th>No</th>
               <th>Kategori</th>
               <th>Nama Item</th>
               <th class="text-center">Qty</th>
               <th class="text-right">Harga Satuan</th>
               <th class="text-right">Diskon</th>
               <th class="text-right">Total</th>
            </tr>
         </thead>
         <tbody>
            <?php
            $no = 1;
            $grand = 0;
            while ($row = mysqli_fetch_assoc($details)):
               $total = ($row['buy_qty'] * $row['buy_price']) - $row['buy_discount'];
               $grand += $total;
            ?>
               <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><?= $row['buy_category'] ?></td>
                  <td><?= $row['buy_item'] ?></td>
                  <td class="text-center"><?= $row['buy_qty'] ?></td>
                  <td class="text-right"><?= number_format($row['buy_price'], 0, ',', '.') ?></td>
                  <td class="text-right"><?= number_format($row['buy_discount'], 0, ',', '.') ?></td>
                  <td class="text-right"><?= number_format($total, 0, ',', '.') ?></td>
               </tr>
            <?php endwhile; ?>
         </tbody>
         <tfoot>
            <tr>
               <th colspan="6" class="text-right">Grand Total</th>
               <th class="text-right"><?= number_format($grand, 0, ',', '.') ?></th>
            </tr>
         </tfoot>
      </table>

      <table class="signature">
         <tr>
            <td>Mengetahui,</td>
            <td>Dibuat oleh,</td>
         </tr>
         <tr>
            <td>(___________________)</td>
            <td>(___________________)</td>
         </tr>
      </table>
   </div>
</body>

</html>