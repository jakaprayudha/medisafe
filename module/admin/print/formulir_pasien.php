<?php
require '../../../database/connect.php';

$id_patient = $_GET['id_patient'] ?? null;

if (!$id_patient) {
   die("ID patient tidak ditemukan");
}

$q = mysqli_query($koneksi, "
  SELECT 
    patient_ktp_file,
    patient_kk_file,
    patient_bpjs_file
  FROM ms_patient
  WHERE id_patient = '$id_patient'
");

$data = mysqli_fetch_assoc($q);

function filePath($file)
{
   if (!$file || $file == 'null') return null;
   return "../../../uploads/patient/" . $file;
}
?>

<!DOCTYPE html>
<html>

<head>
   <title>Dokumen Identitas</title>

   <style>
      @page {
         size: A4;
         margin: 8mm;
         /* 🔥 lebih sempit biar muat */
      }

      body {
         font-family: "Times New Roman", serif;
      }

      /* PAGE */
      .page {
         width: 100%;
         position: relative;
      }

      /* TITLE */
      .title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         margin-bottom: 5px;
      }

      .subtitle {
         text-align: center;
         font-size: 11pt;
         margin-bottom: 10px;
      }

      /* =========================
   KK (FULL WIDTH)
========================= */
      .kk-box {
         border: 1px solid #000;
         padding: 6px;
         margin-bottom: 8px;
      }

      .kk-img {
         width: 100%;
         height: 260px;
         /* 🔥 disesuaikan biar muat */
         border: 1px solid #ccc;
         display: flex;
         align-items: center;
         justify-content: center;
         overflow: hidden;
      }

      .kk-img img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      /* =========================
   BOTTOM GRID (KTP + BPJS)
========================= */
      .bottom-grid {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 8px;
      }

      /* CARD */
      .card {
         border: 1px solid #000;
         padding: 6px;
         text-align: center;
      }

      .img-box {
         width: 100%;
         height: 180px;
         /* 🔥 lebih kecil dari KK */
         border: 1px solid #ccc;
         display: flex;
         align-items: center;
         justify-content: center;
         overflow: hidden;
      }

      .img-box img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      /* LABEL */
      .label {
         margin-top: 5px;
         font-weight: bold;
      }

      /* EMPTY */
      .empty {
         color: red;
         font-size: 11px;
      }

      /* WATERMARK */
      .watermark {
         position: absolute;
         top: 45%;
         left: 50%;
         transform: translate(-50%, -50%);
         font-size: 65pt;
         color: rgba(0, 0, 0, 0.05);
      }



      /* PRINT */
      @media print {
         body {
            margin: 0;
         }
      }
   </style>
</head>

<body onload="window.print()">

   <div class="page">

      <div class="watermark">KLINIK</div>

      <div class="title">DOKUMEN IDENTITAS PASIEN</div>
      <div class="subtitle">Kartu Keluarga, KTP dan BPJS</div>

      <!-- ================= KK ================= -->
      <div class="kk-box">
         <div class="kk-img">
            <?php if ($data['patient_kk_file']) { ?>
               <img src="<?= filePath($data['patient_kk_file']) ?>">
            <?php } else { ?>
               <div class="empty">KK belum upload</div>
            <?php } ?>
         </div>
         <div class="label">KARTU KELUARGA</div>
      </div>

      <!-- ================= BOTTOM ================= -->
      <div class="bottom-grid">

         <!-- KTP -->
         <div class="card">
            <div class="img-box">
               <?php if ($data['patient_ktp_file']) { ?>
                  <img src="<?= filePath($data['patient_ktp_file']) ?>">
               <?php } else { ?>
                  <div class="empty">KTP belum upload</div>
               <?php } ?>
            </div>
            <div class="label">KTP</div>
         </div>

         <!-- BPJS -->
         <div class="card">
            <div class="img-box">
               <?php if ($data['patient_bpjs_file']) { ?>
                  <img src="<?= filePath($data['patient_bpjs_file']) ?>">
               <?php } else { ?>
                  <div class="empty">BPJS belum upload</div>
               <?php } ?>
            </div>
            <div class="label">BPJS</div>
         </div>

      </div>

   </div>

</body>

</html>