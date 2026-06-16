<?php
require '../../database/connect.php';

$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

if (!$start_date || !$end_date) {
   die("Periode belum dipilih.");
}

// Ambil semua visit dulu
$visitQuery = "SELECT 
        v.visit_ID,
        v.visit_date,
        v.visit_time,
        v.id_patient,
        pv.patient_name,
        v.id_doctor,
        dr.doctor_name,
        v.id_poli,
        pl.poli_name
    FROM pasien_visit v
    INNER JOIN ms_patient pv ON v.id_patient = pv.id_patient
    INNER JOIN ms_doctor dr ON dr.id_doctor = v.id_doctor
    INNER JOIN ms_poli pl ON pl.id_poli = v.id_poli
    WHERE v.visit_date BETWEEN '$start_date' AND '$end_date'
    ORDER BY v.visit_date ASC, v.visit_ID ASC";

$visitResult = mysqli_query($koneksi, $visitQuery);

if (!$visitResult) {
   die("Query error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Laporan Transaksi Pasien</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <style>
      @media print {
         .no-print {
            display: none;
         }
      }

      .sub-table {
         margin-top: 10px;
         margin-bottom: 15px;
      }

      .sub-table thead {
         background-color: #f8f9fa;
      }
   </style>
</head>

<body>
   <div class="container mt-4">
      <h4 class="text-center">Laporan Transaksi Pasien</h4>
      <p class="text-center">
         Periode: <?= htmlspecialchars($start_date) ?> s/d <?= htmlspecialchars($end_date) ?>
      </p>

      <?php
      $grandTotal = 0;
      $no = 1;

      while ($visit = mysqli_fetch_assoc($visitResult)):
         $visit_ID = $visit['visit_ID'];

         // Ambil rincian billing untuk visit ini
         $billingQuery = "SELECT 
               billing_item,
               billing_qty,
               billing_price,
               billing_discount,
               (billing_qty * billing_price - billing_discount) AS total
            FROM pasien_billing
            WHERE id_visit = '$visit_ID'";
         $billingResult = mysqli_query($koneksi, $billingQuery);

         // Hitung subtotal per visit
         $subtotal = 0;
         $billingRows = [];
         while ($b = mysqli_fetch_assoc($billingResult)) {
            $subtotal += $b['total'];
            $billingRows[] = $b;
         }
         $grandTotal += $subtotal;
      ?>
         <!-- Header Visit -->
         <div class="card mb-3">
            <div class="card-header bg-light">
               <strong><?= $no++ ?>. Visit ID: <?= $visit['visit_ID'] ?> | <?= $visit['visit_date'] ?> <?= $visit['visit_time'] ?></strong><br>
               Pasien: <?= $visit['patient_name'] ?> | Dokter: <?= $visit['doctor_name'] ?> | Poli: <?= $visit['poli_name'] ?>
            </div>
            <div class="card-body p-2">
               <div class="table-responsive">
                  <table class="table table-sm table-bordered sub-table mb-0">
                     <thead>
                        <tr>
                           <th>Item</th>
                           <th>Qty</th>
                           <th>Harga</th>
                           <th>Diskon</th>
                           <th>Total</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($billingRows as $row): ?>
                           <tr>
                              <td><?= $row['billing_item'] ?></td>
                              <td><?= $row['billing_qty'] ?></td>
                              <td><?= number_format($row['billing_price'], 0, ',', '.') ?></td>
                              <td><?= number_format($row['billing_discount'], 0, ',', '.') ?></td>
                              <td><?= number_format($row['total'], 0, ',', '.') ?></td>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4" class="text-end">Subtotal</th>
                           <th><?= number_format($subtotal, 0, ',', '.') ?></th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      <?php endwhile; ?>

      <div class="alert alert-primary text-end">
         <strong>Grand Total: <?= number_format($grandTotal, 0, ',', '.') ?></strong>
      </div>

      <button class="btn btn-secondary no-print" onclick="window.print()">
         <i class="fas fa-print"></i> Cetak
      </button>
   </div>
</body>

</html>