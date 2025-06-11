<?php
$id = $_GET['id'];
require '../../controller/view.php';
$getorder = tampildata("SELECT * FROM transaction_order_details INNER JOIN product ON transaction_order_details.id_product = product.id WHERE transaction_order_details.status_item!='2' AND transaction_order_details.id_transacation='$id' ");
$check = mysqli_query($koneksi, "SELECT * FROM setting_bisnis LIMIT 1");
$data = mysqli_fetch_array($check);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Struk Kertas Termal</title>
   <link rel="stylesheet" href="style.css">
</head>

<body>
   <div class="receipt">
      <h2 class="store-name"><?= strtoupper($data['business_name']) ?></h2>
      <p class="store-details">
         <?= $data['address'] ?><br>
         Telp: <?= $data['phone'] ?>
      </p>
      <p class="transaction-details">
         <strong>Tanggal:</strong> <span id="currentDate"></span><br>
         <strong>Waktu:</strong> <span id="currentTime"></span><br>
         <strong>No. Transaksi:</strong> <?= $id ?>
      </p>
      <div class="line"></div>
      <?php
      $totalall = 0;
      ?>
      <div class="items">
         <?php foreach ($getorder as $dataorder): ?>
            <div class="item">
               <p><?= $dataorder['product_name'] ?></p>
               <?= number_format($dataorder['qty']) ?>x <span><?= number_format($dataorder['price']) ?></span>
            </div>
         <?php endforeach ?>
      </div>
      <?php
      $total = $dataorder['qty'] * $dataorder['price'];
      $totalall += $total;
      ?>
      <div class="line"></div>

      <div class="total">
         <p><strong>Total:</strong></p>
         <p><strong>Rp. <?= number_format($totalall) ?></strong></p>
      </div>

      <div class="line"></div>

      <p class="thanks">Terima kasih telah berkunjung !</p>
   </div>

   <script>
      // Script to display current date and time
      const date = new Date();
      document.getElementById('currentDate').textContent = date.toLocaleDateString();
      document.getElementById('currentTime').textContent = date.toLocaleTimeString();
   </script>
</body>

<!-- <script>
   window.print();

   if (window.matchMedia) {
      var mediaQueryList = window.matchMedia('print');
      mediaQueryList.addListener(function(mql) {
         if (!mql.matches) {
            setTimeout(function() {
               window.close();
            }, 1);
         }
      });
   } else {
      window.onafterprint = function() {
         setTimeout(function() {
            window.close();
         }, 1);
      };
   }
</script> -->

</html>