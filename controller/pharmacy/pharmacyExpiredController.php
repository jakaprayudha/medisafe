<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;
$username    = $_SESSION['username'] ?? 'system';

if (!$id_customer) {
   echo json_encode([
      'status'  => 'error',
      'message' => 'Session customer tidak ditemukan atau telah kedaluwarsa.'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'GET':
      $action = $_GET['action'] ?? 'list';
      switch ($action) {
         case 'summary':
            getExpiredSummary($id_customer);
            break;
         case 'detail':
            getExpiredDetail($id_customer);
            break;
         case 'list':
         default:
            getExpiredList($id_customer);
            break;
      }
      break;

   default:
      echo json_encode([
         'status'  => 'error',
         'message' => 'Metode HTTP tidak didukung.'
      ]);
      break;
}

/**
 * Helper: Hitung selisih tahun, bulan, hari secara presisi
 */
function calculateExpiryDifference($expiryDateStr)
{
   if (empty($expiryDateStr) || $expiryDateStr === '0000-00-00') {
      return [
         'kategori'         => 'lainnya',
         'kategori_label'   => 'Lainnya / Belum Diatur',
         'kategori_class'   => 'secondary',
         'badge_html'       => '<span class="badge bg-secondary">Belum Diatur</span>',
         'years'            => null,
         'months'           => null,
         'days'             => null,
         'total_days'       => null,
         'is_expired'       => false,
         'is_today'         => false,
         'countdown_text'   => 'Tanggal belum diatur',
         'duration_text'    => '-'
      ];
   }

   try {
      $tz = new DateTimeZone('Asia/Jakarta');
      $today = new DateTime('today', $tz);
      $expiry = new DateTime($expiryDateStr, $tz);
   } catch (Throwable $e) {
      return [
         'kategori'         => 'lainnya',
         'kategori_label'   => 'Format Tidak Valid',
         'kategori_class'   => 'secondary',
         'badge_html'       => '<span class="badge bg-secondary">Tidak Valid</span>',
         'years'            => null,
         'months'           => null,
         'days'             => null,
         'total_days'       => null,
         'is_expired'       => false,
         'is_today'         => false,
         'countdown_text'   => 'Tanggal tidak valid',
         'duration_text'    => '-'
      ];
   }

   $diff = $today->diff($expiry);
   // $diff->invert == 1 means expiry < today (already expired)
   $isExpired = ($diff->invert === 1 && $diff->days > 0);
   $isToday   = ($diff->days === 0);
   $totalDays = $isExpired ? -((int) $diff->days) : (int) $diff->days;

   $years  = (int) $diff->y;
   $months = (int) $diff->m;
   $days   = (int) $diff->d;

   $parts = [];
   if ($years > 0) {
      $parts[] = $years . ' Thn';
   }
   if ($months > 0) {
      $parts[] = $months . ' Bln';
   }
   if ($days > 0 || empty($parts)) {
      $parts[] = $days . ' Hari';
   }
   $durationText = implode(' ', $parts);

   /**
    * Kategori Expired:
    * 1. Aman: > 180 hari (> 6 bulan)
    * 2. Risiko: 0 s/d 180 hari (≤ 6 bulan)
    * 3. Kadaluarsa / Lainnya: < 0 hari (sudah lewat) atau belum terdata
    */
   if ($isExpired) {
      $kategori       = 'kadaluarsa';
      $kategoriLabel  = 'Kadaluarsa';
      $kategoriClass  = 'danger';
      $badgeHtml      = '<span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>Kadaluarsa</span>';
      $countdownText  = 'Lewat ' . $durationText . ' lalu';
   } elseif ($isToday) {
      $kategori       = 'risiko';
      $kategoriLabel  = 'Kadaluarsa Hari Ini';
      $kategoriClass  = 'warning';
      $badgeHtml      = '<span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Hari Ini</span>';
      $countdownText  = 'Kadaluarsa Hari Ini';
   } elseif ($totalDays <= 180) { // Kurang dari atau sama dengan 6 bulan
      $kategori       = 'risiko';
      $kategoriLabel  = 'Risiko (≤ 6 Bulan)';
      $kategoriClass  = 'warning';
      $badgeHtml      = '<span class="badge bg-warning text-dark"><i class="ti ti-alert-circle me-1"></i>Risiko</span>';
      $countdownText  = $durationText . ' lagi';
   } else {
      $kategori       = 'aman';
      $kategoriLabel  = 'Aman (> 6 Bulan)';
      $kategoriClass  = 'success';
      $badgeHtml      = '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Aman</span>';
      $countdownText  = $durationText . ' lagi';
   }

   return [
      'kategori'         => $kategori,
      'kategori_label'   => $kategoriLabel,
      'kategori_class'   => $kategoriClass,
      'badge_html'       => $badgeHtml,
      'years'            => $years,
      'months'           => $months,
      'days'             => $days,
      'total_days'       => $totalDays,
      'is_expired'       => $isExpired,
      'is_today'         => $isToday,
      'countdown_text'   => $countdownText,
      'duration_text'    => $durationText
   ];
}

/**
 * Ambil daftar obat dan status kedaluwarsanya
 */
function getExpiredList($id_customer)
{
   global $koneksi;

   $kategoriFilter     = $_GET['kategori'] ?? 'all'; // all, aman, risiko, kadaluarsa, lainnya, kadaluarsa_lainnya
   $kategoriObatFilter = $_GET['category_obat'] ?? '';
   $fromDateFilter     = $_GET['fromDate'] ?? '';
   $toDateFilter       = $_GET['toDate'] ?? '';

   /**
    * Query gabungan:
    * 1. Data dari pharmacy_stock_penerimaan (setiap batch penerimaan dengan tanggal expired)
    * 2. Data master ms_pharmacy yang belum memiliki batch (kategori lainnya)
    */
   $sql = "
      SELECT 
         psp.id_penerimaan,
         psp.id_pharmacy,
         psp.id_customer,
         psp.tanggal AS tanggal_penerimaan,
         psp.nomor_faktur,
         psp.supplier,
         psp.batch,
         psp.expired,
         COALESCE(psp.jumlah, 0) AS qty_masuk,
         COALESCE(psp.harga_beli, 0) AS harga_beli,
         psp.keterangan,
         psp.created_at,
         mp.pharmacy_code,
         mp.pharmacy_name_generic,
         mp.pharmacy_name_trade,
         mp.pharmacy_category,
         mp.pharmacy_sub_category,
         mp.pharmcy_golongan,
         mp.pharmacy_unit,
         mp.pharmacy_kemasan,
         COALESCE(mp.stok_min, 0) AS stok_min,
         COALESCE(mp.stok_max, 0) AS stok_max,
         COALESCE(mp.pharmacy_stock, 0) AS stok_master
      FROM pharmacy_stock_penerimaan psp
      LEFT JOIN ms_pharmacy mp 
         ON CAST(mp.id_pharmacy AS CHAR) = CAST(psp.id_pharmacy AS CHAR)
         AND CAST(mp.id_customer AS CHAR) = CAST(psp.id_customer AS CHAR)
      WHERE CAST(psp.id_customer AS CHAR) = CAST(? AS CHAR)

      UNION ALL

      SELECT 
         NULL AS id_penerimaan,
         mp.id_pharmacy,
         mp.id_customer,
         NULL AS tanggal_penerimaan,
         NULL AS nomor_faktur,
         mp.pharmacy_supplier AS supplier,
         '-' AS batch,
         NULL AS expired,
         COALESCE(mp.pharmacy_stock, 0) AS qty_masuk,
         COALESCE(mp.pharmacy_buy, 0) AS harga_beli,
         'Stok Master (Belum ada data penerimaan batch)' AS keterangan,
         NULL AS created_at,
         mp.pharmacy_code,
         mp.pharmacy_name_generic,
         mp.pharmacy_name_trade,
         mp.pharmacy_category,
         mp.pharmacy_sub_category,
         mp.pharmcy_golongan,
         mp.pharmacy_unit,
         mp.pharmacy_kemasan,
         COALESCE(mp.stok_min, 0) AS stok_min,
         COALESCE(mp.stok_max, 0) AS stok_max,
         COALESCE(mp.pharmacy_stock, 0) AS stok_master
      FROM ms_pharmacy mp
      WHERE CAST(mp.id_customer AS CHAR) = CAST(? AS CHAR)
        AND NOT EXISTS (
            SELECT 1 FROM pharmacy_stock_penerimaan psp 
            WHERE CAST(psp.id_pharmacy AS CHAR) = CAST(mp.id_pharmacy AS CHAR) 
              AND CAST(psp.id_customer AS CHAR) = CAST(mp.id_customer AS CHAR)
        )
   ";

   $stmt = $koneksi->prepare($sql);
   if (!$stmt) {
      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mempersiapkan query: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param('ss', $id_customer, $id_customer);
   $stmt->execute();
   $result = $stmt->get_result();

   $allRows = [];
   $summary = [
      'total'              => 0,
      'aman'               => 0,
      'risiko'             => 0,
      'kadaluarsa'         => 0,
      'lainnya'            => 0,
      'kadaluarsa_lainnya' => 0
   ];

   while ($row = $result->fetch_assoc()) {
      $diffInfo = calculateExpiryDifference($row['expired']);
      $kategori = $diffInfo['kategori'];

      // Hitung statistik summary
      $summary['total']++;
      if ($kategori === 'aman') {
         $summary['aman']++;
      } elseif ($kategori === 'risiko') {
         $summary['risiko']++;
      } elseif ($kategori === 'kadaluarsa') {
         $summary['kadaluarsa']++;
         $summary['kadaluarsa_lainnya']++;
      } else {
         $summary['lainnya']++;
         $summary['kadaluarsa_lainnya']++;
      }

      $item = array_merge($row, [
         'kategori'       => $kategori,
         'kategori_label' => $diffInfo['kategori_label'],
         'kategori_class' => $diffInfo['kategori_class'],
         'badge_html'     => $diffInfo['badge_html'],
         'years'          => $diffInfo['years'],
         'months'         => $diffInfo['months'],
         'days'           => $diffInfo['days'],
         'total_days'     => $diffInfo['total_days'],
         'is_expired'     => $diffInfo['is_expired'],
         'is_today'       => $diffInfo['is_today'],
         'countdown_text' => $diffInfo['countdown_text'],
         'duration_text'  => $diffInfo['duration_text'],
         'nama_obat'      => trim(($row['pharmacy_name_generic'] ?? '') . ' / ' . ($row['pharmacy_name_trade'] ?? ''), ' / ')
      ]);

      // Filter kategori jika diminta
      if ($kategoriFilter !== 'all') {
         if ($kategoriFilter === 'kadaluarsa_lainnya') {
            if ($kategori !== 'kadaluarsa' && $kategori !== 'lainnya') {
               continue;
            }
         } elseif ($kategori !== $kategoriFilter) {
            continue;
         }
      }

      // Filter kategori obat (Obat, BMHP, Alkes)
      if (!empty($kategoriObatFilter) && strcasecmp($row['pharmacy_category'] ?? '', $kategoriObatFilter) !== 0) {
         continue;
      }

      // Filter tanggal expired jika diisi
      if (!empty($fromDateFilter) && !empty($row['expired']) && $row['expired'] < $fromDateFilter) {
         continue;
      }
      if (!empty($toDateFilter) && !empty($row['expired']) && $row['expired'] > $toDateFilter) {
         continue;
      }

      $allRows[] = $item;
   }

   $stmt->close();

   // Sortir: prioritas Kadaluarsa & Risiko di atas (urgent first / FEFO), lalu Aman, lalu Lainnya
   usort($allRows, function ($a, $b) {
      $priority = [
         'kadaluarsa' => 1,
         'risiko'     => 2,
         'aman'       => 3,
         'lainnya'    => 4
      ];

      $pA = $priority[$a['kategori']] ?? 99;
      $pB = $priority[$b['kategori']] ?? 99;

      if ($pA !== $pB) {
         return $pA - $pB;
      }

      // Jika sama-sama memiliki total_days, urutkan dari sisa hari terkecil (tercepat expired)
      if ($a['total_days'] !== null && $b['total_days'] !== null) {
         return $a['total_days'] - $b['total_days'];
      }

      return strcmp($a['pharmacy_name_generic'] ?? '', $b['pharmacy_name_generic'] ?? '');
   });

   echo json_encode([
      'status'  => 'success',
      'summary' => $summary,
      'data'    => $allRows
   ], JSON_UNESCAPED_UNICODE);
}

/**
 * Ringkasan jumlah kedaluwarsa untuk widget / dashboard
 */
function getExpiredSummary($id_customer)
{
   global $koneksi;

   $sql = "
      SELECT 
         psp.expired
      FROM pharmacy_stock_penerimaan psp
      WHERE CAST(psp.id_customer AS CHAR) = CAST(? AS CHAR)

      UNION ALL

      SELECT 
         NULL AS expired
      FROM ms_pharmacy mp
      WHERE CAST(mp.id_customer AS CHAR) = CAST(? AS CHAR)
        AND NOT EXISTS (
            SELECT 1 FROM pharmacy_stock_penerimaan psp 
            WHERE CAST(psp.id_pharmacy AS CHAR) = CAST(mp.id_pharmacy AS CHAR) 
              AND CAST(psp.id_customer AS CHAR) = CAST(mp.id_customer AS CHAR)
        )
   ";

   $stmt = $koneksi->prepare($sql);
   if (!$stmt) {
      echo json_encode(['status' => 'error', 'message' => $koneksi->error]);
      return;
   }

   $stmt->bind_param('ss', $id_customer, $id_customer);
   $stmt->execute();
   $res = $stmt->get_result();

   $summary = [
      'total'              => 0,
      'aman'               => 0,
      'risiko'             => 0,
      'kadaluarsa'         => 0,
      'lainnya'            => 0,
      'kadaluarsa_lainnya' => 0
   ];

   while ($row = $res->fetch_assoc()) {
      $diff = calculateExpiryDifference($row['expired']);
      $kat  = $diff['kategori'];

      $summary['total']++;
      if ($kat === 'aman') {
         $summary['aman']++;
      } elseif ($kat === 'risiko') {
         $summary['risiko']++;
      } elseif ($kat === 'kadaluarsa') {
         $summary['kadaluarsa']++;
         $summary['kadaluarsa_lainnya']++;
      } else {
         $summary['lainnya']++;
         $summary['kadaluarsa_lainnya']++;
      }
   }

   $stmt->close();

   echo json_encode([
      'status'  => 'success',
      'summary' => $summary
   ]);
}

/**
 * Detail satu batch / obat
 */
function getExpiredDetail($id_customer)
{
   global $koneksi;

   $idPenerimaan = $_GET['id_penerimaan'] ?? null;
   $idPharmacy   = $_GET['id_pharmacy'] ?? null;

   if (!$idPenerimaan && !$idPharmacy) {
      echo json_encode([
         'status'  => 'error',
         'message' => 'ID penerimaan atau ID pharmacy diperlukan.'
      ]);
      return;
   }

   if ($idPenerimaan) {
      $sql = "
         SELECT 
            psp.*,
            mp.pharmacy_code,
            mp.pharmacy_name_generic,
            mp.pharmacy_name_trade,
            mp.pharmacy_category,
            mp.pharmacy_sub_category,
            mp.pharmcy_golongan,
            mp.pharmacy_unit,
            mp.pharmacy_kemasan,
            mp.stok_min,
            mp.stok_max,
            mp.pharmacy_stock
         FROM pharmacy_stock_penerimaan psp
         LEFT JOIN ms_pharmacy mp 
            ON CAST(mp.id_pharmacy AS CHAR) = CAST(psp.id_pharmacy AS CHAR)
            AND CAST(mp.id_customer AS CHAR) = CAST(psp.id_customer AS CHAR)
         WHERE psp.id_penerimaan = ?
           AND CAST(psp.id_customer AS CHAR) = CAST(? AS CHAR)
         LIMIT 1
      ";
      $stmt = $koneksi->prepare($sql);
      $stmt->bind_param('is', $idPenerimaan, $id_customer);
   } else {
      $sql = "
         SELECT 
            NULL AS id_penerimaan,
            mp.id_pharmacy,
            mp.id_customer,
            NULL AS tanggal,
            NULL AS nomor_faktur,
            mp.pharmacy_supplier AS supplier,
            '-' AS batch,
            NULL AS expired,
            COALESCE(mp.pharmacy_stock, 0) AS jumlah,
            COALESCE(mp.pharmacy_buy, 0) AS harga_beli,
            'Stok Master' AS keterangan,
            NULL AS created_at,
            NULL AS created_by,
            mp.pharmacy_code,
            mp.pharmacy_name_generic,
            mp.pharmacy_name_trade,
            mp.pharmacy_category,
            mp.pharmacy_sub_category,
            mp.pharmcy_golongan,
            mp.pharmacy_unit,
            mp.pharmacy_kemasan,
            mp.stok_min,
            mp.stok_max,
            mp.pharmacy_stock
         FROM ms_pharmacy mp
         WHERE mp.id_pharmacy = ?
           AND CAST(mp.id_customer AS CHAR) = CAST(? AS CHAR)
         LIMIT 1
      ";
      $stmt = $koneksi->prepare($sql);
      $stmt->bind_param('is', $idPharmacy, $id_customer);
   }

   $stmt->execute();
   $res = $stmt->get_result();
   $data = $res->fetch_assoc();
   $stmt->close();

   if (!$data) {
      echo json_encode([
         'status'  => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      return;
   }

   $diffInfo = calculateExpiryDifference($data['expired']);
   $data = array_merge($data, $diffInfo);

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ]);
}
