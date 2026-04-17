<?php
include '../../database/connect.php';

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');
$doctor   = $_GET['doctor'] ?? '';
$provider = $_GET['provider'] ?? '';
session_start();
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   die("Session tidak ditemukan");
}
$query = mysqli_query($koneksi, "
  SELECT *
  FROM pasien_visit 
  INNER JOIN ms_provider 
    ON pasien_visit.id_provider = ms_provider.id_provider
  WHERE pasien_visit.id_customer = '$id_customer'
    AND DATE(pasien_visit.visit_date) BETWEEN '$fromDate' AND '$toDate'
    " . ($doctor ? " AND pasien_visit.id_doctor = '$doctor'" : "") . "
    " . ($provider ? " AND pasien_visit.id_provider = '$provider'" : "") . "
  ORDER BY pasien_visit.visit_date DESC
");

?>
<!DOCTYPE html>
<html>

<head>
   <title>Cetak Appointment</title>

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
         padding: 3px 6px;
         border-radius: 4px;
         font-size: 11px;
      }

      .success {
         background: #28a745;
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

   <h2>LAPORAN APPOINTMENT</h2>

   <div class="info">
      Periode: <?= $fromDate ?> s/d <?= $toDate ?>
   </div>

   <table>
      <thead>
         <tr>
            <th>Status</th>
            <th>No BPJS</th>
            <th>Tanggal</th>
            <th>Nama Pasien</th>
            <th>Dokter</th>
            <th>Poli</th>
            <th>Jenis Bayar</th>
         </tr>
      </thead>
      <tbody>

         <?php while ($row = mysqli_fetch_assoc($query)) { ?>
            <tr>
               <td class="text-center">
                  <?php if ($row['visit_status'] == 4) { ?>
                     <span class="badge success">Selesai</span>
                  <?php } else { ?>
                     <span class="badge warning">Belum</span>
                  <?php } ?>
               </td>
               <td><?= $row['noKartu'] ?></td>
               <td><?= $row['visit_date'] ?> <?= $row['visit_time'] ?></td>
               <td><?= $row['patient_name_pcare'] ?></td>
               <td><?= $row['id_doctor'] ?></td>
               <td><?= $row['id_poli'] ?></td>
               <td><?= $row['provider_name'] ?? '-' ?></td>
            </tr>
         <?php } ?>

      </tbody>
   </table>

</body>

</html>