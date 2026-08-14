<?php

/*
|--------------------------------------------------------------------------
| PUBLIC PAGE
|--------------------------------------------------------------------------
|
| Halaman ini dapat diakses tanpa login/session.
|
*/

$is_public_page = true;


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once '../../database/connect.php';


/*
|--------------------------------------------------------------------------
| AMBIL ID HASH
|--------------------------------------------------------------------------
*/

$idHash = $_GET['id'] ?? '';

$statusValid = false;
$dataSurat = null;


/*
|--------------------------------------------------------------------------
| VALIDASI ID
|--------------------------------------------------------------------------
*/

if (!empty($idHash)) {

   /*
   |--------------------------------------------------------------------------
   | CARI SURAT BERDASARKAN MD5 ID
   |--------------------------------------------------------------------------
   */

   $stmt = $koneksi->prepare("
      SELECT
         ss.*,

         pv.id_doctor,
         pv.visit_ID,
         pv.visit_date,
         pv.id_customer,

         mp.patient_name,
         mp.nomor_rm,
         mp.patient_nik,
         mp.patient_datebirth,
         mp.patient_place,
         mp.patient_gender,
         mp.patient_address

      FROM surat_pemeriksaan_mata ss

      INNER JOIN pasien_visit pv
         ON pv.id_visit = ss.id_visit

      INNER JOIN ms_patient mp
         ON mp.id_patient = ss.id_patient

      WHERE MD5(ss.id) = ?

      LIMIT 1
   ");

   if ($stmt) {

      $stmt->bind_param(
         "s",
         $idHash
      );

      $stmt->execute();

      $result = $stmt->get_result();

      $dataSurat = $result->fetch_assoc();

      $stmt->close();

      $statusValid = !empty($dataSurat);
   }
}


/*
|--------------------------------------------------------------------------
| HELPER DATA
|--------------------------------------------------------------------------
*/

function e($value)
{
   return htmlspecialchars(
      (string)($value ?? '-'),
      ENT_QUOTES,
      'UTF-8'
   );
}


function showValue($value)
{
   if ($value === null || trim((string)$value) === '') {
      return '-';
   }

   return e($value);
}


function showWithUnit($value, $unit)
{
   if ($value === null || trim((string)$value) === '') {
      return '-';
   }

   return e($value) . ' ' . e($unit);
}


/*
|--------------------------------------------------------------------------
| DATA SURAT
|--------------------------------------------------------------------------
*/

if ($statusValid) {

   /*
   |--------------------------------------------------------------------------
   | IDENTITAS
   |--------------------------------------------------------------------------
   */

   $nomorSurat =
      $dataSurat['nomor_surat']
      ?? '-';

   $tanggalSurat =
      $dataSurat['tanggal_surat']
      ?? '-';

   $namaPasien =
      $dataSurat['patient_name']
      ?? '-';

   $nomorRM =
      $dataSurat['nomor_rm']
      ?? '-';

   $nik =
      $dataSurat['patient_nik']
      ?? '-';

   $tanggalLahir =
      $dataSurat['patient_datebirth']
      ?? '-';

   $tempatLahir =
      $dataSurat['patient_place']
      ?? '-';

   $jenisKelamin =
      $dataSurat['patient_gender']
      ?? '-';

   $alamat =
      $dataSurat['patient_address']
      ?? '-';


   /*
   |--------------------------------------------------------------------------
   | TANDA VITAL
   |--------------------------------------------------------------------------
   */

   $tekananDarah =
      $dataSurat['tekanan_darah']
      ?? '';

   $nadi =
      $dataSurat['nadi']
      ?? '';

   $suhu =
      $dataSurat['suhu']
      ?? '';

   $respirasi =
      $dataSurat['respirasi']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | LABORATORIUM
   |--------------------------------------------------------------------------
   */

   $gulaDarah =
      $dataSurat['gula_darah_sewaktu']
      ?? '';

   $kolesterol =
      $dataSurat['kolesterol_total']
      ?? '';

   $asamUrat =
      $dataSurat['asam_urat']
      ?? '';

   $hemoglobin =
      $dataSurat['hemoglobin']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | VISUS OD
   |--------------------------------------------------------------------------
   */

   $visusOdTanpaJauh =
      $dataSurat['visus_od_tanpa_koreksi_jauh']
      ?? '';

   $visusOdTanpaDekat =
      $dataSurat['visus_od_tanpa_koreksi_dekat']
      ?? '';

   $visusOdKoreksiJauh =
      $dataSurat['visus_od_dengan_koreksi_jauh']
      ?? '';

   $visusOdKoreksiDekat =
      $dataSurat['visus_od_dengan_koreksi_dekat']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | VISUS OS
   |--------------------------------------------------------------------------
   */

   $visusOsTanpaJauh =
      $dataSurat['visus_os_tanpa_koreksi_jauh']
      ?? '';

   $visusOsTanpaDekat =
      $dataSurat['visus_os_tanpa_koreksi_dekat']
      ?? '';

   $visusOsKoreksiJauh =
      $dataSurat['visus_os_dengan_koreksi_jauh']
      ?? '';

   $visusOsKoreksiDekat =
      $dataSurat['visus_os_dengan_koreksi_dekat']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | REFRAKSI OD
   |--------------------------------------------------------------------------
   */

   $refraksiOdSph =
      $dataSurat['refraksi_od_sph']
      ?? '';

   $refraksiOdCyl =
      $dataSurat['refraksi_od_cyl']
      ?? '';

   $refraksiOdAxis =
      $dataSurat['refraksi_od_axis']
      ?? '';

   $refraksiOdAdd =
      $dataSurat['refraksi_od_add']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | REFRAKSI OS
   |--------------------------------------------------------------------------
   */

   $refraksiOsSph =
      $dataSurat['refraksi_os_sph']
      ?? '';

   $refraksiOsCyl =
      $dataSurat['refraksi_os_cyl']
      ?? '';

   $refraksiOsAxis =
      $dataSurat['refraksi_os_axis']
      ?? '';

   $refraksiOsAdd =
      $dataSurat['refraksi_os_add']
      ?? '';

   $pd =
      $dataSurat['pd']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | PEMERIKSAAN MATA LAINNYA
   |--------------------------------------------------------------------------
   */

   $tioOd =
      $dataSurat['tio_od']
      ?? '';

   $tioOs =
      $dataSurat['tio_os']
      ?? '';

   $segmenAnteriorOd =
      $dataSurat['segmen_anterior_od']
      ?? '';

   $segmenAnteriorOs =
      $dataSurat['segmen_anterior_os']
      ?? '';

   $segmenPosteriorOd =
      $dataSurat['segmen_posterior_od']
      ?? '';

   $segmenPosteriorOs =
      $dataSurat['segmen_posterior_os']
      ?? '';


   /*
   |--------------------------------------------------------------------------
   | KESIMPULAN & REKOMENDASI
   |--------------------------------------------------------------------------
   */

   $kesimpulan =
      $dataSurat['kesimpulan']
      ?? '';

   $rekomendasi =
      $dataSurat['rekomendasi']
      ?? '';
} else {

   /*
   |--------------------------------------------------------------------------
   | DEFAULT
   |--------------------------------------------------------------------------
   */

   $nomorSurat = '-';
   $tanggalSurat = '-';

   $namaPasien = '-';
   $nomorRM = '-';
   $nik = '-';
   $tanggalLahir = '-';
   $tempatLahir = '-';
   $jenisKelamin = '-';
   $alamat = '-';

   $tekananDarah = '';
   $nadi = '';
   $suhu = '';
   $respirasi = '';

   $gulaDarah = '';
   $kolesterol = '';
   $asamUrat = '';
   $hemoglobin = '';

   $visusOdTanpaJauh = '';
   $visusOdTanpaDekat = '';
   $visusOdKoreksiJauh = '';
   $visusOdKoreksiDekat = '';

   $visusOsTanpaJauh = '';
   $visusOsTanpaDekat = '';
   $visusOsKoreksiJauh = '';
   $visusOsKoreksiDekat = '';

   $refraksiOdSph = '';
   $refraksiOdCyl = '';
   $refraksiOdAxis = '';
   $refraksiOdAdd = '';

   $refraksiOsSph = '';
   $refraksiOsCyl = '';
   $refraksiOsAxis = '';
   $refraksiOsAdd = '';

   $pd = '';

   $tioOd = '';
   $tioOs = '';

   $segmenAnteriorOd = '';
   $segmenAnteriorOs = '';

   $segmenPosteriorOd = '';
   $segmenPosteriorOs = '';

   $kesimpulan = '';
   $rekomendasi = '';
}

?>


<!DOCTYPE html>

<html lang="id">

<head>

   <meta charset="UTF-8">

   <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0">

   <title>
      Verifikasi Surat Hasil Pemeriksaan Mata
   </title>


   <!-- Bootstrap -->

   <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">


   <!-- Font Awesome -->

   <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


   <style>
      * {
         box-sizing: border-box;
      }


      body {

         margin: 0;

         min-height: 100vh;

         background:
            linear-gradient(135deg,
               #eef7f3,
               #f8fafc);

         font-family:
            Arial,
            Helvetica,
            sans-serif;

         color: #1f2937;

      }


      /* =====================================================
         HEADER
      ===================================================== */

      .top-header {

         background: #ffffff;

         border-bottom:
            1px solid #e5e7eb;

         padding:
            18px 20px;

      }


      .brand {

         display: flex;

         align-items: center;

         gap: 12px;

         max-width: 1000px;

         margin: auto;

      }


      .brand-icon {

         width: 44px;

         height: 44px;

         border-radius: 11px;

         background: #198754;

         color: white;

         display: flex;

         align-items: center;

         justify-content: center;

         font-size: 20px;

      }


      .brand-title {

         font-size: 17px;

         font-weight: 700;

         margin: 0;

      }


      .brand-subtitle {

         font-size: 12px;

         color: #6b7280;

         margin-top: 2px;

      }


      /* =====================================================
         CONTAINER
      ===================================================== */

      .verification-container {

         width: 100%;

         max-width: 1000px;

         margin:
            35px auto;

         padding:
            0 15px;

      }


      /* =====================================================
         CARD
      ===================================================== */

      .verification-card {

         background: #ffffff;

         border-radius: 18px;

         box-shadow:
            0 12px 40px rgba(0, 0, 0, 0.08);

         overflow: hidden;

      }


      /* =====================================================
         VALID
      ===================================================== */

      .status-valid {

         background:
            linear-gradient(135deg,
               #198754,
               #157347);

         color: #ffffff;

         text-align: center;

         padding:
            30px 20px;

      }


      .status-icon {

         width: 70px;

         height: 70px;

         margin:
            0 auto 15px;

         border-radius: 50%;

         background:
            rgba(255,
               255,
               255,
               0.18);

         display: flex;

         align-items: center;

         justify-content: center;

         font-size: 34px;

      }


      .status-title {

         font-size: 24px;

         font-weight: 700;

         margin-bottom: 6px;

      }


      .status-description {

         font-size: 14px;

         opacity: 0.9;

         margin: 0;

      }


      /* =====================================================
         INVALID
      ===================================================== */

      .status-invalid {

         background:
            linear-gradient(135deg,
               #dc3545,
               #b02a37);

         color: white;

         text-align: center;

         padding:
            40px 20px;

      }


      /* =====================================================
         CONTENT
      ===================================================== */

      .card-content {

         padding: 30px;

      }


      /* =====================================================
         SECTION TITLE
      ===================================================== */

      .section-title {

         font-size: 15px;

         font-weight: 700;

         color: #111827;

         margin-bottom: 15px;

         display: flex;

         align-items: center;

         gap: 8px;

      }


      .section-title i {

         color: #198754;

      }


      /* =====================================================
         NOMOR SURAT
      ===================================================== */

      .nomor-surat {

         background: #f0fdf4;

         border:
            1px solid #bbf7d0;

         border-radius: 10px;

         padding: 15px;

         text-align: center;

         margin-bottom: 25px;

      }


      .nomor-label {

         font-size: 11px;

         color: #15803d;

         margin-bottom: 5px;

      }


      .nomor-value {

         font-size: 18px;

         font-weight: 700;

         color: #166534;

         letter-spacing: 0.5px;

      }


      /* =====================================================
         INFO GRID
      ===================================================== */

      .info-grid {

         display: grid;

         grid-template-columns:
            repeat(2, 1fr);

         gap: 12px;

      }


      .info-item {

         background: #f8fafc;

         border:
            1px solid #e5e7eb;

         border-radius: 10px;

         padding:
            13px 15px;

      }


      .info-label {

         font-size: 11px;

         color: #6b7280;

         margin-bottom: 4px;

      }


      .info-value {

         font-size: 14px;

         font-weight: 600;

         color: #111827;

         word-break: break-word;

      }


      /* =====================================================
         TABLE
      ===================================================== */

      .medical-table-wrapper {

         width: 100%;

         overflow-x: auto;

      }


      .medical-table {

         width: 100%;

         border-collapse: collapse;

         margin-top: 10px;

      }


      .medical-table th {

         background: #f8fafc;

         font-size: 12px;

         text-align: left;

         padding: 10px;

         border:
            1px solid #e5e7eb;

      }


      .medical-table td {

         font-size: 13px;

         padding: 10px;

         border:
            1px solid #e5e7eb;

         vertical-align: top;

      }


      .medical-table th.center,
      .medical-table td.center {

         text-align: center;

      }


      /* =====================================================
         RESULT BOX
      ===================================================== */

      .result-box {

         background: #f8fafc;

         border:
            1px solid #e5e7eb;

         border-radius: 10px;

         padding: 15px;

         margin-top: 10px;

         font-size: 13px;

         line-height: 1.6;

      }


      .result-box strong {

         color: #111827;

      }


      /* =====================================================
         VERIFIED INFO
      ===================================================== */

      .verified-info {

         margin-top: 25px;

         padding: 15px;

         border-radius: 10px;

         background: #f8fafc;

         border:
            1px solid #e5e7eb;

         font-size: 12px;

         color: #6b7280;

      }


      .verified-info strong {

         color: #374151;

      }


      /* =====================================================
         FOOTER
      ===================================================== */

      .page-footer {

         text-align: center;

         padding:
            25px 15px 35px;

         color: #6b7280;

         font-size: 11px;

      }


      /* =====================================================
         MOBILE
      ===================================================== */

      @media (max-width: 650px) {

         .verification-container {

            margin-top: 20px;

         }


         .card-content {

            padding: 20px;

         }


         .info-grid {

            grid-template-columns: 1fr;

         }


         .status-title {

            font-size: 21px;

         }


         .nomor-value {

            font-size: 15px;

         }


         .medical-table th,
         .medical-table td {

            font-size: 12px;

            padding: 8px;

         }

      }
   </style>

</head>


<body>


   <!-- ==========================================================
        HEADER
   ========================================================== -->

   <header class="top-header">

      <div class="brand">

         <div class="brand-icon">

            <i class="fas fa-eye"></i>

         </div>


         <div>

            <div class="brand-title">

               Verifikasi Hasil Pemeriksaan Mata

            </div>

            <div class="brand-subtitle">

               Sistem Verifikasi Surat Elektronik

            </div>

         </div>

      </div>

   </header>



   <!-- ==========================================================
        CONTENT
   ========================================================== -->

   <div class="verification-container">

      <div class="verification-card">


         <?php if ($statusValid): ?>


            <!-- ==================================================
                 STATUS VALID
            =================================================== -->

            <div class="status-valid">

               <div class="status-icon">

                  <i class="fas fa-check"></i>

               </div>


               <div class="status-title">

                  DOKUMEN VALID

               </div>


               <p class="status-description">

                  Surat hasil pemeriksaan mata terdaftar
                  dan telah diverifikasi oleh sistem.

               </p>

            </div>



            <div class="card-content">


               <!-- ==================================================
                    NOMOR SURAT
               =================================================== -->

               <div class="nomor-surat">

                  <div class="nomor-label">

                     NOMOR SURAT

                  </div>


                  <div class="nomor-value">

                     <?= e($nomorSurat) ?>

                  </div>

               </div>



               <!-- ==================================================
                    IDENTITAS PASIEN
               =================================================== -->

               <div class="section-title">

                  <i class="fas fa-user"></i>

                  Identitas Pasien

               </div>


               <div class="info-grid">


                  <div class="info-item">

                     <div class="info-label">
                        Nama Pasien
                     </div>

                     <div class="info-value">

                        <?= e($namaPasien) ?>

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        Nomor RM
                     </div>

                     <div class="info-value">

                        <?= e($nomorRM) ?>

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        NIK
                     </div>

                     <div class="info-value">

                        <?= e($nik) ?>

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        Jenis Kelamin
                     </div>

                     <div class="info-value">

                        <?= e($jenisKelamin) ?>

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        Tempat / Tanggal Lahir
                     </div>

                     <div class="info-value">

                        <?= e($tempatLahir) ?>

                        /

                        <?= e($tanggalLahir) ?>

                     </div>

                  </div>


                  <div
                     class="info-item"
                     style="grid-column: 1 / -1;">

                     <div class="info-label">
                        Alamat
                     </div>

                     <div class="info-value">

                        <?= e($alamat) ?>

                     </div>

                  </div>

               </div>



               <!-- ==================================================
                    INFORMASI SURAT
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-file-medical"></i>

                  Informasi Surat

               </div>


               <div class="info-grid">


                  <div class="info-item">

                     <div class="info-label">
                        Jenis Dokumen
                     </div>

                     <div class="info-value">

                        Hasil Pemeriksaan Mata

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        Tanggal Surat
                     </div>

                     <div class="info-value">

                        <?= e($tanggalSurat) ?>

                     </div>

                  </div>

               </div>



               <!-- ==================================================
                    TANDA VITAL
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-heart-pulse"></i>

                  Pemeriksaan Tanda Vital

               </div>


               <div class="medical-table-wrapper">

                  <table class="medical-table">

                     <thead>

                        <tr>

                           <th>
                              Pemeriksaan
                           </th>

                           <th class="center">
                              Hasil
                           </th>

                        </tr>

                     </thead>


                     <tbody>

                        <tr>

                           <td>
                              Tekanan Darah
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $tekananDarah,
                                 'mmHg'
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Nadi
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $nadi,
                                 'x/menit'
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Suhu
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $suhu,
                                 '°C'
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Respirasi
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $respirasi,
                                 'x/menit'
                              ) ?>

                           </td>

                        </tr>

                     </tbody>

                  </table>

               </div>



               <!-- ==================================================
                    LABORATORIUM
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-flask"></i>

                  Pemeriksaan Laboratorium

               </div>


               <div class="medical-table-wrapper">

                  <table class="medical-table">

                     <thead>

                        <tr>

                           <th>
                              Pemeriksaan
                           </th>

                           <th class="center">
                              Hasil
                           </th>

                           <th class="center">
                              Keterangan
                           </th>

                        </tr>

                     </thead>


                     <tbody>

                        <tr>

                           <td>
                              Gula Darah Sewaktu
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $gulaDarah,
                                 'mg/dL'
                              ) ?>

                           </td>

                           <td class="center">

                              <?= showValue(
                                 $dataSurat['gula_darah_keterangan'] ?? ''
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Kolesterol Total
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $kolesterol,
                                 'mg/dL'
                              ) ?>

                           </td>

                           <td class="center">

                              <?= showValue(
                                 $dataSurat['kolesterol_keterangan'] ?? ''
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Asam Urat
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $asamUrat,
                                 'mg/dL'
                              ) ?>

                           </td>

                           <td class="center">

                              <?= showValue(
                                 $dataSurat['asam_urat_keterangan'] ?? ''
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Hemoglobin
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $hemoglobin,
                                 'g/dL'
                              ) ?>

                           </td>

                           <td class="center">

                              <?= showValue(
                                 $dataSurat['hemoglobin_keterangan'] ?? ''
                              ) ?>

                           </td>

                        </tr>

                     </tbody>

                  </table>

               </div>



               <!-- ==================================================
                    VISUS
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-eye"></i>

                  Pemeriksaan Visus

               </div>


               <div class="medical-table-wrapper">

                  <table class="medical-table">

                     <thead>

                        <tr>

                           <th rowspan="2">
                              Mata
                           </th>

                           <th
                              colspan="2"
                              class="center">

                              Tanpa Koreksi

                           </th>

                           <th
                              colspan="2"
                              class="center">

                              Dengan Koreksi

                           </th>

                        </tr>


                        <tr>

                           <th class="center">
                              Jauh
                           </th>

                           <th class="center">
                              Dekat
                           </th>

                           <th class="center">
                              Jauh
                           </th>

                           <th class="center">
                              Dekat
                           </th>

                        </tr>

                     </thead>


                     <tbody>

                        <tr>

                           <td>
                              OD / Kanan
                           </td>

                           <td class="center">
                              <?= showValue($visusOdTanpaJauh) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOdTanpaDekat) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOdKoreksiJauh) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOdKoreksiDekat) ?>
                           </td>

                        </tr>


                        <tr>

                           <td>
                              OS / Kiri
                           </td>

                           <td class="center">
                              <?= showValue($visusOsTanpaJauh) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOsTanpaDekat) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOsKoreksiJauh) ?>
                           </td>

                           <td class="center">
                              <?= showValue($visusOsKoreksiDekat) ?>
                           </td>

                        </tr>

                     </tbody>

                  </table>

               </div>



               <!-- ==================================================
                    REFRAKSI
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-glasses"></i>

                  Pemeriksaan Refraksi

               </div>


               <div class="medical-table-wrapper">

                  <table class="medical-table">

                     <thead>

                        <tr>

                           <th>
                              Mata
                           </th>

                           <th class="center">
                              SPH
                           </th>

                           <th class="center">
                              CYL
                           </th>

                           <th class="center">
                              AXIS
                           </th>

                           <th class="center">
                              ADD
                           </th>

                           <th class="center">
                              PD
                           </th>

                        </tr>

                     </thead>


                     <tbody>

                        <tr>

                           <td>
                              OD / Kanan
                           </td>

                           <td class="center">
                              <?= showValue($refraksiOdSph) ?>
                           </td>

                           <td class="center">
                              <?= showValue($refraksiOdCyl) ?>
                           </td>

                           <td class="center">

                              <?= showValue($refraksiOdAxis) ?>

                              <?php if (!empty($refraksiOdAxis)): ?>
                                 °
                              <?php endif; ?>

                           </td>

                           <td class="center">
                              <?= showValue($refraksiOdAdd) ?>
                           </td>

                           <td
                              class="center"
                              rowspan="2">

                              <?= showValue($pd) ?>

                              <?php if (!empty($pd)): ?>
                                 mm
                              <?php endif; ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              OS / Kiri
                           </td>

                           <td class="center">
                              <?= showValue($refraksiOsSph) ?>
                           </td>

                           <td class="center">
                              <?= showValue($refraksiOsCyl) ?>
                           </td>

                           <td class="center">

                              <?= showValue($refraksiOsAxis) ?>

                              <?php if (!empty($refraksiOsAxis)): ?>
                                 °
                              <?php endif; ?>

                           </td>

                           <td class="center">
                              <?= showValue($refraksiOsAdd) ?>
                           </td>

                        </tr>

                     </tbody>

                  </table>

               </div>



               <!-- ==================================================
                    PEMERIKSAAN MATA LAINNYA
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-eye"></i>

                  Pemeriksaan Mata Lainnya

               </div>


               <div class="medical-table-wrapper">

                  <table class="medical-table">

                     <thead>

                        <tr>

                           <th>
                              Pemeriksaan
                           </th>

                           <th class="center">
                              OD / Kanan
                           </th>

                           <th class="center">
                              OS / Kiri
                           </th>

                        </tr>

                     </thead>


                     <tbody>

                        <tr>

                           <td>
                              Tekanan Intraokular (TIO)
                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $tioOd,
                                 'mmHg'
                              ) ?>

                           </td>

                           <td class="center">

                              <?= showWithUnit(
                                 $tioOs,
                                 'mmHg'
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Segmen Anterior
                           </td>

                           <td>

                              <?= nl2br(
                                 showValue(
                                    $segmenAnteriorOd
                                 )
                              ) ?>

                           </td>

                           <td>

                              <?= nl2br(
                                 showValue(
                                    $segmenAnteriorOs
                                 )
                              ) ?>

                           </td>

                        </tr>


                        <tr>

                           <td>
                              Segmen Posterior
                           </td>

                           <td>

                              <?= nl2br(
                                 showValue(
                                    $segmenPosteriorOd
                                 )
                              ) ?>

                           </td>

                           <td>

                              <?= nl2br(
                                 showValue(
                                    $segmenPosteriorOs
                                 )
                              ) ?>

                           </td>

                        </tr>

                     </tbody>

                  </table>

               </div>



               <!-- ==================================================
                    KESIMPULAN
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-clipboard-check"></i>

                  Kesimpulan

               </div>


               <div class="result-box">

                  <?php if (!empty(trim($kesimpulan))): ?>

                     <?= nl2br(
                        e($kesimpulan)
                     ) ?>

                  <?php else: ?>

                     Tidak ada kesimpulan yang tercatat.

                  <?php endif; ?>

               </div>



               <!-- ==================================================
                    REKOMENDASI
               =================================================== -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-notes-medical"></i>

                  Rekomendasi

               </div>


               <div class="result-box">

                  <?php if (!empty(trim($rekomendasi))): ?>

                     <?= nl2br(
                        e($rekomendasi)
                     ) ?>

                  <?php else: ?>

                     Tidak ada rekomendasi yang tercatat.

                  <?php endif; ?>

               </div>



               <!-- ==================================================
                    VERIFICATION INFO
               =================================================== -->

               <div class="verified-info">

                  <i class="fas fa-shield-halved me-1"></i>

                  <strong>
                     Verifikasi berhasil.
                  </strong>

                  Dokumen hasil pemeriksaan mata dengan nomor surat
                  <strong><?= e($nomorSurat) ?></strong>
                  terdaftar dalam sistem dan data yang ditampilkan
                  berasal dari dokumen elektronik yang diterbitkan
                  oleh fasilitas kesehatan.

               </div>


            </div>


         <?php else: ?>


            <!-- ==================================================
                 INVALID
            =================================================== -->

            <div class="status-invalid">

               <div class="status-icon">

                  <i class="fas fa-xmark"></i>

               </div>


               <div class="status-title">

                  DOKUMEN TIDAK DITEMUKAN

               </div>


               <p class="status-description">

                  Dokumen yang Anda verifikasi tidak ditemukan
                  atau tautan verifikasi tidak valid.

               </p>

            </div>


            <div class="card-content text-center">

               <p class="text-muted mb-0">

                  Pastikan QR Code atau tautan verifikasi
                  berasal dari dokumen resmi fasilitas kesehatan.

               </p>

            </div>


         <?php endif; ?>


      </div>

   </div>



   <!-- ==========================================================
        FOOTER
   ========================================================== -->

   <div class="page-footer">

      © <?= date('Y') ?>

      Sistem Informasi Klinik

      <br>

      Halaman ini digunakan untuk verifikasi
      keaslian dokumen elektronik.

   </div>


</body>

</html>