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

      <div class="doctor-avatar-large">
        DA
      </div>

      <div>

        <div class="doctor-status-name">
          dr. Andi Saputra
        </div>

        <div class="doctor-status-poli">
          Poli Umum · Praktik 08:00 - 14:00
        </div>

      </div>

      <div class="doctor-online">

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

            <div class="kpi-value">
              32
            </div>

            <div class="kpi-info up">
              ↑ 8,4% dari periode sebelumnya
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
              6
            </div>

            <div class="kpi-info warning">
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

            <div class="kpi-value">
              1
            </div>

            <div class="kpi-info">
              Ruang Pemeriksaan 01
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

            <div class="kpi-value">
              25
            </div>

            <div class="kpi-info up">
              78% dari total pasien
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

          <span class="badge bg-warning text-dark">
            Antrian 021
          </span>

        </div>


        <div class="next-patient">

          <div class="next-patient-label">

            Pasien Berikutnya

          </div>


          <div class="next-patient-content">

            <div class="patient-avatar">
              BS
            </div>

            <div>

              <div class="patient-name">
                Budi Santoso
              </div>

              <div class="patient-meta">
                RM-000182 · Laki-laki · 42 Tahun
              </div>

              <div class="patient-meta">
                BPJS · Kunjungan Baru
              </div>

            </div>

            <div class="next-patient-number">
              A-021
            </div>

          </div>


          <button
            type="button"
            class="btn btn-primary start-examination">

            <iconify-icon
              icon="solar:stethoscope-bold"
              class="me-1">
            </iconify-icon>

            Mulai Pemeriksaan

          </button>

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
              RM-000183 · 35 Tahun
            </div>

          </div>

          <span class="queue-status waiting">
            Menunggu
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
              RM-000184 · 51 Tahun
            </div>

          </div>

          <span class="queue-status waiting">
            Menunggu
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
              RM-000185 · 29 Tahun
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
              RM-000186 · 46 Tahun
            </div>

          </div>

          <span class="queue-status called">
            Dipanggil
          </span>

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

          <span class="badge bg-primary">
            08:00 - 14:00
          </span>

        </div>


        <div class="schedule-item">

          <div class="schedule-time">
            08:00
          </div>

          <div class="schedule-line"></div>

          <div class="schedule-info">

            <div class="schedule-title">
              Poli Umum
            </div>

            <div class="schedule-detail">
              Pasien Umum & BPJS · 15 pasien
            </div>

          </div>

          <span class="schedule-status schedule-done">
            Selesai
          </span>

        </div>


        <div class="schedule-item">

          <div class="schedule-time">
            10:00
          </div>

          <div class="schedule-line"></div>

          <div class="schedule-info">

            <div class="schedule-title">
              Poli Umum
            </div>

            <div class="schedule-detail">
              Kunjungan lanjutan · 8 pasien
            </div>

          </div>

          <span class="schedule-status schedule-active">
            Berlangsung
          </span>

        </div>


        <div class="schedule-item">

          <div class="schedule-time">
            12:00
          </div>

          <div class="schedule-line"></div>

          <div class="schedule-info">

            <div class="schedule-title">
              Konsultasi
            </div>

            <div class="schedule-detail">
              Konsultasi pasien terjadwal · 5 pasien
            </div>

          </div>

          <span class="schedule-status schedule-next">
            Berikutnya
          </span>

        </div>


        <div class="schedule-item">

          <div class="schedule-time">
            13:00
          </div>

          <div class="schedule-line"></div>

          <div class="schedule-info">

            <div class="schedule-title">
              Medical Check Up
            </div>

            <div class="schedule-detail">
              Pemeriksaan kesehatan · 4 pasien
            </div>

          </div>

          <span class="schedule-status schedule-next">
            Terjadwal
          </span>

        </div>


        <div class="schedule-item">

          <div class="schedule-time">
            14:00
          </div>

          <div class="schedule-line"></div>

          <div class="schedule-info">

            <div class="schedule-title">
              Selesai Praktik
            </div>

            <div class="schedule-detail">
              Penutupan pelayanan poli
            </div>

          </div>

          <span class="schedule-status schedule-next">
            Jadwal
          </span>

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

            <div class="dash-subtitle">
              Jumlah pasien yang dilayani
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


        <div style="height:260px">

          <canvas
            id="dokterPatientChart">
          </canvas>

        </div>

      </div>

    </div>


    <!-- RME BELUM LENGKAP -->

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

          <span class="badge bg-warning text-dark">
            4
          </span>

        </div>


        <div class="rme-alert">

          <div class="rme-alert-icon">

            <iconify-icon
              icon="solar:document-text-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="rme-alert-name">
              Ahmad Fauzi
            </div>

            <div class="rme-alert-desc">
              SOAP belum lengkap
            </div>

          </div>

          <a
            href="#"
            class="rme-alert-action">

            Lengkapi

          </a>

        </div>


        <div class="rme-alert">

          <div class="rme-alert-icon">

            <iconify-icon
              icon="solar:clipboard-text-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="rme-alert-name">
              Dewi Lestari
            </div>

            <div class="rme-alert-desc">
              Diagnosis belum disimpan
            </div>

          </div>

          <a
            href="#"
            class="rme-alert-action">

            Lengkapi

          </a>

        </div>


        <div class="rme-alert">

          <div class="rme-alert-icon">

            <iconify-icon
              icon="solar:pills-3-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="rme-alert-name">
              Rudi Hartono
            </div>

            <div class="rme-alert-desc">
              Resep belum dikirim
            </div>

          </div>

          <a
            href="#"
            class="rme-alert-action">

            Proses

          </a>

        </div>


        <div class="rme-alert">

          <div class="rme-alert-icon">

            <iconify-icon
              icon="solar:test-tube-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="rme-alert-name">
              Siti Rahma
            </div>

            <div class="rme-alert-desc">
              Hasil lab menunggu
            </div>

          </div>

          <a
            href="#"
            class="rme-alert-action">

            Lihat

          </a>

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


        <div class="diagnosis-item">

          <div class="diagnosis-rank">
            01
          </div>

          <div class="diagnosis-name">

            ISPA

            <span class="diagnosis-code">
              J06.9
            </span>

          </div>

          <div class="diagnosis-count">
            12
          </div>

        </div>


        <div class="diagnosis-item">

          <div class="diagnosis-rank">
            02
          </div>

          <div class="diagnosis-name">

            Hipertensi Esensial

            <span class="diagnosis-code">
              I10
            </span>

          </div>

          <div class="diagnosis-count">
            8
          </div>

        </div>


        <div class="diagnosis-item">

          <div class="diagnosis-rank">
            03
          </div>

          <div class="diagnosis-name">

            Gastritis

            <span class="diagnosis-code">
              K29.7
            </span>

          </div>

          <div class="diagnosis-count">
            6
          </div>

        </div>


        <div class="diagnosis-item">

          <div class="diagnosis-rank">
            04
          </div>

          <div class="diagnosis-name">

            Diabetes Mellitus

            <span class="diagnosis-code">
              E11.9
            </span>

          </div>

          <div class="diagnosis-count">
            4
          </div>

        </div>


        <div class="diagnosis-item">

          <div class="diagnosis-rank">
            05
          </div>

          <div class="diagnosis-name">

            Myalgia

            <span class="diagnosis-code">
              M79.1
            </span>

          </div>

          <div class="diagnosis-count">
            2
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


      /* =================================================
         FILTER PERIODE
      ================================================= */

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
            "Filter Dokter:", {
              dari: dari,
              sampai: sampai
            }
          );


          /*
           * NANTI BISA DIGANTI
           * DENGAN AJAX DATABASE.
           *
           * Contoh:
           *
           * loadDashboardDokter(
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
         JAM DOKTER
      ================================================= */

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


      /* =================================================
         CHART PASIEN
      ================================================= */

      const chartElement =
        document.getElementById(
          "dokterPatientChart"
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
                    28,
                    34,
                    29,
                    37,
                    32,
                    19,
                    8
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

    }
  );
</script>