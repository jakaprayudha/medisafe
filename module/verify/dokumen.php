<?php

require '../../database/connect.php';

// ===============================
// PARAMETER
// ===============================
$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';

// ===============================
// VALIDASI DATA
// ===============================
$query = mysqli_query($koneksi, "

   SELECT *

   FROM pasien_visit

   WHERE visit_ID='$no'

");

$data = mysqli_fetch_array($query);

// ===============================
// STATUS
// ===============================
$valid = $data ? true : false;

?>

<!DOCTYPE html>
<html lang="id">

<head>

   <meta charset="UTF-8">

   <meta name="viewport"
      content="width=device-width, initial-scale=1.0">

   <title>
      Verifikasi Dokumentasi Perawatan Pasien
   </title>

   <!-- Bootstrap -->
   <link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

   <!-- ICON -->
   <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

   <style>
      body {
         background: #f4f6f9;
         font-family: Arial, sans-serif;
      }

      .verify-card {
         max-width: 700px;
         margin: auto;
         margin-top: 60px;
         border: none;
         border-radius: 20px;
         overflow: hidden;
         box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      }

      .verify-header {
         padding: 30px;
         text-align: center;
         color: white;
      }

      .success {
         background: linear-gradient(135deg, #28a745, #20c997);
      }

      .failed {
         background: linear-gradient(135deg, #dc3545, #ff6b6b);
      }

      .icon-check {
         font-size: 70px;
         margin-bottom: 15px;
      }

      .table td {
         padding: 12px;
         vertical-align: middle;
      }

      .footer-note {
         font-size: 12px;
         color: #777;
         text-align: center;
         margin-top: 20px;
      }
   </style>

</head>

<body>

   <div class="container">

      <div class="card verify-card">

         <!-- ===============================
      HEADER STATUS
      =============================== -->
         <div class="verify-header <?= $valid ? 'success' : 'failed' ?>">

            <?php if ($valid): ?>

               <div class="icon-check">
                  <i class="fa fa-circle-check"></i>
               </div>

               <h2>
                  Dokumentasi Perawatan Terverifikasi
               </h2>

               <p class="mb-0">
                  Dokumen ini valid dan terdaftar di sistem RME
               </p>

            <?php else: ?>

               <div class="icon-check">
                  <i class="fa fa-circle-xmark"></i>
               </div>

               <h2>
                  Data Tidak Valid
               </h2>

               <p class="mb-0">
                  Nomor dokumen tidak ditemukan
               </p>

            <?php endif; ?>

         </div>

         <!-- ===============================
      BODY
      =============================== -->
         <div class="card-body p-4">

            <?php if ($valid): ?>

               <table class="table table-bordered">

                  <tr>

                     <td width="35%">
                        <strong>No. Rawat</strong>
                     </td>

                     <td>
                        <?= $no ?>
                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Nama Pasien</strong>
                     </td>

                     <td>
                        <?= $data['patient_name_pcare'] ?>
                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Tanggal Pengobatan</strong>
                     </td>

                     <td>
                        <?= date('d F Y', strtotime($data['visit_date'])) ?>

                        <?= $data['visit_time'] ?>
                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Dokter</strong>
                     </td>

                     <td>
                        <?= $data['id_doctor'] ?>
                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Poli / Unit</strong>
                     </td>

                     <td>
                        <?= $data['id_poli'] ?>
                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Status Rawat</strong>
                     </td>

                     <td>

                        <?php if ($data['status_rawatinap'] == 1): ?>

                           <span class="badge bg-danger">
                              Rawat Inap
                           </span>

                        <?php else: ?>

                           <span class="badge bg-primary">
                              Rawat Jalan
                           </span>

                        <?php endif; ?>

                     </td>

                  </tr>

                  <tr>

                     <td>
                        <strong>Status Dokumen</strong>
                     </td>

                     <td>

                        <span class="badge bg-success">

                           VERIFIED

                        </span>

                     </td>

                  </tr>

               </table>

               <!-- ALERT -->
               <div class="alert alert-success">

                  Dokumentasi foto perawatan pasien
                  telah diverifikasi secara digital
                  melalui sistem Rekam Medis Elektronik (RME).

               </div>

            <?php else: ?>

               <div class="alert alert-danger">

                  Data verifikasi dokumentasi
                  tidak ditemukan atau QR Code tidak valid.

               </div>

            <?php endif; ?>

            <!-- FOOTER -->
            <div class="footer-note">

               Sistem Rekam Medis Elektronik (RME) MEDISAFE
               <br>
               Dokumen diverifikasi otomatis melalui QR Code.

            </div>

         </div>

      </div>

   </div>

</body>

</html>