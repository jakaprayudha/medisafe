<!-- header.php -->
<?php
$checkfaskes = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$datafaskes = mysqli_fetch_array($checkfaskes);
?>
<div class="header">
   <img src="https://ipqi.org/wp-content/uploads/2018/09/bakti-husada.png" alt="Logo Kiri">
   <div class="header-center">
      <h1>KLINIK</h1>
      <h2><?= $datafaskes['clinic_name'] ?></h2>
   </div>
   <img src="../../../assets/images/logos/medisafe_logo.png" alt="Logo Kanan">
</div>

<div class="address">
   <?= $datafaskes['address'] ?> Telp. <?= $datafaskes['phone_number'] ?><br>
   Email: <i><?= $datafaskes['email'] ?></i>
</div>