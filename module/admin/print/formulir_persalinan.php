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
   <div class="form-title">
      REKAPITULASI PELAYANAN PERSALINAN DI FASILITAS KESEHATAN TINGKAT PERTAMA (FKTP) <br> BPJS KESEHATAN CABANG LUBUK PAKAM
   </div>

   <div class="content">
      <p>Saya yang bertanda tangan di bawah ini :</p>

      <p><span>Nama Penderita</span>: <?= $data['patient_name'] ?></p>
      <p><span>Nomor Identitas</span>: <?= $data['patient_nik'] ?></p>
      <p><span>Tempat / Tanggal Lahir</span>: <?= $data['patient_place'] ?> / <?= $data['patient_datebirth'] ?></p>
      <p><span>Alamat dan Nomor Telepon</span>: <?= $data['patient_address'] ?>,<?= $data['patient_phone'] ?></p>
      <p><span>Tanggal Pelayanan</span>: <?= $tanggalSekarang ?></p>
      <p><span>Gravid</span>: <?= $data['gravid'] ?></p>
      <p><span>Abortus</span>: <?= $data['abortus'] ?></p>
      <p><span>Partus</span>: <?= $data['partus'] ?></p>
      <p><span>Jenis Persalinan</span>: <?= $data['jenis_persalinan'] ?></p>
      <p><span>Besaran Tarif Paket</span>: <?= $data['tarif_paket'] ?></p>
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