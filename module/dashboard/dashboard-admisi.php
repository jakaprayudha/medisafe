<style>
  /* =========================================================
       DASHBOARD ADMISI / RECEPTIONIST
    ========================================================= */

  .admisi-dashboard {

    --adm-primary: #635bff;
    --adm-primary-soft: #eeecff;

    --adm-text: #273444;
    --adm-muted: #7b8494;

    --adm-border: #edf0f5;

    --adm-green: #16a34a;
    --adm-red: #dc2626;
    --adm-orange: #d97706;
    --adm-blue: #1687d9;
  }


  /* =========================================================
       FILTER PERIODE
    ========================================================= */

  .admisi-dashboard .adm-filter-wrapper {

    background: #fff;

    border: 1px solid var(--adm-border);

    border-radius: 18px;

    padding: 16px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 15px;
  }


  .admisi-dashboard .adm-filter-title {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-shrink: 0;
  }


  .admisi-dashboard .adm-filter-icon {

    width: 42px;
    height: 42px;

    border-radius: 12px;

    background: var(--adm-primary-soft);

    color: var(--adm-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;
  }


  .admisi-dashboard .adm-filter-heading {

    color: var(--adm-text);

    font-size: 13px;

    font-weight: 700;
  }


  .admisi-dashboard .adm-filter-description {

    color: var(--adm-muted);

    font-size: 11px;

    margin-top: 2px;
  }


  .admisi-dashboard .adm-filter-form {

    display: flex;

    align-items: flex-end;

    gap: 10px;
  }


  .admisi-dashboard .adm-filter-group {

    min-width: 140px;
  }


  .admisi-dashboard .adm-filter-group label {

    display: block;

    color: var(--adm-muted);

    font-size: 10px;

    font-weight: 600;

    margin-bottom: 5px;
  }


  .admisi-dashboard .adm-filter-group .form-control,
  .admisi-dashboard .adm-filter-group .form-select {

    height: 38px;

    border-radius: 10px;

    border-color: var(--adm-border);

    font-size: 12px;

    box-shadow: none;
  }


  .admisi-dashboard .adm-filter-button {

    height: 38px;

    border-radius: 10px;

    font-size: 12px;

    white-space: nowrap;
  }


  /* =========================================================
       STATUS BAR
    ========================================================= */

  .admisi-dashboard .operational-bar {

    background: #fff;

    border: 1px solid var(--adm-border);

    border-radius: 15px;

    padding: 12px 17px;

    margin-bottom: 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
  }


  .admisi-dashboard .operational-status {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 12px;

    font-weight: 600;
  }


  .admisi-dashboard .online-dot {

    width: 9px;
    height: 9px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
      0 0 0 4px rgba(34, 197, 94, .12);
  }


  .admisi-dashboard .operational-time {

    color: var(--adm-muted);

    font-size: 11px;
  }


  /* =========================================================
       KPI CARD
    ========================================================= */

  .admisi-dashboard .kpi-card {

    background: #fff;

    border: 1px solid var(--adm-border);

    border-radius: 18px;

    padding: 19px;

    height: 100%;

    transition: .2s ease;
  }


  .admisi-dashboard .kpi-card:hover {

    transform: translateY(-2px);

    box-shadow:
      0 10px 28px rgba(30, 40, 60, .06);
  }


  .admisi-dashboard .kpi-top {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;
  }


  .admisi-dashboard .kpi-title {

    color: var(--adm-muted);

    font-size: 12px;

    margin-bottom: 5px;
  }


  .admisi-dashboard .kpi-value {

    color: var(--adm-text);

    font-size: 27px;

    font-weight: 700;

    line-height: 1.15;
  }


  .admisi-dashboard .kpi-info {

    font-size: 10px;

    margin-top: 7px;
  }


  .admisi-dashboard .kpi-icon {

    width: 46px;
    height: 46px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;
  }


  .admisi-dashboard .icon-purple {

    background: #eeecff;

    color: #635bff;
  }


  .admisi-dashboard .icon-blue {

    background: #e8f4ff;

    color: #1687d9;
  }


  .admisi-dashboard .icon-orange {

    background: #fff3e3;

    color: #df861e;
  }


  .admisi-dashboard .icon-green {

    background: #e8f8ef;

    color: #16965a;
  }


  .admisi-dashboard .icon-red {

    background: #feecec;

    color: #dc2626;
  }


  .admisi-dashboard .up {

    color: #16a34a;
  }


  .admisi-dashboard .warning {

    color: #d97706;
  }


  .admisi-dashboard .danger {

    color: #dc2626;
  }


  /* =========================================================
       GENERAL CARD
    ========================================================= */

  .admisi-dashboard .dash-card {

    background: #fff;

    border: 1px solid var(--adm-border);

    border-radius: 18px;

    padding: 20px;

    height: 100%;
  }


  .admisi-dashboard .dash-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 16px;
  }


  .admisi-dashboard .dash-title {

    color: var(--adm-text);

    font-size: 15px;

    font-weight: 700;

    margin: 0;
  }


  .admisi-dashboard .dash-subtitle {

    color: var(--adm-muted);

    font-size: 11px;

    margin-top: 3px;
  }


  .admisi-dashboard .view-all {

    color: var(--adm-primary);

    font-size: 11px;

    text-decoration: none;

    font-weight: 600;
  }


  /* =========================================================
       DOKTER HADIR
    ========================================================= */

  .admisi-dashboard .doctor-item {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .admisi-dashboard .doctor-item:last-child {

    border-bottom: none;
  }


  .admisi-dashboard .doctor-avatar {

    width: 39px;
    height: 39px;

    border-radius: 12px;

    background: #eeecff;

    color: var(--adm-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 12px;

    font-weight: 700;

    flex-shrink: 0;
  }


  .admisi-dashboard .doctor-name {

    color: var(--adm-text);

    font-size: 12px;

    font-weight: 600;
  }


  .admisi-dashboard .doctor-poli {

    color: var(--adm-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .admisi-dashboard .doctor-status {

    margin-left: auto;

    display: flex;

    align-items: center;

    gap: 5px;

    font-size: 10px;

    white-space: nowrap;
  }


  .admisi-dashboard .doctor-status-dot {

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #22c55e;
  }


  .admisi-dashboard .doctor-status.absent {

    color: #dc2626;
  }


  .admisi-dashboard .doctor-status.absent .doctor-status-dot {

    background: #ef4444;
  }


  .admisi-dashboard .doctor-time {

    color: var(--adm-muted);

    font-size: 9px;

    margin-left: 4px;
  }


  /* =========================================================
       ANTRIAN
    ========================================================= */

  .admisi-dashboard .queue-item {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .admisi-dashboard .queue-item:last-child {

    border-bottom: none;
  }


  .admisi-dashboard .queue-number {

    width: 39px;
    height: 39px;

    border-radius: 11px;

    background: #f0efff;

    color: var(--adm-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 11px;

    font-weight: 700;

    flex-shrink: 0;
  }


  .admisi-dashboard .queue-name {

    color: var(--adm-text);

    font-size: 12px;

    font-weight: 600;
  }


  .admisi-dashboard .queue-detail {

    color: var(--adm-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .admisi-dashboard .queue-status {

    margin-left: auto;

    padding: 5px 8px;

    border-radius: 8px;

    font-size: 9px;

    white-space: nowrap;
  }


  .admisi-dashboard .waiting {

    background: #fff3d9;

    color: #c47a00;
  }


  .admisi-dashboard .called {

    background: #e8f2ff;

    color: #2377c7;
  }


  .admisi-dashboard .examination {

    background: #eeecff;

    color: #635bff;
  }


  .admisi-dashboard .completed {

    background: #e7f8ee;

    color: #168a4e;
  }


  /* =========================================================
       POLI / DOKTER
    ========================================================= */

  .admisi-dashboard .doctor-summary {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .admisi-dashboard .doctor-summary:last-child {

    border-bottom: none;
  }


  .admisi-dashboard .doctor-summary-left {

    display: flex;

    align-items: center;

    gap: 10px;
  }


  .admisi-dashboard .summary-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: #f0efff;

    color: var(--adm-primary);

    display: flex;

    align-items: center;

    justify-content: center;
  }


  .admisi-dashboard .summary-doctor {

    font-size: 12px;

    font-weight: 600;

    color: var(--adm-text);
  }


  .admisi-dashboard .summary-poli {

    color: var(--adm-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .admisi-dashboard .summary-count {

    text-align: right;
  }


  .admisi-dashboard .summary-count strong {

    display: block;

    color: var(--adm-text);

    font-size: 15px;
  }


  .admisi-dashboard .summary-count span {

    color: var(--adm-muted);

    font-size: 9px;
  }


  /* =========================================================
       POLI STATUS
    ========================================================= */

  .admisi-dashboard .poli-item {

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .admisi-dashboard .poli-item:last-child {

    border-bottom: none;
  }


  .admisi-dashboard .poli-top {

    display: flex;

    align-items: center;

    justify-content: space-between;
  }


  .admisi-dashboard .poli-name {

    color: var(--adm-text);

    font-size: 12px;

    font-weight: 600;
  }


  .admisi-dashboard .poli-doctor {

    color: var(--adm-muted);

    font-size: 10px;
  }


  .admisi-dashboard .poli-bottom {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 7px;
  }


  .admisi-dashboard .poli-progress {

    flex: 1;

    height: 6px;

    background: #edf0f4;

    border-radius: 20px;

    overflow: hidden;
  }


  .admisi-dashboard .poli-progress span {

    display: block;

    height: 100%;

    background: var(--adm-primary);

    border-radius: inherit;
  }


  .admisi-dashboard .poli-total {

    color: var(--adm-muted);

    font-size: 10px;

    white-space: nowrap;
  }


  /* =========================================================
       PATIENT TYPE
    ========================================================= */

  .admisi-dashboard .patient-type-box {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .admisi-dashboard .patient-type-box:last-child {

    border-bottom: none;
  }


  .admisi-dashboard .patient-type-icon {

    width: 38px;
    height: 38px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
  }


  .admisi-dashboard .patient-type-info {

    flex: 1;
  }


  .admisi-dashboard .patient-type-name {

    color: var(--adm-text);

    font-size: 11px;

    font-weight: 600;
  }


  .admisi-dashboard .patient-type-count {

    color: var(--adm-text);

    font-size: 14px;

    font-weight: 700;
  }


  .admisi-dashboard .patient-type-percent {

    color: var(--adm-muted);

    font-size: 10px;
  }


  /* =========================================================
       QUICK ACCESS
    ========================================================= */

  .admisi-dashboard .quick-item {

    display: block;

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 13px;

    padding: 13px;

    text-decoration: none;

    transition: .2s ease;
  }


  .admisi-dashboard .quick-item:hover {

    background: #f5f4ff;

    border-color: #dedbff;

    transform: translateY(-1px);
  }


  .admisi-dashboard .quick-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: #eeecff;

    color: var(--adm-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
  }


  .admisi-dashboard .quick-title {

    color: var(--adm-text);

    font-size: 11px;

    font-weight: 600;

    margin-top: 8px;
  }


  .admisi-dashboard .quick-description {

    color: var(--adm-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  /* =========================================================
       RESPONSIVE
    ========================================================= */

  @media (max-width: 1100px) {

    .admisi-dashboard .adm-filter-wrapper {

      flex-direction: column;

      align-items: flex-start;
    }

    .admisi-dashboard .adm-filter-form {

      width: 100%;

      flex-wrap: wrap;
    }

  }


  @media (max-width: 767px) {

    .admisi-dashboard .adm-filter-form {

      display: grid;

      grid-template-columns: 1fr 1fr;

      width: 100%;
    }

    .admisi-dashboard .adm-filter-group {

      min-width: 0;
    }

    .admisi-dashboard .adm-filter-button {

      width: 100%;
    }

    .admisi-dashboard .operational-bar {

      align-items: flex-start;

      flex-direction: column;
    }

  }


  @media (max-width: 480px) {

    .admisi-dashboard .adm-filter-form {

      grid-template-columns: 1fr;
    }

  }
</style>


<div class="admisi-dashboard">


  <!-- =====================================================
         FILTER PERIODE
    ====================================================== -->

  <div class="adm-filter-wrapper">

    <div class="adm-filter-title">

      <div class="adm-filter-icon">

        <iconify-icon
          icon="solar:calendar-search-bold">
        </iconify-icon>

      </div>

      <div>

        <div class="adm-filter-heading">
          Periode Pelayanan
        </div>

        <div class="adm-filter-description">
          Filter data kunjungan dan pelayanan pasien
        </div>

      </div>

    </div>


    <div class="adm-filter-form">

      <div class="adm-filter-group">

        <label>
          Periode
        </label>

        <select
          id="admisiPeriod"
          class="form-select">

          <option value="today" selected>
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


      <div class="adm-filter-group">

        <label>
          Dari
        </label>

        <input
          type="date"
          id="admisiStartDate"
          class="form-control"
          value="<?= date('Y-m-d') ?>">

      </div>


      <div class="adm-filter-group">

        <label>
          Sampai
        </label>

        <input
          type="date"
          id="admisiEndDate"
          class="form-control"
          value="<?= date('Y-m-d') ?>">

      </div>


      <button
        type="button"
        class="btn btn-primary adm-filter-button"
        id="applyAdmisiFilter">

        <iconify-icon
          icon="solar:filter-bold"
          class="me-1">
        </iconify-icon>

        Terapkan

      </button>

    </div>

  </div>


  <!-- =====================================================
         OPERATIONAL STATUS
    ====================================================== -->

  <div class="operational-bar">

    <div class="operational-status">

      <span class="online-dot"></span>

      Sistem pelayanan aktif

    </div>

    <div class="operational-time">

      Update terakhir:
      <strong id="admisiLastUpdate">
        <?= date('H:i') ?>
      </strong>
      WIB

    </div>

  </div>


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

            <div class="kpi-value">
              248
            </div>

            <div class="kpi-info up">
              ↑ 12,4% dari kemarin
            </div>

          </div>

          <div class="kpi-icon icon-purple">

            <iconify-icon
              icon="solar:users-group-rounded-bold">
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
              Pasien Menunggu
            </div>

            <div class="kpi-value">
              18
            </div>

            <div class="kpi-info warning">
              Perlu segera dilayani
            </div>

          </div>

          <div class="kpi-icon icon-orange">

            <iconify-icon
              icon="solar:clock-circle-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- DIPERIKSA -->

    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Sedang Diperiksa
            </div>

            <div class="kpi-value">
              12
            </div>

            <div class="kpi-info">
              6 poli aktif
            </div>

          </div>

          <div class="kpi-icon icon-blue">

            <iconify-icon
              icon="solar:stethoscope-bold">
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
              Pelayanan Selesai
            </div>

            <div class="kpi-value">
              218
            </div>

            <div class="kpi-info up">
              88% dari total pasien
            </div>

          </div>

          <div class="kpi-icon icon-green">

            <iconify-icon
              icon="solar:check-circle-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         DOKTER + ANTRIAN
    ====================================================== -->

  <div class="row g-3 mb-3">


    <!-- DOKTER HADIR -->

    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Dokter Hari Ini
            </h6>

            <div class="dash-subtitle">
              Status kehadiran dokter
            </div>

          </div>

          <span class="badge bg-success">
            6 Hadir
          </span>

        </div>


        <!-- DOKTER 1 -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DA
          </div>

          <div>

            <div class="doctor-name">
              dr. Andi Saputra
            </div>

            <div class="doctor-poli">
              Poli Umum
            </div>

          </div>

          <div class="doctor-status">

            <span class="doctor-status-dot"></span>

            Hadir

            <span class="doctor-time">
              07:42
            </span>

          </div>

        </div>


        <!-- DOKTER 2 -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DR
          </div>

          <div>

            <div class="doctor-name">
              dr. Rina Amelia
            </div>

            <div class="doctor-poli">
              Poli Anak
            </div>

          </div>

          <div class="doctor-status">

            <span class="doctor-status-dot"></span>

            Hadir

            <span class="doctor-time">
              07:51
            </span>

          </div>

        </div>


        <!-- DOKTER 3 -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DM
          </div>

          <div>

            <div class="doctor-name">
              dr. Maya Sari
            </div>

            <div class="doctor-poli">
              Poli Gigi
            </div>

          </div>

          <div class="doctor-status">

            <span class="doctor-status-dot"></span>

            Hadir

            <span class="doctor-time">
              08:02
            </span>

          </div>

        </div>


        <!-- DOKTER 4 -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DF
          </div>

          <div>

            <div class="doctor-name">
              dr. Fajar Hidayat
            </div>

            <div class="doctor-poli">
              Poli Penyakit Dalam
            </div>

          </div>

          <div class="doctor-status">

            <span class="doctor-status-dot"></span>

            Hadir

            <span class="doctor-time">
              08:05
            </span>

          </div>

        </div>


        <!-- DOKTER 5 -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DS
          </div>

          <div>

            <div class="doctor-name">
              dr. Sinta Dewi
            </div>

            <div class="doctor-poli">
              Poli Kandungan
            </div>

          </div>

          <div class="doctor-status">

            <span class="doctor-status-dot"></span>

            Hadir

            <span class="doctor-time">
              08:11
            </span>

          </div>

        </div>


        <!-- DOKTER BELUM HADIR -->

        <div class="doctor-item">

          <div class="doctor-avatar">
            DB
          </div>

          <div>

            <div class="doctor-name">
              dr. Budi Pratama
            </div>

            <div class="doctor-poli">
              Poli Saraf
            </div>

          </div>

          <div class="doctor-status absent">

            <span class="doctor-status-dot"></span>

            Belum Hadir

          </div>

        </div>

      </div>

    </div>


    <!-- ANTRIAN -->

    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Antrian Pasien
            </h6>

            <div class="dash-subtitle">
              Monitoring antrian pelayanan saat ini
            </div>

          </div>

          <div>

            <span class="badge bg-warning text-dark me-1">
              18 Menunggu
            </span>

            <span class="badge bg-primary">
              12 Diproses
            </span>

          </div>

        </div>


        <!-- QUEUE -->

        <div class="queue-item">

          <div class="queue-number">
            A-021
          </div>

          <div>

            <div class="queue-name">
              Budi Santoso
            </div>

            <div class="queue-detail">
              Poli Umum · dr. Andi
            </div>

          </div>

          <span class="queue-status waiting">
            Menunggu
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-022
          </div>

          <div>

            <div class="queue-name">
              Siti Rahma
            </div>

            <div class="queue-detail">
              Poli Anak · dr. Rina
            </div>

          </div>

          <span class="queue-status called">
            Dipanggil
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-023
          </div>

          <div>

            <div class="queue-name">
              Ahmad Fauzi
            </div>

            <div class="queue-detail">
              Poli Umum · dr. Andi
            </div>

          </div>

          <span class="queue-status examination">
            Diperiksa
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-024
          </div>

          <div>

            <div class="queue-name">
              Dewi Lestari
            </div>

            <div class="queue-detail">
              Poli Gigi · dr. Maya
            </div>

          </div>

          <span class="queue-status waiting">
            Menunggu
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-025
          </div>

          <div>

            <div class="queue-name">
              Rudi Hartono
            </div>

            <div class="queue-detail">
              Poli Umum · dr. Andi
            </div>

          </div>

          <span class="queue-status completed">
            Selesai
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-026
          </div>

          <div>

            <div class="queue-name">
              Nur Aisyah
            </div>

            <div class="queue-detail">
              Poli Penyakit Dalam · dr. Fajar
            </div>

          </div>

          <span class="queue-status waiting">
            Menunggu
          </span>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         DOKTER / POLI + PASIEN TYPE
    ====================================================== -->

  <div class="row g-3 mb-3">


    <!-- PASIEN PER DOKTER -->

    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Pasien per Dokter
            </h6>

            <div class="dash-subtitle">
              Jumlah pasien berdasarkan dokter
            </div>

          </div>

          <a href="#"
            class="view-all">
            Detail
          </a>

        </div>


        <!-- DOKTER -->

        <div class="doctor-summary">

          <div class="doctor-summary-left">

            <div class="summary-icon">

              <iconify-icon
                icon="solar:stethoscope-bold">
              </iconify-icon>

            </div>

            <div>

              <div class="summary-doctor">
                dr. Andi Saputra
              </div>

              <div class="summary-poli">
                Poli Umum
              </div>

            </div>

          </div>

          <div class="summary-count">

            <strong>
              68
            </strong>

            <span>
              pasien
            </span>

          </div>

        </div>


        <div class="doctor-summary">

          <div class="doctor-summary-left">

            <div class="summary-icon">

              <iconify-icon
                icon="solar:stethoscope-bold">
              </iconify-icon>

            </div>

            <div>

              <div class="summary-doctor">
                dr. Rina Amelia
              </div>

              <div class="summary-poli">
                Poli Anak
              </div>

            </div>

          </div>

          <div class="summary-count">

            <strong>
              45
            </strong>

            <span>
              pasien
            </span>

          </div>

        </div>


        <div class="doctor-summary">

          <div class="doctor-summary-left">

            <div class="summary-icon">

              <iconify-icon
                icon="solar:stethoscope-bold">
              </iconify-icon>

            </div>

            <div>

              <div class="summary-doctor">
                dr. Maya Sari
              </div>

              <div class="summary-poli">
                Poli Gigi
              </div>

            </div>

          </div>

          <div class="summary-count">

            <strong>
              31
            </strong>

            <span>
              pasien
            </span>

          </div>

        </div>


        <div class="doctor-summary">

          <div class="doctor-summary-left">

            <div class="summary-icon">

              <iconify-icon
                icon="solar:stethoscope-bold">
              </iconify-icon>

            </div>

            <div>

              <div class="summary-doctor">
                dr. Fajar Hidayat
              </div>

              <div class="summary-poli">
                Penyakit Dalam
              </div>

            </div>

          </div>

          <div class="summary-count">

            <strong>
              27
            </strong>

            <span>
              pasien
            </span>

          </div>

        </div>


        <div class="doctor-summary">

          <div class="doctor-summary-left">

            <div class="summary-icon">

              <iconify-icon
                icon="solar:stethoscope-bold">
              </iconify-icon>

            </div>

            <div>

              <div class="summary-doctor">
                dr. Sinta Dewi
              </div>

              <div class="summary-poli">
                Poli Kandungan
              </div>

            </div>

          </div>

          <div class="summary-count">

            <strong>
              22
            </strong>

            <span>
              pasien
            </span>

          </div>

        </div>

      </div>

    </div>


    <!-- STATUS POLI -->

    <div class="col-xl-4">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Status Poliklinik
            </h6>

            <div class="dash-subtitle">
              Kondisi pelayanan setiap poli
            </div>

          </div>

        </div>


        <div class="poli-item">

          <div class="poli-top">

            <div>

              <div class="poli-name">
                Poli Umum
              </div>

              <div class="poli-doctor">
                dr. Andi Saputra
              </div>

            </div>

            <span class="badge bg-success">
              Aktif
            </span>

          </div>

          <div class="poli-bottom">

            <div class="poli-progress">

              <span style="width:78%"></span>

            </div>

            <div class="poli-total">
              68 pasien
            </div>

          </div>

        </div>


        <div class="poli-item">

          <div class="poli-top">

            <div>

              <div class="poli-name">
                Poli Anak
              </div>

              <div class="poli-doctor">
                dr. Rina Amelia
              </div>

            </div>

            <span class="badge bg-success">
              Aktif
            </span>

          </div>

          <div class="poli-bottom">

            <div class="poli-progress">

              <span style="width:61%"></span>

            </div>

            <div class="poli-total">
              45 pasien
            </div>

          </div>

        </div>


        <div class="poli-item">

          <div class="poli-top">

            <div>

              <div class="poli-name">
                Poli Gigi
              </div>

              <div class="poli-doctor">
                dr. Maya Sari
              </div>

            </div>

            <span class="badge bg-success">
              Aktif
            </span>

          </div>

          <div class="poli-bottom">

            <div class="poli-progress">

              <span style="width:48%"></span>

            </div>

            <div class="poli-total">
              31 pasien
            </div>

          </div>

        </div>


        <div class="poli-item">

          <div class="poli-top">

            <div>

              <div class="poli-name">
                Penyakit Dalam
              </div>

              <div class="poli-doctor">
                dr. Fajar Hidayat
              </div>

            </div>

            <span class="badge bg-success">
              Aktif
            </span>

          </div>

          <div class="poli-bottom">

            <div class="poli-progress">

              <span style="width:38%"></span>

            </div>

            <div class="poli-total">
              27 pasien
            </div>

          </div>

        </div>


        <div class="poli-item">

          <div class="poli-top">

            <div>

              <div class="poli-name">
                Poli Saraf
              </div>

              <div class="poli-doctor">
                dr. Budi Pratama
              </div>

            </div>

            <span class="badge bg-secondary">
              Belum Mulai
            </span>

          </div>

          <div class="poli-bottom">

            <div class="poli-progress">

              <span style="width:0%"></span>

            </div>

            <div class="poli-total">
              0 pasien
            </div>

          </div>

        </div>

      </div>

    </div>


    <!-- JENIS PASIEN -->

    <div class="col-xl-3">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Jenis Pasien
            </h6>

            <div class="dash-subtitle">
              Distribusi kunjungan
            </div>

          </div>

        </div>


        <!-- BPJS -->

        <div class="patient-type-box">

          <div
            class="patient-type-icon"
            style="
                            background:#eeecff;
                            color:#635bff;
                        ">

            <iconify-icon
              icon="solar:card-bold">
            </iconify-icon>

          </div>

          <div class="patient-type-info">

            <div class="patient-type-name">
              BPJS
            </div>

            <div class="patient-type-percent">
              56% dari total
            </div>

          </div>

          <div class="patient-type-count">
            139
          </div>

        </div>


        <!-- UMUM -->

        <div class="patient-type-box">

          <div
            class="patient-type-icon"
            style="
                            background:#e8f4ff;
                            color:#1687d9;
                        ">

            <iconify-icon
              icon="solar:wallet-money-bold">
            </iconify-icon>

          </div>

          <div class="patient-type-info">

            <div class="patient-type-name">
              Umum
            </div>

            <div class="patient-type-percent">
              31% dari total
            </div>

          </div>

          <div class="patient-type-count">
            77
          </div>

        </div>


        <!-- ASURANSI -->

        <div class="patient-type-box">

          <div
            class="patient-type-icon"
            style="
                            background:#e8f8ef;
                            color:#16965a;
                        ">

            <iconify-icon
              icon="solar:shield-check-bold">
            </iconify-icon>

          </div>

          <div class="patient-type-info">

            <div class="patient-type-name">
              Asuransi
            </div>

            <div class="patient-type-percent">
              13% dari total
            </div>

          </div>

          <div class="patient-type-count">
            32
          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         CHART + QUICK ACCESS
    ====================================================== -->

  <div class="row g-3">


    <!-- KUNJUNGAN -->

    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Grafik Kunjungan Pasien
            </h6>

            <div class="dash-subtitle">
              Jumlah pasien berdasarkan hari
            </div>

          </div>

          <select
            class="form-select"
            style="
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

        <div style="height:270px">

          <canvas id="admisiVisitChart"></canvas>

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
              Menu yang sering digunakan admisi
            </div>

          </div>

        </div>


        <div class="row g-2">


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

              <div class="quick-description">
                Registrasi pasien
              </div>

            </a>

          </div>


          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:users-group-rounded-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Data Pasien
              </div>

              <div class="quick-description">
                Cari pasien
              </div>

            </a>

          </div>


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

              <div class="quick-description">
                Jadwal dokter
              </div>

            </a>

          </div>


          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:ticket-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Antrian
              </div>

              <div class="quick-description">
                Kelola antrian
              </div>

            </a>

          </div>


          <div class="col-6">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:calendar-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Jadwal Dokter
              </div>

              <div class="quick-description">
                Jadwal pelayanan
              </div>

            </a>

          </div>


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
                Surat
              </div>

              <div class="quick-description">
                Surat pasien
              </div>

            </a>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>


<script>
  /* =========================================================
       FILTER PERIODE ADMISI
    ========================================================= */

  document.addEventListener(
    "DOMContentLoaded",
    function() {

      const period =
        document.getElementById(
          "admisiPeriod"
        );

      const startDate =
        document.getElementById(
          "admisiStartDate"
        );

      const endDate =
        document.getElementById(
          "admisiEndDate"
        );

      const applyButton =
        document.getElementById(
          "applyAdmisiFilter"
        );


      function localDate(date) {

        const year =
          date.getFullYear();

        const month =
          String(
            date.getMonth() + 1
          ).padStart(2, "0");

        const day =
          String(
            date.getDate()
          ).padStart(2, "0");

        return (
          year +
          "-" +
          month +
          "-" +
          day
        );

      }


      function updatePeriod() {

        const value =
          period.value;

        const today =
          new Date();

        let start =
          new Date(today);

        let end =
          new Date(today);


        if (value === "today") {

          start =
            new Date(today);

          end =
            new Date(today);

        } else if (
          value === "yesterday"
        ) {

          start =
            new Date(today);

          start.setDate(
            today.getDate() - 1
          );

          end =
            new Date(start);

        } else if (
          value === "7days"
        ) {

          start =
            new Date(today);

          start.setDate(
            today.getDate() - 6
          );

        } else if (
          value === "30days"
        ) {

          start =
            new Date(today);

          start.setDate(
            today.getDate() - 29
          );

        } else if (
          value === "thismonth"
        ) {

          start =
            new Date(
              today.getFullYear(),
              today.getMonth(),
              1
            );

        } else if (
          value === "lastmonth"
        ) {

          start =
            new Date(
              today.getFullYear(),
              today.getMonth() - 1,
              1
            );

          end =
            new Date(
              today.getFullYear(),
              today.getMonth(),
              0
            );

        } else if (
          value === "custom"
        ) {

          return;

        }


        startDate.value =
          localDate(start);

        endDate.value =
          localDate(end);

      }


      period.addEventListener(
        "change",
        updatePeriod
      );


      applyButton.addEventListener(
        "click",
        function() {

          const dari =
            startDate.value;

          const sampai =
            endDate.value;


          console.log(
            "Filter Admisi:", {
              dari: dari,
              sampai: sampai
            }
          );


          /*
           * NANTI DATA DATABASE
           * BISA DI-LOAD DI SINI
           *
           * Contoh:
           *
           * loadDashboardAdmisi(
           *     dari,
           *     sampai
           * );
           */


          if (
            typeof Swal !==
            "undefined"
          ) {

            Swal.fire({

              icon: "success",

              title: "Periode diterapkan",

              text: "Data pelayanan " +
                dari +
                " sampai " +
                sampai,

              timer: 1200,

              showConfirmButton: false

            });

          }

        }
      );


      updatePeriod();


      /* =================================================
         CHART KUNJUNGAN
      ================================================= */

      const chartElement =
        document.getElementById(
          "admisiVisitChart"
        );


      if (
        chartElement &&
        typeof Chart !== "undefined"
      ) {

        new Chart(
          chartElement, {

            type: "line",

            data: {

              labels: [
                "Sen",
                "Sel",
                "Rab",
                "Kam",
                "Jum",
                "Sab",
                "Min"
              ],

              datasets: [

                {

                  label: "Pasien",

                  data: [
                    185,
                    210,
                    178,
                    235,
                    248,
                    162,
                    91
                  ],

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

              plugins: {

                legend: {
                  display: false
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

          }
        );

      }


      /* =================================================
         UPDATE CLOCK
      ================================================= */

      setInterval(
        function() {

          const element =
            document.getElementById(
              "admisiLastUpdate"
            );

          if (!element) {
            return;
          }


          const now =
            new Date();


          element.textContent =
            String(
              now.getHours()
            ).padStart(2, "0") +
            ":" +
            String(
              now.getMinutes()
            ).padStart(2, "0");

        },
        60000
      );

    }
  );
</script>