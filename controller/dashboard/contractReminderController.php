<?php

/**
 * ============================================================
 * CONTRACT REMINDER DASHBOARD
 * ============================================================
 *
 * Endpoint:
 * controller/dashboard/contractReminderController.php?action=dashboard
 *
 * Source:
 * ms_faskes
 *
 * Scope:
 * id_customer dari session
 *
 * Countdown:
 * Tahun | Bulan | Hari | Jam
 *
 * Catatan:
 * contract_end bertipe DATE, sehingga masa kontrak dihitung
 * sampai akhir hari (23:59:59) pada tanggal contract_end.
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('Asia/Jakarta');

session_start();

include '../../database/connect.php';


/**
 * ============================================================
 * CEK SESSION CUSTOMER
 * ============================================================
 */

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = 1;


if (empty($id_customer)) {

   http_response_code(401);

   echo json_encode([
      'status'  => false,
      'message' => 'Session id_customer tidak ditemukan.'
   ]);

   exit;
}


/**
 * ============================================================
 * MAIN
 * ============================================================
 */

try {

   $action = $_GET['action'] ?? 'dashboard';

   switch ($action) {

      case 'dashboard':

         getContractReminder(
            $koneksi,
            $id_customer
         );

         break;


      default:

         echo json_encode([
            'status'  => false,
            'message' => 'Action tidak ditemukan.'
         ]);

         break;
   }
} catch (Throwable $e) {

   http_response_code(500);

   echo json_encode([
      'status'  => false,
      'message' => 'Gagal mengambil data masa kontrak.',
      'error'   => $e->getMessage()
   ]);
}


/**
 * ============================================================
 * GET CONTRACT REMINDER
 * ============================================================
 */

function getContractReminder(
   mysqli $koneksi,
   $id_customer
) {

   /**
    * ========================================================
    * QUERY DATA FASKES
    * ========================================================
    *
    * HANYA mengambil data milik customer yang sedang login.
    */

   $sql = "
        SELECT
            ms.id_faskes,
            sc.clinic_name faskes_code,
            ms.pic_name,
            ms.pic_phone,
            ms.pic_email,
            ms.faskes_address,
            ms.faskes_status,
            ms.contract_start,
            ms.contract_end,
            ms.contract_number,
            ms.order_number,
            ms.contract_amount,
            sc.id_customer
        FROM ms_faskes ms
        LEFT JOIN setting_clinic sc ON ms.id_clinic = sc.id
        WHERE sc.id_customer = ?
          AND ms.contract_start IS NOT NULL
          AND ms.contract_end IS NOT NULL
        ORDER BY ms.contract_end ASC,
                 id_faskes ASC
    ";


   $stmt = $koneksi->prepare($sql);


   if (!$stmt) {

      throw new Exception(
         'Prepare query ms_faskes gagal: ' .
            $koneksi->error
      );
   }


   /**
    * id_customer pada tabel diasumsikan INT.
    */

   $stmt->bind_param(
      "i",
      $id_customer
   );


   $stmt->execute();


   $result = $stmt->get_result();


   if (!$result) {

      throw new Exception(
         'Gagal mengambil result ms_faskes.'
      );
   }


   /**
    * ========================================================
    * TIMEZONE
    * ========================================================
    */

   $timezone = new DateTimeZone(
      'Asia/Jakarta'
   );


   $now = new DateTime(
      'now',
      $timezone
   );


   $items = [];


   /**
    * ========================================================
    * LOOP DATA
    * ========================================================
    */

   while ($row = $result->fetch_assoc()) {


      /**
       * Pastikan tanggal kontrak tersedia.
       */

      if (
         empty($row['contract_start']) ||
         empty($row['contract_end'])
      ) {

         continue;
      }


      /**
       * ====================================================
       * CONTRACT DATE
       * ====================================================
       */

      try {

         $contractStart = new DateTime(
            $row['contract_start'],
            $timezone
         );


         /**
          * contract_end bertipe DATE.
          *
          * Kontrak dianggap berlaku sampai
          * 23:59:59 pada tanggal tersebut.
          */

         $contractEnd = new DateTime(
            $row['contract_end'] . ' 23:59:59',
            $timezone
         );
      } catch (Throwable $e) {

         continue;
      }


      /**
       * ====================================================
       * STATUS KONTRAK
       * ====================================================
       */

      if ($contractEnd < $now) {

         $status = 'expired';

         $statusLabel = 'Kontrak Berakhir';

         $statusClass = 'danger';
      } else {

         /**
          * Hitung sisa hari.
          */

         $remainingDays = (int) $now
            ->diff($contractEnd)
            ->format('%r%a');


         /**
          * 0 - 30 hari
          */

         if ($remainingDays <= 30) {

            $status = 'critical';

            $statusLabel = 'Segera Berakhir';

            $statusClass = 'danger';
         }

         /**
          * 31 - 90 hari
          */

         elseif ($remainingDays <= 90) {

            $status = 'warning';

            $statusLabel = 'Perlu Perhatian';

            $statusClass = 'warning';
         }

         /**
          * Lebih dari 90 hari
          */

         else {

            $status = 'active';

            $statusLabel = 'Kontrak Aktif';

            $statusClass = 'success';
         }
      }


      /**
       * ====================================================
       * RESPONSE ITEM
       * ====================================================
       */

      $items[] = [

         'id_faskes' =>
         (int) $row['id_faskes'],


         'faskes_code' =>
         $row['faskes_code'] ?? '-',


         'pic_name' =>
         $row['pic_name'] ?? '-',


         'pic_phone' =>
         $row['pic_phone'] ?? '-',


         'pic_email' =>
         $row['pic_email'] ?? '-',


         'faskes_address' =>
         $row['faskes_address'] ?? '-',


         'faskes_status' =>
         (int) (
            $row['faskes_status'] ?? 0
         ),


         'contract_start' =>
         $contractStart->format(
            'Y-m-d'
         ),


         'contract_end' =>
         $contractEnd->format(
            'Y-m-d'
         ),


         'contract_number' =>
         $row['contract_number'] ?? '-',


         'order_number' =>
         $row['order_number'] ?? '-',


         'contract_amount' =>
         (int) (
            $row['contract_amount'] ?? 0
         ),


         'status' =>
         $status,


         'status_label' =>
         $statusLabel,


         'status_class' =>
         $statusClass,


         /**
          * Timestamp untuk JavaScript countdown.
          */

         'contract_end_timestamp' =>
         $contractEnd->getTimestamp() * 1000

      ];
   }


   /**
    * ========================================================
    * SUMMARY
    * ========================================================
    */

   $total = count($items);

   $aktif = 0;

   $warning = 0;

   $critical = 0;

   $expired = 0;


   foreach ($items as $item) {

      switch ($item['status']) {

         case 'active':

            $aktif++;

            break;


         case 'warning':

            $warning++;

            break;


         case 'critical':

            $critical++;

            break;


         case 'expired':

            $expired++;

            break;
      }
   }


   /**
    * ========================================================
    * KONTRAK TERDEKAT
    * ========================================================
    */

   $nearest = null;


   foreach ($items as $item) {

      if ($item['status'] !== 'expired') {

         $nearest = $item;

         break;
      }
   }


   /**
    * ========================================================
    * RESPONSE
    * ========================================================
    */

   echo json_encode([

      'status' => true,


      'message' =>
      'Data reminder masa kontrak berhasil diambil.',


      'server_time' =>
      $now->format('Y-m-d H:i:s'),


      'id_customer' =>
      $id_customer,


      'summary' => [

         'total' =>
         $total,

         'aktif' =>
         $aktif,

         'warning' =>
         $warning,

         'critical' =>
         $critical,

         'expired' =>
         $expired

      ],


      'nearest' =>
      $nearest,


      'items' =>
      $items

   ], JSON_UNESCAPED_UNICODE);


   exit;
}
