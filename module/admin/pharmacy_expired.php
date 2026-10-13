<?php
$title = 'Expired Obat';
require '../../controller/view.php';
require '../../utility/env.php';

$env = loadEnv();
$apiUrl = getenv('API_URL');
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    /* Styling Kartu Statistik */
    .stat-card {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 12px;
      transition: all 0.25s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .stat-card.active-filter {
      border-width: 2px;
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
    }

    .stat-card.card-aman {
      background: linear-gradient(135deg, #e8fadf 0%, #ffffff 100%);
      border-left: 5px solid #28a745;
    }

    .stat-card.card-risiko {
      background: linear-gradient(135deg, #fff8e6 0%, #ffffff 100%);
      border-left: 5px solid #ffc107;
    }

    .stat-card.card-kadaluarsa {
      background: linear-gradient(135deg, #fee8e7 0%, #ffffff 100%);
      border-left: 5px solid #dc3545;
    }

    .stat-card.card-total {
      background: linear-gradient(135deg, #eaf2ff 0%, #ffffff 100%);
      border-left: 5px solid #0d6efd;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    /* Badge Realtime */
    .badge-realtime {
      font-size: 0.82rem;
      padding: 0.4rem 0.65rem;
      font-weight: 600;
      letter-spacing: 0.2px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      border-radius: 6px;
    }

    .badge-aman {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }

    .badge-risiko {
      background-color: #fff3cd;
      color: #664d03;
      border: 1px solid #ffecb5;
    }

    .badge-kadaluarsa {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }

    .badge-lainnya {
      background-color: #e2e3e5;
      color: #41464b;
      border: 1px solid #d3d6d8;
    }

    /* Live countdown text */
    .countdown-pill {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }

    .countdown-pill.pill-aman {
      background: #e8fadf;
      color: #1e7e34;
    }

    .countdown-pill.pill-risiko {
      background: #fff3cd;
      color: #856404;
    }

    .countdown-pill.pill-kadaluarsa {
      background: #f8d7da;
      color: #721c24;
    }

    .countdown-pill.pill-lainnya {
      background: #e9ecef;
      color: #495057;
    }

    /* Realtime timer pulse */
    .live-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: #28a745;
      animation: pulse-dot 1.5s infinite;
      vertical-align: middle;
      margin-right: 4px;
    }

    @keyframes pulse-dot {
      0% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
      }
      70% {
        transform: scale(1.1);
        box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
      }
      100% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
      }
    }

    /* Datatable tweak */
    #tableExpired_wrapper {
      padding: 0;
    }

    #tableExpired thead th {
      background-color: #f8f9fa;
      font-weight: 600;
      white-space: nowrap;
    }

    .quick-filter-btn.active {
      font-weight: 700;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    /* Print styles */
    @media print {
      body * {
        visibility: hidden;
      }
      #printArea, #printArea * {
        visibility: visible;
      }
      #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Sidebar Start -->
    <?php require 'sidebar.php'; ?>
    <!-- Sidebar End -->

    <!-- Main wrapper -->
    <div class="body-wrapper">
      
      <!-- Header Start -->
      <?php require 'navbar.php'; ?>
      <!-- Header End -->

      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- Tab Menu Farmasi -->
          <?php require 'menu_farmasi.php'; ?>

          <!-- Judul Halaman & Realtime Status Header -->
          <div class="card mb-3 shadow-none border">
            <div class="card-body p-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-primary fs-7"></iconify-icon>
                    Monitoring Kedaluwarsa Obat Farmasi
                  </h4>
                  <p class="text-muted mb-0 fs-3">
                    Pelacakan masa kadaluarsa obat dan BMHP secara realtime (Tahun, Bulan, Hari) terbagi dalam 3 kategori: <strong>Aman</strong>, <strong>Risiko</strong>, dan <strong>Kadaluarsa / Lainnya</strong>.
                  </p>
                </div>
                <div class="text-end">
                  <div class="badge bg-light text-dark border p-2 fs-3 d-inline-flex align-items-center">
                    <span class="live-dot"></span>
                    <span id="liveClock" class="fw-bold">--:--:-- WIB</span>
                  </div>
                  <div class="fs-2 text-muted mt-1" id="lastUpdatedText">
                    Memuat data...
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 4 KARTU STATISTIK INTERAKTIF -->
          <div class="row g-3 mb-4">
            
            <!-- TOTAL BATCH -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card card-total h-100 mb-0" onclick="applyCategoryFilter('all')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted text-uppercase fs-2 fw-semibold">Total Item / Batch</span>
                    <h3 class="fw-bold my-1 text-primary" id="statTotal">0</h3>
                    <span class="fs-2 text-muted">Seluruh obat terdata</span>
                  </div>
                  <div class="stat-icon bg-primary-subtle text-primary">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- KATEGORI AMAN -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card card-aman h-100 mb-0" onclick="applyCategoryFilter('aman')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-success text-uppercase fs-2 fw-semibold">
                      <i class="ti ti-check me-1"></i>Kategori Aman
                    </span>
                    <h3 class="fw-bold my-1 text-success" id="statAman">0</h3>
                    <span class="fs-2 text-muted">&gt; 6 Bulan (180+ Hari)</span>
                  </div>
                  <div class="stat-icon bg-success-subtle text-success">
                    <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- KATEGORI RISIKO -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card card-risiko h-100 mb-0" onclick="applyCategoryFilter('risiko')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-warning text-uppercase fs-2 fw-semibold">
                      <i class="ti ti-alert-circle me-1"></i>Kategori Risiko
                    </span>
                    <h3 class="fw-bold my-1 text-warning" id="statRisiko">0</h3>
                    <span class="fs-2 text-muted">&le; 6 Bulan (Prioritas FEFO)</span>
                  </div>
                  <div class="stat-icon bg-warning-subtle text-warning">
                    <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- KATEGORI KADALUARSA / LAINNYA -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card card-kadaluarsa h-100 mb-0" onclick="applyCategoryFilter('kadaluarsa_lainnya')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-danger text-uppercase fs-2 fw-semibold">
                      <i class="ti ti-alert-triangle me-1"></i>Kadaluarsa & Lainnya
                    </span>
                    <h3 class="fw-bold my-1 text-danger" id="statKadaluarsaLainnya">0</h3>
                    <span class="fs-2 text-muted" id="statKadaluarsaSub">&le; 0 Hari / Belum Diset</span>
                  </div>
                  <div class="stat-icon bg-danger-subtle text-danger">
                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- CARD UTAMA KONTEN TABEL -->
          <div class="card w-100 shadow-none border">
            <div class="card-body p-4">

              <!-- BAR FILTER & KONTROL -->
              <div class="row g-2 align-items-center mb-3">
                
                <!-- Quick Filter Tabs -->
                <div class="col-lg-7 col-md-12">
                  <div class="btn-group flex-wrap w-100" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm quick-filter-btn active" data-cat="all">
                      Semua (<span id="btnCountAll">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm quick-filter-btn" data-cat="aman">
                      <i class="ti ti-shield-check me-1"></i>Aman (&gt;6 Bln) (<span id="btnCountAman">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm quick-filter-btn" data-cat="risiko">
                      <i class="ti ti-alert-circle me-1"></i>Risiko (&le;6 Bln) (<span id="btnCountRisiko">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm quick-filter-btn" data-cat="kadaluarsa">
                      <i class="ti ti-alert-triangle me-1"></i>Kadaluarsa (<span id="btnCountKadaluarsa">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-filter-btn" data-cat="lainnya">
                      <i class="ti ti-help me-1"></i>Lainnya (<span id="btnCountLainnya">0</span>)
                    </button>
                  </div>
                </div>

                <!-- Tombol Action Kanan -->
                <div class="col-lg-5 col-md-12 text-lg-end">
                  <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-light btn-sm border" id="btnToggleFilterAdv">
                      <i class="ti ti-filter me-1"></i>Filter Lanjutan
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm" id="btnPrint">
                      <i class="ti ti-printer me-1"></i>Cetak Laporan
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnRefresh">
                      <i class="ti ti-refresh me-1"></i>Refresh
                    </button>
                  </div>
                </div>

              </div>

              <!-- PANEL FILTER LANJUTAN (COLLAPSIBLE) -->
              <div class="card bg-light border mb-3 d-none" id="advancedFilterPanel">
                <div class="card-body p-3">
                  <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                      <label class="form-label fs-2 mb-1">Kategori Obat / Item</label>
                      <select class="form-select form-select-sm" id="filterKategoriObat">
                        <option value="">Semua Jenis (Obat, BMHP, Alkes)</option>
                        <option value="Obat">Obat</option>
                        <option value="BMHP">BMHP</option>
                        <option value="Alkes">Alkes</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label fs-2 mb-1">Tanggal Expired Mulai</label>
                      <input type="date" class="form-control form-control-sm" id="filterFromDate">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label fs-2 mb-1">Tanggal Expired Sampai</label>
                      <input type="date" class="form-control form-control-sm" id="filterToDate">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                      <button type="button" class="btn btn-primary btn-sm w-100" id="btnApplyAdvanced">
                        <i class="ti ti-check me-1"></i>Terapkan
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnResetFilter">
                        <i class="ti ti-rotate-clockwise me-1"></i>Reset
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TABEL EXPIRED OBAT -->
              <div class="table-responsive" id="printArea">
                <table class="table table-hover align-middle mb-0 w-100" id="tableExpired">
                  <thead>
                    <tr>
                      <th width="40" class="text-center">No</th>
                      <th width="130" class="text-center">Kategori</th>
                      <th>Nama Obat / BMHP</th>
                      <th>No. Batch</th>
                      <th>Supplier / PBF</th>
                      <th>Tgl Kadaluarsa</th>
                      <th>Hitungan Realtime (Thn / Bln / Hari)</th>
                      <th class="text-center">Stok Masuk</th>
                      <th width="80" class="text-center no-print">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>

            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- MODAL DETAIL BATCH OBAT -->
  <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <iconify-icon icon="solar:document-medicine-bold-duotone" class="text-primary fs-6"></iconify-icon>
            Detail Batch & Status Kedaluwarsa Obat
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="detailContent">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat detail batch...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    const expiredApiUrl = 'controller/pharmacy/pharmacyExpiredController.php';

    let tableExpired = null;
    let currentCategoryFilter = 'all';
    let rawSummary = {};

    // ========================================================
    // REALTIME DIGITAL CLOCK
    // ========================================================
    function updateClock() {
      const now = new Date();
      const options = {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      const timeStr = now.toLocaleTimeString('id-ID', options);
      const dateOptions = {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      };
      const dateStr = now.toLocaleDateString('id-ID', dateOptions);
      $('#liveClock').text(`${dateStr}, ${timeStr} WIB`);
    }

    setInterval(updateClock, 1000);
    updateClock();

    // ========================================================
    // FORMAT TANGGAL INDONESIA
    // ========================================================
    function formatTanggalIndo(dateStr) {
      if (!dateStr || dateStr === '0000-00-00') return '<span class="text-muted fst-italic">Belum diatur</span>';
      try {
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const bulan = [
          '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
          'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        const day = parseInt(parts[2], 10);
        const month = parseInt(parts[1], 10);
        const year = parts[0];
        return `${day} ${bulan[month] || parts[1]} ${year}`;
      } catch (e) {
        return dateStr;
      }
    }

    // ========================================================
    // REALTIME DIFFERENCE CALCULATOR DI JAVASCRIPT
    // ========================================================
    function calculateRealtimeDiff(expiryDateStr) {
      if (!expiryDateStr || expiryDateStr === '0000-00-00') {
        return {
          kategori: 'lainnya',
          kategoriLabel: 'Belum Diatur',
          badgeClass: 'badge-lainnya',
          badgeText: 'Belum Diatur',
          pillClass: 'pill-lainnya',
          icon: 'ti-help',
          diffText: 'Tanggal belum diset',
          totalDays: null,
          years: 0,
          months: 0,
          days: 0,
          isExpired: false
        };
      }

      const now = new Date();
      now.setHours(0, 0, 0, 0);

      const exp = new Date(expiryDateStr + 'T00:00:00');
      if (isNaN(exp.getTime())) {
        return {
          kategori: 'lainnya',
          kategoriLabel: 'Format Salah',
          badgeClass: 'badge-lainnya',
          badgeText: 'Tidak Valid',
          pillClass: 'pill-lainnya',
          icon: 'ti-alert-circle',
          diffText: 'Tanggal tidak valid',
          totalDays: null,
          years: 0,
          months: 0,
          days: 0,
          isExpired: false
        };
      }

      const isExpired = exp < now;
      const isToday = exp.getTime() === now.getTime();

      let start = isExpired ? exp : now;
      let end = isExpired ? now : exp;

      let y = end.getFullYear() - start.getFullYear();
      let m = end.getMonth() - start.getMonth();
      let d = end.getDate() - start.getDate();

      if (d < 0) {
        m--;
        const prevMonthLastDay = new Date(end.getFullYear(), end.getMonth(), 0).getDate();
        d += prevMonthLastDay;
      }
      if (m < 0) {
        y--;
        m += 12;
      }

      const diffMs = exp.getTime() - now.getTime();
      const totalDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

      let parts = [];
      if (y > 0) parts.push(`${y} Thn`);
      if (m > 0) parts.push(`${m} Bln`);
      if (d > 0 || parts.length === 0) parts.push(`${d} Hari`);
      const durationStr = parts.join(' ');

      if (isExpired) {
        return {
          kategori: 'kadaluarsa',
          kategoriLabel: 'Kadaluarsa',
          badgeClass: 'badge-kadaluarsa',
          badgeText: 'Kadaluarsa',
          pillClass: 'pill-kadaluarsa',
          icon: 'ti-alert-triangle',
          diffText: `Lewat ${durationStr} lalu`,
          totalDays: totalDays,
          years: y,
          months: m,
          days: d,
          isExpired: true
        };
      } else if (isToday) {
        return {
          kategori: 'risiko',
          kategoriLabel: 'Hari Ini',
          badgeClass: 'badge-risiko',
          badgeText: 'Hari Ini',
          pillClass: 'pill-risiko',
          icon: 'ti-clock',
          diffText: 'Kadaluarsa Hari Ini',
          totalDays: 0,
          years: 0,
          months: 0,
          days: 0,
          isExpired: false
        };
      } else if (totalDays <= 180) { // <= 6 bulan
        return {
          kategori: 'risiko',
          kategoriLabel: 'Risiko',
          badgeClass: 'badge-risiko',
          badgeText: 'Risiko (≤ 6 Bln)',
          pillClass: 'pill-risiko',
          icon: 'ti-alert-circle',
          diffText: `${durationStr} lagi`,
          totalDays: totalDays,
          years: y,
          months: m,
          days: d,
          isExpired: false
        };
      } else {
        return {
          kategori: 'aman',
          kategoriLabel: 'Aman',
          badgeClass: 'badge-aman',
          badgeText: 'Aman (> 6 Bln)',
          pillClass: 'pill-aman',
          icon: 'ti-shield-check',
          diffText: `${durationStr} lagi`,
          totalDays: totalDays,
          years: y,
          months: m,
          days: d,
          isExpired: false
        };
      }
    }

    // ========================================================
    // UPDATE SUMMARY CARDS & BADGES
    // ========================================================
    function updateSummaryUI(summary) {
      if (!summary) return;
      rawSummary = summary;

      $('#statTotal').text(summary.total || 0);
      $('#statAman').text(summary.aman || 0);
      $('#statRisiko').text(summary.risiko || 0);
      $('#statKadaluarsaLainnya').text(summary.kadaluarsa_lainnya || 0);
      $('#statKadaluarsaSub').text(`${summary.kadaluarsa || 0} Expired, ${summary.lainnya || 0} Belum Diset`);

      $('#btnCountAll').text(summary.total || 0);
      $('#btnCountAman').text(summary.aman || 0);
      $('#btnCountRisiko').text(summary.risiko || 0);
      $('#btnCountKadaluarsa').text(summary.kadaluarsa || 0);
      $('#btnCountLainnya').text(summary.lainnya || 0);

      const now = new Date();
      $('#lastUpdatedText').text(`Diperbarui: ${now.toLocaleTimeString('id-ID')}`);
    }

    // ========================================================
    // FILTER KATEGORI (KLIK CARD ATAU BUTTON)
    // ========================================================
    function applyCategoryFilter(cat) {
      currentCategoryFilter = cat;

      // Update active state di buttons
      $('.quick-filter-btn').removeClass('active');
      $(`.quick-filter-btn[data-cat="${cat}"]`).addClass('active');

      // Update active state di kartu
      $('.stat-card').removeClass('active-filter');
      if (cat === 'all') $('.card-total').addClass('active-filter');
      else if (cat === 'aman') $('.card-aman').addClass('active-filter');
      else if (cat === 'risiko') $('.card-risiko').addClass('active-filter');
      else if (cat === 'kadaluarsa_lainnya' || cat === 'kadaluarsa') $('.card-kadaluarsa').addClass('active-filter');

      // Reload data tabel
      if (tableExpired) {
        tableExpired.ajax.reload();
      }
    }

    // ========================================================
    // INIT DATATABLES
    // ========================================================
    $(document).ready(function() {

      tableExpired = $('#tableExpired').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          url: expiredApiUrl,
          type: 'GET',
          data: function(d) {
            d.action = 'list';
            d.kategori = currentCategoryFilter;
            d.category_obat = $('#filterKategoriObat').val();
            d.fromDate = $('#filterFromDate').val();
            d.toDate = $('#filterToDate').val();
          },
          dataSrc: function(json) {
            if (json.status !== 'success') {
              Swal.fire('Perhatian', json.message || 'Gagal mengambil data', 'warning');
              return [];
            }
            updateSummaryUI(json.summary);
            return json.data || [];
          }
        },
        columns: [
          {
            data: null,
            className: 'text-center text-muted fs-2',
            render: function(data, type, row, meta) {
              return meta.row + 1;
            }
          },
          {
            data: 'expired',
            className: 'text-center',
            render: function(data, type, row) {
              const diff = calculateRealtimeDiff(data);
              return `
                <span class="badge-realtime ${diff.badgeClass}">
                  <i class="ti ${diff.icon}"></i>
                  ${diff.badgeText}
                </span>
              `;
            }
          },
          {
            data: 'nama_obat',
            render: function(data, type, row) {
              const code = row.pharmacy_code ? `<span class="badge bg-light text-dark border me-1">${row.pharmacy_code}</span>` : '';
              const generic = row.pharmacy_name_generic || '-';
              const trade = row.pharmacy_name_trade ? `<div class="fs-2 text-muted">Merk: ${row.pharmacy_name_trade}</div>` : '';
              const cat = row.pharmacy_category ? `<span class="badge bg-primary-subtle text-primary fs-1">${row.pharmacy_category}</span>` : '';
              const golongan = row.pharmcy_golongan ? `<span class="badge bg-secondary-subtle text-secondary fs-1 ms-1">${row.pharmcy_golongan}</span>` : '';
              return `
                <div>
                  <div class="fw-bold">${code} ${generic}</div>
                  ${trade}
                  <div class="mt-1">${cat} ${golongan}</div>
                </div>
              `;
            }
          },
          {
            data: 'batch',
            render: function(data, type, row) {
              const b = (data && data !== '-') ? `<code>${data}</code>` : '<span class="text-muted">-</span>';
              const faktur = row.nomor_faktur ? `<div class="fs-2 text-muted">Faktur: ${row.nomor_faktur}</div>` : '';
              return `<div><span class="fw-semibold">${b}</span>${faktur}</div>`;
            }
          },
          {
            data: 'supplier',
            render: function(data) {
              return data ? `<span class="fs-2">${data}</span>` : '<span class="text-muted fs-2">-</span>';
            }
          },
          {
            data: 'expired',
            render: function(data) {
              return `<span class="fw-semibold">${formatTanggalIndo(data)}</span>`;
            }
          },
          {
            data: 'expired',
            render: function(data, type, row) {
              const diff = calculateRealtimeDiff(data);
              let sisaHariText = '';
              if (diff.totalDays !== null) {
                if (diff.totalDays > 0) {
                  sisaHariText = `<div class="fs-1 text-muted mt-1">Sisa: ${diff.totalDays} hari</div>`;
                } else if (diff.totalDays === 0) {
                  sisaHariText = `<div class="fs-1 text-danger fw-bold mt-1">Hari ini!</div>`;
                } else {
                  sisaHariText = `<div class="fs-1 text-danger fw-bold mt-1">Lewat ${Math.abs(diff.totalDays)} hari</div>`;
                }
              }
              return `
                <div>
                  <span class="countdown-pill ${diff.pillClass}">
                    <i class="ti ${diff.icon} me-1"></i>${diff.diffText}
                  </span>
                  ${sisaHariText}
                </div>
              `;
            }
          },
          {
            data: 'qty_masuk',
            className: 'text-center',
            render: function(data, type, row) {
              const unit = row.pharmacy_unit || '';
              return `<span class="badge bg-light text-dark border fw-bold fs-2">${Number(data).toLocaleString('id-ID')} ${unit}</span>`;
            }
          },
          {
            data: null,
            className: 'text-center no-print',
            orderable: false,
            render: function(data, type, row) {
              const idPenerimaan = row.id_penerimaan || '';
              const idPharmacy = row.id_pharmacy || '';
              return `
                <button type="button" class="btn btn-sm btn-outline-primary btn-detail-batch"
                        data-penerimaan="${idPenerimaan}"
                        data-pharmacy="${idPharmacy}"
                        title="Lihat Detail & Rekomendasi">
                  <i class="ti ti-eye"></i>
                </button>
              `;
            }
          }
        ],
        order: [],
        pageLength: 25,
        language: {
          search: "Cari Obat / Batch:",
          searchPlaceholder: "Nama obat, batch, supplier...",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
          infoEmpty: "Data tidak tersedia",
          zeroRecords: "Tidak ada obat atau batch yang sesuai",
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "»",
            previous: "«"
          }
        }
      });

      // Quick filter buttons click
      $('.quick-filter-btn').on('click', function() {
        const cat = $(this).data('cat');
        applyCategoryFilter(cat);
      });

      // Toggle Advanced Filter Panel
      $('#btnToggleFilterAdv').on('click', function() {
        $('#advancedFilterPanel').toggleClass('d-none');
      });

      // Terapkan Filter Lanjutan
      $('#btnApplyAdvanced').on('click', function() {
        tableExpired.ajax.reload();
      });

      // Reset Filter
      $('#btnResetFilter').on('click', function() {
        $('#filterKategoriObat').val('');
        $('#filterFromDate').val('');
        $('#filterToDate').val('');
        applyCategoryFilter('all');
      });

      // Refresh Button
      $('#btnRefresh').on('click', function() {
        tableExpired.ajax.reload(null, false);
      });

      // Print Button
      $('#btnPrint').on('click', function() {
        window.print();
      });

      // ========================================================
      // DETAIL MODAL HANDLER
      // ========================================================
      $(document).on('click', '.btn-detail-batch', function() {
        const idPenerimaan = $(this).data('penerimaan');
        const idPharmacy = $(this).data('pharmacy');

        $('#detailContent').html(`
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat detail...</p>
          </div>
        `);
        $('#modalDetail').modal('show');

        $.ajax({
          url: expiredApiUrl,
          type: 'GET',
          data: {
            action: 'detail',
            id_penerimaan: idPenerimaan,
            id_pharmacy: idPharmacy
          },
          success: function(res) {
            if (res.status !== 'success' || !res.data) {
              $('#detailContent').html(`
                <div class="alert alert-danger mb-0">
                  <i class="ti ti-alert-triangle me-1"></i>${res.message || 'Data tidak ditemukan'}
                </div>
              `);
              return;
            }

            const d = res.data;
            const diff = calculateRealtimeDiff(d.expired);

            let statusAlertHtml = '';
            let saranHtml = '';

            if (diff.kategori === 'kadaluarsa') {
              statusAlertHtml = `
                <div class="alert alert-danger d-flex align-items-center mb-3">
                  <iconify-icon icon="solar:danger-triangle-bold" class="fs-6 me-2"></iconify-icon>
                  <div>
                    <strong>PERINGATAN: OBAT SUDAH KADALUARSA!</strong>
                    <div>Obat telah melewati batas aman penggunaan. Segera pisahkan ke tempat karantina khusus obat kadaluarsa dan jadwalkan pemusnahan atau retur ke supplier.</div>
                  </div>
                </div>
              `;
              saranHtml = '<span class="badge bg-danger">Karantina & Musnahkan / Retur</span>';
            } else if (diff.kategori === 'risiko') {
              statusAlertHtml = `
                <div class="alert alert-warning d-flex align-items-center mb-3">
                  <iconify-icon icon="solar:bell-bold" class="fs-6 me-2"></iconify-icon>
                  <div>
                    <strong>PERHATIAN: MENDEKATI KADALUARSA (&le; 6 BULAN)!</strong>
                    <div>Terapkan prinsip <strong>FEFO (First Expired, First Out)</strong>. Utamakan obat ini untuk resep pasien terlebih dahulu atau ajukan retur ke supplier jika stok berlebih.</div>
                  </div>
                </div>
              `;
              saranHtml = '<span class="badge bg-warning text-dark">Prioritas FEFO / Pertimbangkan Retur</span>';
            } else if (diff.kategori === 'aman') {
              statusAlertHtml = `
                <div class="alert alert-success d-flex align-items-center mb-3">
                  <iconify-icon icon="solar:shield-check-bold" class="fs-6 me-2"></iconify-icon>
                  <div>
                    <strong>STATUS AMAN (&gt; 6 BULAN)</strong>
                    <div>Masa pakai obat masih panjang. Lakukan penyimpanan sesuai suhu dan kelembapan standar pabrik.</div>
                  </div>
                </div>
              `;
              saranHtml = '<span class="badge bg-success">Aman untuk Didistribusikan</span>';
            } else {
              statusAlertHtml = `
                <div class="alert alert-secondary d-flex align-items-center mb-3">
                  <iconify-icon icon="solar:info-circle-bold" class="fs-6 me-2"></iconify-icon>
                  <div>
                    <strong>TANGGAL BELUM DIATUR</strong>
                    <div>Batch atau master obat ini belum memiliki tanggal kadaluarsa. Harap lengkapi pada modul penerimaan obat.</div>
                  </div>
                </div>
              `;
              saranHtml = '<span class="badge bg-secondary">Perlu Update Tanggal</span>';
            }

            const html = `
              ${statusAlertHtml}

              <div class="card bg-light border mb-3">
                <div class="card-body p-3">
                  <div class="row align-items-center">
                    <div class="col-md-7">
                      <span class="badge bg-primary mb-1">${d.pharmacy_code || 'KODE'}</span>
                      <h5 class="fw-bold mb-1">${d.pharmacy_name_generic || '-'}</h5>
                      <div class="text-muted fs-3">Nama Dagang: <strong>${d.pharmacy_name_trade || '-'}</strong></div>
                      <div class="fs-2 text-muted mt-1">Kategori: ${d.pharmacy_category || '-'} | Golongan: ${d.pharmcy_golongan || '-'}</div>
                    </div>
                    <div class="col-md-5 text-md-end mt-2 mt-md-0">
                      <div class="fs-2 text-muted">Hitungan Waktu Realtime:</div>
                      <h4 class="fw-bold my-1 ${diff.kategori === 'kadaluarsa' ? 'text-danger' : (diff.kategori === 'risiko' ? 'text-warning' : 'text-success')}">
                        ${diff.diffText}
                      </h4>
                      <div>${diff.badgeText}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold border-bottom pb-2 mb-2"><i class="ti ti-box me-1"></i>Data Batch & Penerimaan</h6>
                    <table class="table table-sm table-borderless mb-0 fs-3">
                      <tr>
                        <td width="130" class="text-muted">Nomor Batch</td>
                        <td class="fw-bold"><code>${d.batch || '-'}</code></td>
                      </tr>
                      <tr>
                        <td class="text-muted">No. Faktur</td>
                        <td class="fw-semibold">${d.nomor_faktur || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Supplier / PBF</td>
                        <td>${d.supplier || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Tgl Penerimaan</td>
                        <td>${formatTanggalIndo(d.tanggal)}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Jumlah Masuk</td>
                        <td class="fw-bold text-primary">${Number(d.jumlah || 0).toLocaleString('id-ID')} ${d.pharmacy_unit || ''}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Harga Beli</td>
                        <td>Rp ${Number(d.harga_beli || 0).toLocaleString('id-ID')}</td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold border-bottom pb-2 mb-2"><i class="ti ti-calendar me-1"></i>Informasi Kedaluwarsa</h6>
                    <table class="table table-sm table-borderless mb-0 fs-3">
                      <tr>
                        <td width="130" class="text-muted">Tgl Kadaluarsa</td>
                        <td class="fw-bold fs-4 ${diff.kategori === 'kadaluarsa' ? 'text-danger' : (diff.kategori === 'risiko' ? 'text-warning' : 'text-success')}">
                          ${formatTanggalIndo(d.expired)}
                        </td>
                      </tr>
                      <tr>
                        <td class="text-muted">Kategori</td>
                        <td>${diff.badgeText}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Rincian Durasi</td>
                        <td class="fw-semibold">${diff.years} Tahun, ${diff.months} Bulan, ${diff.days} Hari</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Total Sisa Hari</td>
                        <td class="fw-bold">${diff.totalDays !== null ? diff.totalDays + ' Hari' : '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Rekomendasi</td>
                        <td>${saranHtml}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Keterangan</td>
                        <td class="text-muted">${d.keterangan || '-'}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            `;

            $('#detailContent').html(html);
          },
          error: function() {
            $('#detailContent').html(`
              <div class="alert alert-danger mb-0">
                <i class="ti ti-alert-triangle me-1"></i>Gagal memuat detail dari server.
              </div>
            `);
          }
        });
      });

    });
  </script>
</body>

</html>
