<?php

$title = 'Riwayat Log Update';

require '../../controller/view.php';

/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{
   return htmlspecialchars(
      $value ?? '',
      ENT_QUOTES,
      'UTF-8'
   );
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = isset($_GET['search'])
   ? trim($_GET['search'])
   : '';

$type = isset($_GET['type'])
   ? trim($_GET['type'])
   : '';


/*
|--------------------------------------------------------------------------
| QUERY LOG UPDATE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id_update,
        title,
        description,
        type,
        version,
        created_at,
        is_read
    FROM system_update_log
    WHERE 1=1
";

$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

   $sql .= "
        AND (
            title LIKE ?
            OR description LIKE ?
            OR version LIKE ?
        )
    ";

   $keyword = '%' . $search . '%';

   $params[] = $keyword;
   $params[] = $keyword;
   $params[] = $keyword;

   $types .= "sss";
}


/*
|--------------------------------------------------------------------------
| FILTER TYPE
|--------------------------------------------------------------------------
*/

if ($type !== '') {

   $sql .= "
        AND type = ?
    ";

   $params[] = $type;

   $types .= "s";
}


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY created_at DESC
";


$stmt = $koneksi->prepare($sql);


if (!empty($params)) {

   $stmt->bind_param(
      $types,
      ...$params
   );
}


$stmt->execute();

$result = $stmt->get_result();


$updates = [];

while ($row = $result->fetch_assoc()) {

   $updates[] = $row;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| TOTAL UPDATE
|--------------------------------------------------------------------------
*/

$totalUpdateQuery = mysqli_query(
   $koneksi,
   "SELECT COUNT(*) AS total FROM system_update_log"
);

$totalUpdateData = mysqli_fetch_assoc(
   $totalUpdateQuery
);

$totalUpdate = (int) (
   $totalUpdateData['total'] ?? 0
);


/*
|--------------------------------------------------------------------------
| TOTAL FEATURE
|--------------------------------------------------------------------------
*/

$featureQuery = mysqli_query(
   $koneksi,
   "
    SELECT COUNT(*) AS total
    FROM system_update_log
    WHERE type = 'feature'
    "
);

$featureData = mysqli_fetch_assoc(
   $featureQuery
);

$totalFeature = (int) (
   $featureData['total'] ?? 0
);


/*
|--------------------------------------------------------------------------
| TOTAL IMPROVEMENT
|--------------------------------------------------------------------------
*/

$improvementQuery = mysqli_query(
   $koneksi,
   "
    SELECT COUNT(*) AS total
    FROM system_update_log
    WHERE type = 'improvement'
    "
);

$improvementData = mysqli_fetch_assoc(
   $improvementQuery
);

$totalImprovement = (int) (
   $improvementData['total'] ?? 0
);


/*
|--------------------------------------------------------------------------
| TOTAL BUG / FIX
|--------------------------------------------------------------------------
*/

$fixQuery = mysqli_query(
   $koneksi,
   "
    SELECT COUNT(*) AS total
    FROM system_update_log
    WHERE type IN ('bug', 'fix')
    "
);

$fixData = mysqli_fetch_assoc(
   $fixQuery
);

$totalFix = (int) (
   $fixData['total'] ?? 0
);


/*
|--------------------------------------------------------------------------
| TYPE BADGE
|--------------------------------------------------------------------------
*/

function getTypeClass($type)
{

   $type = strtolower(
      trim($type ?? 'update')
   );

   switch ($type) {

      case 'feature':
         return 'feature';

      case 'improvement':
         return 'improvement';

      case 'bug':
      case 'fix':
         return 'fix';

      case 'security':
         return 'security';

      case 'maintenance':
         return 'maintenance';

      default:
         return 'update';
   }
}


/*
|--------------------------------------------------------------------------
| TYPE ICON
|--------------------------------------------------------------------------
*/

function getTypeIcon($type)
{

   $type = strtolower(
      trim($type ?? 'update')
   );

   switch ($type) {

      case 'feature':
         return 'ti ti-sparkles';

      case 'improvement':
         return 'ti ti-trending-up';

      case 'bug':
      case 'fix':
         return 'ti ti-bug';

      case 'security':
         return 'ti ti-shield-check';

      case 'maintenance':
         return 'ti ti-tool';

      default:
         return 'ti ti-refresh';
   }
}


/*
|--------------------------------------------------------------------------
| FORMAT DATE
|--------------------------------------------------------------------------
*/

function formatDateIndonesia($date)
{

   if (!$date) {
      return '-';
   }

   $timestamp = strtotime($date);

   if (!$timestamp) {
      return $date;
   }

   $bulan = [
      1 => 'Jan',
      2 => 'Feb',
      3 => 'Mar',
      4 => 'Apr',
      5 => 'Mei',
      6 => 'Jun',
      7 => 'Jul',
      8 => 'Agu',
      9 => 'Sep',
      10 => 'Okt',
      11 => 'Nov',
      12 => 'Des'
   ];

   return date('d', $timestamp)
      . ' '
      . $bulan[(int) date('m', $timestamp)]
      . ' '
      . date('Y H:i', $timestamp);
}

?>

<!doctype html>

<html lang="id">

<head>

   <base href="../../">

   <?php
   require '../../assets/template/head.php';
   ?>

   <style>
      /* =========================================================
           PAGE
        ========================================================= */

      .log-page-header {
         margin-bottom: 24px;
      }

      .log-page-title {
         font-size: 24px;
         font-weight: 700;
         color: #111827;
         margin-bottom: 5px;
      }

      .log-page-subtitle {
         color: #64748b;
         font-size: 14px;
      }


      /* =========================================================
           STAT CARD
        ========================================================= */

      .log-stat-card {
         background: #fff;
         border: 1px solid #e5e7eb;
         border-radius: 16px;
         padding: 20px;
         height: 100%;

         transition: all .2s ease;
      }

      .log-stat-card:hover {
         transform: translateY(-2px);
         box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
      }

      .log-stat-icon {
         width: 44px;
         height: 44px;

         display: flex;
         align-items: center;
         justify-content: center;

         border-radius: 12px;

         background: #eef2ff;
         color: #4f46e5;

         font-size: 21px;

         margin-bottom: 14px;
      }

      .log-stat-number {
         font-size: 25px;
         font-weight: 700;
         color: #111827;
         line-height: 1;
      }

      .log-stat-label {
         margin-top: 7px;
         color: #64748b;
         font-size: 13px;
      }


      /* =========================================================
           FILTER
        ========================================================= */

      .log-filter-card {
         background: #fff;
         border: 1px solid #e5e7eb;
         border-radius: 16px;
         padding: 18px;
         margin-bottom: 20px;
      }

      .log-search-wrapper {
         position: relative;
      }

      .log-search-wrapper i {
         position: absolute;
         left: 14px;
         top: 50%;
         transform: translateY(-50%);

         color: #94a3b8;

         font-size: 18px;

         z-index: 2;
      }

      .log-search-input {
         padding-left: 42px;

         height: 44px;

         border-radius: 10px;
         border: 1px solid #e2e8f0;

         color: #1e293b;
      }

      .log-search-input:focus {
         border-color: #6366f1;
         box-shadow: 0 0 0 3px rgba(99, 102, 241, .10);
      }

      .log-type-select {
         height: 44px;
         border-radius: 10px;
         border: 1px solid #e2e8f0;
      }


      /* =========================================================
           UPDATE CARD
        ========================================================= */

      .history-item {
         position: relative;

         display: flex;
         gap: 18px;

         background: #fff;

         border: 1px solid #e5e7eb;
         border-radius: 16px;

         padding: 22px;

         margin-bottom: 16px;

         transition: all .2s ease;
      }

      .history-item:hover {
         transform: translateY(-2px);

         border-color: #c7d2fe;

         box-shadow:
            0 10px 28px rgba(15, 23, 42, .08);
      }


      /* =========================================================
           ICON
        ========================================================= */

      .history-icon {
         flex: 0 0 46px;

         width: 46px;
         height: 46px;

         display: flex;
         align-items: center;
         justify-content: center;

         border-radius: 13px;

         background: #eef2ff;
         color: #4f46e5;

         font-size: 21px;
      }


      /* =========================================================
           CONTENT
        ========================================================= */

      .history-content {
         flex: 1;
         min-width: 0;
      }

      .history-top {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;

         gap: 15px;

         margin-bottom: 8px;
      }

      .history-title {
         margin: 0;

         font-size: 17px;
         font-weight: 700;

         color: #111827;
         line-height: 1.4;
      }

      .history-date {
         flex-shrink: 0;

         color: #64748b;

         font-size: 12px;
         font-weight: 500;

         white-space: nowrap;
      }

      .history-description {
         margin-top: 12px;
         margin-bottom: 14px;

         color: #475569;

         font-size: 14px;
         line-height: 1.65;
      }


      /* =========================================================
           META
        ========================================================= */

      .history-meta {
         display: flex;
         flex-wrap: wrap;
         align-items: center;
         gap: 7px;
      }

      .history-type {
         display: inline-flex;
         align-items: center;

         padding: 5px 10px;

         border-radius: 7px;

         font-size: 10px;
         font-weight: 800;

         text-transform: uppercase;
         letter-spacing: .3px;
      }

      .history-type.feature {
         background: #dcfce7;
         color: #166534;
      }

      .history-type.improvement {
         background: #fef3c7;
         color: #92400e;
      }

      .history-type.fix {
         background: #fee2e2;
         color: #991b1b;
      }

      .history-type.security {
         background: #ede9fe;
         color: #5b21b6;
      }

      .history-type.maintenance {
         background: #e0f2fe;
         color: #075985;
      }

      .history-type.update {
         background: #e0e7ff;
         color: #3730a3;
      }

      .history-version {
         display: inline-flex;
         align-items: center;

         padding: 5px 9px;

         border-radius: 7px;

         background: #f1f5f9;
         color: #475569;

         font-size: 10px;
         font-weight: 700;
      }


      /* =========================================================
           DETAIL BUTTON
        ========================================================= */

      .history-detail-btn {
         display: inline-flex;
         align-items: center;
         gap: 5px;

         margin-top: 5px;

         padding: 6px 11px;

         border: 1px solid #e2e8f0;
         border-radius: 8px;

         background: #fff;
         color: #475569;

         font-size: 12px;
         font-weight: 600;

         transition: all .2s ease;
      }

      .history-detail-btn:hover {
         border-color: #6366f1;
         background: #eef2ff;
         color: #4f46e5;
      }


      /* =========================================================
           EMPTY
        ========================================================= */

      .log-empty {
         background: #fff;

         border: 1px solid #e5e7eb;
         border-radius: 16px;

         padding: 60px 20px;

         text-align: center;
      }

      .log-empty-icon {
         width: 68px;
         height: 68px;

         display: flex;
         align-items: center;
         justify-content: center;

         margin: 0 auto 18px;

         border-radius: 18px;

         background: #ecfdf5;
         color: #10b981;

         font-size: 30px;
      }


      /* =========================================================
           GUIDE
        ========================================================= */

      .guide-item {
         display: flex;
         gap: 14px;

         padding: 16px;

         border: 1px solid #e5e7eb;
         border-radius: 12px;

         margin-bottom: 12px;

         background: #fff;
      }

      .guide-icon {
         width: 38px;
         height: 38px;

         flex-shrink: 0;

         display: flex;
         align-items: center;
         justify-content: center;

         border-radius: 10px;

         background: #eef2ff;
         color: #4f46e5;
      }

      .guide-title {
         font-weight: 700;
         color: #1e293b;
         margin-bottom: 3px;
      }

      .guide-text {
         color: #64748b;
         font-size: 13px;
         line-height: 1.6;
      }


      /* =========================================================
           RESPONSIVE
        ========================================================= */

      @media (max-width: 768px) {

         .history-top {
            display: block;
         }

         .history-date {
            display: block;
            margin-top: 5px;
         }

         .history-item {
            padding: 17px;
            gap: 12px;
         }

         .history-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
            font-size: 18px;
         }

         .log-page-title {
            font-size: 21px;
         }

      }
   </style>

</head>


<body>

   <!--
    |--------------------------------------------------------------------------
    | BODY WRAPPER
    |--------------------------------------------------------------------------
    -->

   <div
      class="page-wrapper"
      id="main-wrapper"
      data-layout="vertical"
      data-navbarbg="skin6"
      data-sidebartype="full"
      data-sidebar-position="fixed"
      data-header-position="fixed">


      <!-- SIDEBAR -->

      <?php
      require '../admin/sidebar.php';
      ?>


      <!-- MAIN -->

      <div class="body-wrapper">


         <!-- NAVBAR -->

         <?php
         require '../admin/navbar.php';
         ?>


         <!-- CONTENT -->

         <div class="body-wrapper-inner">

            <div class="container-fluid">


               <!-- =====================================================
                         PAGE HEADER
                    ====================================================== -->

               <div class="log-page-header">

                  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                     <div>

                        <div class="log-page-title">

                           <i class="ti ti-history text-primary me-2"></i>

                           Riwayat Log Update

                        </div>

                        <div class="log-page-subtitle">

                           Lihat seluruh riwayat pembaruan dan perubahan sistem Medisafe.

                        </div>

                     </div>


                     <div class="d-flex gap-2">

                        <!-- PANDUAN -->

                        <button
                           type="button"
                           class="btn btn-outline-primary"
                           data-bs-toggle="modal"
                           data-bs-target="#guideModal">

                           <i class="ti ti-book-2 me-1"></i>

                           Panduan

                        </button>

                     </div>

                  </div>

               </div>



               <!-- =====================================================
                         STATISTICS
                    ====================================================== -->

               <div class="row g-3 mb-4">


                  <!-- TOTAL -->

                  <div class="col-xl-3 col-md-6">

                     <div class="log-stat-card">

                        <div class="log-stat-icon">

                           <i class="ti ti-history"></i>

                        </div>

                        <div class="log-stat-number">

                           <?= number_format($totalUpdate) ?>

                        </div>

                        <div class="log-stat-label">

                           Total Update

                        </div>

                     </div>

                  </div>


                  <!-- FEATURE -->

                  <div class="col-xl-3 col-md-6">

                     <div class="log-stat-card">

                        <div class="log-stat-icon">

                           <i class="ti ti-sparkles"></i>

                        </div>

                        <div class="log-stat-number">

                           <?= number_format($totalFeature) ?>

                        </div>

                        <div class="log-stat-label">

                           Fitur Baru

                        </div>

                     </div>

                  </div>


                  <!-- IMPROVEMENT -->

                  <div class="col-xl-3 col-md-6">

                     <div class="log-stat-card">

                        <div class="log-stat-icon">

                           <i class="ti ti-trending-up"></i>

                        </div>

                        <div class="log-stat-number">

                           <?= number_format($totalImprovement) ?>

                        </div>

                        <div class="log-stat-label">

                           Improvement

                        </div>

                     </div>

                  </div>


                  <!-- FIX -->

                  <div class="col-xl-3 col-md-6">

                     <div class="log-stat-card">

                        <div class="log-stat-icon">

                           <i class="ti ti-bug"></i>

                        </div>

                        <div class="log-stat-number">

                           <?= number_format($totalFix) ?>

                        </div>

                        <div class="log-stat-label">

                           Bug / Fix

                        </div>

                     </div>

                  </div>

               </div>



               <!-- =====================================================
                         FILTER
                    ====================================================== -->

               <div class="log-filter-card">

                  <form
                     method="GET"
                     action="">

                     <div class="row g-2 align-items-center">


                        <!-- SEARCH -->

                        <div class="col-lg-7">

                           <div class="log-search-wrapper">

                              <i class="ti ti-search"></i>

                              <input
                                 type="text"
                                 name="search"
                                 class="form-control log-search-input"
                                 placeholder="Cari update, fitur, deskripsi atau versi..."
                                 value="<?= e($search) ?>">

                           </div>

                        </div>


                        <!-- TYPE -->

                        <div class="col-lg-3">

                           <select
                              name="type"
                              class="form-select log-type-select">

                              <option value="">
                                 Semua Tipe
                              </option>

                              <option
                                 value="feature"
                                 <?= $type === 'feature' ? 'selected' : '' ?>>
                                 Feature
                              </option>

                              <option
                                 value="improvement"
                                 <?= $type === 'improvement' ? 'selected' : '' ?>>
                                 Improvement
                              </option>

                              <option
                                 value="bug"
                                 <?= $type === 'bug' ? 'selected' : '' ?>>
                                 Bug
                              </option>

                              <option
                                 value="fix"
                                 <?= $type === 'fix' ? 'selected' : '' ?>>
                                 Fix
                              </option>

                              <option
                                 value="security"
                                 <?= $type === 'security' ? 'selected' : '' ?>>
                                 Security
                              </option>

                              <option
                                 value="maintenance"
                                 <?= $type === 'maintenance' ? 'selected' : '' ?>>
                                 Maintenance
                              </option>

                              <option
                                 value="update"
                                 <?= $type === 'update' ? 'selected' : '' ?>>
                                 Update
                              </option>

                           </select>

                        </div>


                        <!-- BUTTON -->

                        <div class="col-lg-2">

                           <div class="d-flex gap-2">

                              <button
                                 type="submit"
                                 class="btn btn-primary w-100">

                                 <i class="ti ti-search me-1"></i>

                                 Cari

                              </button>


                              <?php if ($search !== '' || $type !== ''): ?>

                                 <a
                                    href="module/admin/index.php"
                                    class="btn btn-light"
                                    title="Reset">

                                    <i class="ti ti-refresh"></i>

                                 </a>

                              <?php endif; ?>

                           </div>

                        </div>

                     </div>

                  </form>

               </div>



               <!-- =====================================================
                         RESULT INFO
                    ====================================================== -->

               <div class="d-flex justify-content-between align-items-center mb-3">

                  <div>

                     <span class="fw-semibold text-dark">

                        Riwayat Pembaruan

                     </span>

                     <span class="text-muted ms-1">

                        <?= count($updates) ?> data ditemukan

                     </span>

                  </div>


                  <?php if ($search !== '' || $type !== ''): ?>

                     <span class="badge bg-light text-dark">

                        Filter aktif

                     </span>

                  <?php endif; ?>

               </div>



               <!-- =====================================================
                         UPDATE LIST
                    ====================================================== -->

               <?php if (empty($updates)): ?>


                  <div class="log-empty">

                     <div class="log-empty-icon">

                        <i class="ti ti-search-off"></i>

                     </div>

                     <h6 class="fw-bold text-dark">

                        Tidak ada update ditemukan

                     </h6>

                     <p class="text-muted mb-0">

                        Coba gunakan kata kunci atau filter yang berbeda.

                     </p>

                  </div>


               <?php else: ?>


                  <?php foreach ($updates as $item): ?>

                     <?php

                     $itemType = strtolower(
                        trim($item['type'] ?? 'update')
                     );

                     $typeClass = getTypeClass(
                        $itemType
                     );

                     $typeIcon = getTypeIcon(
                        $itemType
                     );

                     ?>


                     <div class="history-item">


                        <!-- ICON -->

                        <div class="history-icon">

                           <i class="<?= e($typeIcon) ?>"></i>

                        </div>


                        <!-- CONTENT -->

                        <div class="history-content">


                           <!-- TITLE + DATE -->

                           <div class="history-top">

                              <div>

                                 <h6 class="history-title">

                                    <?= e(
                                       $item['title']
                                          ?: 'Update Sistem'
                                    ) ?>

                                 </h6>

                              </div>


                              <div class="history-date">

                                 <i class="ti ti-calendar-event me-1"></i>

                                 <?= e(
                                    formatDateIndonesia(
                                       $item['created_at']
                                    )
                                 ) ?>

                              </div>

                           </div>


                           <!-- META -->

                           <div class="history-meta">


                              <span class="history-type <?= e($typeClass) ?>">

                                 <?= e(
                                    strtoupper(
                                       $itemType
                                    )
                                 ) ?>

                              </span>


                              <?php if (!empty($item['version'])): ?>

                                 <span class="history-version">

                                    <i class="ti ti-tag me-1"></i>

                                    v<?= e(
                                          $item['version']
                                       ) ?>

                                 </span>

                              <?php endif; ?>


                           </div>


                           <!-- DESCRIPTION -->

                           <div class="history-description">

                              <?= e(
                                 $item['description']
                                    ?: 'Tidak ada deskripsi update.'
                              ) ?>

                           </div>


                           <!-- DETAIL -->

                           <button
                              type="button"
                              class="history-detail-btn"
                              data-bs-toggle="modal"
                              data-bs-target="#detailUpdateModal"
                              data-id="<?= (int) $item['id_update'] ?>"
                              data-title="<?= e($item['title']) ?>"
                              data-description="<?= e($item['description']) ?>"
                              data-type="<?= e($itemType) ?>"
                              data-version="<?= e($item['version']) ?>"
                              data-date="<?= e(formatDateIndonesia($item['created_at'])) ?>">

                              <i class="ti ti-eye"></i>

                              Lihat Detail

                           </button>


                        </div>

                     </div>


                  <?php endforeach; ?>


               <?php endif; ?>


            </div>

         </div>

      </div>

   </div>



   <!-- =============================================================
         MODAL DETAIL UPDATE
    ============================================================= -->

   <div
      class="modal fade"
      id="detailUpdateModal"
      tabindex="-1"
      aria-hidden="true">

      <div class="modal-dialog modal-lg modal-dialog-centered">

         <div class="modal-content border-0 shadow-lg">

            <div class="modal-header">

               <div>

                  <h5
                     class="modal-title fw-bold"
                     id="detailUpdateTitle">
                     Detail Update
                  </h5>

                  <small
                     class="text-muted"
                     id="detailUpdateDate">
                     -
                  </small>

               </div>


               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"></button>

            </div>


            <div class="modal-body">


               <!-- META -->

               <div
                  class="history-meta mb-3"
                  id="detailUpdateMeta">
               </div>


               <!-- DESCRIPTION -->

               <div
                  style="
                            background:#f8fafc;
                            border:1px solid #e2e8f0;
                            border-radius:12px;
                            padding:18px;
                            color:#334155;
                            line-height:1.7;
                            font-size:14px;
                        "
                  id="detailUpdateDescription">
                  -
               </div>


            </div>


            <div class="modal-footer">

               <button
                  type="button"
                  class="btn btn-light"
                  data-bs-dismiss="modal">

                  Tutup

               </button>

            </div>

         </div>

      </div>

   </div>



   <!-- =============================================================
         MODAL PANDUAN
    ============================================================= -->

   <div
      class="modal fade"
      id="guideModal"
      tabindex="-1"
      aria-hidden="true">

      <div class="modal-dialog modal-lg modal-dialog-centered">

         <div class="modal-content border-0 shadow-lg">

            <div class="modal-header">

               <div>

                  <h5 class="modal-title fw-bold">

                     <i class="ti ti-book-2 text-primary me-2"></i>

                     Panduan Riwayat Log Update

                  </h5>

                  <small class="text-muted">

                     Cara menggunakan halaman log pembaruan sistem

                  </small>

               </div>


               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"></button>

            </div>


            <div class="modal-body">


               <!-- PANDUAN 1 -->

               <div class="guide-item">

                  <div class="guide-icon">

                     <i class="ti ti-search"></i>

                  </div>

                  <div>

                     <div class="guide-title">

                        Mencari Update

                     </div>

                     <div class="guide-text">

                        Gunakan kolom pencarian untuk mencari
                        berdasarkan judul update, deskripsi,
                        fitur, atau nomor versi.

                     </div>

                  </div>

               </div>


               <!-- PANDUAN 2 -->

               <div class="guide-item">

                  <div class="guide-icon">

                     <i class="ti ti-filter"></i>

                  </div>

                  <div>

                     <div class="guide-title">

                        Filter Berdasarkan Tipe

                     </div>

                     <div class="guide-text">

                        Gunakan filter tipe untuk menampilkan
                        Feature, Improvement, Bug, Fix,
                        Security, Maintenance atau Update.

                     </div>

                  </div>

               </div>


               <!-- PANDUAN 3 -->

               <div class="guide-item">

                  <div class="guide-icon">

                     <i class="ti ti-eye"></i>

                  </div>

                  <div>

                     <div class="guide-title">

                        Lihat Detail

                     </div>

                     <div class="guide-text">

                        Klik tombol "Lihat Detail" untuk membuka
                        informasi lengkap dari setiap pembaruan.

                     </div>

                  </div>

               </div>


               <!-- PANDUAN 4 -->

               <div class="guide-item">

                  <div class="guide-icon">

                     <i class="ti ti-history"></i>

                  </div>

                  <div>

                     <div class="guide-title">

                        Riwayat Sistem

                     </div>

                     <div class="guide-text">

                        Halaman ini menyimpan daftar pembaruan
                        sistem Medisafe berdasarkan tanggal
                        publikasi update.

                     </div>

                  </div>

               </div>


               <!-- INFO -->

               <div class="alert alert-primary border-0 mb-0">

                  <i class="ti ti-info-circle me-1"></i>

                  Pastikan membaca log update sebelum menggunakan
                  fitur baru atau perubahan sistem.

               </div>


            </div>


            <div class="modal-footer">

               <button
                  type="button"
                  class="btn btn-primary"
                  data-bs-dismiss="modal">

                  Mengerti

               </button>

            </div>

         </div>

      </div>

   </div>



   <!-- =============================================================
         JAVASCRIPT
    ============================================================= -->

   <script>
      $(document).ready(function() {


         /* =========================================================
            DETAIL UPDATE
         ========================================================= */

         $('#detailUpdateModal').on(
            'show.bs.modal',
            function(event) {

               const button =
                  $(event.relatedTarget);


               const title =
                  button.data('title') ||
                  'Update Sistem';


               const description =
                  button.data('description') ||
                  'Tidak ada deskripsi update.';


               const type =
                  button.data('type') ||
                  'update';


               const version =
                  button.data('version') ||
                  '';


               const date =
                  button.data('date') ||
                  '-';


               /*
               |--------------------------------------------------------------------------
               | TITLE
               |--------------------------------------------------------------------------
               */

               $('#detailUpdateTitle')
                  .text(title);


               /*
               |--------------------------------------------------------------------------
               | DATE
               |--------------------------------------------------------------------------
               */

               $('#detailUpdateDate')
                  .html(
                     '<i class="ti ti-calendar-event me-1"></i>' +
                     date
                  );


               /*
               |--------------------------------------------------------------------------
               | META
               |--------------------------------------------------------------------------
               */

               let meta = '';

               meta += `
                    <span class="history-type ${type}">
                        ${type.toUpperCase()}
                    </span>
                `;


               if (version) {

                  meta += `
                        <span class="history-version">
                            <i class="ti ti-tag me-1"></i>
                            v${version}
                        </span>
                    `;

               }


               $('#detailUpdateMeta')
                  .html(meta);


               /*
               |--------------------------------------------------------------------------
               | DESCRIPTION
               |--------------------------------------------------------------------------
               */

               $('#detailUpdateDescription')
                  .text(description);

            }
         );


         /* =========================================================
            AUTO FOCUS SEARCH
         ========================================================= */

         <?php if ($search !== ''): ?>

            const searchInput =
               document.querySelector(
                  'input[name="search"]'
               );

            if (searchInput) {

               searchInput.focus();

               searchInput.setSelectionRange(
                  searchInput.value.length,
                  searchInput.value.length
               );

            }

         <?php endif; ?>


      });
   </script>


   <?php
   require '../../assets/template/footer.php';
   ?>


   <?php
   require '../admin/library.php';
   ?>

</body>

</html>