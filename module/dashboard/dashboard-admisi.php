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

  .admisi-dashboard #admisiQueueList {
    scrollbar-width: thin;
  }

  .admisi-dashboard #admisiQueueList::-webkit-scrollbar {
    width: 5px;
  }

  .admisi-dashboard #admisiQueueList::-webkit-scrollbar-thumb {
    background: #dfe2e8;
    border-radius: 10px;
  }

  /* Status Poliklinik: tampil 5 item, sisanya scroll */
  .admisi-dashboard #admisiPoliList {
    max-height: 390px;
    overflow-y: auto;
    scrollbar-width: thin;
    padding-right: 4px;
  }

  .admisi-dashboard #admisiPoliList::-webkit-scrollbar {
    width: 5px;
  }

  .admisi-dashboard #admisiPoliList::-webkit-scrollbar-thumb {
    background: #dfe2e8;
    border-radius: 10px;
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
              <span id="admisiTotalPasien">0</span>
            </div>

            <div class="kpi-info up">
              <span id="admisiPertumbuhan">— dari periode sebelumnya</span>
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
              <span id="admisiPasienMenunggu">0</span>
            </div>

            <div class="kpi-info warning">
              <span id="admisiMenungguInfo">Perlu segera dilayani</span>
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
              <span id="admisiSedangDiperiksa">0</span>
            </div>

            <div class="kpi-info">
              <span id="admisiPoliAktif">0 poli aktif</span>
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
              <span id="admisiPelayananSelesai">0</span>
            </div>

            <div class="kpi-info up">
              <span id="admisiPersentaseSelesai">0% dari total pasien</span>
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

          <span class="badge bg-success" id="admisiDoctorHadir">0 Hadir</span>

        </div>


        <div id="admisiDoctorList">
          <div class="text-muted small py-3 text-center">Memuat data dokter...</div>
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

            <span class="badge bg-warning text-dark me-1" id="admisiQueueWaiting">0 Menunggu</span>
            <span class="badge bg-primary" id="admisiQueueProcessing">0 Diproses</span>

          </div>

        </div>


        <div id="admisiQueueList">
          <div class="text-muted small py-3 text-center">Memuat antrean...</div>
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


        <div id="admisiDoctorSummaryList">
          <div class="text-muted small py-3 text-center">Memuat data pasien per dokter...</div>
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


        <div id="admisiPoliList">
          <div class="text-muted small py-3 text-center">Memuat status poli...</div>
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


        <div id="admisiPatientTypeList">
          <div class="text-muted small py-3 text-center">Memuat jenis pasien...</div>
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
  (function() {
    function initAdmisiDashboard() {
      const period = document.getElementById('admisiPeriod');
      const startDate = document.getElementById('admisiStartDate');
      const endDate = document.getElementById('admisiEndDate');
      const applyButton = document.getElementById('applyAdmisiFilter');
      if (!period || !startDate || !endDate || !applyButton) return;
      const endpoint = 'controller/dashboard/admisiDashboardController.php?action=dashboard';
      let chart = null;
      const esc = v => {
        const d = document.createElement('div');
        d.textContent = v == null ? '' : String(v);
        return d.innerHTML;
      };
      const num = v => Number(v || 0).toLocaleString('id-ID');
      const localDate = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      const setText = (id, v) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
      };

      function updatePeriod() {
        const v = period.value,
          t = new Date();
        let s = new Date(t),
          e = new Date(t);
        if (v === 'yesterday') {
          s.setDate(t.getDate() - 1);
          e = new Date(s);
        } else if (v === '7days') s.setDate(t.getDate() - 6);
        else if (v === '30days') s.setDate(t.getDate() - 29);
        else if (v === 'thismonth') s = new Date(t.getFullYear(), t.getMonth(), 1);
        else if (v === 'lastmonth') {
          s = new Date(t.getFullYear(), t.getMonth() - 1, 1);
          e = new Date(t.getFullYear(), t.getMonth(), 0);
        } else if (v === 'custom') return;
        startDate.value = localDate(s);
        endDate.value = localDate(e);
      }

      function initials(name) {
        const p = String(name || 'DR').trim().split(/\s+/).filter(Boolean);
        return p.length < 2 ? (p[0] || 'DR').slice(0, 2).toUpperCase() : (p[0][0] + p[1][0]).toUpperCase();
      }

      function renderDoctors(d) {
        const el = document.getElementById('admisiDoctorList');
        if (!el) return;
        const a = d.items || [];
        if (!a.length) {
          el.innerHTML = '<div class="text-muted small py-3 text-center">Tidak ada data dokter.</div>';
          return;
        }
        el.innerHTML = a.map(x => `<div class="doctor-item"><div class="doctor-avatar">${esc(x.initial||initials(x.doctor_name))}</div><div><div class="doctor-name">${esc(x.doctor_name||'Dokter')}</div><div class="doctor-poli">${esc(x.poli_name||'-')}</div></div><div class="doctor-status ${x.status==='absent'?'absent':''}"><span class="doctor-status-dot"></span>${esc(x.status_label||'Hadir')}${x.jam_masuk?`<span class="doctor-time">${esc(x.jam_masuk)}</span>`:''}</div></div>`).join('');
      }

      function renderQueue(d) {
        const el = document.getElementById('admisiQueueList');
        if (!el) return;
        const a = d.items || [];
        if (!a.length) {
          el.innerHTML = '<div class="text-muted small py-4 text-center">Tidak ada antrean pasien.</div>';
          return;
        }
        const v = a.slice(0, 10);
        el.style.maxHeight = a.length > 10 ? '520px' : 'none';
        el.style.overflowY = a.length > 10 ? 'auto' : 'visible';
        el.innerHTML = v.map(x => `<div class="queue-item"><div class="queue-number">${esc(x.number||'-')}</div><div><div class="queue-name">${esc(x.patient_name||'Pasien')}</div><div class="queue-detail">${esc(x.poli_name||'-')} · ${esc(x.doctor_name||'-')}</div></div><span class="queue-status ${esc(x.status_class||'waiting')}">${esc(x.status_label||'Menunggu')}</span></div>`).join('') + (a.length > 10 ? `<div class="text-muted text-center small py-2">Menampilkan 10 antrean pertama dari ${num(a.length)}. Scroll untuk melihat lainnya.</div>` : '');
      }

      function renderDoctorSummary(d) {
        const el = document.getElementById('admisiDoctorSummaryList');
        if (!el) return;
        const a = d.items || [];
        if (!a.length) {
          el.innerHTML = '<div class="text-muted small py-3 text-center">Belum ada data pasien per dokter.</div>';
          return;
        }
        el.innerHTML = a.map(x => `<div class="doctor-summary"><div class="doctor-summary-left"><div class="summary-icon"><iconify-icon icon="solar:stethoscope-bold"></iconify-icon></div><div><div class="summary-doctor">${esc(x.doctor_name||'Dokter')}</div><div class="summary-poli">${esc(x.poli_name||'-')}</div></div></div><div class="summary-count"><strong>${num(x.total_pasien)}</strong><span>pasien</span></div></div>`).join('');
      }

      function renderPoli(d) {
        const el = document.getElementById('admisiPoliList');
        if (!el) return;

        const a = d.items || [];

        if (!a.length) {
          el.innerHTML = '<div class="text-muted small py-3 text-center">Belum ada data poli.</div>';
          el.style.maxHeight = 'none';
          el.style.overflowY = 'visible';
          return;
        }

        /*
         * Tampilkan 5 poli pertama.
         * Jika lebih dari 5, container tetap dapat di-scroll
         * untuk melihat poli berikutnya.
         */
        const visible = a.slice(0, 5);

        el.style.maxHeight = a.length > 5 ? '390px' : 'none';
        el.style.overflowY = a.length > 5 ? 'auto' : 'visible';

        el.innerHTML = visible.map(x => {
          const active = x.status === 'active';
          const p = Math.max(
            0,
            Math.min(
              100,
              Number(x.persentase || 0)
            )
          );

          return `
            <div class="poli-item">
              <div class="poli-top">
                <div>
                  <div class="poli-name">
                    ${esc(x.poli_name || '-')}
                  </div>
                  <div class="poli-doctor">
                    ${esc(x.doctor_name || '-')}
                  </div>
                </div>

                <span class="badge ${active ? 'bg-success' : 'bg-secondary'}">
                  ${esc(x.status_label || '-')}
                </span>
              </div>

              <div class="poli-bottom">
                <div class="poli-progress">
                  <span style="width:${p}%"></span>
                </div>

                <div class="poli-total">
                  ${num(x.total_pasien)} pasien
                </div>
              </div>
            </div>
          `;
        }).join('');

        if (a.length > 5) {
          el.innerHTML += `
            <div class="text-muted text-center small py-2">
              Menampilkan 5 poli pertama dari ${num(a.length)} poli.
              Scroll untuk melihat lainnya.
            </div>
          `;
        }
      }

      function renderTypes(d) {
        const el = document.getElementById('admisiPatientTypeList');
        if (!el) return;
        const a = d.items || [],
          icons = {
            bpjs: ['solar:card-bold', 'background:#eeecff;color:#635bff;'],
            umum: ['solar:wallet-money-bold', 'background:#e8f4ff;color:#1687d9;'],
            asuransi: ['solar:shield-check-bold', 'background:#e8f8ef;color:#16965a;']
          };
        if (!a.length) {
          el.innerHTML = '<div class="text-muted small py-3 text-center">Belum ada data jenis pasien.</div>';
          return;
        }
        el.innerHTML = a.map(x => {
          const c = icons[x.key] || icons.umum;
          return `<div class="patient-type-box"><div class="patient-type-icon" style="${c[1]}"><iconify-icon icon="${c[0]}"></iconify-icon></div><div class="patient-type-info"><div class="patient-type-name">${esc(x.label||'-')}</div><div class="patient-type-percent">${num(x.persentase)}% dari total</div></div><div class="patient-type-count">${num(x.total)}</div></div>`;
        }).join('');
      }

      function renderChart(a) {
        const c = document.getElementById('admisiVisitChart');
        if (!c || typeof Chart === 'undefined') return;
        if (chart) chart.destroy();
        chart = new Chart(c, {
          type: 'line',
          data: {
            labels: (a || []).map(x => x.label || x.date || ''),
            datasets: [{
              label: 'Pasien',
              data: (a || []).map(x => Number(x.total || 0)),
              borderColor: '#635bff',
              backgroundColor: 'rgba(99,91,255,.08)',
              fill: true,
              tension: .4,
              borderWidth: 3,
              pointRadius: 3,
              pointHoverRadius: 5
            }]
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
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              },
              x: {
                grid: {
                  display: false
                }
              }
            }
          }
        });
      }
      async function loadDashboard(toast) {
        const s = startDate.value,
          e = endDate.value;
        if (!s || !e) return;
        try {
          const r = await fetch(endpoint + '&period=' + encodeURIComponent(period.value) + '&start=' + encodeURIComponent(s) + '&end=' + encodeURIComponent(e) + '&_=' + Date.now(), {
            credentials: 'same-origin',
            cache: 'no-store'
          });
          const text = await r.text();
          let j;
          try {
            j = JSON.parse(text);
          } catch (err) {
            throw new Error(text || 'Response controller bukan JSON');
          }
          if (!j.status) throw new Error(j.message || 'Gagal mengambil data dashboard.');
          const k = j.kpi || {},
            d = j.doctors || {},
            q = j.queue || {},
            p = j.poli || {};
          setText('admisiTotalPasien', num(k.total_pasien));
          setText('admisiPasienMenunggu', num(k.pasien_menunggu));
          setText('admisiSedangDiperiksa', num(k.sedang_diperiksa));
          setText('admisiPelayananSelesai', num(k.pelayanan_selesai));
          setText('admisiPersentaseSelesai', num(k.persentase_selesai) + '% dari total pasien');
          setText('admisiPoliAktif', num((p.items || []).filter(x => x.status === 'active').length) + ' poli aktif');
          setText('admisiMenungguInfo', Number(k.pasien_menunggu || 0) > 0 ? 'Perlu segera dilayani' : 'Tidak ada antrean menunggu');
          const g = k.pertumbuhan;
          setText('admisiPertumbuhan', g === null || g === undefined ? '— dari periode sebelumnya' : (Number(g) >= 0 ? '↑ ' : '↓ ') + Math.abs(Number(g)).toLocaleString('id-ID') + '% dari periode sebelumnya');
          const ge = document.getElementById('admisiPertumbuhan');
          if (ge) ge.className = 'kpi-info ' + (g === null || g >= 0 ? 'up' : 'danger');
          setText('admisiDoctorHadir', num(d.hadir) + ' Hadir');
          setText('admisiQueueWaiting', num(q.waiting) + ' Menunggu');
          setText('admisiQueueProcessing', num(q.processing) + ' Diproses');
          renderDoctors(d);
          renderQueue(q);
          renderDoctorSummary(j.doctor_summary || {});
          renderPoli(p);
          renderTypes(j.patient_types || {});
          renderChart(j.visit_chart || []);
          const u = document.getElementById('admisiLastUpdate');
          if (u) {
            const now = new Date();
            u.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
          }
          if (toast && typeof Swal !== 'undefined') Swal.fire({
            icon: 'success',
            title: 'Periode diterapkan',
            text: 'Data pelayanan ' + s + ' sampai ' + e,
            timer: 1000,
            showConfirmButton: false
          });
        } catch (err) {
          console.error('Dashboard Admisi:', err);
          if (typeof Swal !== 'undefined') Swal.fire({
            icon: 'error',
            title: 'Gagal Memuat Dashboard',
            text: err.message || 'Terjadi kesalahan saat mengambil data.'
          });
        }
      }
      period.addEventListener('change', updatePeriod);
      applyButton.addEventListener('click', () => loadDashboard(true));
      updatePeriod();
      loadDashboard(false);
      setInterval(() => loadDashboard(false), 60000);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAdmisiDashboard);
    else initAdmisiDashboard();
  })();
</script>