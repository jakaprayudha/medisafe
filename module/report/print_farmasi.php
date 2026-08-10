<?php

require_once '../../database/connect.php';


// ============================================================
// SESSION
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   http_response_code(403);
   exit('Session tidak ditemukan');
}


// ============================================================
// FILTER TANGGAL
// ============================================================

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');


// ============================================================
// VALIDASI TANGGAL
// ============================================================

$fromDateValid = DateTime::createFromFormat('Y-m-d', $fromDate);
$toDateValid   = DateTime::createFromFormat('Y-m-d', $toDate);

if (
   !$fromDateValid ||
   !$toDateValid ||
   $fromDateValid->format('Y-m-d') !== $fromDate ||
   $toDateValid->format('Y-m-d') !== $toDate
) {
   exit('Format tanggal tidak valid');
}

if ($fromDate > $toDate) {
   exit('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
}


// ============================================================
// DATA CLINIC
// ============================================================

$clinicQuery = mysqli_query(
   $koneksi,
   "SELECT * FROM setting_clinic LIMIT 1"
);

$dataclinic = mysqli_fetch_assoc($clinicQuery);


// ============================================================
// QUERY FARMASI
// ============================================================

$sql = "

SELECT

    /* ========================================================
       MASTER OBAT
       ======================================================== */

    p.id_pharmacy,

    p.pharmacy_code,

    p.pharmacy_name_generic,

    p.pharmacy_name_trade,

    p.pharmacy_category,

    p.pharmacy_unit,


    /* ========================================================
       STOCK MIN / MAX
       ======================================================== */

    COALESCE(p.stok_min, 0) AS stok_min,

    COALESCE(p.stok_max, 0) AS stok_max,


    /* ========================================================
       STOCK SAAT INI
       ======================================================== */

    COALESCE(p.pharmacy_stock, 0) AS stok_saat_ini,


    /* ========================================================
       HARGA BELI
       ======================================================== */

    COALESCE(p.pharmacy_price_buy, 0) AS pharmacy_price_buy,


    /* ========================================================
       BARANG MASUK
       ======================================================== */

    COALESCE(

        (

            SELECT SUM(pb.buy_qty)

            FROM pharmacy_buy_detail pb

            WHERE

                (
                    pb.buy_item = p.pharmacy_code
                    OR pb.buy_item = p.pharmacy_name_generic
                    OR pb.buy_item = p.pharmacy_name_trade
                )

                AND pb.buy_status = 1

                AND pb.created_at >= CONCAT(?, ' 00:00:00')

                AND pb.created_at < DATE_ADD(?, INTERVAL 1 DAY)

        ),

        0

    ) AS stok_masuk,


    /* ========================================================
       BARANG KELUAR
       ======================================================== */

    COALESCE(

        (

            SELECT SUM(pd.qty)

            FROM permintaan_pharmacy_details pd

            WHERE

                pd.id_pharmacy = p.id_pharmacy

                AND pd.created_at >= CONCAT(?, ' 00:00:00')

                AND pd.created_at < DATE_ADD(?, INTERVAL 1 DAY)

        ),

        0

    ) AS stok_keluar


FROM ms_pharmacy p


/* ============================================================
   FILTER CUSTOMER
   ============================================================ */

WHERE

    p.id_customer = ?

    AND p.pharmacy_status = 1


/* ============================================================
   ORDER
   ============================================================ */

ORDER BY

    p.pharmacy_name_generic ASC

";


// ============================================================
// PREPARE
// ============================================================

$stmt = $koneksi->prepare($sql);

if (!$stmt) {
   exit('Prepare query gagal: ' . $koneksi->error);
}


// ============================================================
// BIND PARAMETER
// ============================================================

$stmt->bind_param(
   "sssss",
   $fromDate,
   $toDate,
   $fromDate,
   $toDate,
   $id_customer
);


// ============================================================
// EXECUTE
// ============================================================

if (!$stmt->execute()) {
   exit('Execute query gagal: ' . $stmt->error);
}

$result = $stmt->get_result();


// ============================================================
// HELPER FORMAT RUPIAH
// ============================================================

function rupiah($number)
{
   return 'Rp ' . number_format(
      (float) $number,
      0,
      ',',
      '.'
   );
}


// ============================================================
// HELPER FORMAT ANGKA
// ============================================================

function angka($number)
{
   return number_format(
      (float) $number,
      0,
      ',',
      '.'
   );
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

   <meta charset="UTF-8">

   <title>
      Laporan Stok Farmasi
   </title>

   <style>
      @page {
         size: A4 landscape;
         margin: 12mm;
      }

      * {
         box-sizing: border-box;
      }

      body {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 10px;
         color: #222;
         margin: 0;
         padding: 0;
      }


      /* =====================================================
           HEADER
           ===================================================== */

      .header {
         text-align: center;
         margin-bottom: 12px;
      }

      .header h1 {
         margin: 0 0 5px 0;
         font-size: 18px;
         font-weight: bold;
         text-transform: uppercase;
      }

      .header .clinic-name {
         font-size: 13px;
         font-weight: bold;
         margin-bottom: 3px;
      }

      .header .clinic-info {
         font-size: 9px;
         color: #444;
         line-height: 1.5;
      }

      .header .report-title {
         margin-top: 8px;
         font-size: 12px;
         font-weight: bold;
         text-transform: uppercase;
      }

      .header .period {
         margin-top: 4px;
         font-size: 10px;
      }


      /* =====================================================
           LINE
           ===================================================== */

      .line {
         border-top: 2px solid #222;
         margin: 8px 0 12px 0;
      }


      /* =====================================================
           SUMMARY
           ===================================================== */

      .summary {
         margin-bottom: 10px;
      }

      .summary table {
         width: 100%;
         border-collapse: collapse;
      }

      .summary td {
         border: 1px solid #ccc;
         padding: 5px 7px;
      }

      .summary .label {
         font-weight: bold;
         width: 15%;
         background: #f5f5f5;
      }


      /* =====================================================
           TABLE
           ===================================================== */

      table.report {
         width: 100%;
         border-collapse: collapse;
         table-layout: fixed;
      }

      table.report th {
         background: #2563eb;
         color: white;
         border: 1px solid #1e40af;
         padding: 7px 5px;
         text-align: center;
         font-size: 9px;
         font-weight: bold;
         vertical-align: middle;
      }

      table.report td {
         border: 1px solid #cfcfcf;
         padding: 6px 5px;
         font-size: 9px;
         vertical-align: middle;
      }

      table.report tbody tr:nth-child(even) td {
         background: #f8fafc;
      }


      /* =====================================================
           COLUMN WIDTH
           ===================================================== */

      .col-status {
         width: 10%;
      }

      .col-code {
         width: 9%;
      }

      .col-name {
         width: 20%;
      }

      .col-category {
         width: 10%;
      }

      .col-unit {
         width: 6%;
      }

      .col-stock {
         width: 7%;
      }

      .col-value {
         width: 11%;
      }


      /* =====================================================
           ALIGNMENT
           ===================================================== */

      .text-center {
         text-align: center;
      }

      .text-right {
         text-align: right;
      }

      .text-left {
         text-align: left;
      }


      /* =====================================================
           STATUS
           ===================================================== */

      .badge {
         display: inline-block;
         padding: 3px 6px;
         border-radius: 3px;
         font-size: 8px;
         font-weight: bold;
         white-space: nowrap;
      }

      .badge-success {
         background: #d1fae5;
         color: #065f46;
      }

      .badge-danger {
         background: #fee2e2;
         color: #991b1b;
      }

      .badge-warning {
         background: #fef3c7;
         color: #92400e;
      }

      .badge-info {
         background: #dbeafe;
         color: #1e40af;
      }


      /* =====================================================
           TOTAL
           ===================================================== */

      .total-row td {
         background: #e2e8f0 !important;
         font-weight: bold;
         border-top: 2px solid #64748b;
      }


      /* =====================================================
           FOOTER
           ===================================================== */

      .footer {
         margin-top: 15px;
         font-size: 8px;
         color: #555;
      }


      /* =====================================================
           PRINT
           ===================================================== */

      @media print {

         body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
         }

         .no-print {
            display: none !important;
         }

      }


      /* =====================================================
           PRINT BUTTON
           ===================================================== */

      .print-button {
         margin-bottom: 15px;
         padding: 8px 15px;
         border: none;
         background: #2563eb;
         color: white;
         border-radius: 5px;
         cursor: pointer;
      }
   </style>

</head>


<body>


   <!-- ======================================================
         PRINT BUTTON
         ====================================================== -->

   <div class="no-print">

      <button
         class="print-button"
         onclick="window.print()">

         🖨 Cetak Laporan

      </button>

   </div>


   <!-- ======================================================
         HEADER
         ====================================================== -->

   <div class="header">

      <h1>
         Laporan Stok Farmasi
      </h1>


      <?php if (!empty($dataclinic['clinic_name'])): ?>

         <div class="clinic-name">
            <?= htmlspecialchars($dataclinic['clinic_name']) ?>
         </div>

      <?php endif; ?>


      <?php if (!empty($dataclinic['address']) || !empty($dataclinic['phone_number'])): ?>

         <div class="clinic-info">

            <?php if (!empty($dataclinic['address'])): ?>

               Alamat:
               <?= htmlspecialchars($dataclinic['address']) ?>

            <?php endif; ?>


            <?php if (!empty($dataclinic['phone_number'])): ?>

               <?php if (!empty($dataclinic['address'])): ?>
                  &nbsp;|&nbsp;
               <?php endif; ?>

               Telp:
               <?= htmlspecialchars($dataclinic['phone_number']) ?>

            <?php endif; ?>

         </div>

      <?php endif; ?>


      <div class="report-title">
         Laporan Persediaan Obat
      </div>


      <div class="period">

         <strong>Periode:</strong>

         <?= date('d/m/Y', strtotime($fromDate)) ?>

         s/d

         <?= date('d/m/Y', strtotime($toDate)) ?>

      </div>

   </div>


   <div class="line"></div>


   <!-- ======================================================
         SUMMARY
         ====================================================== -->

   <div class="summary">

      <table>

         <tr>

            <td class="label">
               Tanggal Cetak
            </td>

            <td>
               <?= date('d/m/Y H:i:s') ?>
            </td>

            <td class="label">
               Total Item
            </td>

            <td id="totalItem">
               -
            </td>

         </tr>

      </table>

   </div>


   <!-- ======================================================
         REPORT TABLE
         ====================================================== -->

   <table class="report">

      <thead>

         <tr>

            <th class="col-status">
               Status
            </th>

            <th class="col-code">
               Kode Obat
            </th>

            <th class="col-name">
               Nama Obat
            </th>

            <th class="col-category">
               Kategori
            </th>

            <th class="col-unit">
               Satuan
            </th>

            <th class="col-stock">
               Stok Awal
            </th>

            <th class="col-stock">
               Masuk
            </th>

            <th class="col-stock">
               Keluar
            </th>

            <th class="col-stock">
               Stok Akhir
            </th>

            <th class="col-stock">
               Stok Min
            </th>

            <th class="col-stock">
               Stok Max
            </th>

            <th class="col-value">
               Nilai Stok
            </th>

         </tr>

      </thead>


      <tbody>

         <?php

         $totalItem = 0;

         $totalMasuk = 0;

         $totalKeluar = 0;

         $totalStokAkhir = 0;

         $totalNilaiStok = 0;


         while ($row = $result->fetch_assoc()):

            $totalItem++;


            // =================================================
            // STOCK
            // =================================================

            $stokSaatIni =
               (float) ($row['stok_saat_ini'] ?? 0);

            $stokMasuk =
               (float) ($row['stok_masuk'] ?? 0);

            $stokKeluar =
               (float) ($row['stok_keluar'] ?? 0);

            $stokMin =
               (float) ($row['stok_min'] ?? 0);

            $stokMax =
               (float) ($row['stok_max'] ?? 0);

            $hargaBeli =
               (float) ($row['pharmacy_price_buy'] ?? 0);


            // =================================================
            // STOK AKHIR
            // =================================================

            $stokAkhir = $stokSaatIni;


            // =================================================
            // STOK AWAL
            // =================================================

            $stokAwal =
               $stokAkhir
               - $stokMasuk
               + $stokKeluar;


            if ($stokAwal < 0) {
               $stokAwal = 0;
            }


            // =================================================
            // NILAI STOK
            // =================================================

            $nilaiStok =
               $stokAkhir * $hargaBeli;


            // =================================================
            // STATUS
            // =================================================

            if ($stokAkhir <= 0) {

               $statusLabel = 'Habis';

               $statusClass = 'badge-danger';
            } elseif (
               $stokMin > 0 &&
               $stokAkhir < $stokMin
            ) {

               $statusLabel =
                  'Di Bawah Minimum';

               $statusClass =
                  'badge-warning';
            } elseif (
               $stokMax > 0 &&
               $stokAkhir > $stokMax
            ) {

               $statusLabel =
                  'Di Atas Maksimum';

               $statusClass =
                  'badge-info';
            } else {

               $statusLabel =
                  'Normal';

               $statusClass =
                  'badge-success';
            }


            // =================================================
            // TOTAL
            // =================================================

            $totalMasuk += $stokMasuk;

            $totalKeluar += $stokKeluar;

            $totalStokAkhir += $stokAkhir;

            $totalNilaiStok += $nilaiStok;


            // =================================================
            // NAMA OBAT
            // =================================================

            $namaObat =
               $row['pharmacy_name_generic'] ?? '-';

            if (!empty($row['pharmacy_name_trade'])) {

               $namaObat .=
                  ' (' .
                  $row['pharmacy_name_trade'] .
                  ')';
            }

         ?>

            <tr>

               <!-- STATUS -->

               <td class="text-center">

                  <span class="badge <?= $statusClass ?>">

                     <?= htmlspecialchars($statusLabel) ?>

                  </span>

               </td>


               <!-- KODE -->

               <td>
                  <?= htmlspecialchars(
                     $row['pharmacy_code'] ?? '-'
                  ) ?>
               </td>


               <!-- NAMA -->

               <td>

                  <strong>
                     <?= htmlspecialchars($namaObat) ?>
                  </strong>

               </td>


               <!-- KATEGORI -->

               <td>
                  <?= htmlspecialchars(
                     $row['pharmacy_category'] ?? '-'
                  ) ?>
               </td>


               <!-- SATUAN -->

               <td class="text-center">

                  <?= htmlspecialchars(
                     $row['pharmacy_unit'] ?? '-'
                  ) ?>

               </td>


               <!-- STOK AWAL -->

               <td class="text-right">

                  <?= angka($stokAwal) ?>

               </td>


               <!-- MASUK -->

               <td class="text-right">

                  <?php if ($stokMasuk > 0): ?>

                     <span style="color:#15803d;font-weight:bold;">
                        +<?= angka($stokMasuk) ?>
                     </span>

                  <?php else: ?>

                     0

                  <?php endif; ?>

               </td>


               <!-- KELUAR -->

               <td class="text-right">

                  <?php if ($stokKeluar > 0): ?>

                     <span style="color:#dc2626;font-weight:bold;">
                        -<?= angka($stokKeluar) ?>
                     </span>

                  <?php else: ?>

                     0

                  <?php endif; ?>

               </td>


               <!-- STOK AKHIR -->

               <td class="text-right">

                  <strong>
                     <?= angka($stokAkhir) ?>
                  </strong>

               </td>


               <!-- STOK MIN -->

               <td class="text-right">

                  <?= angka($stokMin) ?>

               </td>


               <!-- STOK MAX -->

               <td class="text-right">

                  <?= angka($stokMax) ?>

               </td>


               <!-- NILAI STOK -->

               <td class="text-right">

                  <strong>
                     <?= rupiah($nilaiStok) ?>
                  </strong>

               </td>

            </tr>


         <?php endwhile; ?>


         <!-- ==================================================
                 TOTAL
                 ================================================== -->

         <tr class="total-row">

            <td colspan="5" class="text-right">

               TOTAL

            </td>


            <td>
               -
            </td>


            <td class="text-right">

               <?= angka($totalMasuk) ?>

            </td>


            <td class="text-right">

               <?= angka($totalKeluar) ?>

            </td>


            <td class="text-right">

               <?= angka($totalStokAkhir) ?>

            </td>


            <td>
               -
            </td>


            <td>
               -
            </td>


            <td class="text-right">

               <?= rupiah($totalNilaiStok) ?>

            </td>

         </tr>

      </tbody>

   </table>


   <!-- ======================================================
         FOOTER
         ====================================================== -->

   <div class="footer">

      <strong>Catatan:</strong>

      Laporan ini menampilkan seluruh obat aktif pada master farmasi
      berdasarkan periode transaksi yang dipilih.

      Stok masuk berasal dari transaksi pembelian, sedangkan stok keluar
      berasal dari transaksi permintaan farmasi.

   </div>


   <script>
      document.getElementById('totalItem').innerText =
         '<?= $totalItem ?>';
   </script>


</body>

</html>

<?php

$stmt->close();

?>