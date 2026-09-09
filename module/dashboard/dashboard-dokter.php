<style>
  /* =========================================================
       DASHBOARD DOKTER
    ========================================================= */

  .dokter-dashboard {

    --dok-primary: #635bff;
    --dok-primary-soft: #eeecff;

    --dok-text: #273444;
    --dok-muted: #7b8494;

    --dok-border: #edf0f5;

    --dok-green: #16a34a;
    --dok-red: #dc2626;
    --dok-orange: #d97706;
    --dok-blue: #1687d9;
  }


  /* =========================================================
       FILTER PERIODE
    ========================================================= */

  .dokter-dashboard .dok-filter-wrapper {

    background: #fff;

    border: 1px solid var(--dok-border);

    border-radius: 18px;

    padding: 16px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 15px;
  }


  .dokter-dashboard .dok-filter-title {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-shrink: 0;
  }


  .dokter-dashboard .dok-filter-icon {

    width: 42px;
    height: 42px;

    border-radius: 12px;

    background: var(--dok-primary-soft);

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;
  }


  .dokter-dashboard .dok-filter-heading {

    color: var(--dok-text);

    font-size: 13px;

    font-weight: 700;
  }


  .dokter-dashboard .dok-filter-description {

    color: var(--dok-muted);

    font-size: 11px;

    margin-top: 2px;
  }


  .dokter-dashboard .dok-filter-form {

    display: flex;

    align-items: flex-end;

    gap: 10px;
  }


  .dokter-dashboard .dok-filter-group {

    min-width: 140px;
  }


  .dokter-dashboard .dok-filter-group label {

    display: block;

    color: var(--dok-muted);

    font-size: 10px;

    font-weight: 600;

    margin-bottom: 5px;
  }


  .dokter-dashboard .dok-filter-group .form-control,
  .dokter-dashboard .dok-filter-group .form-select {

    height: 38px;

    border-radius: 10px;

    border-color: var(--dok-border);

    font-size: 12px;

    box-shadow: none;
  }


  .dokter-dashboard .dok-filter-button {

    height: 38px;

    border-radius: 10px;

    font-size: 12px;

    white-space: nowrap;
  }


  /* =========================================================
       DOKTER STATUS
    ========================================================= */

  .dokter-dashboard .doctor-status-bar {

    background: #fff;

    border: 1px solid var(--dok-border);

    border-radius: 15px;

    padding: 13px 17px;

    margin-bottom: 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
  }


  .dokter-dashboard .doctor-status-left {

    display: flex;

    align-items: center;

    gap: 12px;
  }


  .dokter-dashboard .doctor-avatar-large {

    width: 42px;
    height: 42px;

    border-radius: 13px;

    background: var(--dok-primary-soft);

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 12px;

    font-weight: 700;
  }


  .dokter-dashboard .doctor-status-name {

    color: var(--dok-text);

    font-size: 13px;

    font-weight: 700;
  }


  .dokter-dashboard .doctor-status-poli {

    color: var(--dok-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .dokter-dashboard .doctor-online {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 5px 9px;

    border-radius: 8px;

    background: #e8f8ef;

    color: #168a4e;

    font-size: 10px;

    font-weight: 600;
  }


  .dokter-dashboard .doctor-online-dot {

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #22c55e;
  }


  .dokter-dashboard .doctor-status-right {

    text-align: right;
  }


  .dokter-dashboard .doctor-clock {

    color: var(--dok-text);

    font-size: 14px;

    font-weight: 700;
  }


  .dokter-dashboard .doctor-clock-label {

    color: var(--dok-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  /* =========================================================
       KPI
    ========================================================= */

  .dokter-dashboard .kpi-card {

    background: #fff;

    border: 1px solid var(--dok-border);

    border-radius: 18px;

    padding: 19px;

    height: 100%;

    transition: .2s ease;
  }


  .dokter-dashboard .kpi-card:hover {

    transform: translateY(-2px);

    box-shadow:
      0 10px 28px rgba(30, 40, 60, .06);
  }


  .dokter-dashboard .kpi-top {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;
  }


  .dokter-dashboard .kpi-title {

    color: var(--dok-muted);

    font-size: 12px;

    margin-bottom: 5px;
  }


  .dokter-dashboard .kpi-value {

    color: var(--dok-text);

    font-size: 27px;

    font-weight: 700;

    line-height: 1.15;
  }


  .dokter-dashboard .kpi-info {

    font-size: 10px;

    margin-top: 7px;
  }


  .dokter-dashboard .kpi-icon {

    width: 46px;
    height: 46px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;
  }


  .dokter-dashboard .icon-purple {

    background: #eeecff;

    color: #635bff;
  }


  .dokter-dashboard .icon-blue {

    background: #e8f4ff;

    color: #1687d9;
  }


  .dokter-dashboard .icon-orange {

    background: #fff3e3;

    color: #df861e;
  }


  .dokter-dashboard .icon-green {

    background: #e8f8ef;

    color: #16965a;
  }


  .dokter-dashboard .icon-red {

    background: #feecec;

    color: #dc2626;
  }


  .dokter-dashboard .up {

    color: #16a34a;
  }


  .dokter-dashboard .warning {

    color: #d97706;
  }


  .dokter-dashboard .danger {

    color: #dc2626;
  }


  /* =========================================================
       CARD
    ========================================================= */

  .dokter-dashboard .dash-card {

    background: #fff;

    border: 1px solid var(--dok-border);

    border-radius: 18px;

    padding: 20px;

    height: 100%;
  }


  .dokter-dashboard .dash-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 16px;
  }


  .dokter-dashboard .dash-title {

    color: var(--dok-text);

    font-size: 15px;

    font-weight: 700;

    margin: 0;
  }


  .dokter-dashboard .dash-subtitle {

    color: var(--dok-muted);

    font-size: 11px;

    margin-top: 3px;
  }


  .dokter-dashboard .view-all {

    color: var(--dok-primary);

    font-size: 11px;

    text-decoration: none;

    font-weight: 600;
  }


  /* =========================================================
       NEXT PATIENT
    ========================================================= */

  .dokter-dashboard .next-patient {

    background: #f7f6ff;

    border: 1px solid #e7e4ff;

    border-radius: 15px;

    padding: 15px;

    margin-bottom: 15px;
  }


  .dokter-dashboard .next-patient-label {

    color: var(--dok-primary);

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    margin-bottom: 9px;
  }


  .dokter-dashboard .next-patient-content {

    display: flex;

    align-items: center;

    gap: 12px;
  }


  .dokter-dashboard .patient-avatar {

    width: 43px;
    height: 43px;

    border-radius: 13px;

    background: #fff;

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 12px;

    font-weight: 700;

    border: 1px solid #e4e1ff;

    flex-shrink: 0;
  }


  .dokter-dashboard .patient-name {

    color: var(--dok-text);

    font-size: 13px;

    font-weight: 700;
  }


  .dokter-dashboard .patient-meta {

    color: var(--dok-muted);

    font-size: 10px;

    margin-top: 3px;
  }


  .dokter-dashboard .next-patient-number {

    margin-left: auto;

    color: var(--dok-primary);

    font-size: 17px;

    font-weight: 700;
  }


  .dokter-dashboard .start-examination {

    width: 100%;

    margin-top: 13px;

    height: 36px;

    border-radius: 10px;

    font-size: 11px;

    font-weight: 600;
  }


  /* =========================================================
       QUEUE
    ========================================================= */

  .dokter-dashboard .queue-item {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .dokter-dashboard .queue-item:last-child {

    border-bottom: none;
  }


  .dokter-dashboard .queue-number {

    width: 39px;
    height: 39px;

    border-radius: 11px;

    background: #f0efff;

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 10px;

    font-weight: 700;

    flex-shrink: 0;
  }


  .dokter-dashboard .queue-name {

    color: var(--dok-text);

    font-size: 12px;

    font-weight: 600;
  }


  .dokter-dashboard .queue-detail {

    color: var(--dok-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .dokter-dashboard .queue-status {

    margin-left: auto;

    padding: 5px 8px;

    border-radius: 8px;

    font-size: 9px;

    white-space: nowrap;
  }


  .dokter-dashboard .waiting {

    background: #fff3d9;

    color: #c47a00;
  }


  .dokter-dashboard .called {

    background: #e8f2ff;

    color: #2377c7;
  }


  .dokter-dashboard .examination {

    background: #eeecff;

    color: #635bff;
  }


  .dokter-dashboard .completed {

    background: #e7f8ee;

    color: #168a4e;
  }


  /* =========================================================
       SCHEDULE
    ========================================================= */

  .dokter-dashboard .schedule-item {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .dokter-dashboard .schedule-item:last-child {

    border-bottom: none;
  }


  .dokter-dashboard .schedule-time {

    width: 55px;

    color: var(--dok-primary);

    font-size: 11px;

    font-weight: 700;

    flex-shrink: 0;
  }


  .dokter-dashboard .schedule-line {

    width: 2px;

    height: 35px;

    background: #e5e3ff;

    border-radius: 5px;
  }


  .dokter-dashboard .schedule-info {

    flex: 1;
  }


  .dokter-dashboard .schedule-title {

    color: var(--dok-text);

    font-size: 12px;

    font-weight: 600;
  }


  .dokter-dashboard .schedule-detail {

    color: var(--dok-muted);

    font-size: 10px;

    margin-top: 2px;
  }


  .dokter-dashboard .schedule-status {

    font-size: 9px;

    padding: 5px 8px;

    border-radius: 7px;

    white-space: nowrap;
  }


  .dokter-dashboard .schedule-active {

    background: #e8f8ef;

    color: #168a4e;
  }


  .dokter-dashboard .schedule-next {

    background: #eeecff;

    color: #635bff;
  }


  .dokter-dashboard .schedule-done {

    background: #f1f2f4;

    color: #7b8494;
  }


  /* =========================================================
       RME ALERT
    ========================================================= */

  .dokter-dashboard .rme-alert {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 11px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .dokter-dashboard .rme-alert:last-child {

    border-bottom: none;
  }


  .dokter-dashboard .rme-alert-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #fff3e3;

    color: #d97706;

    flex-shrink: 0;
  }


  .dokter-dashboard .rme-alert-name {

    color: var(--dok-text);

    font-size: 11px;

    font-weight: 600;
  }


  .dokter-dashboard .rme-alert-desc {

    color: var(--dok-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  .dokter-dashboard .rme-alert-action {

    margin-left: auto;

    color: var(--dok-primary);

    font-size: 10px;

    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;
  }


  /* =========================================================
       DIAGNOSIS
    ========================================================= */

  .dokter-dashboard .diagnosis-item {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 10px 0;

    border-bottom: 1px solid #f0f1f4;
  }


  .dokter-dashboard .diagnosis-item:last-child {

    border-bottom: none;
  }


  .dokter-dashboard .diagnosis-rank {

    width: 28px;
    height: 28px;

    border-radius: 8px;

    background: #f0efff;

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 10px;

    font-weight: 700;
  }


  .dokter-dashboard .diagnosis-name {

    flex: 1;

    color: var(--dok-text);

    font-size: 11px;

    font-weight: 600;
  }


  .dokter-dashboard .diagnosis-code {

    color: var(--dok-muted);

    font-size: 9px;

    display: block;

    margin-top: 2px;
  }


  .dokter-dashboard .diagnosis-count {

    color: var(--dok-text);

    font-size: 12px;

    font-weight: 700;
  }


  /* =========================================================
       QUICK ACCESS
    ========================================================= */

  .dokter-dashboard .quick-item {

    display: block;

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 13px;

    padding: 13px;

    text-decoration: none;

    transition: .2s ease;
  }


  .dokter-dashboard .quick-item:hover {

    background: #f5f4ff;

    border-color: #dedbff;

    transform: translateY(-1px);
  }


  .dokter-dashboard .quick-icon {

    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: #eeecff;

    color: var(--dok-primary);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
  }


  .dokter-dashboard .quick-title {

    color: var(--dok-text);

    font-size: 11px;

    font-weight: 600;

    margin-top: 8px;
  }


  .dokter-dashboard .quick-description {

    color: var(--dok-muted);

    font-size: 9px;

    margin-top: 2px;
  }


  /* =========================================================
       RESPONSIVE
    ========================================================= */

  @media (max-width: 1100px) {

    .dokter-dashboard .dok-filter-wrapper {

      flex-direction: column;

      align-items: flex-start;
    }

    .dokter-dashboard .dok-filter-form {

      width: 100%;

      flex-wrap: wrap;
    }

  }


  @media (max-width: 767px) {

    .dokter-dashboard .dok-filter-form {

      display: grid;

      grid-template-columns: 1fr 1fr;

      width: 100%;
    }

    .dokter-dashboard .dok-filter-group {

      min-width: 0;
    }

    .dokter-dashboard .dok-filter-button {

      width: 100%;
    }

    .dokter-dashboard .doctor-status-bar {

      align-items: flex-start;

      flex-direction: column;
    }

    .dokter-dashboard .doctor-status-right {

      text-align: left;
    }

  }


  @media (max-width: 480px) {

    .dokter-dashboard .dok-filter-form {

      grid-template-columns: 1fr;
    }

  }
</style>


<div class="dokter-dashboard">

  <!-- =====================================================
     FILTER PERIODE
====================================================== -->
  <div class="dok-filter-wrapper">

    <div class="dok-filter-title">

      <div class="dok-filter-icon">
        <iconify-icon
          icon="solar:calendar-search-bold">
        </iconify-icon>
      </div>

      <div>

        <div class="dok-filter-heading">
          Periode Pelayanan
        </div>

        <div class="dok-filter-description">
          Monitoring pasien dan aktivitas pelayanan
        </div>

      </div>

    </div>


    <div class="dok-filter-form">

      <div class="dok-filter-group">

        <label>
          Periode
        </label>

        <select
          id="dokterPeriod"
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


      <div class="dok-filter-group">

        <label>
          Dari
        </label>

        <input
          type="date"
          id="dokterStartDate"
          class="form-control"
          value="<?= date('Y-m-d') ?>">

      </div>


      <div class="dok-filter-group">

        <label>
          Sampai
        </label>

        <input
          type="date"
          id="dokterEndDate"
          class="form-control"
          value="<?= date('Y-m-d') ?>">

      </div>


      <button
        type="button"
        class="btn btn-primary dok-filter-button"
        id="applyDokterFilter">

        <iconify-icon
          icon="solar:filter-bold"
          class="me-1">
        </iconify-icon>

        Terapkan

      </button>

    </div>

  </div>


  <!-- =====================================================
     STATUS DOKTER
====================================================== -->
  <div class="doctor-status-bar">

    <div class="doctor-status-left">

      <div
        class="doctor-avatar-large"
        id="doctorAvatar">
        DR
      </div>


      <div>

        <div
          class="doctor-status-name"
          id="doctorName">
          Memuat...
        </div>

        <div
          class="doctor-status-poli"
          id="doctorPoli">
          Memuat informasi pelayanan...
        </div>

      </div>


      <div
        class="doctor-online"
        id="doctorOnline">

        <span class="doctor-online-dot"></span>

        Sedang Praktik

      </div>

    </div>


    <div class="doctor-status-right">

      <div
        class="doctor-clock"
        id="dokterCurrentTime">
        <?= date('H:i:s') ?>
      </div>

      <div class="doctor-clock-label">
        Waktu pelayanan
      </div>

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

            <div
              class="kpi-value"
              id="kpiTotalPasien">
              0
            </div>

            <div
              class="kpi-info up"
              id="kpiTotalPasienInfo">
              Berdasarkan periode
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

            <div
              class="kpi-value"
              id="kpiMenunggu">
              0
            </div>

            <div
              class="kpi-info warning"
              id="kpiMenungguInfo">
              Pasien berikutnya siap dipanggil
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

            <div
              class="kpi-value"
              id="kpiDiperiksa">
              0
            </div>

            <div
              class="kpi-info"
              id="kpiDiperiksaInfo">
              Pelayanan aktif
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
              Selesai
            </div>

            <div
              class="kpi-value"
              id="kpiSelesai">
              0
            </div>

            <div
              class="kpi-info up"
              id="kpiSelesaiInfo">
              0% dari total pasien
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
     PASIEN BERIKUTNYA + JADWAL
====================================================== -->
  <div class="row g-3 mb-3">


    <!-- PASIEN BERIKUTNYA -->
    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Pasien Berikutnya
            </h6>

            <div class="dash-subtitle">
              Antrian yang akan dilayani
            </div>

          </div>

          <span
            class="badge bg-warning text-dark"
            id="nextQueueBadge">
            Tidak Ada
          </span>

        </div>


        <div id="nextPatientContainer">

          <div class="text-center py-4 text-muted">
            Memuat data...
          </div>

        </div>


        <div id="queueListContainer">

          <div class="text-center py-4 text-muted">
            Memuat antrian...
          </div>

        </div>

      </div>

    </div>


    <!-- JADWAL -->
    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Jadwal Pelayanan Hari Ini
            </h6>

            <div class="dash-subtitle">
              Agenda pelayanan dokter
            </div>

          </div>

          <span
            class="badge bg-primary"
            id="practiceTimeBadge">
            -
          </span>

        </div>


        <div id="scheduleContainer">

          <div class="text-center py-4 text-muted">
            Memuat jadwal...
          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
     GRAFIK + RME
====================================================== -->
  <div class="row g-3 mb-3">


    <!-- GRAFIK -->
    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Statistik Pasien
            </h6>

            <div
              class="dash-subtitle"
              id="patientChartSubtitle">
              Jumlah pasien yang dilayani
            </div>

          </div>

          <select
            class="form-select"
            id="patientChartPeriod"
            style="
                        width:auto;
                        min-width:110px;
                        font-size:11px;
                        border-radius:10px;
                    ">

            <option value="7days">
              7 Hari
            </option>

            <option value="30days">
              30 Hari
            </option>

            <option value="thismonth">
              Bulan Ini
            </option>

          </select>

        </div>


        <div style="height:260px">

          <canvas
            id="dokterPatientChart">
          </canvas>

        </div>

      </div>

    </div>


    <!-- RME -->
    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              RME Perlu Dilengkapi
            </h6>

            <div class="dash-subtitle">
              Dokumentasi medis yang masih tertunda
            </div>

          </div>

          <span
            class="badge bg-warning text-dark"
            id="rmeBadge">
            0
          </span>

        </div>


        <div id="rmeContainer">

          <div class="text-center py-4 text-muted">
            Memuat data...
          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
     DIAGNOSIS + AKSES CEPAT
====================================================== -->
  <div class="row g-3">


    <!-- DIAGNOSIS -->
    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Diagnosis Terbanyak
            </h6>

            <div class="dash-subtitle">
              Berdasarkan kunjungan periode terpilih
            </div>

          </div>

          <a
            href="#"
            class="view-all">
            Detail
          </a>

        </div>


        <div id="diagnosisContainer">

          <div class="text-center py-4 text-muted">
            Memuat diagnosis...
          </div>

        </div>

      </div>

    </div>


    <!-- AKSES CEPAT -->
    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Akses Cepat
            </h6>

            <div class="dash-subtitle">
              Modul klinis yang sering digunakan
            </div>

          </div>

        </div>


        <div class="row g-2">


          <!-- RME -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:document-text-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Rekam Medis
              </div>

              <div class="quick-description">
                Buka RME pasien
              </div>

            </a>

          </div>


          <!-- SOAP -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:clipboard-text-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                SOAP
              </div>

              <div class="quick-description">
                Catatan pemeriksaan
              </div>

            </a>

          </div>


          <!-- RESEP -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:pills-3-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Resep
              </div>

              <div class="quick-description">
                Buat resep pasien
              </div>

            </a>

          </div>


          <!-- LAB -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:test-tube-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Laboratorium
              </div>

              <div class="quick-description">
                Pemeriksaan lab
              </div>

            </a>

          </div>


          <!-- RADIOLOGI -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:scanner-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Radiologi
              </div>

              <div class="quick-description">
                Pemeriksaan radiologi
              </div>

            </a>

          </div>


          <!-- RIWAYAT -->
          <div class="col-6 col-md-4">

            <a
              href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:history-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Riwayat Pasien
              </div>

              <div class="quick-description">
                Riwayat kunjungan
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
   DASHBOARD DOKTER
========================================================= */

  document.addEventListener(
    "DOMContentLoaded",
    function() {

      "use strict";


      /* =====================================================
         CONFIG
      ====================================================== */

      const API_URL =
        "controller/dashboard/dokterDashboardController.php?action=dashboard";


      let patientChart = null;


      /* =====================================================
         ELEMENT
      ====================================================== */

      const period =
        document.getElementById(
          "dokterPeriod"
        );

      const startDate =
        document.getElementById(
          "dokterStartDate"
        );

      const endDate =
        document.getElementById(
          "dokterEndDate"
        );

      const applyButton =
        document.getElementById(
          "applyDokterFilter"
        );


      /* =====================================================
         HELPER
      ====================================================== */

      function escapeHtml(value) {

        if (
          value === null ||
          value === undefined
        ) {
          return "";
        }

        return String(value)
          .replace(
            /&/g,
            "&amp;"
          )
          .replace(
            /</g,
            "&lt;"
          )
          .replace(
            />/g,
            "&gt;"
          )
          .replace(
            /"/g,
            "&quot;"
          )
          .replace(
            /'/g,
            "&#039;"
          );

      }


      function numberFormat(value) {

        return new Intl.NumberFormat(
          "id-ID"
        ).format(
          Number(value || 0)
        );

      }


      function formatDate(dateString) {

        if (!dateString) {
          return "-";
        }

        const date =
          new Date(
            dateString +
            "T00:00:00"
          );

        if (
          isNaN(
            date.getTime()
          )
        ) {
          return dateString;
        }

        return date.toLocaleDateString(
          "id-ID", {
            day: "2-digit",
            month: "short"
          }
        );

      }


      function initials(name) {

        if (!name) {
          return "DR";
        }

        const parts =
          String(name)
          .trim()
          .split(/\s+/);

        if (parts.length === 1) {

          return parts[0]
            .substring(0, 2)
            .toUpperCase();

        }

        return (
          parts[0][0] +
          parts[1][0]
        ).toUpperCase();

      }


      /* =====================================================
         PERIOD
      ====================================================== */

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


        if (
          value === "today"
        ) {

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

          end =
            new Date(today);

        } else if (
          value === "30days"
        ) {

          start =
            new Date(today);

          start.setDate(
            today.getDate() - 29
          );

          end =
            new Date(today);

        } else if (
          value === "thismonth"
        ) {

          start =
            new Date(
              today.getFullYear(),
              today.getMonth(),
              1
            );

          end =
            new Date(today);

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


      /* =====================================================
         LOAD DASHBOARD
      ====================================================== */

      async function loadDashboard() {

        const selectedPeriod =
          period.value;


        let url =
          API_URL +
          "&period=" +
          encodeURIComponent(
            selectedPeriod
          );


        if (
          selectedPeriod ===
          "custom"
        ) {

          if (
            !startDate.value ||
            !endDate.value
          ) {

            showError(
              "Tanggal custom belum lengkap."
            );

            return;

          }


          if (
            startDate.value >
            endDate.value
          ) {

            showError(
              "Tanggal mulai tidak boleh lebih besar dari tanggal akhir."
            );

            return;

          }


          url +=
            "&start_date=" +
            encodeURIComponent(
              startDate.value
            ) +
            "&end_date=" +
            encodeURIComponent(
              endDate.value
            );

        }


        setLoading(true);


        try {

          const response =
            await fetch(
              url, {
                method: "GET",
                headers: {
                  "Accept": "application/json",
                  "X-Requested-With": "XMLHttpRequest"
                },
                cache: "no-store"
              }
            );


          if (!response.ok) {

            throw new Error(
              "HTTP Error " +
              response.status
            );

          }


          const data =
            await response.json();


          console.log(
            "Dashboard Dokter:",
            data
          );


          if (
            !data.status
          ) {

            throw new Error(
              data.message ||
              "Gagal mengambil data dashboard."
            );

          }


          renderDoctor(
            data.doctor
          );

          renderKPI(
            data.kpi
          );

          renderNextPatient(
            data.next_patient
          );

          renderQueue(
            data.queue
          );

          renderSchedule(
            data.schedule
          );

          renderPatientChart(
            data.patient_chart
          );

          renderRME(
            data.rme
          );

          renderDiagnosis(
            data.diagnosis
          );


        } catch (error) {

          console.error(
            "Dashboard Dokter Error:",
            error
          );

          showError(
            error.message
          );

        } finally {

          setLoading(false);

        }

      }


      /* =====================================================
         DOCTOR
      ====================================================== */

      function renderDoctor(
        doctor
      ) {

        doctor =
          doctor || {};


        const name =
          doctor.name &&
          doctor.name !== "Dokter" ?
          doctor.name :
          doctor.id ||
          doctor.code ||
          "Dokter";


        document.getElementById(
            "doctorName"
          ).textContent =
          name;


        document.getElementById(
            "doctorAvatar"
          ).textContent =
          initials(name);


        /*
         * Controller saat ini belum mengirim
         * poli dan jadwal sebagai field doctor.
         */

        const poliElement =
          document.getElementById(
            "doctorPoli"
          );


        if (
          doctor.poli
        ) {

          poliElement.textContent =
            doctor.poli;

        } else {

          poliElement.textContent =
            "Dokter · Informasi jadwal pelayanan";

        }

      }


      /* =====================================================
         KPI
      ====================================================== */

      function renderKPI(
        kpi
      ) {

        kpi =
          kpi || {};


        const total =
          Number(
            kpi.total_pasien || 0
          );

        const waiting =
          Number(
            kpi.pasien_menunggu || 0
          );

        const checking =
          Number(
            kpi.sedang_diperiksa || 0
          );

        const done =
          Number(
            kpi.selesai || 0
          );

        const percentage =
          Number(
            kpi.persentase_selesai || 0
          );


        document.getElementById(
            "kpiTotalPasien"
          ).textContent =
          numberFormat(total);


        document.getElementById(
            "kpiMenunggu"
          ).textContent =
          numberFormat(waiting);


        document.getElementById(
            "kpiDiperiksa"
          ).textContent =
          numberFormat(checking);


        document.getElementById(
            "kpiSelesai"
          ).textContent =
          numberFormat(done);


        document.getElementById(
            "kpiTotalPasienInfo"
          ).textContent =
          "Pasien unik pada periode terpilih";


        document.getElementById(
            "kpiMenungguInfo"
          ).textContent =
          waiting > 0 ?
          "Pasien berikutnya siap dipanggil" :
          "Tidak ada pasien menunggu";


        document.getElementById(
            "kpiDiperiksaInfo"
          ).textContent =
          checking > 0 ?
          "Pasien sedang dalam pelayanan" :
          "Tidak ada pemeriksaan aktif";


        document.getElementById(
            "kpiSelesaiInfo"
          ).textContent =
          percentage +
          "% dari total pasien";

      }


      /* =====================================================
         NEXT PATIENT
      ====================================================== */

      function renderNextPatient(
        patient
      ) {

        const container =
          document.getElementById(
            "nextPatientContainer"
          );

        const badge =
          document.getElementById(
            "nextQueueBadge"
          );


        if (!patient) {

          badge.textContent =
            "Tidak Ada";


          container.innerHTML = `

                    <div class="text-center py-5 text-muted">

                        <iconify-icon
                            icon="solar:user-cross-bold"
                            style="font-size:35px;">
                        </iconify-icon>

                        <div class="mt-2">
                            Tidak ada pasien berikutnya
                        </div>

                    </div>

                `;

          return;

        }


        badge.textContent =
          "Antrian " +
          escapeHtml(
            patient.nomor || "-"
          );


        const patientName =
          patient.nama ||
          "Pasien";


        container.innerHTML = `

                <div class="next-patient">

                    <div class="next-patient-label">
                        Pasien Berikutnya
                    </div>

                    <div class="next-patient-content">

                        <div class="patient-avatar">

                            ${escapeHtml(
                                initials(
                                    patientName
                                )
                            )}

                        </div>

                        <div>

                            <div class="patient-name">

                                ${escapeHtml(
                                    patientName
                                )}

                            </div>

                            <div class="patient-meta">

                                ${escapeHtml(
                                    patient.nomor_rm ||
                                    "-"
                                )}

                            </div>

                            <div class="patient-meta">

                                ${escapeHtml(
                                    patient.metode_bayar ||
                                    (
                                        patient.noKartu
                                            ? "BPJS"
                                            : "-"
                                    )
                                )}

                            </div>

                        </div>

                        <div class="next-patient-number">

                            ${escapeHtml(
                                patient.nomor ||
                                "-"
                            )}

                        </div>

                    </div>


                    <button
                        type="button"
                        class="btn btn-primary start-examination"
                        data-id-visit="${escapeHtml(
                            patient.nomor_visit || ""
                        )}"
                        data-id-patient="${escapeHtml(
                            patient.id_patient || ""
                        )}">

                        <iconify-icon
                            icon="solar:stethoscope-bold"
                            class="me-1">
                        </iconify-icon>

                        Mulai Pemeriksaan

                    </button>

                </div>

            `;

      }


      /* =====================================================
         QUEUE
      ====================================================== */

      function renderQueue(
        queue
      ) {

        const container =
          document.getElementById(
            "queueListContainer"
          );


        queue =
          queue || {};


        const items =
          queue.items || [];


        if (
          items.length === 0
        ) {

          container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        Tidak ada antrian lainnya.
                    </div>
                `;

          return;

        }


        let html = "";


        items.forEach(
          function(item) {

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
                                        item.nama ||
                                        "Pasien"
                                    )}

                                </div>


                                <div class="queue-detail">

                                    ${escapeHtml(
                                        item.nomor_rm ||
                                        "-"
                                    )}

                                    ${item.poli
                                        ? " · " +
                                          escapeHtml(
                                              item.poli
                                          )
                                        : ""
                                    }

                                </div>

                            </div>


                            <span
                                class="queue-status ${escapeHtml(
                                    item.status_class ||
                                    "waiting"
                                )}">

                                ${escapeHtml(
                                    item.status ||
                                    "Menunggu"
                                )}

                            </span>

                        </div>

                    `;

          }
        );


        container.innerHTML =
          html;

      }


      /* =====================================================
         SCHEDULE
      ====================================================== */

      function renderSchedule(
        schedule
      ) {

        const container =
          document.getElementById(
            "scheduleContainer"
          );

        const badge =
          document.getElementById(
            "practiceTimeBadge"
          );


        schedule =
          schedule || {};


        const items =
          schedule.today || [];


        if (
          items.length === 0
        ) {

          badge.textContent =
            "-";


          container.innerHTML = `

                    <div class="text-center py-5 text-muted">

                        <iconify-icon
                            icon="solar:calendar-minimalistic-bold"
                            style="font-size:35px;">
                        </iconify-icon>

                        <div class="mt-2">
                            Belum ada data jadwal pelayanan hari ini.
                        </div>

                    </div>

                `;

          return;

        }


        let html = "";

        let firstTime = "";
        let lastTime = "";


        items.forEach(
          function(item, index) {

            const time =
              item.jampraktek ||
              "-";


            if (
              index === 0
            ) {

              firstTime =
                time;

            }


            lastTime =
              time;


            const statusClass =
              index === 0 ?
              "schedule-active" :
              "schedule-next";


            const statusText =
              index === 0 ?
              "Berlangsung" :
              "Terjadwal";


            html += `

                        <div class="schedule-item">

                            <div class="schedule-time">

                                ${escapeHtml(
                                    time
                                )}

                            </div>


                            <div class="schedule-line"></div>


                            <div class="schedule-info">

                                <div class="schedule-title">

                                    ${escapeHtml(
                                        item.poli ||
                                        "Pelayanan Poli"
                                    )}

                                </div>


                                <div class="schedule-detail">

                                    ${numberFormat(
                                        item.jumlah_pasien ||
                                        0
                                    )}

                                    pasien

                                </div>

                            </div>


                            <span
                                class="schedule-status ${statusClass}">

                                ${statusText}

                            </span>

                        </div>

                    `;

          }
        );


        badge.textContent =
          firstTime +
          (
            firstTime !== lastTime ?
            " - " + lastTime :
            ""
          );


        container.innerHTML =
          html;

      }


      /* =====================================================
         PATIENT CHART
      ====================================================== */

      function renderPatientChart(
        chartData
      ) {

        const canvas =
          document.getElementById(
            "dokterPatientChart"
          );


        if (
          !canvas ||
          typeof Chart === "undefined"
        ) {

          return;

        }


        if (
          patientChart
        ) {

          patientChart.destroy();

          patientChart = null;

        }


        chartData =
          chartData || [];


        const labels =
          chartData.map(
            function(item) {

              return formatDate(
                item.date
              );

            }
          );


        const values =
          chartData.map(
            function(item) {

              return Number(
                item.total || 0
              );

            }
          );


        patientChart =
          new Chart(
            canvas, {

              type: "line",

              data: {

                labels: labels,

                datasets: [

                  {

                    label: "Pasien",

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

                      label: function(
                        context
                      ) {

                        return (
                          " " +
                          numberFormat(
                            context.raw
                          ) +
                          " pasien"
                        );

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

            }
          );

      }


      /* =====================================================
         RME
      ====================================================== */

      function renderRME(
        rme
      ) {

        const container =
          document.getElementById(
            "rmeContainer"
          );

        const badge =
          document.getElementById(
            "rmeBadge"
          );


        rme =
          rme || {};


        const items =
          rme.items || [];


        badge.textContent =
          numberFormat(
            rme.total || 0
          );


        if (
          items.length === 0
        ) {

          container.innerHTML = `

                    <div class="text-center py-5 text-success">

                        <iconify-icon
                            icon="solar:check-circle-bold"
                            style="font-size:35px;">
                        </iconify-icon>

                        <div class="mt-2">
                            RME sudah lengkap.
                        </div>

                    </div>

                `;

          return;

        }


        let html = "";


        items.forEach(
          function(item) {

            html += `

                        <div class="rme-alert">

                            <div class="rme-alert-icon">

                                <iconify-icon
                                    icon="${escapeHtml(
                                        item.icon ||
                                        "solar:document-text-bold"
                                    )}">
                                </iconify-icon>

                            </div>


                            <div>

                                <div class="rme-alert-name">

                                    ${escapeHtml(
                                        item.nama ||
                                        "Pasien"
                                    )}

                                </div>


                                <div class="rme-alert-desc">

                                    ${escapeHtml(
                                        item.description ||
                                        "Dokumentasi belum lengkap"
                                    )}

                                </div>

                            </div>


                            <a
                                href="#"
                                class="rme-alert-action"
                                data-id-visit="${escapeHtml(
                                    item.visit_ID ||
                                    ""
                                )}"
                                data-id-patient="${escapeHtml(
                                    item.id_patient ||
                                    ""
                                )}">

                                ${escapeHtml(
                                    item.action ||
                                    "Lengkapi"
                                )}

                            </a>

                        </div>

                    `;

          }
        );


        container.innerHTML =
          html;

      }


      /* =====================================================
         DIAGNOSIS
      ====================================================== */

      function renderDiagnosis(
        diagnosis
      ) {

        const container =
          document.getElementById(
            "diagnosisContainer"
          );


        diagnosis =
          diagnosis || [];


        if (
          diagnosis.length === 0
        ) {

          container.innerHTML = `

                    <div class="text-center py-5 text-muted">

                        <iconify-icon
                            icon="solar:clipboard-remove-bold"
                            style="font-size:35px;">
                        </iconify-icon>

                        <div class="mt-2">
                            Belum ada diagnosis pada periode ini.
                        </div>

                    </div>

                `;

          return;

        }


        let html = "";


        diagnosis.forEach(
          function(item) {

            html += `

                        <div class="diagnosis-item">

                            <div class="diagnosis-rank">

                                ${escapeHtml(
                                    item.rank ||
                                    "-"
                                )}

                            </div>


                            <div class="diagnosis-name">

                                ${escapeHtml(
                                    item.nama ||
                                    "Tidak diketahui"
                                )}

                                <span class="diagnosis-code">

                                    ${escapeHtml(
                                        item.kode ||
                                        "-"
                                    )}

                                </span>

                            </div>


                            <div class="diagnosis-count">

                                ${numberFormat(
                                    item.total ||
                                    0
                                )}

                            </div>

                        </div>

                    `;

          }
        );


        container.innerHTML =
          html;

      }


      /* =====================================================
         CLOCK
      ====================================================== */

      function updateDoctorClock() {

        const clock =
          document.getElementById(
            "dokterCurrentTime"
          );


        if (!clock) {
          return;
        }


        const now =
          new Date();


        clock.textContent =
          String(
            now.getHours()
          ).padStart(2, "0") +

          ":" +

          String(
            now.getMinutes()
          ).padStart(2, "0") +

          ":" +

          String(
            now.getSeconds()
          ).padStart(2, "0");

      }


      updateDoctorClock();


      setInterval(
        updateDoctorClock,
        1000
      );


      /* =====================================================
         LOADING
      ====================================================== */

      function setLoading(
        loading
      ) {

        if (!applyButton) {
          return;
        }


        if (loading) {

          applyButton.disabled =
            true;

          applyButton.innerHTML = `

                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>

                    Memuat...

                `;

        } else {

          applyButton.disabled =
            false;

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
         ERROR
      ====================================================== */

      function showError(
        message
      ) {

        if (
          typeof Swal !==
          "undefined"
        ) {

          Swal.fire({

            icon: "error",

            title: "Gagal memuat dashboard",

            text: message ||
              "Terjadi kesalahan."

          });

        } else {

          alert(
            message ||
            "Terjadi kesalahan."
          );

        }

      }


      /* =====================================================
         FILTER BUTTON
      ====================================================== */

      applyButton.addEventListener(
        "click",
        function() {

          loadDashboard();

        }
      );


      /* =====================================================
         CHART PERIOD
      ====================================================== */

      const chartPeriod =
        document.getElementById(
          "patientChartPeriod"
        );


      if (chartPeriod) {

        chartPeriod.addEventListener(
          "change",
          function() {

            period.value =
              this.value;

            updatePeriod();

            loadDashboard();

          }
        );

      }


      /* =====================================================
         INITIAL
      ====================================================== */

      updatePeriod();

      loadDashboard();

    }

  );
</script>