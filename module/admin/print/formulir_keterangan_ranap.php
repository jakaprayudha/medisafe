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
?>

<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Keterangan Rawat Inap</title>
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
   <div class="form-title">KETERANGAN RAWAT INAP</div>
   <div class="nomor">NOMOR : _____/RITP/TS/<?= date('Y') ?></div>

   <!-- Isi Surat -->
   <div class="content">
      <p>Yang bertanda tangan di bawah ini :</p>
      <p><span>Nama</span>: <?= htmlspecialchars($data['doctor_name']) ?></p>
      <p><span>Jabatan</span>: Dokter Spesialis</p>

      <p>Dengan ini menyatakan bahwa pasien:</p>
      <p><span>Nama</span>: <?= htmlspecialchars($data['patient_name']) ?></p>
      <p><span>Alamat</span>: <?= htmlspecialchars($data['patient_address']) ?></p>
      <p><span>No. Kartu Peserta BPJS Kesehatan</span>: <?= htmlspecialchars($data['patient_nik'] ?? '-') ?></p>

      <p>Telah mendapat pelayanan kesehatan rawat inap.</p>

      <p><span>Tempat</span>: KLINIK RAWAT INAP TUTUN SEHATI</p>
      <p><span>Tanggal</span>: <?= $tanggalMasuk ?> s/d <?= $tanggalKeluar ?></p>
      <p><span>Diagnosa</span>: <?= htmlspecialchars($data['diagnosa_awal'] ?? '................................') ?></p>
      <p><span>Dokter yang merawat</span>: <?= htmlspecialchars($data['doctor_name'] ?? '................................') ?></p>

      <p style="margin-top:15px;">Demikian pernyataan ini dibuat dengan sebenarnya untuk dipergunakan dalam pengajuan klaim biaya rawat inap.</p>
   </div>

   <!-- Tanda Tangan -->
   <table class="footer">
      <tr>
         <td>Peserta / Keluarga Peserta</td>
         <td>Dokter yang Merawat</td>
         <td>Tanjung Morawa, <?= $tanggalSekarang ?><br>Dokter Penanggung Jawab</td>
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