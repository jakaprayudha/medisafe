<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';
?>

<style>
  .kopsurat-wrapper {
    width: 100%;
    margin-bottom: 5px;
    font-family: "Times New Roman", serif;
  }

  /* GRID */
  .kopsurat-grid {
    display: grid;
    grid-template-columns: 70px 1fr 70px;
    align-items: center;
    column-gap: 8px;
  }

  /* LOGO */
  .kopsurat-logo {
    text-align: center;
  }

  .kopsurat-logo img {
    width: 60px;
    height: auto;
  }

  /* CENTER (JUDUL + ALAMAT JADI 1 BLOK) */
  .kopsurat-center {
    text-align: center;
    line-height: 1.1;
  }

  /* NAMA KLINIK */
  .kopsurat-center h1 {
    font-size: 19px;
    margin: 0;
    font-weight: bold;
    letter-spacing: 1px;
  }

  /* ALAMAT */
  .kopsurat-address {
    font-size: 11px;
    margin-top: 3px;
    line-height: 1.2;
    text-align: center;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  /* GARIS */
  .kopsurat-divider {
    border: 0;
    border-top: 2px solid #000;
    margin-top: 6px;
  }
</style>

<div class="kopsurat-wrapper">

  <div class="kopsurat-grid">

    <!-- LOGO KIRI -->
    <div class="kopsurat-logo">
      <img src="../../../assets/images/logos/logodeliserdang.png">
    </div>

    <!-- JUDUL + ALAMAT (DIGABUNG) -->
    <div class="kopsurat-center">
      <h1><?= strtoupper($datafaskes['clinic_name']) ?></h1>

      <div class="kopsurat-address">
        <?= htmlspecialchars($datafaskes['faskes_address']) ?> |
        Telp. <?= htmlspecialchars($datafaskes['faskes_phone']) ?><br>
        Email: <i><?= htmlspecialchars($datafaskes['pic_email']) ?></i>
      </div>
    </div>

    <!-- LOGO KANAN -->
    <div class="kopsurat-logo">
      <img src="../../../uploads/<?= $datafaskes['image_clinic'] ?>">
    </div>

  </div>

  <hr class="kopsurat-divider">
</div>