<?php
require '../../../database/connect.php';
$checkfaskes = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$datafaskes = mysqli_fetch_array($checkfaskes);
?>

<style>
   /* ===========================
   KOP SURAT – ANTI BENTROK
=========================== */

   .kopsurat-wrapper {
      width: 100%;
      text-align: center;
      margin-bottom: 10px;
      position: relative;
      font-family: "Times New Roman", serif;
   }

   .kopsurat-wrapper .kopsurat-left,
   .kopsurat-wrapper .kopsurat-right {
      width: 80px;
      position: absolute;
      top: 0;
   }

   .kopsurat-wrapper .kopsurat-left {
      left: 10px;
   }

   .kopsurat-wrapper .kopsurat-right {
      right: 10px;
   }

   .kopsurat-wrapper img {
      width: 80px;
      height: auto;
   }

   .kopsurat-wrapper .kopsurat-center h1 {
      font-size: 28px;
      margin: 0;
      font-weight: bold;
      letter-spacing: 1px;
   }

   .kopsurat-wrapper .kopsurat-center h2 {
      font-size: 20px;
      margin: 0;
      font-weight: bold;
   }

   .kopsurat-wrapper .kopsurat-address {
      font-size: 12px;
      margin-top: 8px;
      line-height: 1.4;
   }

   .kopsurat-wrapper hr {
      border: 0;
      border-top: 2px solid #000;
      margin-top: 10px;
      margin-bottom: 0;
   }
</style>


<div class="kopsurat-wrapper">

   <img src="../../../assets/images/logos/logodeliserdang.png"
      class="kopsurat-left">

   <img src="../../../assets/images/logos/logotutun.png"
      class="kopsurat-right">

   <div class="kopsurat-center">
      <h1>KLINIK</h1>
      <h2><?= strtoupper($datafaskes['clinic_name']) ?></h2>
   </div>

   <div class="kopsurat-address">
      <?= $datafaskes['address'] ?> | Telp. <?= $datafaskes['phone_number'] ?><br>
      Email: <i><?= $datafaskes['email'] ?></i>
   </div>

   <hr>
</div>