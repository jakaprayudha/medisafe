<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';
?>

<style>
  /* ===========================
   KOP SURAT – GRID SAFE
=========================== */

  .kopsurat-wrapper {
    width: 100%;
    margin-bottom: 10px;
    font-family: "Times New Roman", serif;
  }

  /* GRID UTAMA */
  .kopsurat-grid {
    display: grid;
    grid-template-columns: 90px 1fr 90px;
    /* kiri | tengah | kanan */
    align-items: center;
    column-gap: 10px;
  }

  /* LOGO */
  .kopsurat-logo {
    text-align: center;
  }

  .kopsurat-logo img {
    width: 80px;
    height: auto;
  }

  /* TENGAH */
  .kopsurat-center {
    text-align: center;
  }

  .kopsurat-center h1 {
    font-size: 28px;
    margin: 0;
    font-weight: bold;
    letter-spacing: 1px;
  }

  .kopsurat-center h2 {
    font-size: 20px;
    margin: 0;
    font-weight: bold;
  }

  /* ALAMAT */
  .kopsurat-address {
    font-size: 12px;
    margin-top: 8px;
    line-height: 1.4;
    text-align: center;

    /* KUNCI UTAMA */
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  /* GARIS */
  .kopsurat-divider {
    border: 0;
    border-top: 2px solid #000;
    margin-top: 10px;
  }
</style>

<div class="kopsurat-wrapper">

  <div class="kopsurat-grid">

    <!-- LOGO KIRI -->
    <div class="kopsurat-logo">
      <img src="../../../assets/images/logos/logodeliserdang.png" alt="Logo Daerah">
    </div>

    <!-- JUDUL -->
    <div class="kopsurat-center">
      <h1>KLINIK</h1>
      <h2><?= strtoupper($datafaskes['clinic_name']) ?></h2>
    </div>

    <!-- LOGO KANAN -->
    <div class="kopsurat-logo">
      <img src="../../../uploads/<?= $datafaskes['image_clinic'] ?>" alt="Logo Klinik">
    </div>

  </div>

  <!-- ALAMAT (DI BARIS SENDIRI, AMAN PANJANG) -->
  <div class="kopsurat-address">
    <?= htmlspecialchars($datafaskes['faskes_address']) ?> |
    Telp. <?= htmlspecialchars($datafaskes['faskes_phone']) ?><br>
    Email: <i><?= htmlspecialchars($datafaskes['pic_email']) ?></i>
  </div>

  <hr class="kopsurat-divider">
</div>