<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;
if (!$id_customer) {
   echo json_encode([
      'status' => false,
      'message' => 'Session customer tidak ditemukan'
   ]);
   exit;
}

$action = $_GET['action'] ?? '';

/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function responseJson($data)
{
   echo json_encode($data, JSON_UNESCAPED_UNICODE);
   exit;
}

function bindDynamic(mysqli_stmt $stmt, $types, ...$params)
{
   $stmt->bind_param($types, ...$params);
}

/*
|--------------------------------------------------------------------------
| RANGE TANGGAL
|--------------------------------------------------------------------------
*/

function getDateRange($period, $start = null, $end = null)
{
   $today = date('Y-m-d');

   switch ($period) {

      case 'today':
         return [$today, $today];

      case 'yesterday':
         $date = date('Y-m-d', strtotime('-1 day'));
         return [$date, $date];

      case '7days':
         return [
            date('Y-m-d', strtotime('-6 days')),
            $today
         ];

      case '30days':
         return [
            date('Y-m-d', strtotime('-29 days')),
            $today
         ];

      case 'thismonth':
         return [
            date('Y-m-01'),
            $today
         ];

      case 'lastmonth':
         return [
            date('Y-m-01', strtotime('-1 month')),
            date('Y-m-t', strtotime('-1 month'))
         ];

      case 'custom':

         if (
            !empty($start) &&
            !empty($end)
         ) {
            return [$start, $end];
         }

         return [
            date('Y-m-01'),
            $today
         ];

      default:

         return [
            date('Y-m-01'),
            $today
         ];
   }
}


/*
|--------------------------------------------------------------------------
| DASHBOARD UTAMA
|--------------------------------------------------------------------------
*/

if ($action === 'dashboard') {

   $period = $_GET['period'] ?? 'thismonth';

   $start = $_GET['start'] ?? null;
   $end   = $_GET['end'] ?? null;

   [$dateStart, $dateEnd] = getDateRange(
      $period,
      $start,
      $end
   );


   /*
    |--------------------------------------------------------------------------
    | KPI
    |--------------------------------------------------------------------------
    */

   // TOTAL PASIEN UNIK
   $sql = "
        SELECT COUNT(DISTINCT id_patient) AS total
        FROM pasien_visit
        WHERE id_customer = ?
        AND visit_date BETWEEN ? AND ?
        AND id_patient IS NOT NULL
        AND id_patient <> ''
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      responseJson([
         'status' => false,
         'message' => $koneksi->error
      ]);
   }

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result()->fetch_assoc();

   $totalPasien = (int)($result['total'] ?? 0);

   $stmt->close();


   // TOTAL KUNJUNGAN RAWAT JALAN
   $sql = "
        SELECT COUNT(*) AS total
        FROM pasien_visit
        WHERE id_customer = ?
        AND visit_date BETWEEN ? AND ?
        AND (
            status_rawatinap = 0
            OR status_rawatinap IS NULL
        )
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result()->fetch_assoc();

   $kunjunganRawatJalan = (int)($result['total'] ?? 0);

   $stmt->close();


   /*
|--------------------------------------------------------------------------
| PASIEN BARU
|--------------------------------------------------------------------------
|
| Pasien baru = pasien yang pertama kali mempunyai kunjungan
| pada customer tersebut, dan kunjungan pertamanya berada
| dalam periode yang dipilih.
|
*/

   $sql = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT
            pv.id_patient,
            MIN(pv.visit_date) AS first_visit
        FROM pasien_visit pv
        WHERE pv.id_customer = ?
          AND pv.id_patient IS NOT NULL
          AND TRIM(pv.id_patient) <> ''
        GROUP BY pv.id_patient
    ) AS first_visits
    WHERE first_visit BETWEEN ? AND ?
";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      responseJson([
         'status' => false,
         'message' => 'Query pasien baru gagal: ' . $koneksi->error
      ]);
   }

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result()->fetch_assoc();

   $pasienBaru = (int)($result['total'] ?? 0);

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | PENDAPATAN
    |--------------------------------------------------------------------------
    |
    | Menggunakan amount_payment dari pasien_visit.
    | Hanya transaksi yang mempunyai pembayaran.
    |
    */

   $sql = "
        SELECT COALESCE(SUM(amount_payment), 0) AS total
        FROM pasien_visit
        WHERE id_customer = ?
        AND visit_date BETWEEN ? AND ?
        AND amount_payment > 0
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result()->fetch_assoc();

   $pendapatan = (float)($result['total'] ?? 0);

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | CHART KUNJUNGAN PER HARI
    |--------------------------------------------------------------------------
    */

   $sql = "
        SELECT
            visit_date,
            COUNT(*) AS total
        FROM pasien_visit
        WHERE id_customer = ?
        AND visit_date BETWEEN ? AND ?
        GROUP BY visit_date
        ORDER BY visit_date ASC
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $visitChart = [];

   while ($row = $result->fetch_assoc()) {

      $visitChart[] = [
         'date' => $row['visit_date'],
         'total' => (int)$row['total']
      ];
   }

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | METODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

   $sql = "
        SELECT
            CASE
                WHEN metode_bayar IS NULL
                    OR TRIM(metode_bayar) = ''
                    THEN 'Tidak Diketahui'
                ELSE metode_bayar
            END AS metode_bayar,
            COUNT(*) AS total
        FROM pasien_visit
        WHERE id_customer = ?
        AND visit_date BETWEEN ? AND ?
        GROUP BY
            CASE
                WHEN metode_bayar IS NULL
                    OR TRIM(metode_bayar) = ''
                    THEN 'Tidak Diketahui'
                ELSE metode_bayar
            END
        ORDER BY total DESC
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "iss",
      $id_customer,
      $dateStart,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $paymentChart = [];

   while ($row = $result->fetch_assoc()) {

      $paymentChart[] = [
         'label' => $row['metode_bayar'],
         'total' => (int)$row['total']
      ];
   }

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | ANTRIAN
    |--------------------------------------------------------------------------
    */

   $sql = "
        SELECT
            ap.id,
            ap.nomor,
            ap.kode_antri,
            ap.poli,
            ap.tanggal,
            ap.nomor_visit,
            ap.status,
            ap.id_dokter,

            pv.id_patient,
            mp.patient_name

        FROM antrian_poli ap

        LEFT JOIN pasien_visit pv
            ON pv.visit_ID = ap.nomor_visit
            AND pv.id_customer = ?

        LEFT JOIN ms_patient mp
            ON mp.id_patient = pv.id_patient

        WHERE ap.id_customer = ?
        AND ap.tanggal = ?

        ORDER BY ap.id ASC
        LIMIT 10
    ";

   $stmt = $koneksi->prepare($sql);

   /*
     * ap.id_customer VARCHAR
     * id_customer session biasanya integer.
     * Untuk aman kita bind string.
     */

   $customerString = (string)$id_customer;

   $stmt->bind_param(
      "sss",
      $customerString,
      $customerString,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $queue = [];

   while ($row = $result->fetch_assoc()) {

      $status = (int)$row['status'];

      if ($status === 0) {
         $statusText = 'Menunggu';
         $statusClass = 'waiting';
      } elseif ($status === 1) {
         $statusText = 'Dipanggil';
         $statusClass = 'process';
      } elseif ($status === 2) {
         $statusText = 'Selesai';
         $statusClass = 'done';
      } else {
         $statusText = 'Menunggu';
         $statusClass = 'waiting';
      }

      $queue[] = [
         'id' => (int)$row['id'],
         'nomor' => $row['kode_antri']
            ? $row['kode_antri'] . '-' . $row['nomor']
            : $row['nomor'],
         'nama' => $row['patient_name'] ?: 'Pasien',
         'poli' => $row['poli'] ?: '-',
         'dokter' => $row['id_dokter'] ?: '-',
         'status' => $statusText,
         'status_class' => $statusClass
      ];
   }

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | JUMLAH ANTRIAN MENUNGGU
    |--------------------------------------------------------------------------
    */

   $sql = "
        SELECT COUNT(*) AS total
        FROM antrian_poli
        WHERE id_customer = ?
        AND tanggal = ?
        AND status = 0
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "ss",
      $customerString,
      $dateEnd
   );

   $stmt->execute();

   $result = $stmt->get_result()->fetch_assoc();

   $queueWaiting = (int)($result['total'] ?? 0);

   $stmt->close();


   /*
|--------------------------------------------------------------------------
| KAMAR / BED
|--------------------------------------------------------------------------
|
| Dashboard menampilkan agregasi berdasarkan service_class.
|
*/

   $sql = "
    SELECT
        r.service_class,

        COUNT(b.id_bed) AS total_bed,

        SUM(
            CASE
                WHEN b.bed_status = 0 THEN 1
                ELSE 0
            END
        ) AS bed_kosong,

        SUM(
            CASE
                WHEN b.bed_status <> 0 THEN 1
                ELSE 0
            END
        ) AS bed_terisi

    FROM ms_room r

    LEFT JOIN ms_room_bed b
        ON b.id_room = r.id_room
        AND b.id_customer = r.id_customer

    WHERE r.id_customer = ?
      AND r.room_status = 1

    GROUP BY r.service_class

    ORDER BY r.service_class ASC
";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      responseJson([
         'status' => false,
         'message' => 'Query kamar gagal: ' . $koneksi->error
      ]);
   }

   $stmt->bind_param(
      "i",
      $id_customer
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $rooms = [];

   while ($row = $result->fetch_assoc()) {

      $totalBed = (int)($row['total_bed'] ?? 0);
      $terisi   = (int)($row['bed_terisi'] ?? 0);
      $kosong   = (int)($row['bed_kosong'] ?? 0);

      $occupancy = $totalBed > 0
         ? round(($terisi / $totalBed) * 100)
         : 0;

      $rooms[] = [
         'class'      => $row['service_class'] ?: 'Tanpa Kelas',
         'total'      => $totalBed,
         'terisi'     => $terisi,
         'kosong'     => $kosong,
         'occupancy'  => $occupancy
      ];
   }

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | STOK FARMASI
    |--------------------------------------------------------------------------
    */

   $sql = "
        SELECT
            p.id_pharmacy,
            p.pharmacy_code,
            p.pharmacy_name_generic,
            p.pharmacy_name_trade,
            p.pharmacy_stock,
            p.stok_min,
            p.pharmacy_unit,

            COALESCE(
                (
                    SELECT SUM(psp.jumlah)
                    FROM pharmacy_stock_penerimaan psp
                    WHERE psp.id_pharmacy = CAST(p.id_pharmacy AS CHAR)
                    AND psp.id_customer = CAST(p.id_customer AS CHAR)
                ),
                0
            ) AS stok_masuk,

            COALESCE(
                (
                    SELECT SUM(ppd.qty)
                    FROM permintaan_pharmacy_details ppd
                    INNER JOIN permintaan_pharmacy pp
                        ON pp.id_permintaan_farmasi =
                           ppd.id_permintaan_farmasi

                    WHERE ppd.id_pharmacy = p.id_pharmacy
                    AND ppd.status_item = 1
                ),
                0
            ) AS stok_keluar

        FROM ms_pharmacy p

        WHERE p.id_customer = ?
        AND p.pharmacy_status = '1'

        ORDER BY p.pharmacy_name_generic ASC
    ";

   $stmt = $koneksi->prepare($sql);

   $stmt->bind_param(
      "i",
      $id_customer
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $lowStock = [];

   while ($row = $result->fetch_assoc()) {

      $stokAwal = (float)$row['pharmacy_stock'];
      $stokMasuk = (float)$row['stok_masuk'];
      $stokKeluar = (float)$row['stok_keluar'];

      $stokTersedia =
         $stokAwal +
         $stokMasuk -
         $stokKeluar;

      $stokMin = (float)$row['stok_min'];

      if (
         $stokMin !== null &&
         $stokTersedia <= $stokMin
      ) {

         $nama = $row['pharmacy_name_generic'];

         if (
            !empty($row['pharmacy_name_trade']) &&
            $row['pharmacy_name_trade'] !==
            $row['pharmacy_name_generic']
         ) {
            $nama .= ' (' .
               $row['pharmacy_name_trade'] .
               ')';
         }

         $lowStock[] = [
            'id' => (int)$row['id_pharmacy'],
            'name' => $nama,
            'code' => $row['pharmacy_code'] ?: '-',
            'stock' => $stokTersedia,
            'unit' => $row['pharmacy_unit'] ?: ''
         ];
      }
   }

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | SORT STOK TERENDAH
    |--------------------------------------------------------------------------
    */

   usort(
      $lowStock,
      function ($a, $b) {
         return $a['stock'] <=> $b['stock'];
      }
   );

   $lowStockTotal = count($lowStock);

   /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

   responseJson([
      'status' => true,

      'period' => [
         'type' => $period,
         'start' => $dateStart,
         'end' => $dateEnd
      ],

      'kpi' => [
         'total_pasien' => $totalPasien,
         'pasien_baru' => $pasienBaru,
         'kunjungan_rawat_jalan' => $kunjunganRawatJalan,
         'pendapatan' => $pendapatan
      ],

      'visit_chart' => $visitChart,

      'payment_chart' => $paymentChart,

      'queue' => [
         'waiting' => $queueWaiting,
         'items' => $queue
      ],

      'rooms' => $rooms,

      'low_stock' => [
         'total' => $lowStockTotal,
         'items' => array_slice($lowStock, 0, 10)
      ]
   ]);
}


/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/

responseJson([
   'status' => false,
   'message' => 'Action tidak ditemukan'
]);
