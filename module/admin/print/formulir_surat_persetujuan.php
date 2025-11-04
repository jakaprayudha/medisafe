<?php
require_once '../../../database/connect.php';
$no = $_GET['no'];

// Ambil data pasien & kunjungan
$check = mysqli_query($koneksi, "SELECT pasien_visit.*, ms_patient.*, ms_doctor.doctor_name, permintaan_ranap.* 
   FROM pasien_visit
   INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   LEFT JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
   INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_outpatient = pasien_visit.visit_ID
   WHERE pasien_visit.visit_ID = '$no'
");
$data = mysqli_fetch_array($check);

// Fungsi format tanggal Indonesia
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
$tanggalMasuk = isset($data['visit_date']) ? formatTanggalIndonesia($data['visit_date']) : '-';
$tanggalKeluar = isset($data['visit_out']) && $data['visit_out'] != '0000-00-00' ? formatTanggalIndonesia($data['visit_out']) : '...';
// Pastikan dua tanggal tersedia
$tgl_lahir = new DateTime($data['patient_datebirth']);
$tgl_kunjungan = new DateTime($data['visit_date']);

// Hitung selisih
$diff = $tgl_lahir->diff($tgl_kunjungan);

// Format hasil
$umur = $diff->y . " Tahun " . $diff->m . " Bulan " . $diff->d . " Hari";
?>

<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Persetujuan Tindakan Medis</title>
   <link rel="stylesheet" href="style.css">
   <style>
      /* Tambahan kecil khusus halaman ini */
      .nomor {
         text-align: center;
         margin-top: 10px;
         margin-bottom: 25px;
         font-size: 12pt;
      }

      .footer {
         margin-top: 60px;
         text-align: center;
         width: 100%;
      }

      .footer td {
         vertical-align: top;
         width: 33%;
         text-align: center;
      }

      .footer .space {
         height: 80px;
      }
   </style>
</head>

<body>

   <!-- Header mengikuti style.css -->
   <?php include 'kopsurat.php'; ?>

   <!-- Judul Surat -->
   <div class="form-title">SURAT PERSETUJUAN TINDAKAN MEDIS</div>
   <div class="nomor">NOMOR : _____/RITP/TS/<?= date('Y') ?></div>

   <!-- Isi Surat -->
   <div class="content">
      <p>Saya yang bertandatangan dibawah ini:</p>
      <p><span>Nama</span>: <?= htmlspecialchars($data['patient_name']) ?></p>
      <p><span>Umur</span>: <?= htmlspecialchars($umur) ?></p>
      <p><span>Jenis Kelamin</span>: <?= htmlspecialchars($data['patient_gender']) ?></p>

      <p>
         Dengan ini menyatakan sesungguhnya telah memberikan persetujuan untuk dilakukan tindakan medis berupa ....., terhadap diri saya sendiri*/Suami*/Istri*/Anak*/Ayah*/Ibu Saya, dengan
      </p>
      <p><span>Nama</span>: <?= htmlspecialchars($data['patient_name']) ?></p>
      <p><span>Umur</span>: <?= htmlspecialchars($umur) ?></p>
      <p><span>Jenis Kelamin</span>: <?= htmlspecialchars($data['patient_gender']) ?></p>
      <p><span>No.Kartu </span>: <?= htmlspecialchars($data['patient_nik']) ?></p>

      <p style="margin-top:15px;">Saya Juga telah menyatakan dengan sesungguhnya bahwa saya.
      <ul>1. Telah diberikan informasi dan penjelasan terhadap tindakan medis yang akan dilakukan tersebut
      </ul>
      <ul>2. Telah memahami sepenuhnya informasi dan penjelasan yang diberikan oleh dokter</ul>
      </p>
      <p>Demikian pernyataan persetujuan tindakan medis ini saya buat dengan penuh kesadaran dan tanpa paksaan.</p>
   </div>

   <!-- Tanda Tangan -->
   <table class="footer">
      <tr>
         <td>Saksi Saksi</td>
         <td>Dokter yang Merawat</td>
         <td>Tanjung Morawa, <?= $tanggalSekarang ?><br>Yang membuat pernyataan</td>
      </tr>
      <tr class="space">
         <td></td>
         <td></td>
         <td></td>
      </tr>
      <tr>
         <td>( <?= htmlspecialchars($data['patient_name']) ?> )</td>
         <td>( <?= htmlspecialchars($data['doctor_name'] ?? '................') ?> )</td>
         <td>( .......................................... )</td>
      </tr>
   </table>

</body>

</html>