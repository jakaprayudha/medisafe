<?php
require_once '../../../database/connect.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN pasien_ttd_pernyataan ON pasien_ttd_pernyataan.visit_ID = pasien_visit.visit_ID  WHERE pasien_visit.visit_ID='$no' AND pasien_ttd_pernyataan.nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

function formatTanggalIndonesia($tanggal)
{
   $bulanIndo = [
      1 => 'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
   ];

   $pecah = explode('-', $tanggal);
   return intval($pecah[2]) . ' ' . $bulanIndo[(int)$pecah[1]] . ' ' . $pecah[0];
}

$tanggalSekarang = formatTanggalIndonesia(date('Y-m-d'));
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



   <div class="signature">
      <p>Tanjung Morawa, <?= $tanggalSekarang ?></p>
      <div class="signature-block">
         <div class="signature-image">
            <img src="../../../uploads/ttd/ttd_000002_1762298006.png" width="200" alt="Tanda Tangan Pasien">
         </div>
         <strong><u><?= htmlspecialchars($data['patient_name'] ?? '....................................') ?></u></strong>
         <div class="signature-name">Yang Membuat Pernyataan</div>
      </div>
   </div>

</body>

</html>