<?php
require_once '../../../database/connect.php';
$no = $_GET['no'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient  WHERE pasien_visit.visit_ID='$no'");
$data = mysqli_fetch_array($check);
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Formulir Pernyataan Peserta</title>
   <link rel="stylesheet" href="style.css">
</head>

<body>

   <?php include 'kopsurat.php'; ?>

   <!-- content.php -->
   <div class="form-title">Formulir Pernyataan Peserta</div>

   <div class="content">
      <p>Saya yang bertanda tangan di bawah ini :</p>

      <p><span>Nama</span>: <?= $data['patient_name'] ?></p>
      <p><span>Tempat / Tanggal Lahir</span>: <?= $data['patient_place'] ?> / <?= $data['patient_datebirth'] ?></p>
      <p><span>Jenis Kelamin</span>: <?= $data['patient_gender'] ?></p>
      <p><span>NIK / No. Kartu BPJS</span>: <?= $data['patient_nik'] ?></p>
      <p><span>Nomor Telepon</span>: <?= $data['patient_phone'] ?></p>

      <p>Dengan sadar, terkait pemanfaatan jaminan pelayanan kesehatan BPJS Kesehatan, dengan ini menyatakan :</p>

      <p class="quote">
         “Kesediaan atas data medis (Rekam Medis) diri saya untuk dipergunakan oleh Dokter / Rumah Sakit / BPJS Kesehatan
         sesuai dengan kepentingan.”
      </p>
   </div>

   <?php include 'tandatangan.php'; ?>

</body>

</html>