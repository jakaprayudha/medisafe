<?php
require '../../database/connect.php';
require '../../controller/view.php';

$no = $_GET['no'];
$rm = $_GET['rm'];
$id = $_GET['id'];

$checkklinik = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataklinik = mysqli_fetch_array($checkklinik);

$checkpasien = mysqli_query($koneksi, "SELECT * FROM pasien_visit 
   INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient  INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
   WHERE pasien_visit.visit_ID='$no'");
$datapasien = mysqli_fetch_array($checkpasien);

$checkobat = tampildata("SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$id' ");

$total = 0;
foreach ($checkobat as $obat) {
  $total += $obat['qty'] * $obat['harga'];
}

$tglLahir = new DateTime($datapasien['patient_datebirth']);
$today    = new DateTime();

$usia = $today->diff($tglLahir);

// hasil:
$usiaTahun = $usia->y;
$usiaBulan = $usia->m;
$usiaHari  = $usia->d;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Resep Obat</title>

  <style>
    body {
      font-family: "Times New Roman", serif;
      font-size: 14px;
      color: #000;
    }

    .resep-container {
      width: 700px;
      margin: auto;
    }

    .kop {
      text-align: center;
      border-bottom: 2px solid #000;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }

    .kop h2 {
      margin: 0;
      font-size: 20px;
      text-transform: uppercase;
    }

    .kop p {
      margin: 2px 0;
      font-size: 12px;
    }

    .info {
      margin-bottom: 10px;
    }

    .info table {
      width: 100%;
      border-collapse: collapse;
    }

    .info td {
      padding: 2px 0;
    }

    .resep-title {
      text-align: center;
      font-weight: bold;
      margin: 12px 0;
      text-decoration: underline;
    }

    .obat {
      margin-top: 10px;
    }

    .obat table {
      width: 100%;
      border-collapse: collapse;
    }

    .obat th,
    .obat td {
      border: 1px solid #000;
      padding: 6px;
      text-align: left;
    }

    .obat th {
      background: #f2f2f2;
    }

    .footer {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
    }

    .ttd {
      text-align: center;
      width: 200px;
    }

    .ttd .nama {
      margin-top: 60px;
      font-weight: bold;
      text-decoration: underline;
    }

    .note {
      font-size: 11px;
      margin-top: 20px;
      font-style: italic;
    }

    @media print {
      body {
        margin: 0;
      }
    }
  </style>
</head>

<body>

  <div class="resep-container">

    <!-- KOP -->
    <div class="kop">
      <h2><?= $dataklinik['clinic_name'] ?></h2>
      <p><?= $dataklinik['address'] ?></p>
      <p>Telp. <?= $dataklinik['phone_number'] ?></p>
    </div>

    <!-- INFO PASIEN -->
    <div class="info">
      <table>
        <tr>
          <td width="20%">Nama</td>
          <td width="2%">:</td>
          <td width="48%"><?= $datapasien['patient_name'] ?></td>
          <td width="15%">Tanggal</td>
          <td width="2%">:</td>
          <td width="13%"><?= date('d-m-Y') ?></td>
        </tr>
        <tr>
          <td>No. RM</td>
          <td>:</td>
          <td><?= $datapasien['nomor_rm'] ?></td>
          <td>Umur</td>
          <td>:</td>
          <td> <?= $usiaTahun ?> Th <?= $usiaBulan ?> Bl <?= $usiaHari ?> Hr</td>
        </tr>
        <tr>
          <td>Dokter</td>
          <td>:</td>
          <td colspan="4"><?= $datapasien['doctor_name'] ?></td>
        </tr>
      </table>
    </div>

    <!-- JUDUL -->
    <div class="resep-title">RESEP</div>

    <!-- DAFTAR OBAT -->
    <div class="obat">
      <table>
        <thead>
          <tr>
            <th width="5%">No</th>
            <th width="35%">Nama Obat</th>
            <th width="15%">Jumlah</th>
            <th width="45%">Aturan Pakai (Signa)</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1;
          foreach ($checkobat as $obat): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td class="wrap"><?= $obat['pharmacy_name_trade'] ?>/<?= $obat['pharmacy_name_generic'] ?></td>
              <td><?= $obat['qty'] ?></td>
              <td><?= $obat['signa'] ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">

      <div class="note">
        * Obat harus dihabiskan sesuai petunjuk dokter
      </div>

      <div class="ttd">
        <div><?= $dataklinik['kabupaten'] ?>, <?= date('d-m-Y') ?></div>
        <div class="nama"><?= $datapasien['doctor_name'] ?></div>
        <div>SIP. </div>
      </div>

    </div>

  </div>

</body>

</html>