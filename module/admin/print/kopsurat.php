<?php
// Menggunakan require_once mencegah fatal error saat cetak bundling
require_once '../../../database/connect.php';
require_once '../../admin/getdataclinic.php';
?>

<!-- 
  Menggunakan layout Tabel Murni (1 Baris, 3 Kolom). 
  Ini adalah arsitektur paling kebal (bulletproof) untuk DOMPDF.
-->
<table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 2px; font-family: 'Times New Roman', serif;">
  <tr>
    <!-- KOLOM 1: LOGO KIRI -->
    <td style="width: 80px; vertical-align: middle; text-align: left; padding: 0;">
      <img src="../../../assets/images/logos/logodeliserdang.png" style="width: 70px; height: auto; display: block;">
    </td>

    <!-- KOLOM 2: NAMA KLINIK & ALAMAT (TENGAH) -->
    <td style="vertical-align: middle; text-align: center; padding: 0 10px;">
      <h1 style="font-size: 20px; margin: 0 0 5px 0; font-weight: bold; letter-spacing: 1px;">
        <?= strtoupper(htmlspecialchars($datafaskes['clinic_name'] ?? '')) ?>
      </h1>
      <div style="font-size: 11pt; line-height: 1.3;">
        <?= htmlspecialchars($datafaskes['faskes_address'] ?? '') ?> |
        Telp. <?= htmlspecialchars($datafaskes['faskes_phone'] ?? '') ?><br>
        Email: <i><?= htmlspecialchars($datafaskes['pic_email'] ?? '') ?></i>
      </div>
    </td>

    <!-- KOLOM 3: LOGO KANAN -->
    <td style="width: 80px; vertical-align: middle; text-align: right; padding: 0;">
      <?php if (!empty($datafaskes['image_clinic'])): ?>
        <img src="../../../uploads/<?= htmlspecialchars($datafaskes['image_clinic']) ?>" style="width: 70px; height: auto; display: block; margin-left: auto;">
      <?php endif; ?>
    </td>
  </tr>
</table>

<!-- GARIS PEMISAH -->
<hr style="border: 0; border-top: 2.5px solid #000; margin-top: 6px; margin-bottom: 10px;">