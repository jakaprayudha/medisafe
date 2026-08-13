<?php

/*
|--------------------------------------------------------------------------
| PUBLIC PAGE
|--------------------------------------------------------------------------
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
| FUNGSI FORMAT TANGGAL INDONESIA
|--------------------------------------------------------------------------
*/

function tanggalIndonesia($tanggal)
{
   if (empty($tanggal) || $tanggal == '0000-00-00') {
      return '-';
   }

   $bulan = [
      1  => 'Januari',
      2  => 'Februari',
      3  => 'Maret',
      4  => 'April',
      5  => 'Mei',
      6  => 'Juni',
      7  => 'Juli',
      8  => 'Agustus',
      9  => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember'
   ];

   $timestamp = strtotime($tanggal);

   if (!$timestamp) {
      return '-';
   }

   $hari  = date('d', $timestamp);
   $bulanIndex = (int) date('m', $timestamp);
   $tahun = date('Y', $timestamp);

   return $hari . ' ' . $bulan[$bulanIndex] . ' ' . $tahun;
}


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

         /* =========================
            SURAT KEMATIAN
         ========================= */

         sk.*,

         /* =========================
            VISIT
         ========================= */

         pv.id_doctor,
         pv.visit_ID,
         pv.visit_date,
         pv.id_customer,

         /* =========================
            PASIEN
         ========================= */

         mp.patient_name,
         mp.nomor_rm,
         mp.patient_nik,
         mp.patient_datebirth,
         mp.patient_place,
         mp.patient_gender,
         mp.patient_address,

         /* =========================
            DOKTER
         ========================= */

         md.doctor_name

      FROM surat_kematian sk

      INNER JOIN pasien_visit pv
         ON pv.id_visit = sk.id_visit

      INNER JOIN ms_patient mp
         ON mp.id_patient = sk.id_patient

      LEFT JOIN ms_doctor md
         ON md.id_doctor = sk.dokter_menyatakan

      WHERE MD5(sk.id) = ?

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
| DATA DEFAULT
|--------------------------------------------------------------------------
*/

if ($statusValid) {

   /*
   |--------------------------------------------------------------------------
   | SURAT
   |--------------------------------------------------------------------------
   */

   $nomorSurat =
      $dataSurat['nomor_surat'] ?? '-';

   $tanggalSurat =
      $dataSurat['tanggal_surat'] ?? '';

   /*
   |--------------------------------------------------------------------------
   | PASIEN
   |--------------------------------------------------------------------------
   */

   $namaPasien =
      $dataSurat['patient_name'] ?? '-';

   $nik =
      $dataSurat['patient_nik'] ?? '-';

   $tanggalLahir =
      $dataSurat['patient_datebirth'] ?? '';

   $tempatLahir =
      $dataSurat['patient_place'] ?? '-';

   $jenisKelamin =
      $dataSurat['patient_gender'] ?? '-';

   $alamat =
      $dataSurat['patient_address'] ?? '-';

   /*
   |--------------------------------------------------------------------------
   | KEMATIAN
   |--------------------------------------------------------------------------
   */

   $tanggalKematian =
      $dataSurat['tanggal_kematian'] ?? '';

   $waktuKematian =
      $dataSurat['waktu_kematian'] ?? '-';

   $ruangan =
      $dataSurat['ruangan'] ?? '-';

   $dokterDPJP =
      $dataSurat['id_doctor']
      ?? '-';

   /*
   |--------------------------------------------------------------------------
   | DOKTER
   |--------------------------------------------------------------------------
   */

   $namaDokter =
      $dataSurat['doctor_name']
      ?? '-';
} else {

   $nomorSurat = '-';

   $tanggalSurat = '';

   $namaPasien = '-';

   $nik = '-';

   $tanggalLahir = '';

   $tempatLahir = '-';

   $jenisKelamin = '-';

   $alamat = '-';

   $tanggalKematian = '';

   $waktuKematian = '-';

   $ruangan = '-';

   $dokterMenyatakan = '-';

   $namaDokter = '-';
}


/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

$tanggalSuratFormatted =
   tanggalIndonesia($tanggalSurat);

$tanggalLahirFormatted =
   tanggalIndonesia($tanggalLahir);

$tanggalKematianFormatted =
   tanggalIndonesia($tanggalKematian);

?>


<!DOCTYPE html>

<html lang="id">

<head>

   <meta charset="UTF-8">

   <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0">

   <title>
      Verifikasi Surat Keterangan Kematian
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

         max-width: 900px;

         margin: auto;

      }


      .brand-icon {

         width: 42px;

         height: 42px;

         border-radius: 10px;

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

         max-width: 900px;

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
         STATUS VALID
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
            auto auto 15px auto;

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

         background:
            #f0fdf4;

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

         background:
            #f8fafc;

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
         DEATH INFO
      ===================================================== */

      .death-card {

         background:
            #fff7f7;

         border:
            1px solid #fecaca;

         border-radius: 10px;

         padding: 15px;

      }


      .death-card .info-label {

         color: #991b1b;

      }


      .death-card .info-value {

         color: #7f1d1d;

      }


      /* =====================================================
         DOCTOR
      ===================================================== */

      .doctor-card {

         margin-top: 12px;

         background:
            #f8fafc;

         border:
            1px solid #e5e7eb;

         border-radius: 10px;

         padding: 15px;

      }


      /* =====================================================
         VERIFIED INFO
      ===================================================== */

      .verified-info {

         margin-top: 25px;

         padding: 15px;

         border-radius: 10px;

         background:
            #f8fafc;

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

      }
   </style>

</head>


<body>


   <!-- =====================================================
        HEADER
   ===================================================== -->

   <header class="top-header">

      <div class="brand">

         <div class="brand-icon">

            <i class="fas fa-file-medical"></i>

         </div>


         <div>

            <div class="brand-title">

               Verifikasi Dokumen Medis

            </div>

            <div class="brand-subtitle">

               Sistem Verifikasi Surat Keterangan Kematian

            </div>

         </div>

      </div>

   </header>



   <!-- =====================================================
        CONTENT
   ===================================================== -->

   <div class="verification-container">

      <div class="verification-card">


         <?php if ($statusValid): ?>


            <!-- =================================================
                 VALID
            ================================================= -->

            <div class="status-valid">

               <div class="status-icon">

                  <i class="fas fa-check"></i>

               </div>


               <div class="status-title">

                  DOKUMEN VALID

               </div>


               <p class="status-description">

                  Surat Keterangan Kematian ini
                  terdaftar dan dapat diverifikasi
                  melalui sistem.

               </p>

            </div>



            <div class="card-content">


               <!-- =================================================
                    NOMOR SURAT
               ================================================= -->

               <div class="nomor-surat">

                  <div class="nomor-label">

                     NOMOR SURAT

                  </div>


                  <div class="nomor-value">

                     <?= htmlspecialchars(
                        $nomorSurat
                     ) ?>

                  </div>

               </div>



               <!-- =================================================
                    IDENTITAS PASIEN
               ================================================= -->

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

                        <?= htmlspecialchars(
                           $namaPasien
                        ) ?>

                     </div>

                  </div>



                  <div class="info-item">

                     <div class="info-label">

                        NIK

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $nik
                        ) ?>

                     </div>

                  </div>



                  <div class="info-item">

                     <div class="info-label">

                        Tempat / Tanggal Lahir

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $tempatLahir
                        ) ?>

                        /

                        <?= htmlspecialchars(
                           $tanggalLahirFormatted
                        ) ?>

                     </div>

                  </div>



                  <div class="info-item">

                     <div class="info-label">

                        Jenis Kelamin

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $jenisKelamin
                        ) ?>

                     </div>

                  </div>



                  <div
                     class="info-item"
                     style="grid-column: 1 / -1;">

                     <div class="info-label">

                        Alamat

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $alamat
                        ) ?>

                     </div>

                  </div>


               </div>



               <!-- =================================================
                    INFORMASI SURAT
               ================================================= -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i class="fas fa-file-medical"></i>

                  Informasi Surat

               </div>


               <div class="info-grid">


                  <div class="info-item">

                     <div class="info-label">

                        Tanggal Surat

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $tanggalSuratFormatted
                        ) ?>

                     </div>

                  </div>



                  <div class="info-item">

                     <div class="info-label">

                        DPJP

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $dokterDPJP
                        ) ?>

                     </div>

                  </div>


               </div>



               <!-- =================================================
                    INFORMASI KEMATIAN
               ================================================= -->

               <div
                  class="section-title"
                  style="margin-top: 25px;">

                  <i
                     class="fas fa-heart-pulse"
                     style="color:#dc3545;"></i>

                  Informasi Kematian

               </div>


               <div class="info-grid">


                  <div
                     class="info-item death-card">

                     <div class="info-label">

                        Tanggal Kematian

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $tanggalKematianFormatted
                        ) ?>

                     </div>

                  </div>



                  <div
                     class="info-item death-card">

                     <div class="info-label">

                        Waktu Kematian

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $waktuKematian
                        ) ?>

                     </div>

                  </div>



                  <div
                     class="info-item death-card">

                     <div class="info-label">

                        Ruangan

                     </div>


                     <div class="info-value">

                        <?= htmlspecialchars(
                           $ruangan
                        ) ?>

                     </div>

                  </div>


               </div>



               <!-- =================================================
                    DOKTER
               ================================================= -->

               <div class="doctor-card">

                  <div class="info-label">

                     Dokter yang Menyatakan

                  </div>


                  <div class="info-value">

                     <i
                        class="fas fa-user-doctor me-1"
                        style="color:#198754;"></i>

                     <?= htmlspecialchars(
                        $namaDokter
                     ) ?>

                  </div>

               </div>



               <!-- =================================================
                    VERIFIED INFO
               ================================================= -->

               <div class="verified-info">

                  <i
                     class="fas fa-shield-halved me-1"
                     style="color:#198754;"></i>

                  <strong>
                     Verifikasi berhasil.
                  </strong>

                  Data yang ditampilkan berasal dari
                  database fasilitas kesehatan dan
                  sesuai dengan dokumen elektronik
                  yang diterbitkan.

               </div>


            </div>


         <?php else: ?>


            <!-- =================================================
                 INVALID
            ================================================= -->

            <div class="status-invalid">

               <div class="status-icon">

                  <i class="fas fa-xmark"></i>

               </div>


               <div class="status-title">

                  DOKUMEN TIDAK DITEMUKAN

               </div>


               <p class="status-description">

                  Surat Keterangan Kematian yang
                  Anda verifikasi tidak ditemukan
                  dalam sistem.

               </p>

            </div>


            <div class="card-content text-center">

               <p class="text-muted mb-0">

                  Pastikan QR Code atau tautan
                  verifikasi berasal dari dokumen
                  resmi fasilitas kesehatan.

               </p>

            </div>


         <?php endif; ?>


      </div>

   </div>



   <!-- =====================================================
        FOOTER
   ===================================================== -->

   <div class="page-footer">

      © <?= date('Y') ?>

      Sistem Informasi Klinik

      <br>

      Halaman ini digunakan untuk verifikasi
      keaslian dokumen elektronik.

   </div>


</body>

</html>