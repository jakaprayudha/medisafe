<?php

/**
 * ============================================================
 * DASHBOARD FARMASI
 * ============================================================
 * Endpoint:
 * controller/dashboard/farmasiDashboardController.php?action=dashboard
 *
 * Prinsip:
 * - Data farmasi GLOBAL berdasarkan id_customer + periode.
 * - Tidak mengikat data ke dokter/perawat tertentu.
 * - Sumber stok:
 *   ms_pharmacy.pharmacy_stock
 *   + pharmacy_stock_penerimaan.jumlah
 *   - permintaan_pharmacy_details.qty (status_item = 1)
 * - Resep menggunakan tabel permintaan_pharmacy dan detailnya.
 * - Schema permintaan_pharmacy memakai created_at sebagai tanggal,
 *   status_permintaan sebagai status, permintaan_number sebagai nomor,
 *   id_visit sebagai relasi visit, dan id_customer sebagai customer.
 * ============================================================
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../database/connect.php';

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

function getPeriod()
{
   $periode = $_GET['periode'] ?? 'today';
   $today   = date('Y-m-d');

   switch ($periode) {
      case 'yesterday':
         $start = date('Y-m-d', strtotime('-1 day'));
         $end   = $start;
         break;

      case '7days':
         $start = date('Y-m-d', strtotime('-6 days'));
         $end   = $today;
         break;

      case '30days':
         $start = date('Y-m-d', strtotime('-29 days'));
         $end   = $today;
         break;

      case 'thismonth':
         $start = date('Y-m-01');
         $end   = $today;
         break;

      case 'lastmonth':
         $start = date('Y-m-01', strtotime('first day of last month'));
         $end   = date('Y-m-t', strtotime('last day of last month'));
         break;

      case 'custom':
         $start = $_GET['tanggal_mulai'] ?? $today;
         $end   = $_GET['tanggal_selesai'] ?? $today;

         $sv = DateTime::createFromFormat('Y-m-d', $start);
         $ev = DateTime::createFromFormat('Y-m-d', $end);

         if (
            !$sv ||
            !$ev ||
            $sv->format('Y-m-d') !== $start ||
            $ev->format('Y-m-d') !== $end
         ) {
            $start = $today;
            $end   = $today;
         }
         break;

      case 'today':
      default:
         $periode = 'today';
         $start   = $today;
         $end     = $today;
         break;
   }

   if ($start > $end) {
      [$start, $end] = [$end, $start];
   }

   return [
      'type'  => $periode,
      'start' => $start,
      'end'   => $end
   ];
}

function tableExists($table)
{
   global $koneksi;

   $table = mysqli_real_escape_string($koneksi, $table);
   $sql = "SELECT 1
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = '$table'
            LIMIT 1";

   $result = mysqli_query($koneksi, $sql);
   return $result && mysqli_num_rows($result) > 0;
}

function columnExists($table, $column)
{
   global $koneksi;

   $table  = mysqli_real_escape_string($koneksi, $table);
   $column = mysqli_real_escape_string($koneksi, $column);

   $sql = "SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = '$table'
              AND column_name = '$column'
            LIMIT 1";

   $result = mysqli_query($koneksi, $sql);
   return $result && mysqli_num_rows($result) > 0;
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

function fetchOne($sql, $types = '', $params = [])
{
   global $koneksi;

   $stmt = mysqli_prepare($koneksi, $sql);

   if (!$stmt) {
      return null;
   }

   if ($types !== '' && count($params) > 0) {
      mysqli_stmt_bind_param($stmt, $types, ...$params);
   }

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);
   $row    = $result ? mysqli_fetch_assoc($result) : null;

   mysqli_stmt_close($stmt);

   return $row;
}

function fetchAllRows($sql, $types = '', $params = [])
{
   global $koneksi;

   $stmt = mysqli_prepare($koneksi, $sql);

   if (!$stmt) {
      return [];
   }

   if ($types !== '' && count($params) > 0) {
      mysqli_stmt_bind_param($stmt, $types, ...$params);
   }

   mysqli_stmt_execute($stmt);

   $result = mysqli_stmt_get_result($stmt);
   $rows   = [];

   if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
         $rows[] = $row;
      }
   }

   mysqli_stmt_close($stmt);

   return $rows;
}

function cleanString($value)
{
   return trim((string)($value ?? ''));
}

// $idCustomer = $_SESSION['id_customer'] ?? null;

$idCustomer = 1;

if ($idCustomer === null || $idCustomer === '') {
   responseJson(false, 'Session id_customer tidak ditemukan.');
}

$action = $_GET['action'] ?? 'dashboard';

if ($action !== 'dashboard') {
   responseJson(false, 'Action tidak dikenali.');
}

$period    = getPeriod();
$startDate = $period['start'];
$endDate   = $period['end'];

/*
 * ============================================================
 * DETEKSI TABEL RESEP
 * ============================================================
 */

$hasRequestTable = tableExists('permintaan_pharmacy');
$hasDetailTable  = tableExists('permintaan_pharmacy_details');

/*
 * Kolom tanggal pada permintaan farmasi.
 */
$requestDateColumn = 'created_at';

/*
 * Kolom customer pada permintaan farmasi.
 */
$requestCustomerColumn = 'id_customer';

/*
 * Kolom status pada permintaan farmasi.
 */
$requestStatusColumn = 'status_permintaan';

/*
 * Kolom nomor resep / transaksi.
 */
$requestNumberColumn = 'permintaan_number';

/*
 * Kolom visit.
 */
$requestVisitColumn = 'id_visit';

/*
 * ============================================================
 * KPI
 * ============================================================
 */

$kpi = [
   'resep_masuk'          => 0,
   'menunggu_diproses'    => 0,
   'sedang_diproses'      => 0,
   'resep_selesai'        => 0,
   'persentase_selesai'   => 0
];

if (
   $hasRequestTable &&
   $requestDateColumn &&
   $requestCustomerColumn
) {
   $dateExpr = "DATE(r.`$requestDateColumn`)";

   $where = "WHERE r.`$requestCustomerColumn` = ?
              AND $dateExpr BETWEEN ? AND ?";

   /*
     * Total resep masuk.
     */
   $row = fetchOne(
      "SELECT COUNT(*) AS total
         FROM permintaan_pharmacy r
         $where",
      "sss",
      [(string)$idCustomer, $startDate, $endDate]
   );

   $kpi['resep_masuk'] = (int)($row['total'] ?? 0);

   /*
     * Status dipetakan secara fleksibel karena implementasi
     * status permintaan dapat berbeda antar instalasi.
     */
   if ($requestStatusColumn) {
      $statusCol = "r.`$requestStatusColumn`";

      $waitingCondition = "(
            $statusCol IN ('0', 'menunggu', 'pending', 'baru', 'waiting')
            OR $statusCol IS NULL
        )";

      $processCondition = "(
            $statusCol IN ('1', 'diproses', 'process', 'processing', 'dispensing')
        )";

      $doneCondition = "(
            $statusCol IN (
                '2',
                '3',
                'selesai',
                'done',
                'completed',
                'diserahkan',
                'ready',
                'siap_diambil'
            )
        )";

      $row = fetchOne(
         "SELECT COUNT(*) AS total
             FROM permintaan_pharmacy r
             $where
             AND $waitingCondition",
         "sss",
         [(string)$idCustomer, $startDate, $endDate]
      );
      $kpi['menunggu_diproses'] = (int)($row['total'] ?? 0);

      $row = fetchOne(
         "SELECT COUNT(*) AS total
             FROM permintaan_pharmacy r
             $where
             AND $processCondition",
         "sss",
         [(string)$idCustomer, $startDate, $endDate]
      );
      $kpi['sedang_diproses'] = (int)($row['total'] ?? 0);

      $row = fetchOne(
         "SELECT COUNT(*) AS total
             FROM permintaan_pharmacy r
             $where
             AND $doneCondition",
         "sss",
         [(string)$idCustomer, $startDate, $endDate]
      );
      $kpi['resep_selesai'] = (int)($row['total'] ?? 0);
   }

   /*
     * Jika status tidak tersedia, jangan membuat angka dummy.
     * Resep masuk tetap valid, status lainnya 0.
     */
   if ($kpi['resep_masuk'] > 0) {
      $kpi['persentase_selesai'] = round(
         ($kpi['resep_selesai'] / $kpi['resep_masuk']) * 100
      );
   }
}

/*
 * ============================================================
 * ANTRIAN RESEP
 * ============================================================
 */

$prescriptions = [];

if (
   $hasRequestTable &&
   $requestDateColumn &&
   $requestCustomerColumn
) {
   $numberSelect = $requestNumberColumn
      ? "r.`$requestNumberColumn` AS nomor_resep"
      : "NULL AS nomor_resep";

   $visitSelect = $requestVisitColumn
      ? "r.`$requestVisitColumn` AS visit_ref"
      : "NULL AS visit_ref";

   $statusSelect = $requestStatusColumn
      ? "r.`$requestStatusColumn` AS status_resep"
      : "NULL AS status_resep";

   /*
     * Ambil beberapa resep terbaru.
     * Nama pasien/dokter/poli dicoba melalui visit jika relasinya tersedia.
     */
   $joinVisit = '';
   $joinPatient = '';

   if ($requestVisitColumn && tableExists('pasien_visit')) {
      if ($requestVisitColumn === 'id_visit') {
         $joinVisit = "
                LEFT JOIN pasien_visit pv
                    ON CAST(pv.visit_ID AS CHAR) =
                       CAST(r.`$requestVisitColumn` AS CHAR)
            ";
      } else {
         $joinVisit = "
                LEFT JOIN pasien_visit pv
                    ON CAST(pv.visit_ID AS CHAR) =
                       CAST(r.`$requestVisitColumn` AS CHAR)
            ";
      }

      if (tableExists('ms_patient')) {
         $joinPatient = "
                LEFT JOIN ms_patient mp
                    ON CAST(mp.id_patient AS CHAR) =
                       CAST(pv.id_patient AS CHAR)
            ";
      }
   }

   $patientNameSelect = $joinPatient
      ? "pv.patient_name_pcare AS nama_pasien, mp.nomor_rm"
      : "NULL AS nama_pasien, NULL AS nomor_rm";

   $doctorSelect = $joinVisit
      ? "pv.id_doctor AS dokter"
      : "NULL AS dokter";

   $poliSelect = $joinVisit
      ? "pv.id_poli AS poli"
      : "NULL AS poli";

   $rows = fetchAllRows(
      "SELECT
            r.*,
            $numberSelect,
            $visitSelect,
            $statusSelect,
            $patientNameSelect,
            $doctorSelect,
            $poliSelect
         FROM permintaan_pharmacy r
         $joinVisit
         $joinPatient
         WHERE r.`$requestCustomerColumn` = ?
           AND DATE(r.`$requestDateColumn`) BETWEEN ? AND ?
         ORDER BY r.`$requestDateColumn` DESC
         LIMIT 20",
      "sss",
      [(string)$idCustomer, $startDate, $endDate]
   );

   foreach ($rows as $row) {
      $statusRaw = strtolower(cleanString($row['status_resep'] ?? ''));

      if (
         in_array($statusRaw, [
            '1',
            'diproses',
            'process',
            'processing',
            'dispensing'
         ], true)
      ) {
         $status      = 'Diproses';
         $statusClass = 'status-process';
      } elseif (
         in_array($statusRaw, [
            '2',
            '3',
            'selesai',
            'done',
            'completed'
         ], true)
      ) {
         $status      = 'Selesai';
         $statusClass = 'status-done';
      } elseif (
         in_array($statusRaw, [
            'ready',
            'siap_diambil'
         ], true)
      ) {
         $status      = 'Siap Diambil';
         $statusClass = 'status-ready';
      } else {
         $status      = 'Menunggu';
         $statusClass = 'status-waiting';
      }

      $prescriptions[] = [
         'id'            => $row['id_permintaan_farmasi']
            ?? $row['id_permintaan']
            ?? $row['id']
            ?? null,
         'nomor_resep'   => $row['id_visit'] ?? $row['nomor_resep']
            ?? '-',
         'nama_pasien'   => $row['nama_pasien']
            ?? '-',
         'nomor_rm'      => $row['nomor_rm']
            ?? '-',
         'dokter'        => $row['dokter']
            ?? '-',
         'poli'          => $row['poli']
            ?? '-',
         'status'        => $status,
         'status_class'  => $statusClass,
         'tanggal'       => $row[$requestDateColumn] ?? null
      ];
   }
}

/*
 * ============================================================
 * STOK OBAT
 * ============================================================
 */

$lowStock = [];

if (tableExists('ms_pharmacy')) {
   $stockExpression = "COALESCE(mp.pharmacy_stock, 0)";

   /*
     * Tambahkan penerimaan stok.
     */
   if (
      tableExists('pharmacy_stock_penerimaan') &&
      columnExists('pharmacy_stock_penerimaan', 'id_pharmacy') &&
      columnExists('pharmacy_stock_penerimaan', 'id_customer') &&
      columnExists('pharmacy_stock_penerimaan', 'jumlah')
   ) {
      $stockExpression .= "
            + COALESCE((
                SELECT SUM(psp.jumlah)
                FROM pharmacy_stock_penerimaan psp
                WHERE CAST(psp.id_pharmacy AS CHAR) =
                      CAST(mp.id_pharmacy AS CHAR)
                  AND CAST(psp.id_customer AS CHAR) =
                      CAST(mp.id_customer AS CHAR)
            ), 0)";
   }

   /*
     * Kurangi pengeluaran farmasi.
     */
   if (
      tableExists('permintaan_pharmacy_details') &&
      columnExists('permintaan_pharmacy_details', 'id_pharmacy') &&
      columnExists('permintaan_pharmacy_details', 'qty') &&
      columnExists('permintaan_pharmacy_details', 'status_item')
   ) {
      $stockExpression .= "
            - COALESCE((
                SELECT SUM(ppd.qty)
                FROM permintaan_pharmacy_details ppd
                WHERE ppd.id_pharmacy = mp.id_pharmacy
                  AND ppd.status_item = 1
            ), 0)";
   }

   $sql = "
        SELECT
            mp.id_pharmacy,
            mp.pharmacy_code,
            mp.pharmacy_name_generic,
            mp.pharmacy_name_trade,
            mp.pharmacy_unit,
            mp.stok_min,
            $stockExpression AS stock_available
        FROM ms_pharmacy mp
        WHERE mp.id_customer = ?
          AND (
                mp.pharmacy_status IS NULL
                OR mp.pharmacy_status = ''
                OR LOWER(mp.pharmacy_status) NOT IN (
                    '0',
                    'inactive',
                    'nonaktif'
                )
          )
        ORDER BY stock_available ASC, mp.pharmacy_name_generic ASC
        LIMIT 10
    ";

   $rows = fetchAllRows(
      $sql,
      "i",
      [$idCustomer]
   );

   foreach ($rows as $row) {
      $stock = (float)($row['stock_available'] ?? 0);
      $min   = (float)($row['stok_min'] ?? 0);

      /*
         * Hanya tampilkan obat yang benar-benar berada di bawah
         * batas minimum. Jika stok_min kosong/0, gunakan stok <= 0.
         */
      $isLow = $min > 0
         ? $stock <= $min
         : $stock <= 0;

      if (!$isLow) {
         continue;
      }

      $warning = $stock <= 0 ? 'Stok Kosong' : 'Stok Menipis';

      $lowStock[] = [
         'id'       => (int)$row['id_pharmacy'],
         'kode'     => $row['pharmacy_code'] ?? '-',
         'nama'     => $row['pharmacy_name_generic']
            ?: ($row['pharmacy_name_trade'] ?? '-'),
         'unit'     => $row['pharmacy_unit'] ?? '-',
         'stock'    => $stock,
         'stok_min' => $min,
         'warning'  => $warning
      ];
   }
}

/*
 * ============================================================
 * DISTRIBUSI OBAT BERDASARKAN BENTUK SEDIAAN
 * ============================================================
 */

$category = [];

if (
   tableExists('ms_pharmacy') &&
   tableExists('permintaan_pharmacy_details')
) {
   $rows = fetchAllRows(
      "SELECT
          COALESCE(
              NULLIF(TRIM(mp.pharmacy_bentuk_sediaan), ''),
              'Tidak Diketahui'
          ) AS kategori,
          COALESCE(SUM(ppd.qty), 0) AS total
       FROM permintaan_pharmacy_details ppd
       INNER JOIN ms_pharmacy mp
           ON mp.id_pharmacy = ppd.id_pharmacy
       INNER JOIN permintaan_pharmacy r
           ON r.id_permintaan_farmasi = ppd.id_permintaan_farmasi
       WHERE mp.id_customer = ?
         AND ppd.status_item = 1
         AND DATE(r.created_at) BETWEEN ? AND ?
       GROUP BY kategori
       ORDER BY total DESC
       LIMIT 10",
      "sss",
      [(string)$idCustomer, $startDate, $endDate]
   );
   foreach ($rows as $row) {
      $category[] = [
         'kategori' => $row['kategori'],
         'total'    => (int)$row['total']
      ];
   }
}

/*
 * ============================================================
 * PROGRESS PELAYANAN
 * ============================================================
 */

$progress = [
   [
      'name'  => 'Verifikasi Resep',
      'value' => 0,
      'total' => $kpi['resep_masuk'],
      'class' => ''
   ],
   [
      'name'  => 'Dispensing',
      'value' => 0,
      'total' => $kpi['resep_masuk'],
      'class' => 'blue'
   ],
   [
      'name'  => 'Siap Diserahkan',
      'value' => 0,
      'total' => $kpi['resep_masuk'],
      'class' => 'green'
   ],
   [
      'name'  => 'Sudah Diserahkan',
      'value' => $kpi['resep_selesai'],
      'total' => $kpi['resep_masuk'],
      'class' => 'orange'
   ]
];

if ($kpi['resep_masuk'] > 0) {
   $progress[0]['value'] = max(
      0,
      $kpi['resep_masuk'] -
         $kpi['menunggu_diproses']
   );

   $progress[1]['value'] = $kpi['sedang_diproses'] +
      $kpi['resep_selesai'];

   $progress[2]['value'] = $kpi['resep_selesai'];
}

foreach ($progress as &$item) {
   $item['percentage'] = $item['total'] > 0
      ? min(
         100,
         round(
            ($item['value'] / $item['total']) * 100
         )
      )
      : 0;
}
unset($item);

/*
 * ============================================================
 * ALERT
 * ============================================================
 */

$alerts = [];

$emptyStockCount = 0;
$criticalStockCount = 0;

foreach ($lowStock as $item) {
   if ($item['stock'] <= 0) {
      $emptyStockCount++;
   } else {
      $criticalStockCount++;
   }
}

if ($criticalStockCount > 0) {
   $alerts[] = [
      'type'  => 'danger',
      'title' => 'Stok Kritis',
      'text'  => $criticalStockCount . ' item berada di bawah stok minimum.',
      'icon'  => 'solar:danger-triangle-bold'
   ];
}

if ($emptyStockCount > 0) {
   $alerts[] = [
      'type'  => 'warning',
      'title' => 'Stok Kosong',
      'text'  => $emptyStockCount . ' item tidak memiliki stok tersedia.',
      'icon'  => 'solar:box-minimalistic-bold'
   ];
}

if ($kpi['menunggu_diproses'] > 0) {
   $alerts[] = [
      'type'  => 'warning',
      'title' => 'Resep Menunggu Diproses',
      'text'  => $kpi['menunggu_diproses'] . ' resep membutuhkan proses farmasi.',
      'icon'  => 'solar:clock-circle-bold'
   ];
}

/*
 * ============================================================
 * TREND RESEP
 * ============================================================
 */

$trend = [];

if (
   $hasRequestTable &&
   $requestDateColumn &&
   $requestCustomerColumn
) {
   $trendRows = fetchAllRows(
      "SELECT
            DATE(r.`$requestDateColumn`) AS tanggal,
            COUNT(*) AS total
         FROM permintaan_pharmacy r
         WHERE r.`$requestCustomerColumn` = ?
           AND DATE(r.`$requestDateColumn`) BETWEEN ? AND ?
         GROUP BY DATE(r.`$requestDateColumn`)
         ORDER BY tanggal ASC",
      "sss",
      [(string)$idCustomer, $startDate, $endDate]
   );

   foreach ($trendRows as $row) {
      $trend[] = [
         'date'   => $row['tanggal'],
         'masuk'  => (int)$row['total'],
         'selesai' => 0
      ];
   }

   /*
     * Jika status tersedia, hitung resep selesai per tanggal.
     */
   if ($requestStatusColumn) {
      $doneRows = fetchAllRows(
         "SELECT
                DATE(r.`$requestDateColumn`) AS tanggal,
                COUNT(*) AS total
             FROM permintaan_pharmacy r
             WHERE r.`$requestCustomerColumn` = ?
               AND DATE(r.`$requestDateColumn`) BETWEEN ? AND ?
               AND r.`$requestStatusColumn` IN (
                    '2',
                    '3',
                    'selesai',
                    'done',
                    'completed',
                    'diserahkan'
               )
             GROUP BY DATE(r.`$requestDateColumn`)
             ORDER BY tanggal ASC",
         "sss",
         [(string)$idCustomer, $startDate, $endDate]
      );

      $doneMap = [];

      foreach ($doneRows as $row) {
         $doneMap[$row['tanggal']] = (int)$row['total'];
      }

      foreach ($trend as &$item) {
         $item['selesai'] = $doneMap[$item['date']] ?? 0;
      }
      unset($item);
   }
}

/*
 * ============================================================
 * RESPONSE
 * ============================================================
 */

responseJson(
   true,
   'Data dashboard farmasi berhasil diambil.',
   [
      'period' => $period,

      'kpi' => $kpi,

      'prescriptions' => [
         'total' => count($prescriptions),
         'items' => $prescriptions
      ],

      'process' => [
         'waiting'  => $kpi['menunggu_diproses'],
         'processing' => $kpi['sedang_diproses'],
         'ready'    => $kpi['resep_selesai'],
         'completed' => $kpi['resep_selesai'],
         'items'    => $progress
      ],

      'stock' => [
         'low_total' => count($lowStock),
         'items'     => $lowStock
      ],

      'category' => $category,

      'alerts' => [
         'total' => count($alerts),
         'items' => $alerts
      ],

      'trend' => $trend
   ]
);
