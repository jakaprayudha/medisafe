<style>
  /* =========================================================
       DASHBOARD FARMASI
    ========================================================= */

  .farmasi-dashboard {

    --far-primary: #635bff;
    --far-primary-soft: #eeecff;

    --far-text: #273444;
    --far-muted: #7b8494;

    --far-border: #edf0f5;

    --far-green: #16a34a;
    --far-red: #dc2626;
    --far-orange: #d97706;
    --far-blue: #1687d9;
  }


  /* =========================================================
       FILTER PERIODE
    ========================================================= */

  .farmasi-dashboard .far-filter-wrapper {

    background: #fff;

    border: 1px solid var(--far-border);

    border-radius: 18px;

    padding: 16px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 15px;
  }


  .farmasi-dashboard .far-filter-title {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-shrink: 0;
  }


  .farmasi-dashboard .far-filter-icon {

    width: 42px;
    height: 42px;

    border-radius: 12px;

    background: var(--far-primary-soft);

    color: var(--far-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;
  }


  .farmasi-dashboard .far-filter-heading {

    color: var(--far-text);

    font-size: 13px;

    font-weight: 700;
  }


  .farmasi-dashboard .far-filter-description {

    color: var(--far-muted);

    font-size: 11px;

    margin-top: 2px;
  }


  .farmasi-dashboard .far-filter-form {

    display: flex;

    align-items: flex-end;

    gap: 10px;
  }


  .farmasi-dashboard .far-filter-group {

    min-width: 140px;
  }


  .farmasi-dashboard .far-filter-group label {

    display: block;

    color: var(--far-muted);

    font-size: 10px;

    font-weight: 600;

    margin-bottom: 5px;
  }


  .farmasi-dashboard .far-filter-group .form-control,
  .farmasi-dashboard .far-filter-group .form-select {

    height: 38px;

    border-radius: 10px;

    border-color: var(--far-border);

    font-size: 12px;

    box-shadow: none;
  }


  .farmasi-dashboard .far-filter-button {

    height: 38px;

    border-radius: 10px;

    font-size: 12px;

    white-space: nowrap;
  }


  /* =========================================================
       STATUS FARMASI
    ========================================================= */

  .farmasi-dashboard .far-status-bar {

    background: #fff;

    border: 1px solid var(--far-border);

    border-radius: 15px;

    padding: 12px 17px;

    margin-bottom: 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
  }


  .farmasi-dashboard .far-status-left {

    display: flex;

    align-items: center;

    gap: 9px;

    color: var(--far-text);

    font-size: 12px;

    font-weight: 600;
  }


  .farmasi-dashboard .far-online-dot {

    width: 9px;
    height: 9px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
      0 0 0 4px rgba(34, 197, 94, .12);
  }


  .farmasi-dashboard .far-status-right {

    color: var(--far-muted);

    font-size: 10px;
  }


  /* =========================================================
       KPI
    ========================================================= */

  .farmasi-dashboard .kpi-card {

    background: #fff;

    border: 1px solid var(--far-border);

    border-radius: 18px;

    padding: 19px;

    height: 100%;

    transition: .2s ease;
  }


  .farmasi-dashboard .kpi-card:hover {

    transform: translateY(-2px);

    box-shadow:
      0 10px 28px rgba(30, 40, 60, .06);
  }


  .farmasi-dashboard .kpi-top {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;
  }


  .farmasi-dashboard .kpi-title {

    color: var(--far-muted);

    font-size: 12px;

    margin-bottom: 5px;
  }


  .farmasi-dashboard .kpi-value {

    color: var(--far-text);

    font-size: 27px;

    font-weight: 700;

    line-height: 1.15;
  }


  .farmasi-dashboard .kpi-info {

    font-size: 10px;

    margin-top: 7px;
  }


  .farmasi-dashboard .kpi-icon {

    width: 46px;
    height: 46px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;
  }


  .farmasi-dashboard .icon-purple {

    background: #eeecff;

    color: #635bff;
  }


  .farmasi-dashboard .icon-blue {

    background: #e8f4ff;

    color: #1687d9;
  }


  .farmasi-dashboard .icon-orange {

    background: #fff3e3;

    color: #df861e;
  }


  .farmasi-dashboard .icon-green {

    background: #e8f8ef;

    color: #16965a;
  }


  .farmasi-dashboard .icon-red {

    background: #feecec;

    color: #dc2626;
  }


  .farmasi-dashboard .up {

    color: #16a34a;
  }


  .farmasi-dashboard .warning {

    color: #d97706;
  }


  .farmasi-dashboard .danger {

    color: #dc2626;
  }


  /* =========================================================
       CARD
    ========================================================= */

  .farmasi-dashboard .dash-card {

    background: #fff;

    border: 1px solid var(--far-border);

    border-radius: 18px;

    padding: 20px;

    height: 100%;
  }


  .farmasi-dashboard .dash-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 16px;
  }


  .farmasi-dashboard .dash-title {

    color: var(--far-text);

    font-size: 15px;

    font-weight: 700;

    margin: 0;
  }


  .farmasi-dashboard .dash-subtitle {

    color: var(--far-muted);

    font-size: 11px;

    margin-top: 3px;
  }


  .farmasi-dashboard .view-all {

    color: var(--far-primary);

    font-size: 11px;

    text-decoration: none;

    font-weight: 600;
  }


  /* =========================================================
       RESEP
    ========================================================= */

  .farmasi-dashboard .prescription-item {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .farmasi-dashboard .prescription-item:last-child {

    border-bottom: none;
  }


  .farmasi-dashboard .prescription-number {

    width: 40px;
    height: 40px;

    border-radius: 11px;

    background: #f0efff;

    color: var(--far-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 10px;

    font-weight: 700;

    flex-shrink: 0;
  }


  .farmasi-dashboard .prescription-name {

    color: var(--far-text);

    font-size: 12px;

    font-weight: 600;
  }


  .farmasi-dashboard .prescription-detail {

    color: var(--far-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .farmasi-dashboard .prescription-status {

    margin-left: auto;

    padding: 5px 8px;

    border-radius: 8px;

    font-size: 9px;

    white-space: nowrap;
  }


  .farmasi-dashboard .status-waiting {

    background: #fff3d9;

    color: #c47a00;
  }


  .farmasi-dashboard .status-process {

    background: #e8f2ff;

    color: #2377c7;
  }


  .farmasi-dashboard .status-ready {

    background: #e7f8ee;

    color: #168a4e;
  }


  .farmasi-dashboard .status-done {

    background: #f1f2f4;

    color: #6b7280;
  }


  /* =========================================================
       PROGRESS
    ========================================================= */

  .farmasi-dashboard .process-box {

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 14px;

    padding: 14px;

    margin-bottom: 12px;
  }


  .farmasi-dashboard .process-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 9px;
  }


  .farmasi-dashboard .process-title {

    color: var(--far-text);

    font-size: 11px;

    font-weight: 600;
  }


  .farmasi-dashboard .process-value {

    color: var(--far-primary);

    font-size: 12px;

    font-weight: 700;
  }


  .farmasi-dashboard .process-bar {

    height: 7px;

    background: #e9ebf0;

    border-radius: 20px;

    overflow: hidden;
  }


  .farmasi-dashboard .process-bar span {

    display: block;

    height: 100%;

    background: var(--far-primary);

    border-radius: inherit;
  }


  /* =========================================================
       STOCK
    ========================================================= */

  .farmasi-dashboard .stock-item {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .farmasi-dashboard .stock-item:last-child {

    border-bottom: none;
  }


  .farmasi-dashboard .stock-icon {

    width: 37px;
    height: 37px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eeecff;

    color: var(--far-primary);

    flex-shrink: 0;
  }


  .farmasi-dashboard .stock-name {

    color: var(--far-text);

    font-size: 11px;

    font-weight: 600;
  }


  .farmasi-dashboard .stock-code {

    color: var(--far-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  .farmasi-dashboard .stock-value {

    margin-left: auto;

    text-align: right;
  }


  .farmasi-dashboard .stock-number {

    color: var(--far-text);

    font-size: 12px;

    font-weight: 700;
  }


  .farmasi-dashboard .stock-unit {

    color: var(--far-muted);

    font-size: 9px;
  }


  .farmasi-dashboard .stock-warning {

    color: var(--far-red);

    font-size: 9px;

    font-weight: 600;
  }


  .farmasi-dashboard .stock-safe {

    color: var(--far-green);

    font-size: 9px;

    font-weight: 600;
  }


  /* =========================================================
       CATEGORY
    ========================================================= */

  .farmasi-dashboard .category-item {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .farmasi-dashboard .category-item:last-child {

    border-bottom: none;
  }


  .farmasi-dashboard .category-icon {

    width: 37px;
    height: 37px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;
  }


  .farmasi-dashboard .category-name {

    flex: 1;

    color: var(--far-text);

    font-size: 11px;

    font-weight: 600;
  }


  .farmasi-dashboard .category-count {

    color: var(--far-text);

    font-size: 13px;

    font-weight: 700;
  }


  /* =========================================================
       DRUG ALERT
    ========================================================= */

  .farmasi-dashboard .drug-alert {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .farmasi-dashboard .drug-alert:last-child {

    border-bottom: none;
  }


  .farmasi-dashboard .drug-alert-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: #fff3e3;

    color: var(--far-orange);

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;
  }


  .farmasi-dashboard .drug-alert-name {

    color: var(--far-text);

    font-size: 11px;

    font-weight: 600;
  }


  .farmasi-dashboard .drug-alert-desc {

    color: var(--far-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  .farmasi-dashboard .drug-alert-action {

    margin-left: auto;

    color: var(--far-primary);

    font-size: 10px;

    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;
  }


  /* =========================================================
       QUICK ACCESS
    ========================================================= */

  .farmasi-dashboard .quick-item {

    display: block;

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 13px;

    padding: 13px;

    text-decoration: none;

    transition: .2s ease;
  }


  .farmasi-dashboard .quick-item:hover {

    background: #f5f4ff;

    border-color: #dedbff;

    transform: translateY(-1px);
  }


  .farmasi-dashboard .quick-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: #eeecff;

    color: var(--far-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
  }


  .farmasi-dashboard .quick-title {

    color: var(--far-text);

    font-size: 11px;

    font-weight: 600;

    margin-top: 8px;
  }


  .farmasi-dashboard .quick-description {

    color: var(--far-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  /* =========================================================
       RESPONSIVE
    ========================================================= */

  @media (max-width: 1100px) {

    .farmasi-dashboard .far-filter-wrapper {

      flex-direction: column;

      align-items: flex-start;
    }

    .farmasi-dashboard .far-filter-form {

      width: 100%;

      flex-wrap: wrap;
    }

  }


  @media (max-width: 767px) {

    .farmasi-dashboard .far-filter-form {

      display: grid;

      grid-template-columns: 1fr 1fr;

      width: 100%;
    }

    .farmasi-dashboard .far-filter-group {

      min-width: 0;
    }

    .farmasi-dashboard .far-filter-button {

      width: 100%;
    }

    .farmasi-dashboard .far-status-bar {

      align-items: flex-start;

      flex-direction: column;
    }

  }


  @media (max-width: 480px) {

    .farmasi-dashboard .far-filter-form {

      grid-template-columns: 1fr;
    }

  }
</style>


<div class="farmasi-dashboard">
  <!-- =====================================================
         FILTER PERIODE
    ====================================================== -->
  <div class="far-filter-wrapper">
    <div class="far-filter-title">
      <div class="far-filter-icon">
        <iconify-icon icon="solar:calendar-search-bold">
        </iconify-icon>
      </div>
      <div>
        <div class="far-filter-heading">
          Periode Farmasi
        </div>
        <div class="far-filter-description">
          Monitoring resep, dispensing dan stok obat
        </div>
      </div>
    </div>
    <div class="far-filter-form">
      <div class="far-filter-group">
        <label>
          Periode
        </label>
        <select class="form-select" id="farmasiPeriod">
          <option selected="" value="today">
            Hari Ini
          </option>
          <option value="yesterday">
            Kemarin
          </option>
          <option value="7days">
            7 Hari Terakhir
          </option>
          <option value="30days">
            30 Hari Terakhir
          </option>
          <option value="thismonth">
            Bulan Ini
          </option>
          <option value="lastmonth">
            Bulan Lalu
          </option>
          <option value="custom">
            Custom Periode
          </option>
        </select>
      </div>
      <div class="far-filter-group">
        <label>
          Dari
        </label>
        <input class="form-control" id="farmasiStartDate" type="date" value="&lt;?= date('Y-m-d') ?&gt;" />
      </div>
      <div class="far-filter-group">
        <label>
          Sampai
        </label>
        <input class="form-control" id="farmasiEndDate" type="date" value="&lt;?= date('Y-m-d') ?&gt;" />
      </div>
      <button class="btn btn-primary far-filter-button" id="applyFarmasiFilter" type="button">
        <iconify-icon class="me-1" icon="solar:filter-bold">
        </iconify-icon>

        Terapkan

      </button>
    </div>
  </div>
  <!-- =====================================================
         STATUS FARMASI
    ====================================================== -->
  <div class="far-status-bar">
    <div class="far-status-left">
      <span class="far-online-dot"></span>

      Pelayanan Farmasi Aktif

    </div>
    <div class="far-status-right">

      Update terakhir:

      <strong id="farmasiLastUpdate">
        <?= date('H:i') ?>
      </strong>

      WIB

    </div>
  </div>
  <!-- =====================================================
         KPI
    ====================================================== -->
  <div class="row g-3 mb-3">
    <!-- RESEP MASUK -->
    <div class="col-xl-3 col-md-6">
      <div class="kpi-card">
        <div class="kpi-top">
          <div>
            <div class="kpi-title">
              Resep Masuk
            </div>
            <div class="kpi-value">
              <span id="farKpiResepMasuk">0</span>
            </div>
            <div class="kpi-info up">
              <span id="farKpiResepInfo">Data periode terpilih</span>
            </div>
          </div>
          <div class="kpi-icon icon-purple">
            <iconify-icon icon="solar:document-text-bold">
            </iconify-icon>
          </div>
        </div>
      </div>
    </div>
    <!-- MENUNGGU -->
    <div class="col-xl-3 col-md-6">
      <div class="kpi-card">
        <div class="kpi-top">
          <div>
            <div class="kpi-title">
              Menunggu Diproses
            </div>
            <div class="kpi-value">
              <span id="farKpiMenunggu">0</span>
            </div>
            <div class="kpi-info warning">
              Perlu segera diproses
            </div>
          </div>
          <div class="kpi-icon icon-orange">
            <iconify-icon icon="solar:clock-circle-bold">
            </iconify-icon>
          </div>
        </div>
      </div>
    </div>
    <!-- DIPROSES -->
    <div class="col-xl-3 col-md-6">
      <div class="kpi-card">
        <div class="kpi-top">
          <div>
            <div class="kpi-title">
              Sedang Diproses
            </div>
            <div class="kpi-value">
              <span id="farKpiDiproses">0</span>
            </div>
            <div class="kpi-info">
              Dalam tahap dispensing
            </div>
          </div>
          <div class="kpi-icon icon-blue">
            <iconify-icon icon="solar:pill-bold">
            </iconify-icon>
          </div>
        </div>
      </div>
    </div>
    <!-- SELESAI -->
    <div class="col-xl-3 col-md-6">
      <div class="kpi-card">
        <div class="kpi-top">
          <div>
            <div class="kpi-title">
              Resep Selesai
            </div>
            <div class="kpi-value">
              <span id="farKpiSelesai">0</span>
            </div>
            <div class="kpi-info up">
              <span id="farKpiPersentase">0%</span> dari total resep
            </div>
          </div>
          <div class="kpi-icon icon-green">
            <iconify-icon icon="solar:check-circle-bold">
            </iconify-icon>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- =====================================================
         RESEP + PROSES
    ====================================================== -->
  <div class="row g-3 mb-3">
    <!-- RESEP TERBARU -->
    <div class="col-xl-7">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Antrian Resep
            </h6>
            <div class="dash-subtitle">
              Resep pasien yang masuk ke farmasi
            </div>
          </div>
          <span class="badge bg-warning text-dark"><span id="farWaitingBadge">0</span> Menunggu</span>
        </div>
        <div id="farmasiPrescriptionList">
          <div class="text-center text-muted py-4">Memuat data resep...</div>
        </div>





      </div>
    </div>
    <!-- PROSES RESEP -->
    <div class="col-xl-5">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Status Pelayanan
            </h6>
            <div class="dash-subtitle">
              Progress resep hari ini
            </div>
          </div>
        </div>
        <div id="farmasiProcessList">
          <div class="text-center text-muted py-4">Memuat status pelayanan...</div>
        </div>



      </div>
    </div>
  </div>
  <!-- =====================================================
         STOK + OBAT MENIPIS + KATEGORI
    ====================================================== -->
  <div class="row g-3 mb-3">
    <!-- OBAT MENIPIS -->
    <div class="col-xl-5">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Stok Obat Menipis
            </h6>
            <div class="dash-subtitle">
              Obat yang perlu segera diperhatikan
            </div>
          </div>
          <span class="badge bg-danger"><span id="farmasiLowStockBadge"></span></span>
        </div>
        <div id="farmasiLowStockList">
          <div class="text-center text-muted py-4">Memuat stok obat...</div>
        </div>




      </div>
    </div>
    <!-- KATEGORI OBAT -->
    <div class="col-xl-4">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Distribusi Obat
            </h6>
            <div class="dash-subtitle">
              Pengeluaran berdasarkan kategori
            </div>
          </div>
        </div>
        <div id="farmasiCategoryList">
          <div class="text-center text-muted py-4">Memuat distribusi obat...</div>
        </div>




      </div>
    </div>
    <!-- ALERT OBAT -->
    <div class="col-xl-3">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Perhatian
            </h6>
            <div class="dash-subtitle">
              Item yang perlu ditindaklanjuti
            </div>
          </div>
        </div>
        <div id="farmasiAlertList">
          <div class="text-center text-muted py-4">Memuat alert...</div>
        </div>



      </div>
    </div>
  </div>
  <!-- =====================================================
         GRAFIK + QUICK ACCESS
    ====================================================== -->
  <div class="row g-3">
    <!-- GRAFIK -->
    <div class="col-xl-7">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Tren Resep
            </h6>
            <div class="dash-subtitle">
              Jumlah resep yang diterima dan diselesaikan
            </div>
          </div>
          <select class="form-select" style="
                            width:auto;
                            min-width:110px;
                            font-size:11px;
                            border-radius:10px;
                        ">
            <option>
              7 Hari
            </option>
            <option>
              30 Hari
            </option>
            <option>
              3 Bulan
            </option>
          </select>
        </div>
        <div style="height:260px">
          <canvas id="farmasiPrescriptionChart">
          </canvas>
        </div>
      </div>
    </div>
    <!-- QUICK ACCESS -->
    <div class="col-xl-5">
      <div class="dash-card">
        <div class="dash-header">
          <div>
            <h6 class="dash-title">
              Akses Cepat
            </h6>
            <div class="dash-subtitle">
              Modul yang sering digunakan
            </div>
          </div>
        </div>
        <div class="row g-2">
          <!-- RESEP -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:document-text-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Resep Masuk
              </div>
              <div class="quick-description">
                Verifikasi resep
              </div>
            </a>
          </div>
          <!-- DISPENSING -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:pills-3-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Dispensing
              </div>
              <div class="quick-description">
                Siapkan obat
              </div>
            </a>
          </div>
          <!-- STOK -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:box-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Stok Obat
              </div>
              <div class="quick-description">
                Kelola persediaan
              </div>
            </a>
          </div>
          <!-- PENERIMAAN -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:inbox-in-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Penerimaan
              </div>
              <div class="quick-description">
                Penerimaan obat
              </div>
            </a>
          </div>
          <!-- RETUR -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:undo-left-round-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Retur Obat
              </div>
              <div class="quick-description">
                Kelola retur
              </div>
            </a>
          </div>
          <!-- LAPORAN -->
          <div class="col-6">
            <a class="quick-item" href="#">
              <div class="quick-icon">
                <iconify-icon icon="solar:chart-2-bold">
                </iconify-icon>
              </div>
              <div class="quick-title">
                Laporan
              </div>
              <div class="quick-description">
                Laporan farmasi
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {

    const endpoint = "controller/dashboard/farmasiDashboardController.php";

    const period = document.getElementById("farmasiPeriod");
    const startDate = document.getElementById("farmasiStartDate");
    const endDate = document.getElementById("farmasiEndDate");
    const applyBtn = document.getElementById("applyFarmasiFilter");
    const lastUpdate = document.getElementById("farmasiLastUpdate");

    let prescriptionChart = null;

    function escapeHtml(value) {
      return String(value ?? "-")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    function numberFormat(value) {
      return new Intl.NumberFormat("id-ID").format(Number(value || 0));
    }

    function localDate(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, "0");
      const d = String(date.getDate()).padStart(2, "0");
      return `${y}-${m}-${d}`;
    }

    function formatDate(value) {
      if (!value) return "-";

      const d = new Date(String(value).replace(" ", "T"));

      if (Number.isNaN(d.getTime())) {
        return escapeHtml(value);
      }

      return d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    }

    function formatDateTime(value) {
      if (!value) return "-";

      const d = new Date(String(value).replace(" ", "T"));

      if (Number.isNaN(d.getTime())) {
        return escapeHtml(value);
      }

      return d.toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
        hour: "2-digit",
        minute: "2-digit"
      });
    }

    function updatePeriod() {
      const value = period.value;
      const today = new Date();

      let start = new Date(today);
      let end = new Date(today);

      if (value === "yesterday") {
        start.setDate(today.getDate() - 1);
        end = new Date(start);

      } else if (value === "7days") {
        start.setDate(today.getDate() - 6);

      } else if (value === "30days") {
        start.setDate(today.getDate() - 29);

      } else if (value === "thismonth") {
        start = new Date(today.getFullYear(), today.getMonth(), 1);

      } else if (value === "lastmonth") {
        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        end = new Date(today.getFullYear(), today.getMonth(), 0);

      } else if (value === "custom") {
        return;
      }

      startDate.value = localDate(start);
      endDate.value = localDate(end);
    }

    function setLoading() {
      document.getElementById("farKpiResepMasuk").textContent = "...";
      document.getElementById("farKpiMenunggu").textContent = "...";
      document.getElementById("farKpiDiproses").textContent = "...";
      document.getElementById("farKpiSelesai").textContent = "...";
      document.getElementById("farKpiPersentase").textContent = "...%";
      document.getElementById("farWaitingBadge").textContent = "...";

      document.getElementById("farmasiPrescriptionList").innerHTML =
        '<div class="text-center text-muted py-4">Memuat data resep...</div>';

      document.getElementById("farmasiProcessList").innerHTML =
        '<div class="text-center text-muted py-4">Memuat status pelayanan...</div>';

      document.getElementById("farmasiLowStockList").innerHTML =
        '<div class="text-center text-muted py-4">Memuat stok obat...</div>';

      document.getElementById("farmasiCategoryList").innerHTML =
        '<div class="text-center text-muted py-4">Memuat distribusi obat...</div>';

      document.getElementById("farmasiAlertList").innerHTML =
        '<div class="text-center text-muted py-4">Memuat alert...</div>';
    }

    function renderKPI(data) {
      const kpi = data.kpi || {};

      document.getElementById("farKpiResepMasuk").textContent =
        numberFormat(kpi.resep_masuk);

      document.getElementById("farKpiMenunggu").textContent =
        numberFormat(kpi.menunggu_diproses);

      document.getElementById("farKpiDiproses").textContent =
        numberFormat(kpi.sedang_diproses);

      document.getElementById("farKpiSelesai").textContent =
        numberFormat(kpi.resep_selesai);

      document.getElementById("farKpiPersentase").textContent =
        numberFormat(kpi.persentase_selesai) + "%";

      document.getElementById("farWaitingBadge").textContent =
        numberFormat(kpi.menunggu_diproses);

      document.getElementById("farKpiResepInfo").textContent =
        "Periode " +
        formatDate(data.period?.start) +
        " s/d " +
        formatDate(data.period?.end);
    }

    function renderPrescriptions(data) {
      const box = document.getElementById("farmasiPrescriptionList");
      const items = data.prescriptions?.items || [];

      if (!items.length) {
        box.innerHTML = `
        <div class="text-center text-muted py-4">
          <iconify-icon icon="solar:document-text-bold"
            style="font-size:30px;opacity:.35;">
          </iconify-icon>
          <div class="mt-2">Tidak ada resep pada periode ini.</div>
        </div>
      `;
        return;
      }

      box.innerHTML = items.map(item => `
      <div class="prescription-item">
        <div class="prescription-number">
          ${escapeHtml(item.nomor_resep || "-")}
        </div>

        <div style="min-width:0;">
          <div class="prescription-name">
            ${escapeHtml(item.nama_pasien || "-")}
          </div>

          <div class="prescription-detail">
            ${escapeHtml(item.nomor_rm || "-")}
            · ${escapeHtml(item.dokter || "-")}
            · ${escapeHtml(item.poli || "-")}
            · ${formatDateTime(item.tanggal)}
          </div>
        </div>

        <span class="prescription-status ${escapeHtml(item.status_class || "status-waiting")}">
          ${escapeHtml(item.status || "Menunggu")}
        </span>
      </div>
    `).join("");
    }

    function renderProcess(data) {
      const box = document.getElementById("farmasiProcessList");
      const items = data.process?.items || [];

      if (!items.length) {
        box.innerHTML =
          '<div class="text-center text-muted py-4">Belum ada data proses.</div>';
        return;
      }

      box.innerHTML = items.map(item => {
        const value = Number(item.value || 0);
        const total = Number(item.total || 0);
        const percentage = Math.max(
          0,
          Math.min(100, Number(item.percentage || 0))
        );

        return `
        <div class="process-box">
          <div class="process-top">
            <div class="process-title">
              ${escapeHtml(item.name || "-")}
            </div>

            <div class="process-value">
              ${numberFormat(value)} / ${numberFormat(total)}
            </div>
          </div>

          <div class="process-bar">
            <span style="width:${percentage}%"></span>
          </div>
        </div>
      `;
      }).join("");
    }

    function renderLowStock(data) {
      const box = document.getElementById("farmasiLowStockList");
      const items = data.stock?.items || [];

      const badge = document.getElementById("farmasiLowStockBadge");

      if (badge) {
        badge.textContent =
          numberFormat(data.stock?.low_total || 0) + " Item";
      }

      if (!items.length) {
        box.innerHTML = `
        <div class="text-center text-success py-4">
          <iconify-icon icon="solar:check-circle-bold"
            style="font-size:30px;">
          </iconify-icon>
          <div class="mt-2">Tidak ada stok di bawah minimum.</div>
        </div>
      `;
        return;
      }

      box.innerHTML = items.slice(0, 10).map(item => {
        const stock = Number(item.stock || 0);

        return `
        <div class="stock-item">
          <div class="stock-icon">
            <iconify-icon icon="solar:pills-3-bold"></iconify-icon>
          </div>

          <div style="min-width:0;">
            <div class="stock-name">
              ${escapeHtml(item.nama || "-")}
            </div>

            <div class="stock-code">
              ${escapeHtml(item.kode || "-")}
              · ${escapeHtml(item.unit || "-")}
            </div>
          </div>

          <div class="stock-value">
            <div class="stock-number">
              ${numberFormat(stock)}
            </div>

            <div class="stock-warning">
              ${escapeHtml(item.warning || "Stok Menipis")}
            </div>
          </div>
        </div>
      `;
      }).join("");
    }

    function renderCategory(data) {
      const box = document.getElementById("farmasiCategoryList");
      const items = data.category || [];

      if (!items.length) {
        box.innerHTML =
          '<div class="text-center text-muted py-4">Belum ada data distribusi obat.</div>';
        return;
      }

      const icons = [
        "solar:pills-3-bold",
        "solar:pills-3-bold",
        "solar:waterdrops-bold",
        "solar:syringe-bold",
        "solar:medical-kit-bold"
      ];

      box.innerHTML = items.map((item, index) => `
      <div class="category-item">
        <div class="category-icon">
          <iconify-icon icon="${icons[index % icons.length]}"></iconify-icon>
        </div>

        <div class="category-name">
          ${escapeHtml(item.kategori || "Tidak Diketahui")}
        </div>

        <div class="category-count">
          ${numberFormat(item.total)}
        </div>
      </div>
    `).join("");
    }

    function renderAlerts(data) {
      const box = document.getElementById("farmasiAlertList");
      const items = data.alerts?.items || [];

      if (!items.length) {
        box.innerHTML = `
        <div class="text-center text-success py-4">
          <iconify-icon icon="solar:check-circle-bold"
            style="font-size:30px;">
          </iconify-icon>
          <div class="mt-2">Tidak ada perhatian khusus.</div>
        </div>
      `;
        return;
      }

      box.innerHTML = items.map(item => `
      <div class="drug-alert">
        <div class="drug-alert-icon">
          <iconify-icon
            icon="${escapeHtml(item.icon || "solar:danger-triangle-bold")}">
          </iconify-icon>
        </div>

        <div style="min-width:0;">
          <div class="drug-alert-name">
            ${escapeHtml(item.title || "-")}
          </div>

          <div class="drug-alert-desc">
            ${escapeHtml(item.text || "-")}
          </div>
        </div>

        <a href="#" class="drug-alert-action"
           onclick="return false;">
          Lihat
        </a>
      </div>
    `).join("");
    }

    function renderChart(data) {
      const canvas = document.getElementById("farmasiPrescriptionChart");

      if (!canvas || typeof Chart === "undefined") return;

      const trend = data.trend || [];

      const labels = trend.map(item => formatDate(item.date));
      const masuk = trend.map(item => Number(item.masuk || 0));
      const selesai = trend.map(item => Number(item.selesai || 0));

      if (prescriptionChart) {
        prescriptionChart.destroy();
      }

      prescriptionChart = new Chart(canvas, {
        type: "bar",
        data: {
          labels,
          datasets: [{
              label: "Resep Masuk",
              data: masuk,
              backgroundColor: "#635bff",
              borderRadius: 7
            },
            {
              label: "Resep Selesai",
              data: selesai,
              backgroundColor: "#93c5fd",
              borderRadius: 7
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                boxWidth: 10,
                font: {
                  size: 10
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              border: {
                display: false
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: "#f0f1f5"
              },
              border: {
                display: false
              }
            }
          }
        }
      });
    }

    function updateTime() {
      if (!lastUpdate) return;

      const now = new Date();

      lastUpdate.textContent =
        String(now.getHours()).padStart(2, "0") +
        ":" +
        String(now.getMinutes()).padStart(2, "0");
    }

    async function loadDashboardFarmasi() {
      setLoading();

      const params = new URLSearchParams({
        action: "dashboard",
        periode: period.value,
        tanggal_mulai: startDate.value,
        tanggal_selesai: endDate.value
      });

      try {
        const response = await fetch(
          endpoint + "?" + params.toString(), {
            method: "GET",
            headers: {
              "Accept": "application/json"
            },
            cache: "no-store"
          }
        );

        const text = await response.text();

        let data;

        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Response bukan JSON:", text);
          throw new Error(
            "Controller farmasi mengembalikan response yang bukan JSON."
          );
        }

        if (!response.ok || !data.status) {
          throw new Error(
            data.message || "Gagal mengambil data dashboard farmasi."
          );
        }

        renderKPI(data);
        renderPrescriptions(data);
        renderProcess(data);
        renderLowStock(data);
        renderCategory(data);
        renderAlerts(data);
        renderChart(data);
        updateTime();

      } catch (error) {
        console.error("Dashboard Farmasi:", error);

        const message = escapeHtml(error.message);

        document.getElementById("farmasiPrescriptionList").innerHTML =
          `<div class="alert alert-danger mb-0">${message}</div>`;

        document.getElementById("farmasiProcessList").innerHTML =
          `<div class="alert alert-danger mb-0">${message}</div>`;

        document.getElementById("farmasiLowStockList").innerHTML =
          `<div class="alert alert-danger mb-0">${message}</div>`;

        document.getElementById("farmasiCategoryList").innerHTML =
          `<div class="alert alert-danger mb-0">${message}</div>`;

        document.getElementById("farmasiAlertList").innerHTML =
          `<div class="alert alert-danger mb-0">${message}</div>`;

        document.getElementById("farKpiResepMasuk").textContent = "0";
        document.getElementById("farKpiMenunggu").textContent = "0";
        document.getElementById("farKpiDiproses").textContent = "0";
        document.getElementById("farKpiSelesai").textContent = "0";
        document.getElementById("farKpiPersentase").textContent = "0%";
        document.getElementById("farWaitingBadge").textContent = "0";

        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "error",
            title: "Dashboard Farmasi",
            text: error.message,
            confirmButtonText: "OK"
          });
        }
      }
    }

    period.addEventListener("change", updatePeriod);

    applyBtn.addEventListener("click", function() {
      if (!startDate.value || !endDate.value) {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "warning",
            title: "Periode belum lengkap",
            text: "Silakan tentukan tanggal mulai dan tanggal selesai."
          });
        }
        return;
      }

      loadDashboardFarmasi();
    });

    updatePeriod();
    loadDashboardFarmasi();

    // Jam tampilan saja.
    setInterval(updateTime, 60000);

  });
</script>