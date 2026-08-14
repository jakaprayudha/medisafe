<?php
/*
|--------------------------------------------------------------------------
| PUBLIC PAGE
|--------------------------------------------------------------------------
*/

$is_public_page = true;
/*
|--------------------------------------------------------------------------
| VERIFIKASI SURAT
|--------------------------------------------------------------------------
*/

require_once '../../database/connect.php';


/*
|--------------------------------------------------------------------------
| AMBIL ID HASH
|--------------------------------------------------------------------------
*/

$idHash = $_GET['id'] ?? '';

if (empty($idHash)) {
   $statusValid = false;
} else {

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

        FROM surat_sehat ss

        INNER JOIN pasien_visit pv
            ON pv.id_visit = ss.id_visit

        INNER JOIN ms_patient mp
            ON mp.id_patient = ss.id_patient

        WHERE MD5(ss.id) = ?

        LIMIT 1
    ");

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


/*
|--------------------------------------------------------------------------
| DATA DEFAULT
|--------------------------------------------------------------------------
*/

if ($statusValid) {

   $nomorSurat = $dataSurat['nomor_surat'] ?? '-';

   $tanggalSurat = $dataSurat['tanggal_surat'] ?? '-';

   $namaPasien = $dataSurat['patient_name'] ?? '-';

   $nik = $dataSurat['patient_nik'] ?? '-';

   $tanggalLahir = $dataSurat['patient_datebirth'] ?? '-';

   $tempatLahir = $dataSurat['patient_place'] ?? '-';

   $jenisKelamin = $dataSurat['patient_gender'] ?? '-';

   $alamat = $dataSurat['patient_address'] ?? '-';

   $keperluan = $dataSurat['keperluan'] ?? '-';

   $tekananDarah = $dataSurat['tekanan_darah'] ?? '-';

   $nadi = $dataSurat['nadi'] ?? '-';

   $beratBadan = $dataSurat['berat_badan'] ?? '-';

   $tinggiBadan = $dataSurat['tinggi_badan'] ?? '-';
} else {

   $nomorSurat = '-';

   $tanggalSurat = '-';

   $namaPasien = '-';

   $nik = '-';

   $tanggalLahir = '-';

   $tempatLahir = '-';

   $jenisKelamin = '-';

   $alamat = '-';

   $keperluan = '-';

   $tekananDarah = '-';

   $nadi = '-';

   $beratBadan = '-';

   $tinggiBadan = '-';
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
      Verifikasi Surat
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
           STATUS
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

         margin: auto;

         border-radius: 50%;

         background: rgba(255,
               255,
               255,
               0.18);

         display: flex;

         align-items: center;

         justify-content: center;

         font-size: 34px;

         margin-bottom: 15px;

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

         padding: 13px 15px;

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
           NUMBER
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
           TABLE
        ===================================================== */

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

      }


      /* =====================================================
           VERIFIED FOOTER
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


         .medical-table {

            font-size: 12px;

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

            <i class="fas fa-shield-check"></i>

         </div>


         <div>

            <div class="brand-title">

               Verifikasi Dokumen Medis

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
                 VALID
            =================================================== -->

            <div class="status-valid">

               <div class="status-icon">

                  <i class="fas fa-check"></i>

               </div>


               <div class="status-title">

                  DOKUMEN VALID

               </div>


               <p class="status-description">

                  Dokumen ini terdaftar dan
                  telah diverifikasi oleh sistem.

               </p>

            </div>



            <div class="card-content">


               <!-- NOMOR SURAT -->

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



               <!-- IDENTITAS -->

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
                           $tanggalLahir
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



               <!-- PEMERIKSAAN -->

               <div
                  class="section-title"
                  style="margin-top: 28px;">

                  <i class="fas fa-stethoscope"></i>

                  Hasil Pemeriksaan

               </div>


               <table class="medical-table">

                  <thead>

                     <tr>

                        <th>
                           Pemeriksaan
                        </th>

                        <th>
                           Hasil
                        </th>

                     </tr>

                  </thead>


                  <tbody>


                     <tr>

                        <td>
                           Tekanan Darah
                        </td>

                        <td>

                           <?= htmlspecialchars(
                              $tekananDarah
                           ) ?>

                           mmHg

                        </td>

                     </tr>


                     <tr>

                        <td>
                           Nadi
                        </td>

                        <td>

                           <?= htmlspecialchars(
                              $nadi
                           ) ?>

                           x/menit

                        </td>

                     </tr>


                     <tr>

                        <td>
                           Berat Badan
                        </td>

                        <td>

                           <?= htmlspecialchars(
                              $beratBadan
                           ) ?>

                           Kg

                        </td>

                     </tr>


                     <tr>

                        <td>
                           Tinggi Badan
                        </td>

                        <td>

                           <?= htmlspecialchars(
                              $tinggiBadan
                           ) ?>

                           cm

                        </td>

                     </tr>


                  </tbody>

               </table>



               <!-- KEPERLUAN -->

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
                           $tanggalSurat
                        ) ?>

                     </div>

                  </div>


                  <div class="info-item">

                     <div class="info-label">
                        Keperluan
                     </div>

                     <div class="info-value">

                        <?= htmlspecialchars(
                           $keperluan
                        ) ?>

                     </div>

                  </div>


               </div>



               <!-- VERIFICATION INFO -->

               <div class="verified-info">

                  <i class="fas fa-shield-halved me-1"></i>

                  <strong>
                     Verifikasi berhasil.
                  </strong>

                  Data yang ditampilkan berasal dari
                  database resmi fasilitas kesehatan
                  dan sesuai dengan dokumen elektronik
                  yang diterbitkan.

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

                  Nomor atau identitas dokumen yang
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