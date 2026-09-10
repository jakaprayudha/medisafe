<style>
  /* =========================================================
       DASHBOARD ADMIN
    ========================================================= */

  .admin-dashboard {
    --ad-primary: #635bff;
    --ad-primary-soft: #eeecff;
    --ad-text: #273444;
    --ad-muted: #7b8494;
    --ad-border: #edf0f5;
  }

  .admin-dashboard .dash-card {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
  }

  .admin-dashboard .dash-card:hover {
    box-shadow: 0 10px 30px rgba(30, 40, 60, .05);
  }

  /* =========================================================
       KPI
    ========================================================= */

  .admin-dashboard .kpi-card {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
    transition: .2s ease;
  }

  .admin-dashboard .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(30, 40, 60, .06);
  }

  .admin-dashboard .kpi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
  }

  .admin-dashboard .kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 23px;
  }

  .admin-dashboard .kpi-purple {
    background: #eeecff;
    color: #635bff;
  }

  .admin-dashboard .kpi-blue {
    background: #e8f4ff;
    color: #2185d0;
  }

  .admin-dashboard .kpi-green {
    background: #e8f8ef;
    color: #1a9b5f;
  }

  .admin-dashboard .kpi-orange {
    background: #fff2e3;
    color: #ed8b22;
  }

  .admin-dashboard .kpi-title {
    font-size: 13px;
    color: var(--ad-muted);
    margin-bottom: 5px;
  }

  .admin-dashboard .kpi-value {
    color: var(--ad-text);
    font-size: 26px;
    font-weight: 700;
    line-height: 1.2;
  }

  .admin-dashboard .kpi-info {
    font-size: 11px;
    margin-top: 7px;
  }

  .admin-dashboard .kpi-up {
    color: #16a34a;
  }

  .admin-dashboard .kpi-down {
    color: #dc2626;
  }

  /* =========================================================
       CARD HEADER
    ========================================================= */

  .admin-dashboard .dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;

    margin-bottom: 18px;
  }

  .admin-dashboard .dash-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--ad-text);
    margin: 0;
  }

  .admin-dashboard .dash-subtitle {
    color: var(--ad-muted);
    font-size: 12px;
    margin-top: 3px;
  }

  .admin-dashboard .dash-filter {
    width: auto;
    min-width: 110px;

    border-radius: 10px;
    border-color: var(--ad-border);

    font-size: 12px;
  }

  /* =========================================================
       QUEUE
    ========================================================= */

  .admin-dashboard .queue-item {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .queue-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .queue-number {
    width: 38px;
    height: 38px;

    border-radius: 11px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #f0efff;
    color: var(--ad-primary);

    font-size: 12px;
    font-weight: 700;

    flex-shrink: 0;
  }

  .admin-dashboard .queue-name {
    color: var(--ad-text);
    font-size: 13px;
    font-weight: 600;
  }

  .admin-dashboard .queue-detail {
    color: var(--ad-muted);
    font-size: 11px;
    margin-top: 2px;
  }

  .admin-dashboard .queue-status {
    margin-left: auto;

    padding: 5px 9px;

    border-radius: 8px;

    font-size: 10px;
    white-space: nowrap;
  }

  .admin-dashboard .status-waiting {
    background: #fff3d9;
    color: #c47a00;
  }

  .admin-dashboard .status-process {
    background: #e8f2ff;
    color: #2377c7;
  }

  .admin-dashboard .status-done {
    background: #e7f8ee;
    color: #168a4e;
  }

  /* =========================================================
       ROOM
    ========================================================= */

  .admin-dashboard .room-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .room-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .room-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--ad-text);
  }

  .admin-dashboard .room-total {
    font-size: 11px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .room-progress {
    width: 120px;
    height: 6px;

    background: #edf0f4;

    border-radius: 20px;

    overflow: hidden;

    margin-top: 7px;
  }

  .admin-dashboard .room-progress span {
    display: block;

    height: 100%;

    background: var(--ad-primary);

    border-radius: inherit;
  }

  .admin-dashboard .room-value {
    font-size: 11px;
    font-weight: 600;
    color: var(--ad-text);
  }

  /* =========================================================
       STOCK
    ========================================================= */

  .admin-dashboard .stock-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .stock-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .stock-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--ad-text);
  }

  .admin-dashboard .stock-code {
    font-size: 10px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .stock-value {
    color: #dc2626;
    font-size: 12px;
    font-weight: 700;
  }

  /* =========================================================
       ACTIVITY
    ========================================================= */

  .admin-dashboard .activity-item {
    display: flex;
    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .activity-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .activity-icon {
    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: var(--ad-primary-soft);
    color: var(--ad-primary);

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;
  }

  .admin-dashboard .activity-text {
    font-size: 12px;
    line-height: 1.5;
    color: var(--ad-text);
  }

  .admin-dashboard .activity-time {
    font-size: 10px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  /* =========================================================
       QUICK ACCESS
    ========================================================= */

  .admin-dashboard .quick-item {
    display: block;

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 14px;

    padding: 15px;

    text-decoration: none;

    transition: .2s ease;
  }

  .admin-dashboard .quick-item:hover {
    background: #f5f4ff;
    border-color: #e2dfff;
  }

  .admin-dashboard .quick-icon {
    width: 36px;
    height: 36px;

    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eeecff;
    color: var(--ad-primary);

    font-size: 19px;
  }

  .admin-dashboard .quick-title {
    color: var(--ad-text);

    font-size: 12px;
    font-weight: 600;

    margin-top: 9px;
  }

  .admin-dashboard .quick-desc {
    color: var(--ad-muted);

    font-size: 10px;

    margin-top: 2px;
  }

  /* =========================================================
       RESPONSIVE
    ========================================================= */

  @media (max-width: 767px) {

    .admin-dashboard .dash-header {
      align-items: flex-start;
    }

    .admin-dashboard .dash-filter {
      min-width: 95px;
    }

    .admin-dashboard .room-progress {
      width: 90px;
    }

  }

  /* =========================================================
   FILTER PERIODE
========================================================= */

  .admin-dashboard .dash-filter-wrapper {

    background: #fff;

    border: 1px solid var(--ad-border);

    border-radius: 18px;

    padding: 17px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

  }


  .admin-dashboard .dash-filter-title {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-shrink: 0;

  }


  .admin-dashboard .dash-filter-icon {

    width: 42px;

    height: 42px;

    border-radius: 12px;

    background: #eeecff;

    color: #635bff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

  }


  .admin-dashboard .dash-filter-heading {

    font-size: 13px;

    font-weight: 700;

    color: var(--ad-text);

  }


  .admin-dashboard .dash-filter-description {

    font-size: 11px;

    color: var(--ad-muted);

    margin-top: 2px;

  }


  .admin-dashboard .dash-filter-form {

    display: flex;

    align-items: flex-end;

    gap: 10px;

  }


  .admin-dashboard .dash-filter-group {

    min-width: 145px;

  }


  .admin-dashboard .dash-filter-group label {

    display: block;

    font-size: 10px;

    font-weight: 600;

    color: var(--ad-muted);

    margin-bottom: 5px;

  }


  .admin-dashboard .dash-filter-group .form-select,
  .admin-dashboard .dash-filter-group .form-control {

    height: 38px;

    border-radius: 10px;

    border-color: var(--ad-border);

    font-size: 12px;

    color: var(--ad-text);

    box-shadow: none;

  }


  .admin-dashboard .dash-filter-group .form-select:focus,
  .admin-dashboard .dash-filter-group .form-control:focus {

    border-color: #635bff;

    box-shadow:
      0 0 0 3px rgba(99, 91, 255, .08);

  }


  .admin-dashboard .dash-filter-button {

    height: 38px;

    border-radius: 10px;

    font-size: 12px;

    padding-left: 16px;

    padding-right: 16px;

    white-space: nowrap;

  }


  /* CUSTOM DATE */

  .admin-dashboard .custom-date-field {

    display: block;

  }


  /* MOBILE */

  @media (max-width: 1100px) {

    .admin-dashboard .dash-filter-wrapper {

      align-items: flex-start;

      flex-direction: column;

    }

    .admin-dashboard .dash-filter-form {

      width: 100%;

      flex-wrap: wrap;

    }

  }


  @media (max-width: 767px) {

    .admin-dashboard .dash-filter-form {

      display: grid;

      grid-template-columns: 1fr 1fr;

      width: 100%;

    }

    .admin-dashboard .dash-filter-group {

      min-width: 0;

    }

    .admin-dashboard .dash-filter-group:first-child {

      grid-column: span 2;

    }

    .admin-dashboard .dash-filter-button {

      width: 100%;

    }

  }


  @media (max-width: 480px) {

    .admin-dashboard .dash-filter-form {

      grid-template-columns: 1fr;

    }

    .admin-dashboard .dash-filter-group:first-child {

      grid-column: auto;

    }

  }

  /* =========================================================
     REMINDER MASA KONTRAK
  ========================================================== */

  .admin-dashboard .contract-reminder-wrapper {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    overflow: hidden;
  }

  .admin-dashboard .contract-reminder-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 17px 20px;
    border-bottom: 1px solid var(--ad-border);
  }

  .admin-dashboard .contract-reminder-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .admin-dashboard .contract-reminder-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eeecff;
    color: #635bff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    flex-shrink: 0;
  }

  .admin-dashboard .contract-reminder-heading {
    font-size: 13px;
    font-weight: 700;
    color: var(--ad-text);
  }

  .admin-dashboard .contract-reminder-description {
    font-size: 11px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .contract-reminder-body {
    padding: 17px 20px;
  }

  .admin-dashboard .contract-info-card,
  .admin-dashboard .contract-countdown-card {
    height: 100%;
    border: 1px solid var(--ad-border);
    border-radius: 14px;
    padding: 15px;
    background: #fafbfc;
  }

  .admin-dashboard .contract-info-label {
    font-size: 10px;
    color: var(--ad-muted);
    font-weight: 600;
    margin-bottom: 6px;
  }

  .admin-dashboard .contract-info-value {
    font-size: 15px;
    line-height: 1.35;
    font-weight: 700;
    color: var(--ad-text);
  }

  .admin-dashboard .contract-info-sub {
    margin-top: 5px;
    font-size: 11px;
    color: var(--ad-muted);
  }

  .admin-dashboard .contract-countdown-card {
    background: linear-gradient(135deg, #f8f7ff, #ffffff);
    border-color: #e5e2ff;
  }

  .admin-dashboard .contract-countdown-title {
    font-size: 11px;
    color: var(--ad-muted);
    font-weight: 600;
    margin-bottom: 9px;
  }

  .admin-dashboard .contract-countdown {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .admin-dashboard .countdown-item {
    min-width: 54px;
    text-align: center;
  }

  .admin-dashboard .countdown-value {
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    color: var(--ad-primary);
  }

  .admin-dashboard .countdown-label {
    margin-top: 5px;
    font-size: 9px;
    color: var(--ad-muted);
    text-transform: uppercase;
    letter-spacing: .35px;
  }

  .admin-dashboard .countdown-separator {
    font-size: 18px;
    font-weight: 700;
    color: #adb5bd;
    margin-top: -12px;
  }

  .admin-dashboard .contract-summary {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
  }

  .admin-dashboard .contract-summary-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #f8f9fa;
    font-size: 11px;
    color: var(--ad-muted);
  }

  .admin-dashboard .contract-summary-item strong {
    color: var(--ad-text);
    font-weight: 700;
  }

  .admin-dashboard .contract-summary-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
  }

  @media (max-width: 767px) {
    .admin-dashboard .contract-reminder-header {
      align-items: flex-start;
    }

    .admin-dashboard .contract-countdown {
      gap: 4px;
    }

    .admin-dashboard .countdown-item {
      min-width: 46px;
    }

    .admin-dashboard .countdown-value {
      font-size: 20px;
    }

    .admin-dashboard .countdown-separator {
      font-size: 15px;
    }
  }
</style>
<?php
/**
 * ============================================================
 * DASHBOARD ADMIN
 * Data dinamis dari:
 * controller/dashboard/dashboardController.php?action=dashboard
 * ============================================================
 */
?>

<!-- =========================================================
     REMINDER MASA KONTRAK FASKES
========================================================== -->
<div class="admin-dashboard mb-3">

  <div class="contract-reminder-wrapper">

    <div class="contract-reminder-header">

      <div class="contract-reminder-title">

        <div class="contract-reminder-icon">
          <iconify-icon icon="solar:document-text-bold"></iconify-icon>
        </div>

        <div>
          <div class="contract-reminder-heading">
            Masa Kontrak Faskes
          </div>

          <div class="contract-reminder-description">
            Monitoring masa berlaku kontrak dan pengingat perpanjangan
          </div>
        </div>

      </div>

      <div id="contractReminderStatus">
        <span class="badge bg-secondary-subtle text-secondary">
          Memuat...
        </span>
      </div>

    </div>

    <div class="contract-reminder-body">

      <div class="row g-3">

        <div class="col-lg-4">
          <div class="contract-info-card">
            <div class="contract-info-label">Faskes</div>

            <div class="contract-info-value" id="contractFaskes">
              Memuat...
            </div>

            <div class="contract-info-sub" id="contractNumber">
              -
            </div>
          </div>
        </div>

        <div class="col-lg-3">
          <div class="contract-info-card">
            <div class="contract-info-label">Periode Kontrak</div>

            <div class="contract-info-value" id="contractPeriod">
              -
            </div>

            <div class="contract-info-sub" id="contractEndDate">
              -
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="contract-countdown-card">

            <div class="contract-countdown-title">
              <iconify-icon
                icon="solar:alarm-bold"
                class="me-1">
              </iconify-icon>
              Sisa Masa Kontrak
            </div>

            <div class="contract-countdown" id="contractCountdown">

              <div class="countdown-item">
                <div class="countdown-value" id="contractYears">00</div>
                <div class="countdown-label">Tahun</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractMonths">00</div>
                <div class="countdown-label">Bulan</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractDays">00</div>
                <div class="countdown-label">Hari</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractHours">00</div>
                <div class="countdown-label">Jam</div>
              </div>

            </div>

          </div>
        </div>

      </div>



    </div>

  </div>

</div>

<!-- =========================================================
     FILTER PERIODE DASHBOARD
========================================================== -->
<div class="admin-dashboard mb-3">

  <div class="dash-filter-wrapper">

    <div class="dash-filter-title">

      <div class="dash-filter-icon">
        <iconify-icon icon="solar:calendar-search-bold"></iconify-icon>
      </div>

      <div>
        <div class="dash-filter-heading">
          Periode Dashboard
        </div>

        <div class="dash-filter-description">
          Pilih periode data yang ingin ditampilkan
        </div>
      </div>

    </div>


    <div class="dash-filter-form">

      <!-- PERIODE -->
      <div class="dash-filter-group">

        <label>
          Periode
        </label>

        <select
          id="dashboardPeriod"
          class="form-select">

          <option value="today">
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

          <option value="thismonth" selected>
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


      <!-- TANGGAL MULAI -->
      <div
        class="dash-filter-group custom-date-field"
        id="startDateGroup">

        <label>
          Dari
        </label>

        <input
          type="date"
          id="dashboardStartDate"
          class="form-control"
          value="<?= date('Y-m-01') ?>">

      </div>


      <!-- TANGGAL AKHIR -->
      <div
        class="dash-filter-group custom-date-field"
        id="endDateGroup">

        <label>
          Sampai
        </label>

        <input
          type="date"
          id="dashboardEndDate"
          class="form-control"
          value="<?= date('Y-m-d') ?>">

      </div>


      <!-- BUTTON -->
      <button
        type="button"
        class="btn btn-primary dash-filter-button"
        id="applyDashboardFilter">

        <iconify-icon
          icon="solar:filter-bold"
          class="me-1">
        </iconify-icon>

        Terapkan

      </button>

    </div>

  </div>

</div>


<div class="admin-dashboard">

  <!-- =====================================================
         KPI
    ====================================================== -->
  <div class="row g-3 mb-3">

    <!-- TOTAL PASIEN -->
    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Total Pasien
            </div>

            <div
              class="kpi-value"
              id="kpiTotalPasien">

              0

            </div>

            <div
              class="kpi-info"
              id="kpiTotalPasienInfo">

              Data berdasarkan periode

            </div>

          </div>

          <div class="kpi-icon kpi-purple">

            <iconify-icon
              icon="solar:users-group-rounded-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- PASIEN BARU -->
    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Pasien Baru
            </div>

            <div
              class="kpi-value"
              id="kpiPasienBaru">

              0

            </div>

            <div
              class="kpi-info"
              id="kpiPasienBaruInfo">

              Pasien dengan kunjungan pertama

            </div>

          </div>

          <div class="kpi-icon kpi-blue">

            <iconify-icon
              icon="solar:user-plus-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- KUNJUNGAN RAWAT JALAN -->
    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Kunjungan Rawat Jalan
            </div>

            <div
              class="kpi-value"
              id="kpiRawatJalan">

              0

            </div>

            <div
              class="kpi-info"
              id="kpiRawatJalanInfo">

              Total kunjungan rawat jalan

            </div>

          </div>

          <div class="kpi-icon kpi-green">

            <iconify-icon
              icon="solar:stethoscope-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- PENDAPATAN -->
    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Total Pembayaran
            </div>

            <div
              class="kpi-value"
              id="kpiPendapatan"
              style="font-size:22px">

              Rp 0

            </div>

            <div
              class="kpi-info"
              id="kpiPendapatanInfo">

              Berdasarkan periode

            </div>

          </div>

          <div class="kpi-icon kpi-orange">

            <iconify-icon
              icon="solar:wallet-money-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         CHART
    ====================================================== -->
  <div class="row g-3 mb-3">

    <!-- KUNJUNGAN -->
    <div class="col-xl-8">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Statistik Kunjungan
            </h6>

            <div
              class="dash-subtitle"
              id="visitChartSubtitle">

              Jumlah kunjungan pasien

            </div>

          </div>

          <select
            class="form-select dash-filter"
            id="visitChartMode">

            <option value="period">
              Periode
            </option>

          </select>

        </div>

        <div style="height:280px">

          <canvas id="adminVisitChart"></canvas>

        </div>

      </div>

    </div>


    <!-- JENIS PEMBAYARAN -->
    <div class="col-xl-4">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Jenis Pembayaran
            </h6>

            <div
              class="dash-subtitle"
              id="paymentChartSubtitle">

              Distribusi pembayaran

            </div>

          </div>

        </div>


        <div style="height:210px">

          <canvas id="adminPaymentChart"></canvas>

        </div>


        <div
          class="row text-center mt-3"
          id="paymentLegend">

          <div class="col-12">

            <small class="text-muted">
              Memuat data...
            </small>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         OPERASIONAL
    ====================================================== -->
  <div class="row g-3 mb-3">

    <!-- ANTRIAN -->
    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Antrian Pasien
            </h6>

            <div
              class="dash-subtitle"
              id="queueSubtitle">

              Data antrian

            </div>

          </div>

          <span
            class="badge bg-primary"
            id="queueWaitingBadge">

            0 Menunggu

          </span>

        </div>


        <div id="queueContainer">

          <div class="text-center py-4 text-muted">

            Memuat data antrian...

          </div>

        </div>

      </div>

    </div>


    <!-- KAMAR -->
    <div class="col-xl-4">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Ketersediaan Kamar
            </h6>

            <div class="dash-subtitle">
              Status rawat inap
            </div>

          </div>

          <iconify-icon
            icon="solar:bed-bold"
            width="23">
          </iconify-icon>

        </div>


        <div id="roomContainer">

          <div class="text-center py-4 text-muted">

            Memuat data kamar...

          </div>

        </div>

      </div>

    </div>


    <!-- STOK OBAT -->
    <div class="col-xl-3">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Stok Menipis
            </h6>

            <div class="dash-subtitle">
              Perlu diperhatikan
            </div>

          </div>

          <span
            class="badge bg-danger"
            id="lowStockBadge">

            0 Item

          </span>

        </div>


        <div id="stockContainer">

          <div class="text-center py-4 text-muted">

            Memuat stok...

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         ACTIVITY + QUICK ACCESS
    ====================================================== -->
  <div class="row g-3">

    <!-- ACTIVITY -->
    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Aktivitas Terbaru
            </h6>

            <div class="dash-subtitle">
              Aktivitas sistem
            </div>

          </div>

          <a
            href="#"
            class="small text-decoration-none">

            Lihat Semua

          </a>

        </div>


        <div id="activityContainer">

          <div class="text-center py-4 text-muted">

            Memuat aktivitas...

          </div>

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

          <!-- PASIEN -->
          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:user-plus-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Pasien Baru
              </div>

              <div class="quick-desc">
                Registrasi pasien
              </div>

            </a>

          </div>


          <!-- POLIKLINIK -->
          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:stethoscope-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Poliklinik
              </div>

              <div class="quick-desc">
                Pelayanan pasien
              </div>

            </a>

          </div>


          <!-- FARMASI -->
          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:pills-3-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Farmasi
              </div>

              <div class="quick-desc">
                Resep & obat
              </div>

            </a>

          </div>


          <!-- RME -->
          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:document-text-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Monitoring RME
              </div>

              <div class="quick-desc">
                Rekam medis elektronik
              </div>

            </a>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>



<!-- =========================================================
     CONTRACT REMINDER JAVASCRIPT
========================================================== -->
<script>
  (function() {

    "use strict";

    const CONTRACT_URL =
      "controller/dashboard/contractReminderController.php?action=dashboard";

    let contractCountdownTimer = null;


    function escapeContractHtml(value) {

      if (value === null || value === undefined) {
        return "";
      }

      return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }


    function formatContractDate(value) {

      if (!value) {
        return "-";
      }

      const date = new Date(value + "T00:00:00");

      if (isNaN(date.getTime())) {
        return value;
      }

      return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    }


    function setContractZero() {

      const ids = [
        "contractYears",
        "contractMonths",
        "contractDays",
        "contractHours"
      ];

      ids.forEach(function(id) {

        const element = document.getElementById(id);

        if (element) {
          element.textContent = "00";
        }

      });
    }


    function updateContractCountdown(timestamp) {

      const now = new Date();
      const end = new Date(Number(timestamp));

      if (isNaN(end.getTime()) || end <= now) {

        setContractZero();

        return false;
      }


      /*
       * Hitung kalender secara presisi:
       * Tahun -> Bulan -> Hari -> Jam
       */
      let cursor = new Date(now);


      let years =
        end.getFullYear() -
        cursor.getFullYear();


      let yearTest = new Date(cursor);

      yearTest.setFullYear(
        cursor.getFullYear() + years
      );


      if (yearTest > end) {
        years--;
      }


      cursor.setFullYear(
        cursor.getFullYear() + years
      );


      let months =
        (end.getFullYear() - cursor.getFullYear()) * 12 +
        (end.getMonth() - cursor.getMonth());


      let monthTest = new Date(cursor);

      monthTest.setMonth(
        cursor.getMonth() + months
      );


      if (monthTest > end) {
        months--;
      }


      cursor.setMonth(
        cursor.getMonth() + months
      );


      let remainingMs = end.getTime() - cursor.getTime();

      let days = Math.floor(
        remainingMs / (1000 * 60 * 60 * 24)
      );

      cursor.setDate(
        cursor.getDate() + days
      );


      remainingMs = end.getTime() - cursor.getTime();

      let hours = Math.floor(
        remainingMs / (1000 * 60 * 60)
      );


      document.getElementById("contractYears").textContent =
        String(Math.max(0, years)).padStart(2, "0");

      document.getElementById("contractMonths").textContent =
        String(Math.max(0, months)).padStart(2, "0");

      document.getElementById("contractDays").textContent =
        String(Math.max(0, days)).padStart(2, "0");

      document.getElementById("contractHours").textContent =
        String(Math.max(0, hours)).padStart(2, "0");


      return true;
    }


    function startContractCountdown(timestamp) {

      if (contractCountdownTimer) {

        clearInterval(contractCountdownTimer);

        contractCountdownTimer = null;
      }


      const run = function() {

        const stillActive =
          updateContractCountdown(timestamp);

        if (!stillActive && contractCountdownTimer) {

          clearInterval(contractCountdownTimer);

          contractCountdownTimer = null;
        }

      };


      run();

      /*
       * Update setiap menit karena tampilan hanya
       * sampai satuan jam.
       */
      contractCountdownTimer =
        setInterval(run, 60 * 1000);
    }


    function renderContractReminder(response) {

      /*
       * Data kontrak bersifat PER KONTRAK.
       *
       * Backend lama bisa mengirim:
       * nearest: null
       * items: [ ...kontrak... ]
       *
       * Jadi frontend tidak boleh bergantung pada nearest.
       * Gunakan nearest jika tersedia, jika tidak gunakan
       * kontrak pertama dari items.
       */
      const items = Array.isArray(response.items) ?
        response.items :
        [];

      const nearest =
        response.nearest ||
        (items.length > 0 ? items[0] : null);

      const statusElement =
        document.getElementById("contractReminderStatus");

      if (!nearest) {

        document.getElementById("contractFaskes").textContent =
          "Tidak ada data kontrak";

        document.getElementById("contractNumber").textContent =
          "-";

        document.getElementById("contractPeriod").textContent =
          "-";

        document.getElementById("contractEndDate").textContent =
          "-";

        statusElement.innerHTML =
          '<span class="badge bg-secondary-subtle text-secondary">' +
          'Tidak Ada Data' +
          '</span>';

        setContractZero();

        return;
      }

      /*
       * IDENTITAS FASKES
       */
      document.getElementById("contractFaskes").textContent =
        nearest.faskes_code || "-";

      /*
       * NOMOR KONTRAK
       */
      document.getElementById("contractNumber").textContent =
        nearest.contract_number &&
        nearest.contract_number !== "-" ?
        "No. Kontrak: " + nearest.contract_number :
        "Nomor kontrak tidak tersedia";

      /*
       * PERIODE KONTRAK
       */
      document.getElementById("contractPeriod").textContent =
        formatContractDate(nearest.contract_start) +
        " - " +
        formatContractDate(nearest.contract_end);

      /*
       * TANGGAL BERAKHIR
       */
      document.getElementById("contractEndDate").textContent =
        "Berakhir pada " +
        formatContractDate(nearest.contract_end);

      /*
       * STATUS KONTRAK
       */
      let statusHtml = "";

      if (nearest.status === "active") {

        statusHtml =
          '<span class="badge bg-success-subtle text-success">' +
          'Kontrak Aktif' +
          '</span>';

      } else if (nearest.status === "warning") {

        statusHtml =
          '<span class="badge bg-warning-subtle text-warning">' +
          'Perlu Perhatian' +
          '</span>';

      } else if (nearest.status === "critical") {

        statusHtml =
          '<span class="badge bg-danger-subtle text-danger">' +
          'Segera Berakhir' +
          '</span>';

      } else {

        statusHtml =
          '<span class="badge bg-dark-subtle text-dark">' +
          'Kontrak Berakhir' +
          '</span>';
      }

      statusElement.innerHTML = statusHtml;

      /*
       * COUNTDOWN
       *
       * Untuk kontrak aktif:
       * Tahun : Bulan : Hari : Jam
       *
       * Untuk kontrak expired:
       * 00 : 00 : 00 : 00
       */
      startContractCountdown(
        nearest.contract_end_timestamp
      );
    }


    async function loadContractReminder() {

      try {

        const response =
          await fetch(CONTRACT_URL, {
            method: "GET",
            headers: {
              "Accept": "application/json",
              "X-Requested-With": "XMLHttpRequest"
            },
            cache: "no-store"
          });


        if (!response.ok) {

          throw new Error(
            "HTTP Error " + response.status
          );
        }


        const result =
          await response.json();


        console.log(
          "Contract Reminder Response:",
          result
        );


        if (!result.status) {

          throw new Error(
            result.message ||
            "Gagal mengambil data kontrak."
          );
        }


        renderContractReminder(result);


      } catch (error) {

        console.error(
          "Contract Reminder Error:",
          error
        );


        document.getElementById("contractFaskes").textContent =
          "Gagal memuat data";

        document.getElementById("contractNumber").textContent =
          error.message || "Terjadi kesalahan";

        document.getElementById("contractPeriod").textContent =
          "-";

        document.getElementById("contractEndDate").textContent =
          "-";

        document.getElementById("contractReminderStatus").innerHTML =
          '<span class="badge bg-danger-subtle text-danger">' +
          'Error' +
          '</span>';

        setContractZero();
      }

    }


    loadContractReminder();

  })();
</script>

<!-- =========================================================
     DASHBOARD JAVASCRIPT
========================================================== -->
<script>
  (function() {

    "use strict";


    /* =====================================================
       CONFIG
    ====================================================== */

    const DASHBOARD_URL =
      "controller/dashboard/dashboardController.php?action=dashboard";


    let visitChart = null;
    let paymentChart = null;


    /* =====================================================
       ELEMENT
    ====================================================== */

    const periodSelect =
      document.getElementById("dashboardPeriod");

    const startDate =
      document.getElementById("dashboardStartDate");

    const endDate =
      document.getElementById("dashboardEndDate");

    const startDateGroup =
      document.getElementById("startDateGroup");

    const endDateGroup =
      document.getElementById("endDateGroup");

    const applyButton =
      document.getElementById("applyDashboardFilter");


    /* =====================================================
       ESCAPE HTML
    ====================================================== */

    function escapeHtml(value) {

      if (value === null || value === undefined) {
        return "";
      }

      return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }


    /* =====================================================
       NUMBER FORMAT
    ====================================================== */

    function numberFormat(value) {

      value = Number(value || 0);

      return new Intl.NumberFormat("id-ID")
        .format(value);
    }


    /* =====================================================
       RUPIAH FORMAT
    ====================================================== */

    function rupiah(value) {

      value = Number(value || 0);

      return "Rp " +
        new Intl.NumberFormat("id-ID")
        .format(value);

    }


    /* =====================================================
       DATE FORMAT
    ====================================================== */

    function formatDate(dateString) {

      if (!dateString) {
        return "-";
      }

      const date =
        new Date(dateString + "T00:00:00");

      if (isNaN(date.getTime())) {
        return dateString;
      }

      return date.toLocaleDateString(
        "id-ID", {
          day: "2-digit",
          month: "short"
        }
      );

    }


    /* =====================================================
       DATETIME RELATIVE
    ====================================================== */

    function relativeTime(dateString) {

      if (!dateString) {
        return "";
      }

      const date =
        new Date(
          dateString.replace(" ", "T")
        );

      if (isNaN(date.getTime())) {
        return dateString;
      }

      const now = new Date();

      const diff =
        Math.floor(
          (now - date) / 1000
        );

      if (diff < 60) {
        return "Baru saja";
      }

      if (diff < 3600) {

        const minute =
          Math.floor(diff / 60);

        return minute + " menit yang lalu";

      }

      if (diff < 86400) {

        const hour =
          Math.floor(diff / 3600);

        return hour + " jam yang lalu";

      }

      const day =
        Math.floor(diff / 86400);

      return day + " hari yang lalu";

    }


    /* =====================================================
       UPDATE DATE FIELD
    ====================================================== */

    function updateDateFields() {

      const period =
        periodSelect.value;

      const isCustom =
        period === "custom";

      if (startDateGroup) {
        startDateGroup.style.display =
          isCustom ? "" : "none";
      }

      if (endDateGroup) {
        endDateGroup.style.display =
          isCustom ? "" : "none";
      }

    }


    /* =====================================================
       LOADING STATE
    ====================================================== */

    function setLoading(isLoading) {

      if (!applyButton) {
        return;
      }

      if (isLoading) {

        applyButton.disabled = true;

        applyButton.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                    role="status">
                </span>
                Memuat...
            `;

      } else {

        applyButton.disabled = false;

        applyButton.innerHTML = `
                <iconify-icon
                    icon="solar:filter-bold"
                    class="me-1">
                </iconify-icon>
                Terapkan
            `;

      }

    }


    /* =====================================================
       EMPTY STATE
    ====================================================== */

    function emptyState(message) {

      return `
            <div class="text-center py-4 text-muted">
                ${escapeHtml(message)}
            </div>
        `;

    }


    /* =====================================================
       KPI
    ====================================================== */

    function renderKPI(data) {

      const kpi =
        data.kpi || {};

      document.getElementById(
          "kpiTotalPasien"
        ).textContent =
        numberFormat(kpi.total_pasien);


      document.getElementById(
          "kpiPasienBaru"
        ).textContent =
        numberFormat(kpi.pasien_baru);


      document.getElementById(
          "kpiRawatJalan"
        ).textContent =
        numberFormat(kpi.kunjungan_rawat_jalan);


      document.getElementById(
          "kpiPendapatan"
        ).textContent =
        rupiah(kpi.pendapatan);


      /* INFO */

      const period =
        data.period || {};

      const start =
        period.start || "";

      const end =
        period.end || "";


      document.getElementById(
          "kpiTotalPasienInfo"
        ).textContent =
        start && end ?
        `${formatDate(start)} - ${formatDate(end)}` :
        "Data berdasarkan periode";


      document.getElementById(
          "kpiPasienBaruInfo"
        ).textContent =
        "Kunjungan pertama pasien";


      document.getElementById(
          "kpiRawatJalanInfo"
        ).textContent =
        "Total kunjungan rawat jalan";


      document.getElementById(
          "kpiPendapatanInfo"
        ).textContent =
        "Total pembayaran periode";

    }


    /* =====================================================
       VISIT CHART
    ====================================================== */

    function renderVisitChart(data) {

      const canvas =
        document.getElementById(
          "adminVisitChart"
        );

      if (!canvas ||
        typeof Chart === "undefined") {

        return;
      }


      if (visitChart) {

        visitChart.destroy();

        visitChart = null;

      }


      const chartData =
        data.visit_chart || [];


      const labels =
        chartData.map(function(item) {

          return formatDate(item.date);

        });


      const values =
        chartData.map(function(item) {

          return Number(item.total || 0);

        });


      visitChart =
        new Chart(canvas, {

          type: "line",

          data: {

            labels: labels,

            datasets: [

              {

                label: "Kunjungan",

                data: values,

                borderColor: "#635bff",

                backgroundColor: "rgba(99,91,255,.08)",

                fill: true,

                tension: .4,

                borderWidth: 3,

                pointRadius: 3,

                pointHoverRadius: 5

              }

            ]

          },

          options: {

            responsive: true,

            maintainAspectRatio: false,

            interaction: {

              intersect: false,

              mode: "index"

            },

            plugins: {

              legend: {

                display: false

              },

              tooltip: {

                callbacks: {

                  label: function(context) {

                    return " " +
                      numberFormat(
                        context.raw
                      ) +
                      " kunjungan";

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

                ticks: {

                  precision: 0

                },

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


      const subtitle =
        document.getElementById(
          "visitChartSubtitle"
        );

      if (subtitle) {

        subtitle.textContent =
          "Jumlah kunjungan pasien";

      }

    }


    /* =====================================================
       PAYMENT CHART
    ====================================================== */

    function renderPaymentChart(data) {

      const canvas =
        document.getElementById(
          "adminPaymentChart"
        );

      if (!canvas ||
        typeof Chart === "undefined") {

        return;
      }


      if (paymentChart) {

        paymentChart.destroy();

        paymentChart = null;

      }


      const chartData =
        data.payment_chart || [];


      const labels =
        chartData.map(function(item) {

          return item.label || "Tidak Diketahui";

        });


      const values =
        chartData.map(function(item) {

          return Number(item.total || 0);

        });


      /* Jika kosong */

      if (labels.length === 0) {

        labels.push("Tidak Ada Data");
        values.push(1);

      }


      paymentChart =
        new Chart(canvas, {

          type: "doughnut",

          data: {

            labels: labels,

            datasets: [

              {

                data: values,

                backgroundColor: [

                  "#635bff",
                  "#8fd3ff",
                  "#9fe2bd",
                  "#ffb870",
                  "#ff8fa3",
                  "#b9b9b9"

                ],

                borderWidth: 0,

                spacing: 4

              }

            ]

          },

          options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: "72%",

            plugins: {

              legend: {

                display: false

              },

              tooltip: {

                callbacks: {

                  label: function(context) {

                    return " " +
                      context.label +
                      ": " +
                      numberFormat(
                        context.raw
                      );

                  }

                }

              }

            }

          }

        });


      renderPaymentLegend(
        chartData
      );

    }


    /* =====================================================
       PAYMENT LEGEND
    ====================================================== */

    function renderPaymentLegend(items) {

      const container =
        document.getElementById(
          "paymentLegend"
        );

      if (!container) {
        return;
      }


      if (!items || items.length === 0) {

        container.innerHTML = `
                <div class="col-12">
                    <small class="text-muted">
                        Tidak ada data pembayaran
                    </small>
                </div>
            `;

        return;

      }


      const total =
        items.reduce(
          function(sum, item) {

            return sum +
              Number(item.total || 0);

          },
          0
        );


      let html = "";


      items.slice(0, 3).forEach(
        function(item) {

          const value =
            Number(item.total || 0);

          const percentage =
            total > 0 ?
            ((value / total) * 100)
            .toFixed(1) :
            0;


          html += `

                    <div class="col-4">

                        <div class="fw-bold">

                            ${percentage}%

                        </div>

                        <small class="text-muted">

                            ${escapeHtml(
                                item.label ||
                                "Tidak Diketahui"
                            )}

                        </small>

                    </div>

                `;

        }
      );


      container.innerHTML = html;

    }


    /* =====================================================
       QUEUE
    ====================================================== */

    function renderQueue(data) {

      const container =
        document.getElementById(
          "queueContainer"
        );

      if (!container) {
        return;
      }


      const queue =
        data.queue || {};

      const items =
        queue.items || [];


      document.getElementById(
          "queueWaitingBadge"
        ).textContent =
        numberFormat(
          queue.waiting || 0
        ) + " Menunggu";


      if (items.length === 0) {

        container.innerHTML =
          emptyState(
            "Tidak ada antrian pada periode ini."
          );

        return;

      }


      let html = "";


      items.forEach(
        function(item) {

          let statusClass =
            item.status_class ||
            "status-waiting";


          let status =
            item.status ||
            "Menunggu";


          html += `

                    <div class="queue-item">

                        <div class="queue-number">

                            ${escapeHtml(
                                item.nomor || "-"
                            )}

                        </div>


                        <div>

                            <div class="queue-name">

                                ${escapeHtml(
                                    item.nama || "-"
                                )}

                            </div>


                            <div class="queue-detail">

                                ${escapeHtml(
                                    item.poli || "-"
                                )}

                                ·

                                ${escapeHtml(
                                    item.dokter || "-"
                                )}

                            </div>

                        </div>


                        <span
                            class="queue-status ${escapeHtml(
                                statusClass
                            )}">

                            ${escapeHtml(status)}

                        </span>

                    </div>

                `;

        }
      );


      container.innerHTML = html;

    }


    /* =====================================================
       ROOM
    ====================================================== */

    function renderRooms(data) {

      const container =
        document.getElementById(
          "roomContainer"
        );

      if (!container) {
        return;
      }


      const rooms =
        data.rooms || [];


      if (rooms.length === 0) {

        container.innerHTML =
          emptyState(
            "Belum ada data kamar."
          );

        return;

      }


      let html = "";


      rooms.forEach(
        function(room) {

          const total =
            Number(room.total || 0);

          const terisi =
            Number(room.terisi || 0);

          const kosong =
            Number(room.kosong || 0);


          let occupancy =
            Number(
              room.occupancy || 0
            );


          /* Proteksi */

          if (occupancy < 0) {
            occupancy = 0;
          }

          if (occupancy > 100) {
            occupancy = 100;
          }


          html += `

                    <div class="room-item">

                        <div>

                            <div class="room-name">

                                ${escapeHtml(
                                    room.class || "-"
                                )}

                            </div>

                            <div class="room-total">

                                ${numberFormat(total)}
                                tempat tidur

                            </div>


                            <div class="room-progress">

                                <span
                                    style="width:${occupancy}%">
                                </span>

                            </div>

                        </div>


                        <div class="room-value">

                            ${numberFormat(kosong)}
                            kosong

                        </div>

                    </div>

                `;

        }
      );


      container.innerHTML = html;

    }


    /* =====================================================
       LOW STOCK
    ====================================================== */

    function renderLowStock(data) {

      const container =
        document.getElementById(
          "stockContainer"
        );

      if (!container) {
        return;
      }


      const lowStock =
        data.low_stock || {};

      const items =
        lowStock.items || [];


      document.getElementById(
          "lowStockBadge"
        ).textContent =
        numberFormat(
          lowStock.total || 0
        ) + " Item";


      if (items.length === 0) {

        container.innerHTML = `
                <div class="text-center py-4 text-success">

                    <iconify-icon
                        icon="solar:check-circle-bold"
                        style="font-size:28px">
                    </iconify-icon>

                    <div class="mt-2">
                        Stok dalam kondisi aman
                    </div>

                </div>
            `;

        return;

      }


      let html = "";


      items.forEach(
        function(item) {

          html += `

                    <div class="stock-item">

                        <div>

                            <div class="stock-name">

                                ${escapeHtml(
                                    item.name || "-"
                                )}

                            </div>

                            <div class="stock-code">

                                ${escapeHtml(
                                    item.code || "-"
                                )}

                            </div>

                        </div>


                        <div class="stock-value">

                            ${numberFormat(
                                item.stock || 0
                            )}

                            ${escapeHtml(
                                item.unit || ""
                            )}

                        </div>

                    </div>

                `;

        }
      );


      container.innerHTML = html;

    }


    /* =====================================================
       ACTIVITY
    ====================================================== */

    function renderActivity(data) {

      const container =
        document.getElementById(
          "activityContainer"
        );

      if (!container) {
        return;
      }


      const activities =
        data.activities || [];


      /*
       * Jika controller belum mengirim activities,
       * jangan tampilkan data dummy.
       */

      if (activities.length === 0) {

        container.innerHTML =
          emptyState(
            "Belum ada aktivitas terbaru."
          );

        return;

      }


      let html = "";


      activities.forEach(
        function(item) {

          html += `

                    <div class="activity-item">

                        <div class="activity-icon">

                            <iconify-icon
                                icon="${escapeHtml(
                                    item.icon ||
                                    "solar:activity-bold"
                                )}">
                            </iconify-icon>

                        </div>


                        <div>

                            <div class="activity-text">

                                ${item.text || ""}

                            </div>


                            <div class="activity-time">

                                ${escapeHtml(
                                    item.time ||
                                    relativeTime(
                                        item.created_at
                                    )
                                )}

                            </div>

                        </div>

                    </div>

                `;

        }
      );


      container.innerHTML = html;

    }


    /* =====================================================
       LOAD DASHBOARD
    ====================================================== */

    async function loadDashboard() {

      const period =
        periodSelect.value;


      let url =
        DASHBOARD_URL +
        "&period=" +
        encodeURIComponent(period);


      /* CUSTOM */

      if (period === "custom") {

        const start =
          startDate.value;

        const end =
          endDate.value;


        if (!start || !end) {

          if (typeof Swal !== "undefined") {

            Swal.fire({

              icon: "warning",

              title: "Periode belum lengkap",

              text: "Silakan pilih tanggal mulai dan tanggal akhir."

            });

          } else {

            alert(
              "Silakan pilih tanggal mulai dan tanggal akhir."
            );

          }

          return;

        }


        if (start > end) {

          if (typeof Swal !== "undefined") {

            Swal.fire({

              icon: "warning",

              title: "Periode tidak valid",

              text: "Tanggal mulai tidak boleh lebih besar dari tanggal akhir."

            });

          } else {

            alert(
              "Tanggal mulai tidak boleh lebih besar dari tanggal akhir."
            );

          }

          return;

        }


        url +=
          "&start_date=" +
          encodeURIComponent(start) +
          "&end_date=" +
          encodeURIComponent(end);

      }


      setLoading(true);


      try {

        const response =
          await fetch(url, {

            method: "GET",

            headers: {

              "Accept": "application/json",

              "X-Requested-With": "XMLHttpRequest"

            },

            cache: "no-store"

          });


        if (!response.ok) {

          throw new Error(
            "HTTP Error " +
            response.status
          );

        }


        const result =
          await response.json();


        console.log(
          "Dashboard Response:",
          result
        );


        if (!result.status) {

          throw new Error(
            result.message ||
            "Gagal mengambil data dashboard."
          );

        }


        /* RENDER */

        renderKPI(result);

        renderVisitChart(result);

        renderPaymentChart(result);

        renderQueue(result);

        renderRooms(result);

        renderLowStock(result);

        renderActivity(result);


      } catch (error) {

        console.error(
          "Dashboard Error:",
          error
        );


        if (typeof Swal !== "undefined") {

          Swal.fire({

            icon: "error",

            title: "Gagal memuat dashboard",

            text: error.message ||
              "Terjadi kesalahan saat mengambil data."

          });

        }


      } finally {

        setLoading(false);

      }

    }


    /* =====================================================
       EVENT FILTER
    ====================================================== */

    if (periodSelect) {

      periodSelect.addEventListener(
        "change",
        function() {

          updateDateFields();

        }
      );

    }


    if (applyButton) {

      applyButton.addEventListener(
        "click",
        function() {

          loadDashboard();

        }
      );

    }


    /* =====================================================
       ENTER PADA CUSTOM DATE
    ====================================================== */

    [startDate, endDate].forEach(
      function(element) {

        if (!element) {
          return;
        }

        element.addEventListener(
          "keydown",
          function(event) {

            if (event.key === "Enter") {

              event.preventDefault();

              loadDashboard();

            }

          }
        );

      }
    );


    /* =====================================================
       INITIAL LOAD
    ====================================================== */

    updateDateFields();

    loadDashboard();


  })();
</script>