<?php
require_once '../../../database/connect.php';

$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

/* ================================
   LOAD DATA DARI ENDPOINT
================================ */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
   ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'];
$current_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$api_url = $protocol . $host . $current_path . "/getpersalinan.php?no=$no&rm=$rm";
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$decode = json_decode($response, true);
$data = $decode['data'] ?? [];

/* ================================
   FORMAT TANGGAL INDONESIA
================================ */
function formatTanggalIndonesia($tanggal)
{
   if (!$tanggal) return "-";
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

<!-- ===========================
     STYLE DI-ISOLASI (PREFIX)
=========================== -->
<style>
   .form-persalinan {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 0 10mm;
      font-family: "Times New Roman", serif;
   }

   /* Judul */
   .form-persalinan .form-title {
      text-align: center;
      font-size: 18pt;
      font-weight: bold;
      margin-top: 10px;
      margin-bottom: 20px;
      line-height: 1.4;
      text-transform: uppercase;
   }

   /* Isi */
   .form-persalinan .content {
      font-size: 14pt;
      line-height: 1.5;
      margin-top: 10px;
   }

   .form-persalinan .content p {
      margin: 4px 0;
   }

   .form-persalinan .content span {
      font-weight: bold;
      display: inline-block;
      width: 240px;
   }

   /* Signature */
   .form-persalinan .signature {
      margin-top: 40px;
      text-align: right;
      font-size: 14pt;
   }

   .form-persalinan .signature-block {
      display: inline-block;
      text-align: center;
      margin-top: 20px;
   }

   .form-persalinan .signature-image img {
      max-width: 220px;
      height: auto;
   }

   .form-persalinan .signature-name {
      margin-top: 5px;
      font-size: 14pt;
   }
</style>


<!-- =======================================================
     FORMULIR PERSALINAN (AMAN DIBUNDLE – NO HEAD / NO BODY)
=========================================================== -->
<div class="form-persalinan">

   <?php include 'kopsurat.php'; ?>

   <div class="form-title">
      REKAPITULASI PELAYANAN PERSALINAN DI FASILITAS KESEHATAN TINGKAT PERTAMA (FKTP)<br>
      BPJS KESEHATAN CABANG LUBUK PAKAM
   </div>

   <div class="content">

      <p>Saya yang bertanda tangan di bawah ini :</p>

      <p><span>Nama Penderita</span>: <?= $data['patient_name'] ?? '-' ?></p>
      <p><span>Nomor Identitas</span>: <?= $data['patient_nik'] ?? '-' ?></p>

      <p>
         <span>Tempat / Tanggal Lahir</span>:
         <?= ($data['patient_place'] ?? '-') . " / " . formatTanggalIndonesia($data['patient_datebirth'] ?? '-') ?>
      </p>

      <p>
         <span>Alamat dan Nomor Telepon</span>:
         <?= ($data['patient_address'] ?? '-') . ", " . ($data['patient_phone'] ?? '-') ?>
      </p>

      <p><span>Tanggal Pelayanan</span>: <?= $tanggalSekarang ?></p>

      <p><span>Gravid</span>: <?= $data['gravid'] ?? '-' ?></p>
      <p><span>Abortus</span>: <?= $data['abortus'] ?? '-' ?></p>
      <p><span>Partus</span>: <?= $data['partus'] ?? '-' ?></p>
      <p><span>Jenis Persalinan</span>: <?= $data['jenis_persalinan'] ?? '-' ?></p>
      <p><span>Besaran Tarif Paket</span>: <?= $data['tarif_paket'] ?? '-' ?></p>

   </div>


   <div class="signature">
      <p>Tanjung Morawa, <?= $tanggalSekarang ?></p>

      <div class="signature-block">

         <div class="signature-image">
            <?php if (!empty($data['ttd_file'])): ?>
               <img src="../../../uploads/ttd/<?= $data['ttd_file'] ?>">
            <?php else: ?>
               <img src="../../../uploads/ttd/default.png">
            <?php endif; ?>
         </div>

         <strong><u><?= $data['patient_name'] ?? '................................................' ?></u></strong>
         <div class="signature-name">Yang Membuat Pernyataan</div>

      </div>
   </div>

</div>