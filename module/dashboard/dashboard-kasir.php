<style>
  .kasir-dashboard {
    --kas-primary: #635bff;
    --kas-primary-soft: #eeecff;
    --kas-text: #273444;
    --kas-muted: #7b8494;
    --kas-border: #edf0f5;
    --kas-green: #16a34a;
    --kas-green-soft: #eaf8ef;
    --kas-red: #dc2626;
    --kas-red-soft: #fff0f0;
    --kas-orange: #d97706;
    --kas-orange-soft: #fff7e8;
    --kas-blue: #1687d9;
    --kas-blue-soft: #edf7ff;
    --kas-bg: #ffffff;
  }

  .kasir-dashboard {
    color: var(--kas-text);
  }

  /* =========================
   FILTER
========================= */
  .kasir-filter-card {
    background: var(--kas-bg);
    border: 1px solid var(--kas-border);
    border-radius: 18px;
    padding: 16px 18px;
    margin-bottom: 16px;
  }

  .kasir-filter-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--kas-muted);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .3px;
  }

  .kasir-filter-card .form-control,
  .kasir-filter-card .form-select {
    border: 1px solid var(--kas-border);
    border-radius: 11px;
    min-height: 40px;
    font-size: 13px;
    box-shadow: none;
  }

  .kasir-filter-card .form-control:focus,
  .kasir-filter-card .form-select:focus {
    border-color: var(--kas-primary);
    box-shadow: 0 0 0 3px rgba(99, 91, 255, .08);
  }

  .kasir-filter-btn {
    min-height: 40px;
    border-radius: 11px;
    border: none;
    background: var(--kas-primary);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 0 17px;
  }

  .kasir-filter-btn:hover {
    background: #5149e8;
    color: #fff;
  }

  /* =========================
   SERVICE STATUS
========================= */
  .kasir-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    background: var(--kas-green-soft);
    color: var(--kas-green);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
  }

  .kasir-status-dot {
    width: 7px;
    height: 7px;
    background: var(--kas-green);
    border-radius: 50%;
  }

  /* =========================
   KPI
========================= */
  .kasir-kpi {
    background: #fff;
    border: 1px solid var(--kas-border);
    border-radius: 18px;
    padding: 18px;
    height: 100%;
    transition: .2s ease;
  }

  .kasir-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(39, 52, 68, .06);
  }

  .kasir-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
  }

  .kasir-kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--kas-primary-soft);
    color: var(--kas-primary);
  }

  .kasir-kpi-icon.green {
    background: var(--kas-green-soft);
    color: var(--kas-green);
  }

  .kasir-kpi-icon.orange {
    background: var(--kas-orange-soft);
    color: var(--kas-orange);
  }

  .kasir-kpi-icon.blue {
    background: var(--kas-blue-soft);
    color: var(--kas-blue);
  }

  .kasir-kpi-label {
    font-size: 11px;
    color: var(--kas-muted);
    margin-bottom: 4px;
  }

  .kasir-kpi-value {
    font-size: 24px;
    font-weight: 800;
    line-height: 1.15;
    color: var(--kas-text);
  }

  .kasir-kpi-sub {
    font-size: 10px;
    color: var(--kas-muted);
    margin-top: 5px;
  }

  .kasir-kpi-sub strong {
    color: var(--kas-green);
  }

  /* =========================
   CARD
========================= */
  .kasir-card {
    background: #fff;
    border: 1px solid var(--kas-border);
    border-radius: 18px;
    padding: 19px;
    height: 100%;
  }

  .kasir-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 17px;
  }

  .kasir-card-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--kas-text);
    margin: 0;
  }

  .kasir-card-subtitle {
    font-size: 10px;
    color: var(--kas-muted);
    margin-top: 3px;
  }

  .kasir-view-all {
    font-size: 11px;
    color: var(--kas-primary);
    text-decoration: none;
    font-weight: 700;
  }

  .kasir-view-all:hover {
    color: #5149e8;
  }

  /* =========================
   TRANSACTION
========================= */
  .kasir-transaction-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f2f5;
  }

  .kasir-transaction-item:last-child {
    border-bottom: none;
  }

  .kasir-transaction-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
  }

  .kasir-transaction-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--kas-primary-soft);
    color: var(--kas-primary);
  }

  .kasir-transaction-icon.green {
    background: var(--kas-green-soft);
    color: var(--kas-green);
  }

  .kasir-transaction-icon.orange {
    background: var(--kas-orange-soft);
    color: var(--kas-orange);
  }

  .kasir-transaction-icon.blue {
    background: var(--kas-blue-soft);
    color: var(--kas-blue);
  }

  .kasir-transaction-info {
    min-width: 0;
  }

  .kasir-transaction-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--kas-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .kasir-transaction-meta {
    font-size: 10px;
    color: var(--kas-muted);
    margin-top: 2px;
  }

  .kasir-transaction-right {
    text-align: right;
    flex-shrink: 0;
  }

  .kasir-transaction-price {
    font-size: 12px;
    font-weight: 800;
    color: var(--kas-text);
  }

  .kasir-transaction-status {
    font-size: 9px;
    font-weight: 700;
    margin-top: 3px;
  }

  .status-paid {
    color: var(--kas-green);
  }

  .status-pending {
    color: var(--kas-orange);
  }

  .status-cancel {
    color: var(--kas-red);
  }

  /* =========================
   QUEUE
========================= */
  .kasir-queue {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--kas-border);
    border-radius: 13px;
    margin-bottom: 10px;
  }

  .kasir-queue:last-child {
    margin-bottom: 0;
  }

  .kasir-queue-number {
    width: 38px;
    height: 38px;
    background: var(--kas-primary-soft);
    color: var(--kas-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    flex-shrink: 0;
  }

  .kasir-queue-info {
    flex: 1;
    min-width: 0;
  }

  .kasir-queue-name {
    font-size: 12px;
    font-weight: 700;
  }

  .kasir-queue-meta {
    font-size: 10px;
    color: var(--kas-muted);
    margin-top: 2px;
  }

  .kasir-queue-action {
    font-size: 10px;
    font-weight: 700;
    padding: 5px 9px;
    border-radius: 8px;
    background: var(--kas-orange-soft);
    color: var(--kas-orange);
  }

  /* =========================
   PAYMENT METHOD
========================= */
  .kasir-payment-row {
    margin-bottom: 15px;
  }

  .kasir-payment-row:last-child {
    margin-bottom: 0;
  }

  .kasir-payment-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
  }

  .kasir-payment-name {
    font-size: 11px;
    font-weight: 700;
  }

  .kasir-payment-value {
    font-size: 11px;
    font-weight: 800;
  }

  .kasir-progress {
    height: 7px;
    border-radius: 99px;
    background: #f0f1f5;
    overflow: hidden;
  }

  .kasir-progress-bar {
    height: 100%;
    border-radius: 99px;
    background: var(--kas-primary);
  }

  .kasir-progress-bar.green {
    background: var(--kas-green);
  }

  .kasir-progress-bar.orange {
    background: var(--kas-orange);
  }

  .kasir-progress-bar.blue {
    background: var(--kas-blue);
  }

  /* =========================
   ALERT
========================= */
  .kasir-alert {
    display: flex;
    gap: 11px;
    padding: 12px;
    border-radius: 13px;
    margin-bottom: 10px;
  }

  .kasir-alert:last-child {
    margin-bottom: 0;
  }

  .kasir-alert-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .kasir-alert-content {
    flex: 1;
  }

  .kasir-alert-title {
    font-size: 11px;
    font-weight: 800;
    margin-bottom: 2px;
  }

  .kasir-alert-text {
    font-size: 10px;
    line-height: 1.45;
  }

  .kasir-alert.warning {
    background: var(--kas-orange-soft);
  }

  .kasir-alert.warning .kasir-alert-icon {
    background: #ffeac2;
    color: var(--kas-orange);
  }

  .kasir-alert.warning .kasir-alert-text {
    color: #8a5a0a;
  }

  .kasir-alert.danger {
    background: var(--kas-red-soft);
  }

  .kasir-alert.danger .kasir-alert-icon {
    background: #ffdada;
    color: var(--kas-red);
  }

  .kasir-alert.danger .kasir-alert-text {
    color: #9f2424;
  }

  .kasir-alert.info {
    background: var(--kas-blue-soft);
  }

  .kasir-alert.info .kasir-alert-icon {
    background: #d9efff;
    color: var(--kas-blue);
  }

  .kasir-alert.info .kasir-alert-text {
    color: #17648f;
  }

  /* =========================
   QUICK ACCESS
========================= */
  .kasir-quick {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 12px;
    border: 1px solid var(--kas-border);
    border-radius: 13px;
    text-decoration: none;
    color: var(--kas-text);
    height: 100%;
    transition: .2s ease;
  }

  .kasir-quick:hover {
    border-color: #dcd9ff;
    background: #faf9ff;
    color: var(--kas-primary);
    transform: translateY(-2px);
  }

  .kasir-quick-icon {
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: var(--kas-primary-soft);
    color: var(--kas-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .kasir-quick-title {
    font-size: 11px;
    font-weight: 800;
  }

  .kasir-quick-desc {
    font-size: 9px;
    color: var(--kas-muted);
    margin-top: 2px;
  }

  /* =========================
   SUMMARY BOX
========================= */
  .kasir-summary {
    padding: 15px;
    border-radius: 14px;
    background: #fafbfc;
    border: 1px solid var(--kas-border);
    margin-bottom: 10px;
  }

  .kasir-summary:last-child {
    margin-bottom: 0;
  }

  .kasir-summary-label {
    font-size: 10px;
    color: var(--kas-muted);
  }

  .kasir-summary-value {
    font-size: 17px;
    font-weight: 800;
    margin-top: 3px;
  }

  /* =========================
   RESPONSIVE
========================= */
  @media (max-width: 767.98px) {
    .kasir-filter-card {
      padding: 14px;
    }

    .kasir-kpi-value {
      font-size: 21px;
    }

    .kasir-card {
      padding: 15px;
    }

    .kasir-filter-btn {
      width: 100%;
    }
  }
</style>

<div class="kasir-dashboard">

  <!-- =========================
         FILTER PERIODE
    ========================== -->
  <div class="kasir-filter-card">
    <div class="row g-2 align-items-end">

      <div class="col-xl-3 col-md-6">
        <div class="kasir-filter-label">Periode</div>
        <select class="form-select" id="kasirPeriode">
          <option value="today">Hari Ini</option>
          <option value="week">Minggu Ini</option>
          <option value="month">Bulan Ini</option>
          <option value="custom">Custom</option>
        </select>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="kasir-filter-label">Tanggal Mulai</div>
        <input type="date" class="form-control" id="kasirTanggalMulai"
          value="<?= date('Y-m-d') ?>">
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="kasir-filter-label">Tanggal Selesai</div>
        <input type="date" class="form-control" id="kasirTanggalSelesai"
          value="<?= date('Y-m-d') ?>">
      </div>

      <div class="col-xl-3 col-md-6">
        <button type="button" class="kasir-filter-btn w-100" id="btnFilterKasir">
          <iconify-icon icon="solar:filter-bold" width="16"></iconify-icon>
          Terapkan Filter
        </button>
      </div>

    </div>
  </div>

  <!-- =========================
         HEADER STATUS
    ========================== -->
  <div class="d-flex justify-content-between align-items-center mb-3">

    <div>
      <div style="font-size:18px;font-weight:800;">
        Dashboard Kasir
      </div>
      <div style="font-size:11px;color:var(--kas-muted);">
        Monitoring transaksi dan pelayanan pembayaran pasien
      </div>
    </div>

    <div class="kasir-status">
      <span class="kasir-status-dot"></span>
      Kasir Aktif
    </div>

  </div>

  <!-- =========================
         KPI
    ========================== -->
  <div class="row g-3 mb-3">

    <!-- Total Transaksi -->
    <div class="col-xl-3 col-md-6">
      <div class="kasir-kpi">

        <div class="kasir-kpi-top">
          <div class="kasir-kpi-icon">
            <iconify-icon icon="solar:bill-list-bold" width="24"></iconify-icon>
          </div>

          <iconify-icon
            icon="solar:arrow-right-up-linear"
            width="17"
            style="color:var(--kas-green);">
          </iconify-icon>
        </div>

        <div class="kasir-kpi-label">
          Total Transaksi
        </div>

        <div class="kasir-kpi-value">
          128
        </div>

        <div class="kasir-kpi-sub">
          <strong>+12,5%</strong> dibanding periode sebelumnya
        </div>

      </div>
    </div>

    <!-- Menunggu Pembayaran -->
    <div class="col-xl-3 col-md-6">
      <div class="kasir-kpi">

        <div class="kasir-kpi-top">
          <div class="kasir-kpi-icon orange">
            <iconify-icon icon="solar:clock-circle-bold" width="24"></iconify-icon>
          </div>
        </div>

        <div class="kasir-kpi-label">
          Menunggu Pembayaran
        </div>

        <div class="kasir-kpi-value">
          14
        </div>

        <div class="kasir-kpi-sub">
          Pasien dalam antrean kasir
        </div>

      </div>
    </div>

    <!-- Sudah Dibayar -->
    <div class="col-xl-3 col-md-6">
      <div class="kasir-kpi">

        <div class="kasir-kpi-top">
          <div class="kasir-kpi-icon green">
            <iconify-icon icon="solar:check-circle-bold" width="24"></iconify-icon>
          </div>
        </div>

        <div class="kasir-kpi-label">
          Transaksi Lunas
        </div>

        <div class="kasir-kpi-value">
          107
        </div>

        <div class="kasir-kpi-sub">
          <strong>83,6%</strong> dari total transaksi
        </div>

      </div>
    </div>

    <!-- Pendapatan -->
    <div class="col-xl-3 col-md-6">
      <div class="kasir-kpi">

        <div class="kasir-kpi-top">
          <div class="kasir-kpi-icon blue">
            <iconify-icon icon="solar:wallet-money-bold" width="24"></iconify-icon>
          </div>
        </div>

        <div class="kasir-kpi-label">
          Pendapatan Hari Ini
        </div>

        <div class="kasir-kpi-value" style="font-size:21px;">
          Rp 18,7 Jt
        </div>

        <div class="kasir-kpi-sub">
          Target Rp 20 Jt
        </div>

      </div>
    </div>

  </div>

  <!-- =========================
         ROW UTAMA
    ========================== -->
  <div class="row g-3 mb-3">

    <!-- TRANSAKSI TERBARU -->
    <div class="col-xl-8">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Transaksi Terbaru
            </div>

            <div class="kasir-card-subtitle">
              Aktivitas pembayaran pasien hari ini
            </div>
          </div>

          <a href="#" class="kasir-view-all">
            Lihat Semua
          </a>

        </div>

        <!-- Transaction 1 -->
        <div class="kasir-transaction-item">

          <div class="kasir-transaction-left">

            <div class="kasir-transaction-icon green">
              <iconify-icon icon="solar:check-circle-bold" width="20"></iconify-icon>
            </div>

            <div class="kasir-transaction-info">

              <div class="kasir-transaction-name">
                INV-20260904-00128
              </div>

              <div class="kasir-transaction-meta">
                Ahmad Fauzan • Rawat Jalan • 08:12
              </div>

            </div>

          </div>

          <div class="kasir-transaction-right">

            <div class="kasir-transaction-price">
              Rp 325.000
            </div>

            <div class="kasir-transaction-status status-paid">
              LUNAS
            </div>

          </div>

        </div>

        <!-- Transaction 2 -->
        <div class="kasir-transaction-item">

          <div class="kasir-transaction-left">

            <div class="kasir-transaction-icon">
              <iconify-icon icon="solar:card-bold" width="20"></iconify-icon>
            </div>

            <div class="kasir-transaction-info">

              <div class="kasir-transaction-name">
                INV-20260904-00127
              </div>

              <div class="kasir-transaction-meta">
                Siti Rahma • Laboratorium • 08:05
              </div>

            </div>

          </div>

          <div class="kasir-transaction-right">

            <div class="kasir-transaction-price">
              Rp 475.000
            </div>

            <div class="kasir-transaction-status status-paid">
              LUNAS
            </div>

          </div>

        </div>

        <!-- Transaction 3 -->
        <div class="kasir-transaction-item">

          <div class="kasir-transaction-left">

            <div class="kasir-transaction-icon orange">
              <iconify-icon icon="solar:clock-circle-bold" width="20"></iconify-icon>
            </div>

            <div class="kasir-transaction-info">

              <div class="kasir-transaction-name">
                INV-20260904-00126
              </div>

              <div class="kasir-transaction-meta">
                Budi Santoso • Farmasi • 07:58
              </div>

            </div>

          </div>

          <div class="kasir-transaction-right">

            <div class="kasir-transaction-price">
              Rp 186.500
            </div>

            <div class="kasir-transaction-status status-pending">
              MENUNGGU
            </div>

          </div>

        </div>

        <!-- Transaction 4 -->
        <div class="kasir-transaction-item">

          <div class="kasir-transaction-left">

            <div class="kasir-transaction-icon blue">
              <iconify-icon icon="solar:wallet-money-bold" width="20"></iconify-icon>
            </div>

            <div class="kasir-transaction-info">

              <div class="kasir-transaction-name">
                INV-20260904-00125
              </div>

              <div class="kasir-transaction-meta">
                Nur Aisyah • Rawat Jalan • 07:45
              </div>

            </div>

          </div>

          <div class="kasir-transaction-right">

            <div class="kasir-transaction-price">
              Rp 650.000
            </div>

            <div class="kasir-transaction-status status-paid">
              LUNAS
            </div>

          </div>

        </div>

        <!-- Transaction 5 -->
        <div class="kasir-transaction-item">

          <div class="kasir-transaction-left">

            <div class="kasir-transaction-icon green">
              <iconify-icon icon="solar:banknote-2-bold" width="20"></iconify-icon>
            </div>

            <div class="kasir-transaction-info">

              <div class="kasir-transaction-name">
                INV-20260904-00124
              </div>

              <div class="kasir-transaction-meta">
                Dedi Irawan • IGD • 07:31
              </div>

            </div>

          </div>

          <div class="kasir-transaction-right">

            <div class="kasir-transaction-price">
              Rp 1.250.000
            </div>

            <div class="kasir-transaction-status status-paid">
              LUNAS
            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- ANTREAN PEMBAYARAN -->
    <div class="col-xl-4">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Antrean Pembayaran
            </div>

            <div class="kasir-card-subtitle">
              Pasien yang menunggu pembayaran
            </div>
          </div>

          <span style="
                        background:var(--kas-orange-soft);
                        color:var(--kas-orange);
                        padding:6px 9px;
                        border-radius:9px;
                        font-size:10px;
                        font-weight:800;">
            14 Pasien
          </span>

        </div>

        <div class="kasir-queue">

          <div class="kasir-queue-number">
            A-021
          </div>

          <div class="kasir-queue-info">
            <div class="kasir-queue-name">
              Andi Saputra
            </div>
            <div class="kasir-queue-meta">
              Rawat Jalan • Poli Umum
            </div>
          </div>

          <div class="kasir-queue-action">
            Proses
          </div>

        </div>

        <div class="kasir-queue">

          <div class="kasir-queue-number">
            A-022
          </div>

          <div class="kasir-queue-info">
            <div class="kasir-queue-name">
              Maria Ulfa
            </div>
            <div class="kasir-queue-meta">
              Farmasi • Resep
            </div>
          </div>

          <div class="kasir-queue-action">
            Proses
          </div>

        </div>

        <div class="kasir-queue">

          <div class="kasir-queue-number">
            A-023
          </div>

          <div class="kasir-queue-info">
            <div class="kasir-queue-name">
              Rudi Hartono
            </div>
            <div class="kasir-queue-meta">
              Laboratorium
            </div>
          </div>

          <div class="kasir-queue-action">
            Proses
          </div>

        </div>

        <div class="kasir-queue">

          <div class="kasir-queue-number">
            A-024
          </div>

          <div class="kasir-queue-info">
            <div class="kasir-queue-name">
              Dewi Lestari
            </div>
            <div class="kasir-queue-meta">
              Rawat Jalan • Poli Anak
            </div>
          </div>

          <div class="kasir-queue-action">
            Proses
          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- =========================
         ROW ANALYTICS
    ========================== -->
  <div class="row g-3 mb-3">

    <!-- CHART PENDAPATAN -->
    <div class="col-xl-8">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Pendapatan
            </div>

            <div class="kasir-card-subtitle">
              Total transaksi pembayaran per hari
            </div>
          </div>

          <span style="
                        font-size:12px;
                        font-weight:800;
                        color:var(--kas-primary);">
            Rp 18,7 Jt
          </span>

        </div>

        <div style="height:270px;">
          <canvas id="kasirRevenueChart"></canvas>
        </div>

      </div>

    </div>

    <!-- METODE PEMBAYARAN -->
    <div class="col-xl-4">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Metode Pembayaran
            </div>

            <div class="kasir-card-subtitle">
              Distribusi transaksi
            </div>
          </div>

        </div>

        <div class="kasir-payment-row">

          <div class="kasir-payment-top">
            <span class="kasir-payment-name">
              Tunai
            </span>
            <span class="kasir-payment-value">
              42%
            </span>
          </div>

          <div class="kasir-progress">
            <div class="kasir-progress-bar"
              style="width:42%;"></div>
          </div>

        </div>

        <div class="kasir-payment-row">

          <div class="kasir-payment-top">
            <span class="kasir-payment-name">
              Transfer / VA
            </span>
            <span class="kasir-payment-value">
              28%
            </span>
          </div>

          <div class="kasir-progress">
            <div class="kasir-progress-bar blue"
              style="width:28%;"></div>
          </div>

        </div>

        <div class="kasir-payment-row">

          <div class="kasir-payment-top">
            <span class="kasir-payment-name">
              Debit / EDC
            </span>
            <span class="kasir-payment-value">
              18%
            </span>
          </div>

          <div class="kasir-progress">
            <div class="kasir-progress-bar green"
              style="width:18%;"></div>
          </div>

        </div>

        <div class="kasir-payment-row">

          <div class="kasir-payment-top">
            <span class="kasir-payment-name">
              QRIS
            </span>
            <span class="kasir-payment-value">
              12%
            </span>
          </div>

          <div class="kasir-progress">
            <div class="kasir-progress-bar orange"
              style="width:12%;"></div>
          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- =========================
         RINGKASAN KEUANGAN + ALERT
    ========================== -->
  <div class="row g-3 mb-3">

    <!-- RINGKASAN -->
    <div class="col-xl-4">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Ringkasan Keuangan
            </div>

            <div class="kasir-card-subtitle">
              Rekap transaksi hari ini
            </div>
          </div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Total Tagihan
          </div>

          <div class="kasir-summary-value">
            Rp 21.450.000
          </div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Sudah Dibayar
          </div>

          <div class="kasir-summary-value"
            style="color:var(--kas-green);">
            Rp 18.725.000
          </div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Piutang / Belum Dibayar
          </div>

          <div class="kasir-summary-value"
            style="color:var(--kas-orange);">
            Rp 2.725.000
          </div>

        </div>

      </div>

    </div>

    <!-- ALERT -->
    <div class="col-xl-8">

      <div class="kasir-card">

        <div class="kasir-card-header">

          <div>
            <div class="kasir-card-title">
              Perlu Perhatian
            </div>

            <div class="kasir-card-subtitle">
              Informasi transaksi yang perlu ditindaklanjuti
            </div>
          </div>

        </div>

        <div class="kasir-alert warning">

          <div class="kasir-alert-icon">
            <iconify-icon
              icon="solar:clock-circle-bold"
              width="18">
            </iconify-icon>
          </div>

          <div class="kasir-alert-content">

            <div class="kasir-alert-title">
              14 Transaksi Menunggu Pembayaran
            </div>

            <div class="kasir-alert-text">
              Terdapat pasien yang masih berada dalam antrean kasir.
            </div>

          </div>

        </div>

        <div class="kasir-alert danger">

          <div class="kasir-alert-icon">
            <iconify-icon
              icon="solar:danger-triangle-bold"
              width="18">
            </iconify-icon>
          </div>

          <div class="kasir-alert-content">

            <div class="kasir-alert-title">
              3 Transaksi Gagal
            </div>

            <div class="kasir-alert-text">
              Periksa kembali transaksi pembayaran yang gagal atau belum terkonfirmasi.
            </div>

          </div>

        </div>

        <div class="kasir-alert info">

          <div class="kasir-alert-icon">
            <iconify-icon
              icon="solar:document-text-bold"
              width="18">
            </iconify-icon>
          </div>

          <div class="kasir-alert-content">

            <div class="kasir-alert-title">
              Rekap Kas Harian Belum Ditutup
            </div>

            <div class="kasir-alert-text">
              Silakan lakukan closing kas setelah seluruh transaksi selesai.
            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- =========================
         QUICK ACCESS
    ========================== -->
  <div class="kasir-card mb-3">

    <div class="kasir-card-header">

      <div>
        <div class="kasir-card-title">
          Akses Cepat
        </div>

        <div class="kasir-card-subtitle">
          Menu yang sering digunakan oleh kasir
        </div>
      </div>

    </div>

    <div class="row g-2">

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:bill-list-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Transaksi
            </div>

            <div class="kasir-quick-desc">
              Pembayaran
            </div>
          </div>

        </a>
      </div>

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:wallet-money-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Pembayaran
            </div>

            <div class="kasir-quick-desc">
              Tagihan pasien
            </div>
          </div>

        </a>
      </div>

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:receipt-text-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Invoice
            </div>

            <div class="kasir-quick-desc">
              Cetak invoice
            </div>
          </div>

        </a>
      </div>

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:history-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Riwayat
            </div>

            <div class="kasir-quick-desc">
              Transaksi
            </div>
          </div>

        </a>
      </div>

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:calculator-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Closing Kas
            </div>

            <div class="kasir-quick-desc">
              Rekap kas
            </div>
          </div>

        </a>
      </div>

      <div class="col-xl-2 col-md-4 col-6">
        <a href="#" class="kasir-quick">

          <div class="kasir-quick-icon">
            <iconify-icon
              icon="solar:chart-2-bold"
              width="20">
            </iconify-icon>
          </div>

          <div>
            <div class="kasir-quick-title">
              Laporan
            </div>

            <div class="kasir-quick-desc">
              Keuangan
            </div>
          </div>

        </a>
      </div>

    </div>

  </div>

</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {

    /* =========================
       FILTER PERIODE
    ========================== */

    const periode = document.getElementById("kasirPeriode");
    const tanggalMulai = document.getElementById("kasirTanggalMulai");
    const tanggalSelesai = document.getElementById("kasirTanggalSelesai");
    const btnFilter = document.getElementById("btnFilterKasir");

    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");

      return `${year}-${month}-${day}`;
    }

    periode.addEventListener("change", function() {

      const today = new Date();

      if (this.value === "today") {

        const date = formatDate(today);

        tanggalMulai.value = date;
        tanggalSelesai.value = date;

      } else if (this.value === "week") {

        const start = new Date(today);

        start.setDate(
          today.getDate() - today.getDay() + 1
        );

        tanggalMulai.value = formatDate(start);
        tanggalSelesai.value = formatDate(today);

      } else if (this.value === "month") {

        const start = new Date(
          today.getFullYear(),
          today.getMonth(),
          1
        );

        tanggalMulai.value = formatDate(start);
        tanggalSelesai.value = formatDate(today);

      }

    });

    btnFilter.addEventListener("click", function() {

      const mulai = tanggalMulai.value;
      const selesai = tanggalSelesai.value;

      if (!mulai || !selesai) {

        if (typeof Swal !== "undefined") {

          Swal.fire({
            icon: "warning",
            title: "Periode Belum Lengkap",
            text: "Silakan pilih tanggal mulai dan tanggal selesai."
          });

        } else {

          alert("Silakan pilih periode terlebih dahulu.");

        }

        return;
      }

      if (mulai > selesai) {

        if (typeof Swal !== "undefined") {

          Swal.fire({
            icon: "warning",
            title: "Periode Tidak Valid",
            text: "Tanggal mulai tidak boleh lebih besar dari tanggal selesai."
          });

        } else {

          alert("Tanggal mulai tidak boleh lebih besar dari tanggal selesai.");

        }

        return;
      }

      console.log(
        "Filter kasir:",
        mulai,
        "sampai",
        selesai
      );

    });


    /* =========================
       REVENUE CHART
    ========================== */

    const revenueCanvas =
      document.getElementById("kasirRevenueChart");

    if (revenueCanvas && typeof Chart !== "undefined") {

      new Chart(revenueCanvas, {

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

          datasets: [{
            label: "Pendapatan",

            data: [
              14200000,
              16800000,
              15400000,
              19200000,
              17700000,
              18700000,
              18725000
            ],

            borderColor: "#635bff",

            backgroundColor: "rgba(99,91,255,.08)",

            borderWidth: 2,

            fill: true,

            tension: .4,

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
            },

            tooltip: {

              callbacks: {

                label: function(context) {

                  return "Rp " +
                    new Intl.NumberFormat(
                      "id-ID"
                    ).format(context.raw);

                }

              }

            }

          },

          scales: {

            y: {

              beginAtZero: true,

              grid: {
                color: "#f1f2f5"
              },

              ticks: {

                font: {
                  size: 9
                },

                callback: function(value) {

                  if (value >= 1000000) {
                    return (
                      value / 1000000
                    ) + " Jt";
                  }

                  return value;
                }

              }

            },

            x: {

              grid: {
                display: false
              },

              ticks: {
                font: {
                  size: 9
                }
              }

            }

          }

        }

      });

    }

  });
</script>