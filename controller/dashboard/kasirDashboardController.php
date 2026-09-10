<?php

/**
 * ============================================================
 * DASHBOARD KASIR
 * ============================================================
 * Endpoint:
 * controller/dashboard/kasirDashboardController.php?action=dashboard
 *
 * Scope:
 * - Global transaksi fasilitas berdasarkan id_customer
 * - Periode tanggal
 * - Tidak mengikat ke dokter/perawat
 *
 * Sumber utama:
 * 1. invoice (jika tersedia dan mempunyai kolom yang diperlukan)
 * 2. pasien_billing sebagai fallback transaksi/tagihan
 * 3. pasien_visit untuk nama pasien (patient_name_pcare), antrean/status pembayaran
 * 4. ms_faskes_payment untuk distribusi pembayaran jika dapat
 *    direlasikan dengan invoice
 *
 * Controller dibuat defensif terhadap variasi schema.
 * ============================================================
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../database/connect.php';

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
   echo json_encode([
      'status'  => false,
      'message' => 'Koneksi database tidak tersedia.'
   ]);
   exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

/* ============================================================
   HELPER RESPONSE
============================================================ */

function responseJson($status, $message, $data = [])
{
   echo json_encode(
      array_merge([
         'status'  => $status,
         'message' => $message
      ], $data),
      JSON_UNESCAPED_UNICODE
   );
   exit;
}

/* ============================================================
   HELPER SCHEMA
============================================================ */

function tableExists($table)
{
   global $koneksi;

   $table = $koneksi->real_escape_string($table);

   $q = $koneksi->query("
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = '{$table}'
        LIMIT 1
    ");

   return $q && $q->num_rows > 0;
}

function columnExists($table, $column)
{
   global $koneksi;

   $table  = $koneksi->real_escape_string($table);
   $column = $koneksi->real_escape_string($column);

   $q = $koneksi->query("
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = '{$table}'
          AND column_name = '{$column}'
        LIMIT 1
    ");

   return $q && $q->num_rows > 0;
}

function firstExistingColumn($table, $columns)
{
   foreach ($columns as $column) {
      if (columnExists($table, $column)) {
         return $column;
      }
   }

   return null;
}

function qi($name)
{
   return '`' . str_replace('`', '``', $name) . '`';
}

function moneyValue($value)
{
   return (float) ($value ?? 0);
}

function moneyFormat($value)
{
   return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function percentage($value, $total)
{
   if ((float)$total <= 0) {
      return 0;
   }

   return round(((float)$value / (float)$total) * 100, 1);
}

/**
 * Execute query and return rows.
 * Tidak melempar fatal error supaya dashboard tetap dapat response
 * jika salah satu tabel opsional tidak tersedia.
 */
function queryRows($sql, $types = '', $params = [])
{
   global $koneksi;

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      return [];
   }

   if ($types !== '' && count($params) > 0) {
      $refs = [];
      $refs[] = $types;

      foreach ($params as $key => $value) {
         $refs[] = &$params[$key];
      }

      call_user_func_array([$stmt, 'bind_param'], $refs);
   }

   if (!$stmt->execute()) {
      $stmt->close();
      return [];
   }

   $result = $stmt->get_result();

   if (!$result) {
      $stmt->close();
      return [];
   }

   $rows = $result->fetch_all(MYSQLI_ASSOC);

   $stmt->close();

   return $rows;
}

function queryOne($sql, $types = '', $params = [])
{
   $rows = queryRows($sql, $types, $params);
   return $rows[0] ?? null;
}

function queryScalar($sql, $types = '', $params = [], $default = 0)
{
   $row = queryOne($sql, $types, $params);

   if (!$row) {
      return $default;
   }

   $value = array_values($row)[0] ?? $default;

   return is_numeric($value) ? $value : $default;
}

/* ============================================================
   PERIOD
============================================================ */

function resolvePeriod()
{
   $today = date('Y-m-d');

   $periode = strtolower(trim($_GET['periode'] ?? 'today'));

   $start = $today;
   $end   = $today;

   switch ($periode) {

      case 'week':
      case '7days':
         $start = date('Y-m-d', strtotime('monday this week'));
         $end   = $today;
         $periode = 'week';
         break;

      case 'month':
      case 'thismonth':
         $start = date('Y-m-01');
         $end   = $today;
         $periode = 'month';
         break;

      case 'custom':
         $start = trim($_GET['tanggal_mulai'] ?? '');
         $end   = trim($_GET['tanggal_selesai'] ?? '');

         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
            $start = $today;
         }

         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end = $today;
         }

         if ($start > $end) {
            $tmp   = $start;
            $start = $end;
            $end   = $tmp;
         }
         break;

      case 'today':
      default:
         $periode = 'today';
         $start = $today;
         $end   = $today;
         break;
   }

   return [
      'type'  => $periode,
      'start' => $start,
      'end'   => $end
   ];
}

/* ============================================================
   INVOICE SCHEMA
============================================================ */

function getInvoiceConfig()
{
   if (!tableExists('invoice')) {
      return null;
   }

   return [
      'id' => firstExistingColumn('invoice', [
         'id_invoice',
         'invoice_id',
         'id'
      ]),

      'number' => firstExistingColumn('invoice', [
         'invoice_number',
         'nomor_invoice',
         'no_invoice',
         'order_number',
         'billing_number'
      ]),

      'date' => firstExistingColumn('invoice', [
         'invoice_date',
         'tanggal_invoice',
         'tanggal',
         'created_at'
      ]),

      'customer' => firstExistingColumn('invoice', [
         'id_customer',
         'customer_id'
      ]),

      'visit' => firstExistingColumn('invoice', [
         'id_visit',
         'visit_ID',
         'visit_id'
      ]),

      'patient' => firstExistingColumn('invoice', [
         'id_patient',
         'patient_id'
      ]),

      'total' => firstExistingColumn('invoice', [
         'grand_total',
         'total_amount',
         'total',
         'invoice_total',
         'amount',
         'nominal',
         'total_tagihan'
      ]),

      'paid' => firstExistingColumn('invoice', [
         'paid_amount',
         'payment_amount',
         'amount_payment',
         'total_paid',
         'jumlah_bayar'
      ]),

      'status' => firstExistingColumn('invoice', [
         'payment_status',
         'status_payment',
         'status_bayar',
         'invoice_status',
         'status'
      ]),

      'method' => firstExistingColumn('invoice', [
         'payment_method',
         'metode_bayar',
         'metode_pembayaran'
      ])
   ];
}

/* ============================================================
   STATUS HELPERS
============================================================ */

function isPaidStatus($status)
{
   $s = strtolower(trim((string)$status));

   return in_array($s, [
      '1',
      '2',
      'paid',
      'lunas',
      'dibayar',
      'terbayar',
      'success',
      'berhasil',
      'completed',
      'selesai'
   ], true);
}

function isPendingStatus($status)
{
   $s = strtolower(trim((string)$status));

   return $s === ''
      || in_array($s, [
         '0',
         'pending',
         'menunggu',
         'belum',
         'unpaid',
         'belum_bayar',
         'waiting'
      ], true);
}

/* ============================================================
   DASHBOARD
============================================================ */

function dashboard($id_customer, $period)
{
   global $koneksi;

   $start = $period['start'];
   $end   = $period['end'];

   $invoiceConfig = getInvoiceConfig();

   $totalTransactions = 0;
   $paidTransactions  = 0;
   $pendingPayments   = 0;
   $failedTransactions = 0;
   $totalBilled       = 0;
   $totalPaid         = 0;

   $transactions = [];
   $revenueChart = [];
   $paymentMethods = [];

   /* ========================================================
       1. INVOICE SEBAGAI SUMBER UTAMA
    ======================================================== */

   if ($invoiceConfig && $invoiceConfig['date']) {

      $dateCol = qi($invoiceConfig['date']);

      $where = "DATE(i.{$dateCol}) BETWEEN ? AND ?";
      $types = "ss";
      $params = [$start, $end];

      if ($invoiceConfig['customer']) {
         $where .= " AND i." . qi($invoiceConfig['customer']) . " = ?";
         $types .= "s";
         $params[] = (string)$id_customer;
      }

      $totalTransactions = (int) queryScalar(
         "SELECT COUNT(*) FROM invoice i WHERE {$where}",
         $types,
         $params,
         0
      );

      /* Total tagihan */
      if ($invoiceConfig['total']) {
         $totalBilled = (float) queryScalar(
            "SELECT COALESCE(SUM(i." . qi($invoiceConfig['total']) . "),0)
                 FROM invoice i
                 WHERE {$where}",
            $types,
            $params,
            0
         );
      }

      /* Total pembayaran */
      if ($invoiceConfig['paid']) {
         $totalPaid = (float) queryScalar(
            "SELECT COALESCE(SUM(i." . qi($invoiceConfig['paid']) . "),0)
                 FROM invoice i
                 WHERE {$where}",
            $types,
            $params,
            0
         );
      }

      /* Status pembayaran */
      if ($invoiceConfig['status']) {

         $statusCol = qi($invoiceConfig['status']);

         $statusRows = queryRows(
            "SELECT i.{$statusCol} AS payment_status, COUNT(*) AS total
                 FROM invoice i
                 WHERE {$where}
                 GROUP BY i.{$statusCol}",
            $types,
            $params
         );

         foreach ($statusRows as $row) {

            $status = $row['payment_status'];
            $count  = (int)$row['total'];

            if (isPaidStatus($status)) {
               $paidTransactions += $count;
            } elseif (isPendingStatus($status)) {
               $pendingPayments += $count;
            } else {
               $failedTransactions += $count;
            }
         }
      } else {
         /*
             * Jika tidak ada kolom status, gunakan nilai pembayaran
             * sebagai indikator sederhana.
             */
         $paidTransactions = $totalPaid > 0 ? $totalTransactions : 0;
         $pendingPayments  = max(0, $totalTransactions - $paidTransactions);
      }

      /* ----------------------------------------------------
           Transaksi terbaru
        ---------------------------------------------------- */

      $select = [];

      $select[] = $invoiceConfig['id']
         ? "i." . qi($invoiceConfig['id']) . " AS id"
         : "0 AS id";

      $select[] = $invoiceConfig['number']
         ? "i." . qi($invoiceConfig['number']) . " AS nomor_transaksi"
         : "'' AS nomor_transaksi";

      $select[] = "i.{$dateCol} AS tanggal";

      $select[] = $invoiceConfig['total']
         ? "i." . qi($invoiceConfig['total']) . " AS total_tagihan"
         : "0 AS total_tagihan";

      $select[] = $invoiceConfig['paid']
         ? "i." . qi($invoiceConfig['paid']) . " AS total_bayar"
         : "0 AS total_bayar";

      $select[] = $invoiceConfig['status']
         ? "i." . qi($invoiceConfig['status']) . " AS payment_status"
         : "'' AS payment_status";

      $select[] = $invoiceConfig['method']
         ? "i." . qi($invoiceConfig['method']) . " AS payment_method"
         : "'' AS payment_method";

      if ($invoiceConfig['visit'] && tableExists('pasien_visit')) {
         $select[] = "pv.visit_ID AS visit_number";
      } else {
         $select[] = "'' AS visit_number";
      }

      $join = '';

      if ($invoiceConfig['visit'] && tableExists('pasien_visit')) {
         $join .= "
                LEFT JOIN pasien_visit pv
                    ON pv.visit_ID = i." . qi($invoiceConfig['visit']);
      }

      if ($invoiceConfig['patient'] && tableExists('ms_patient')) {
         $select[] = "pv.patient_name_pcare";
         $select[] = "mp.nomor_rm";

         $join .= "
                LEFT JOIN ms_patient mp
                    ON mp.id_patient = i." . qi($invoiceConfig['patient']);
      } elseif ($invoiceConfig['visit'] && tableExists('pasien_visit') && tableExists('ms_patient')) {
         $select[] = "pv.patient_name_pcare";
         $select[] = "mp.nomor_rm";

         $join .= "
                LEFT JOIN ms_patient mp
                    ON CAST(mp.id_patient AS CHAR) = pv.id_patient";
      } else {
         $select[] = "'' AS patient_name_pcare";
         $select[] = "'' AS nomor_rm";
      }

      $rows = queryRows(
         "SELECT " . implode(", ", $select) . "
             FROM invoice i
             {$join}
             WHERE {$where}
             ORDER BY i.{$dateCol} DESC
             LIMIT 10",
         $types,
         $params
      );

      foreach ($rows as $row) {

         $statusText = 'Menunggu';

         if (isPaidStatus($row['payment_status'])) {
            $statusText = 'Lunas';
         } elseif (!isPendingStatus($row['payment_status'])) {
            $statusText = 'Gagal';
         }

         $transactions[] = [
            'id'              => (int)$row['id'],
            'nomor_transaksi' => trim((string)($row['nomor_transaksi'] ?? $row['visit_number'] ?? '')) ?: '-',
            'nama_pasien'     => trim((string)($row['patient_name_pcare'] ?? '')) ?: '-',
            'nomor_rm'        => trim((string)($row['nomor_rm'] ?? '')) ?: '-',
            'tanggal'         => $row['tanggal'],
            'total_tagihan'   => moneyValue($row['total_tagihan']),
            'total_bayar'     => moneyValue($row['total_bayar']),
            'payment_method'  => trim((string)$row['payment_method']),
            'payment_status'  => $statusText,
            'status_class'    => strtolower($statusText) === 'lunas'
               ? 'status-paid'
               : (strtolower($statusText) === 'gagal'
                  ? 'status-failed'
                  : 'status-pending')
         ];
      }

      /* ----------------------------------------------------
           Revenue chart
        ---------------------------------------------------- */

      if ($invoiceConfig['total']) {

         $chartRows = queryRows(
            "SELECT
                    DATE(i.{$dateCol}) AS tanggal,
                    COALESCE(SUM(i." . qi($invoiceConfig['total']) . "),0) AS total
                 FROM invoice i
                 WHERE {$where}
                 GROUP BY DATE(i.{$dateCol})
                 ORDER BY DATE(i.{$dateCol}) ASC",
            $types,
            $params
         );

         foreach ($chartRows as $row) {
            $revenueChart[] = [
               'date'  => $row['tanggal'],
               'total' => moneyValue($row['total'])
            ];
         }
      }

      /* ----------------------------------------------------
           Payment method
        ---------------------------------------------------- */

      if ($invoiceConfig['method']) {

         $methodRows = queryRows(
            "SELECT
                    COALESCE(NULLIF(TRIM(i." . qi($invoiceConfig['method']) . "),''),'Tidak Diketahui') AS metode,
                    COUNT(*) AS total
                 FROM invoice i
                 WHERE {$where}
                 GROUP BY COALESCE(NULLIF(TRIM(i." . qi($invoiceConfig['method']) . "),''),'Tidak Diketahui')
                 ORDER BY total DESC",
            $types,
            $params
         );

         foreach ($methodRows as $row) {
            $paymentMethods[] = [
               'label'     => $row['metode'],
               'total'     => (int)$row['total'],
               'percentage' => percentage($row['total'], $totalTransactions)
            ];
         }
      }
   }

   /* ========================================================
       2. FALLBACK / DATA BILLING
       Digunakan jika invoice belum tersedia / tidak lengkap.
    ======================================================== */

   if (tableExists('pasien_billing')) {

      $billingCustomer = columnExists('pasien_billing', 'id_customer')
         ? 'id_customer'
         : null;

      $billingDate = firstExistingColumn('pasien_billing', [
         'created_at',
         'tanggal'
      ]);

      $billingId = firstExistingColumn('pasien_billing', [
         'id_billing',
         'id'
      ]);

      $billingVisit = columnExists('pasien_billing', 'id_visit')
         ? 'id_visit'
         : null;

      if ($billingDate) {

         $whereBilling = "DATE(pb." . qi($billingDate) . ") BETWEEN ? AND ?";
         $typesBilling = "ss";
         $paramsBilling = [$start, $end];

         if ($billingCustomer) {
            $whereBilling .= " AND pb." . qi($billingCustomer) . " = ?";
            $typesBilling .= "s";
            $paramsBilling[] = (string)$id_customer;
         }

         /*
             * Bila invoice tidak menghasilkan transaksi,
             * gunakan billing sebagai sumber transaksi.
             */
         if ($totalTransactions === 0) {

            if ($billingVisit) {
               $totalTransactions = (int) queryScalar(
                  "SELECT COUNT(DISTINCT pb." . qi($billingVisit) . ")
                         FROM pasien_billing pb
                         WHERE {$whereBilling}",
                  $typesBilling,
                  $paramsBilling,
                  0
               );
            } else {
               $totalTransactions = (int) queryScalar(
                  "SELECT COUNT(*)
                         FROM pasien_billing pb
                         WHERE {$whereBilling}",
                  $typesBilling,
                  $paramsBilling,
                  0
               );
            }
         }

         /*
             * Total tagihan billing:
             * price x qty - discount
             */
         if (columnExists('pasien_billing', 'billing_price')) {

            $qtyExpr = columnExists('pasien_billing', 'billing_qty')
               ? "COALESCE(pb.billing_qty,1)"
               : "1";

            $discountExpr = columnExists('pasien_billing', 'billing_discount')
               ? "COALESCE(pb.billing_discount,0)"
               : "0";

            $billingTotal = (float) queryScalar(
               "SELECT COALESCE(SUM(
                        (COALESCE(pb.billing_price,0) * {$qtyExpr}) - {$discountExpr}
                    ),0)
                    FROM pasien_billing pb
                    WHERE {$whereBilling}",
               $typesBilling,
               $paramsBilling,
               0
            );

            if ($totalBilled <= 0) {
               $totalBilled = $billingTotal;
            }
         }

         /*
             * Billing status 1 = aktif/ditagihkan, bukan otomatis lunas.
             * Karena schema tidak menyimpan status pembayaran eksplisit,
             * pembayaran tetap mengacu invoice/payment/visit jika ada.
             */
         if (empty($transactions)) {

            $select = [];

            $select[] = $billingId
               ? "pb." . qi($billingId) . " AS id"
               : "0 AS id";

            $select[] = $billingVisit
               ? "pb." . qi($billingVisit) . " AS visit_number"
               : "'' AS visit_number";

            $select[] = "pb.{$billingDate} AS tanggal";

            $select[] = columnExists('pasien_billing', 'billing_number')
               ? "pb.billing_number AS nomor_transaksi"
               : "'' AS nomor_transaksi";

            $select[] = columnExists('pasien_billing', 'billing_price')
               ? "pb.billing_price AS billing_price"
               : "0 AS billing_price";

            $select[] = columnExists('pasien_billing', 'billing_qty')
               ? "pb.billing_qty AS billing_qty"
               : "1 AS billing_qty";

            $select[] = columnExists('pasien_billing', 'billing_discount')
               ? "pb.billing_discount AS billing_discount"
               : "0 AS billing_discount";

            $join = '';

            if ($billingVisit && tableExists('pasien_visit')) {
               $select[] = "pv.id_patient";
               $select[] = "pv.visit_ID";
               $join .= "
                        LEFT JOIN pasien_visit pv
                            ON pv.visit_ID = pb.id_visit
                           AND pv.id_customer = ?
                    ";

               /*
                     * Tambahkan customer visit ke params hanya untuk query
                     * detail billing.
                     */
               $detailTypes = $typesBilling . "s";
               $detailParams = $paramsBilling;
               $detailParams[] = (string)$id_customer;
            } else {
               $select[] = "'' AS id_patient";
               $select[] = "'' AS visit_ID";
               $detailTypes = $typesBilling;
               $detailParams = $paramsBilling;
            }

            if (tableExists('ms_patient') && $billingVisit && tableExists('pasien_visit')) {
               $select[] = "pv.patient_name_pcare";
               $select[] = "mp.nomor_rm";

               $join .= "
                        LEFT JOIN ms_patient mp
                            ON CAST(mp.id_patient AS CHAR) = CAST(pv.id_patient AS CHAR)
                    ";
            } else {
               $select[] = "'' AS patient_name";
               $select[] = "'' AS nomor_rm";
            }

            $detailRows = queryRows(
               "SELECT " . implode(", ", $select) . "
                     FROM pasien_billing pb
                     {$join}
                     WHERE {$whereBilling}
                     ORDER BY pb.{$billingDate} DESC
                     LIMIT 10",
               $detailTypes,
               $detailParams
            );

            foreach ($detailRows as $row) {

               $lineTotal =
                  ((float)$row['billing_price'] * (int)$row['billing_qty'])
                  - (float)$row['billing_discount'];

               $transactions[] = [
                  'id'              => (int)$row['id'],
                  'nomor_transaksi' => trim((string)$row['nomor_transaksi']) ?: '-',
                  'nama_pasien'     => trim((string)($row['patient_name_pcare'] ?? '')) ?: '-',
                  'nomor_rm'        => trim((string)$row['nomor_rm']) ?: '-',
                  'tanggal'         => $row['tanggal'],
                  'total_tagihan'   => max(0, $lineTotal),
                  'total_bayar'     => 0,
                  'payment_method'  => '',
                  'payment_status'  => 'Menunggu',
                  'status_class'    => 'status-pending'
               ];
            }
         }

         /* Revenue chart fallback */
         if (empty($revenueChart) && columnExists('pasien_billing', 'billing_price')) {

            $qtyExpr = columnExists('pasien_billing', 'billing_qty')
               ? "COALESCE(pb.billing_qty,1)"
               : "1";

            $discountExpr = columnExists('pasien_billing', 'billing_discount')
               ? "COALESCE(pb.billing_discount,0)"
               : "0";

            $chartRows = queryRows(
               "SELECT
                        DATE(pb." . qi($billingDate) . ") AS tanggal,
                        COALESCE(SUM(
                            (COALESCE(pb.billing_price,0) * {$qtyExpr}) - {$discountExpr}
                        ),0) AS total
                     FROM pasien_billing pb
                     WHERE {$whereBilling}
                     GROUP BY DATE(pb." . qi($billingDate) . ")
                     ORDER BY DATE(pb." . qi($billingDate) . ") ASC",
               $typesBilling,
               $paramsBilling
            );

            foreach ($chartRows as $row) {
               $revenueChart[] = [
                  'date'  => $row['tanggal'],
                  'total' => moneyValue($row['total'])
               ];
            }
         }
      }
   }

   /* ========================================================
       3. STATUS PEMBAYARAN DARI PASIEN_VISIT
       Dipakai untuk antrean pembayaran.
    ======================================================== */

   $queue = [];

   if (tableExists('pasien_visit')) {

      $visitDate = columnExists('pasien_visit', 'visit_date')
         ? 'visit_date'
         : null;

      if ($visitDate) {

         $visitWhere = "
                pv.{$visitDate} BETWEEN ? AND ?
                AND pv.id_customer = ?
            ";

         $visitTypes = "ssi";
         $visitParams = [
            $start,
            $end,
            (int)$id_customer
         ];

         $statusBayarExists = columnExists('pasien_visit', 'status_bayar');

         if ($statusBayarExists) {
            /*
                 * Konvensi yang digunakan sistem:
                 * 0 = belum bayar
                 * selain 0 = sudah/berproses.
                 *
                 * Tidak menganggap semua nilai non-zero sebagai lunas
                 * untuk transaksi invoice; ini hanya antrean.
                 */
            $queueWhere = $visitWhere . "
                    AND (pv.status_bayar IS NULL OR pv.status_bayar = 0)
                ";

            $queueRows = queryRows(
               "SELECT
                        pv.id_visit,
                        pv.visit_ID,
                        pv.id_patient,
                        pv.visit_date,
                        pv.visit_time,
                        pv.status_bayar,
                        pv.patient_name_pcare,
                        mp.nomor_rm,
                        pv.id_poli
                     FROM pasien_visit pv
                     LEFT JOIN ms_patient mp
                        ON CAST(mp.id_patient AS CHAR) = CAST(pv.id_patient AS CHAR)
                     WHERE {$queueWhere}
                     ORDER BY pv.visit_date ASC, pv.visit_time ASC, pv.id_visit ASC
                     LIMIT 20",
               $visitTypes,
               $visitParams
            );

            foreach ($queueRows as $index => $row) {
               $queue[] = [
                  'id'        => (int)$row['id_visit'],
                  'nomor'     => trim((string)$row['visit_ID']) ?: ('A-' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT)),
                  'nama'      => trim((string)($row['patient_name_pcare'] ?? '')) ?: '-',
                  'nomor_rm'  => trim((string)$row['nomor_rm']) ?: '-',
                  'poli'      => trim((string)$row['id_poli']) ?: '-',
                  'tanggal'   => $row['visit_date'],
                  'jam'       => trim((string)$row['visit_time']) ?: '-',
                  'status'    => 'Menunggu',
                  'status_class' => 'status-pending'
               ];
            }

            $pendingPayments = count($queue);
         }
      }
   }

   /* ========================================================
       4. PAYMENT TABLE
       ms_faskes_payment tidak memiliki id_customer pada schema
       yang tersedia. Karena itu tidak dipakai sebagai sumber
       global customer tanpa relasi invoice yang aman.
    ======================================================== */

   /*
     * Jika invoice tidak menyediakan total pembayaran, gunakan
     * payment table hanya bila dapat direlasikan ke invoice.
     */
   if (
      $invoiceConfig &&
      $invoiceConfig['number'] &&
      tableExists('ms_faskes_payment')
   ) {

      $paymentInvoice = columnExists('ms_faskes_payment', 'invoice_number')
         ? 'invoice_number'
         : null;

      $paymentDate = columnExists('ms_faskes_payment', 'payment_date')
         ? 'payment_date'
         : null;

      $paymentAmount = columnExists('ms_faskes_payment', 'payment_amount')
         ? 'payment_amount'
         : null;

      $paymentMethod = columnExists('ms_faskes_payment', 'payment_method')
         ? 'payment_method'
         : null;

      $paymentStatus = columnExists('ms_faskes_payment', 'payment_status')
         ? 'payment_status'
         : null;

      if ($paymentInvoice && $paymentDate && $paymentAmount) {

         $invDate = qi($invoiceConfig['date']);
         $invNum  = qi($invoiceConfig['number']);

         $paymentWhere = "
                DATE(p.payment_date) BETWEEN ? AND ?
            ";

         $paymentTypes = "ss";
         $paymentParams = [$start, $end];

         if ($paymentStatus) {
            $paymentWhere .= "
                    AND (p.payment_status IS NULL OR p.payment_status = 1)
                ";
         }

         if ($totalPaid <= 0) {

            $totalPaid = (float) queryScalar(
               "SELECT COALESCE(SUM(p.payment_amount),0)
                     FROM ms_faskes_payment p
                     INNER JOIN invoice i
                        ON i.{$invNum} = p.invoice_number
                     WHERE {$paymentWhere}",
               $paymentTypes,
               $paymentParams,
               0
            );
         }

         if ($paymentMethod) {

            $methodRows = queryRows(
               "SELECT
                        COALESCE(NULLIF(TRIM(p.payment_method),''),'Tidak Diketahui') AS metode,
                        COUNT(*) AS total,
                        COALESCE(SUM(p.payment_amount),0) AS nominal
                     FROM ms_faskes_payment p
                     INNER JOIN invoice i
                        ON i.{$invNum} = p.invoice_number
                     WHERE {$paymentWhere}
                     GROUP BY COALESCE(NULLIF(TRIM(p.payment_method),''),'Tidak Diketahui')
                     ORDER BY nominal DESC",
               $paymentTypes,
               $paymentParams
            );

            if (!empty($methodRows)) {

               $paymentMethods = [];

               $paymentCount = 0;

               foreach ($methodRows as $row) {
                  $paymentCount += (int)$row['total'];
               }

               foreach ($methodRows as $row) {
                  $paymentMethods[] = [
                     'label'      => $row['metode'],
                     'total'      => (int)$row['total'],
                     'nominal'    => moneyValue($row['nominal']),
                     'percentage' => percentage($row['total'], $paymentCount)
                  ];
               }
            }
         }
      }
   }

   /* ========================================================
       5. FINANCIAL SUMMARY
    ======================================================== */

   $outstanding = max(0, $totalBilled - $totalPaid);

   /*
     * Jika totalPaid tidak tersedia tetapi tidak ada transaksi,
     * jangan membuat angka pembayaran fiktif.
     */
   $paidTransactions = min($paidTransactions, $totalTransactions);

   if ($paidTransactions === 0 && $totalPaid > 0 && $totalTransactions > 0) {
      $paidTransactions = min(
         $totalTransactions,
         (int) round(($totalPaid / max(1, $totalBilled)) * $totalTransactions)
      );
   }

   $completionPercentage = percentage(
      $paidTransactions,
      $totalTransactions
   );

   /* ========================================================
       6. ALERT
    ======================================================== */

   $alerts = [];

   if ($pendingPayments > 0) {
      $alerts[] = [
         'type'  => 'warning',
         'title' => $pendingPayments . ' Transaksi Menunggu Pembayaran',
         'text'  => 'Terdapat pasien yang masih menunggu proses pembayaran.',
         'icon'  => 'solar:clock-circle-bold'
      ];
   }

   if ($failedTransactions > 0) {
      $alerts[] = [
         'type'  => 'danger',
         'title' => $failedTransactions . ' Transaksi Gagal',
         'text'  => 'Periksa transaksi pembayaran yang gagal atau belum terkonfirmasi.',
         'icon'  => 'solar:danger-triangle-bold'
      ];
   }

   if ($outstanding > 0) {
      $alerts[] = [
         'type'  => 'info',
         'title' => 'Terdapat Piutang / Tagihan Belum Dibayar',
         'text'  => moneyFormat($outstanding) . ' belum memiliki pembayaran yang teridentifikasi.',
         'icon'  => 'solar:wallet-money-bold'
      ];
   }

   if (empty($alerts)) {
      $alerts[] = [
         'type'  => 'success',
         'title' => 'Tidak Ada Transaksi yang Perlu Perhatian',
         'text'  => 'Tidak ditemukan antrean atau masalah pembayaran pada periode ini.',
         'icon'  => 'solar:check-circle-bold'
      ];
   }

   /* ========================================================
       7. NORMALISASI CHART
    ======================================================== */

   /*
     * Chart mengikuti tanggal yang benar-benar ada di database.
     * Tidak mengisi angka dummy.
     */
   $chartMap = [];

   foreach ($revenueChart as $row) {
      $chartMap[$row['date']] = (float)$row['total'];
   }

   ksort($chartMap);

   $revenueChart = [];

   foreach ($chartMap as $date => $total) {
      $revenueChart[] = [
         'date'  => $date,
         'total' => $total
      ];
   }

   /* ========================================================
       8. RESPONSE
    ======================================================== */

   responseJson(true, 'Data dashboard kasir berhasil diambil.', [
      'period' => $period,

      'kpi' => [
         'total_transaksi'      => (int)$totalTransactions,
         'menunggu_pembayaran'  => (int)$pendingPayments,
         'transaksi_lunas'      => (int)$paidTransactions,
         'persentase_lunas'     => $completionPercentage,
         'pendapatan'            => (float)$totalPaid,
         'total_tagihan'         => (float)$totalBilled,
         'piutang'               => (float)$outstanding
      ],

      'transactions' => [
         'total' => count($transactions),
         'items' => $transactions
      ],

      'queue' => [
         'waiting' => count($queue),
         'items'   => $queue
      ],

      'revenue_chart' => $revenueChart,

      'payment_methods' => $paymentMethods,

      'financial_summary' => [
         'total_tagihan' => (float)$totalBilled,
         'sudah_dibayar' => (float)$totalPaid,
         'piutang'       => (float)$outstanding
      ],

      'alerts' => [
         'total' => count($alerts),
         'items' => $alerts
      ]
   ]);
}

/* ============================================================
   ROUTER
============================================================ */

$action = strtolower(trim($_GET['action'] ?? 'dashboard'));

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;

if ($id_customer === null || $id_customer === '') {
   responseJson(false, 'Session id_customer tidak ditemukan.');
}

switch ($action) {

   case 'dashboard':

      $period = resolvePeriod();

      dashboard($id_customer, $period);

      break;

   default:

      responseJson(false, 'Action tidak dikenali.');
}
