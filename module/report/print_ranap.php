<?php
include '../../database/connect.php';

session_start();

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');

$doctor   = $_GET['doctor'] ?? '';
$provider = $_GET['provider'] ?? '';
$status   = $_GET['status'] ?? '';

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   die("Session tidak ditemukan");
}

// =============================
// QUERY
// =============================
$sql = "
SELECT 

   pv.*,
   mp.provider_name,

   -- 🔥 STATUS CPPT
   CASE
      WHEN EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )
      THEN 1
      ELSE 0
   END as status_cppt,

   -- 🔥 STATUS PULANG
   CASE
      WHEN EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
      THEN 1
      ELSE 0
   END as status_pulang

FROM pasien_visit pv

LEFT JOIN ms_provider mp
   ON mp.id_provider = pv.id_provider

WHERE pv.id_customer = '$id_customer'
AND pv.status_rawatinap = 1

AND DATE(pv.visit_date)
BETWEEN '$fromDate' AND '$toDate'
";

// =============================
// FILTER DOKTER
// =============================
if (!empty($doctor)) {

   $sql .= "
      AND pv.id_doctor = '$doctor'
   ";
}

// =============================
// FILTER PROVIDER
// =============================
if (!empty($provider)) {

   $sql .= "
      AND pv.id_provider = '$provider'
   ";
}

// =============================
// FILTER STATUS
// =============================
if ($status == 'Belum') {

   // 🔴 BELUM DILAYANI
   $sql .= "
      AND NOT EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )

      AND NOT EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
} elseif ($status == 'Pemeriksaan') {

   // 🟡 PEMERIKSAAN
   $sql .= "
      AND EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )

      AND NOT EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
} elseif ($status == 'Selesai') {

   // 🟢 SELESAI
   $sql .= "
      AND EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
}

// =============================
// ORDER
// =============================
$sql .= "
ORDER BY 
   pv.visit_date DESC,
   pv.visit_time DESC
";

// =============================
// EXECUTE
// =============================
$query = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html>

<head>

   <title>Laporan Rawat Inap</title>

   <style>
      @page {
         size: A4 landscape;
         margin: 10mm;
      }

      body {
         font-family: Arial, sans-serif;
         font-size: 12px;
      }

      h2 {
         text-align: center;
         margin-bottom: 5px;
      }

      .info {
         margin-bottom: 10px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
      }

      th,
      td {
         border: 1px solid #000;
         padding: 6px;
         text-align: left;
      }

      th {
         background: #eee;
      }

      .text-center {
         text-align: center;
      }

      .badge {
         padding: 4px 8px;
         border-radius: 4px;
         font-size: 11px;
         display: inline-block;
      }

      .success {
         background: #28a745;
         color: white;
      }

      .primary {
         background: #0d6efd;
         color: white;
      }

      .warning {
         background: #ffc107;
         color: black;
      }

      @media print {

         body {
            margin: 0;
         }
      }
   </style>
</head>

<body onload="window.print()">

   <h2>LAPORAN RAWAT INAP</h2>

   <div class="info">

      <strong>Periode:</strong>
      <?= $fromDate ?> s/d <?= $toDate ?>

   </div>

   <table>

      <thead>

         <tr>
            <th>Status</th>
            <th>No BPJS</th>
            <th>Tanggal</th>
            <th>Nama Pasien</th>
            <th>Dokter</th>
            <th>Jenis Bayar</th>
         </tr>

      </thead>

      <tbody>

         <?php while ($row = mysqli_fetch_assoc($query)) { ?>

            <?php

            // =============================
            // STATUS LABEL
            // =============================
            if ($row['status_pulang'] == 1) {

               $statusLabel = 'Selesai';
               $statusClass = 'success';
            } elseif ($row['status_cppt'] == 1) {

               $statusLabel = 'Pemeriksaan';
               $statusClass = 'primary';
            } else {

               $statusLabel = 'Belum Dilayani';
               $statusClass = 'warning';
            }

            ?>

            <tr>

               <!-- STATUS -->
               <td class="text-center">
                  <span class="badge <?= $statusClass ?>">
                     <?= $statusLabel ?>
                  </span>
               </td>

               <!-- NO BPJS -->
               <td>
                  <?= $row['noKartu'] ?? '-' ?>
               </td>

               <!-- TANGGAL -->
               <td>
                  <?= $row['visit_date'] ?? '-' ?>
                  <?= $row['visit_time'] ?? '' ?>
               </td>

               <!-- NAMA -->
               <td>
                  <?= $row['patient_name_pcare'] ?? '-' ?>
               </td>

               <!-- DOKTER -->
               <td>
                  <?= $row['id_doctor'] ?? '-' ?>
               </td>

               <!-- PROVIDER -->
               <td>
                  <?= $row['provider_name'] ?? '-' ?>
               </td>

            </tr>

         <?php } ?>

      </tbody>

   </table>

</body>

</html>