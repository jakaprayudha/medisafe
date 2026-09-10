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

        <div class="kasir-kpi-value" id="kasirTotalTransaksi">0</div>
        <div class="kasir-kpi-sub" id="kasirTotalTransaksiSub">Transaksi pada periode terpilih</div>

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

        <div class="kasir-kpi-value" id="kasirMenungguPembayaran">0</div>

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

        <div class="kasir-kpi-value" id="kasirTransaksiLunas">0</div>
        <div class="kasir-kpi-sub" id="kasirLunasSub">0% dari total transaksi</div>

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

        <div class="kasir-kpi-value" id="kasirPendapatan" style="font-size:21px;">Rp 0</div>
        <div class="kasir-kpi-sub" id="kasirPendapatanSub">Pendapatan transaksi lunas</div>

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
              Aktivitas pembayaran pasien pada periode terpilih
            </div>
          </div>

          <a href="#" class="kasir-view-all">
            Lihat Semua
          </a>

        </div>
        <div id="kasirTransactionList">
          <div class="text-center py-4 text-muted">Memuat transaksi...</div>
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
          <span id="kasirQueueBadge" style="background:var(--kas-orange-soft);color:var(--kas-orange);padding:6px 9px;border-radius:9px;font-size:10px;font-weight:800;">0 Pasien</span>

        </div>

        <div id="kasirQueueList">
          <div class="text-center py-4 text-muted">Memuat antrean...</div>
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

          <span id="kasirRevenueTotal" style="font-size:12px;font-weight:800;color:var(--kas-primary);">Rp 0</span>

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
        <div id="kasirPaymentMethods">
          <div class="text-center py-4 text-muted">Data metode pembayaran belum tersedia.</div>
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
              Rekap transaksi pada periode terpilih
            </div>
          </div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Total Tagihan
          </div>

          <div class="kasir-summary-value" id="kasirTotalTagihan">Rp 0</div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Sudah Dibayar
          </div>

          <div class="kasir-summary-value" id="kasirSudahDibayar" style="color:var(--kas-green);">Rp 0</div>

        </div>

        <div class="kasir-summary">

          <div class="kasir-summary-label">
            Piutang / Belum Dibayar
          </div>

          <div class="kasir-summary-value" id="kasirPiutang" style="color:var(--kas-orange);">Rp 0</div>

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
        <div id="kasirAlertList">
          <div class="text-center py-4 text-muted">Memuat informasi...</div>
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

    const endpoint = "controller/dashboard/kasirDashboardController.php?action=dashboard";

    const periode = document.getElementById("kasirPeriode");
    const tanggalMulai = document.getElementById("kasirTanggalMulai");
    const tanggalSelesai = document.getElementById("kasirTanggalSelesai");
    const btnFilter = document.getElementById("btnFilterKasir");

    let revenueChart = null;

    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    }

    function formatRupiah(value) {
      value = Number(value || 0);
      return "Rp " + new Intl.NumberFormat("id-ID").format(value);
    }

    function escapeHtml(value) {
      return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    function setLoading() {
      document.getElementById("kasirTransactionList").innerHTML =
        '<div class="text-center py-4 text-muted">Memuat transaksi...</div>';

      document.getElementById("kasirQueueList").innerHTML =
        '<div class="text-center py-4 text-muted">Memuat antrean...</div>';

      document.getElementById("kasirAlertList").innerHTML =
        '<div class="text-center py-4 text-muted">Memuat informasi...</div>';
    }

    function renderTransactions(data) {
      const el = document.getElementById("kasirTransactionList");
      const items = data?.transactions?.items || [];

      if (!items.length) {
        el.innerHTML = '<div class="text-center py-4 text-muted">Belum ada transaksi pada periode ini.</div>';
        return;
      }

      el.innerHTML = items.map((item, index) => {
        const paid = item.status === "paid";
        const iconClass = paid ? "green" : "orange";
        const icon = paid ? "solar:check-circle-bold" : "solar:clock-circle-bold";
        const statusClass = paid ? "status-paid" : "status-pending";

        return `
        <div class="kasir-transaction-item">
          <div class="kasir-transaction-left">
            <div class="kasir-transaction-icon ${iconClass}">
              <iconify-icon icon="${icon}" width="20"></iconify-icon>
            </div>
            <div class="kasir-transaction-info">
              <div class="kasir-transaction-name">
                ${escapeHtml(item.visit_ID || "-")}
              </div>
              <div class="kasir-transaction-meta">
                ${escapeHtml(item.patient_name_pcare || "-")} •
                ${escapeHtml(item.service || "Pelayanan")} •
                ${escapeHtml(item.jam || "-")}
              </div>
            </div>
          </div>
          <div class="kasir-transaction-right">
            <div class="kasir-transaction-price">
              ${formatRupiah(item.total)}
            </div>
            <div class="kasir-transaction-status ${statusClass}">
              ${escapeHtml(item.status_label || (paid ? "LUNAS" : "MENUNGGU"))}
            </div>
          </div>
        </div>
      `;
      }).join("");
    }

    function renderQueue(data) {
      const el = document.getElementById("kasirQueueList");
      const items = data?.queue?.items || [];
      const waiting = Number(data?.queue?.waiting || 0);

      document.getElementById("kasirQueueBadge").textContent = waiting + " Pasien";

      // Tampilkan maksimal 10 antrean sekaligus.
      // Antrean berikutnya tetap tersedia di dalam container dan dapat discroll.
      const visibleItems = items.slice(0, 10);

      if (!items.length) {
        el.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada antrean pembayaran.</div>';
        return;
      }

      // Tinggi container dibatasi agar dashboard tidak memanjang.
      el.style.maxHeight = "520px";
      el.style.overflowY = "auto";
      el.style.overflowX = "hidden";
      el.style.paddingRight = "4px";

      el.innerHTML = visibleItems.map(item => `
      <div class="kasir-queue">
        <div class="kasir-queue-number">
          ${escapeHtml(item.nomor_rm || "-")}
        </div>
        <div class="kasir-queue-info">
          <div class="kasir-queue-name">
            ${escapeHtml(item.nama || "-")}
          </div>
          <div class="kasir-queue-meta">
            ${escapeHtml(item.service || "Pelayanan")}
            ${item.poli && item.poli !== "-" ? " • " + escapeHtml(item.poli) : ""}
          </div>
        </div>
        <div class="kasir-queue-action">
          Proses
        </div>
      </div>
    `).join("");

      if (items.length > 10) {
        el.insertAdjacentHTML(
          "beforeend",
          '<div class="text-center py-2 text-muted" style="font-size:11px;">' +
          'Menampilkan 10 antrean pertama dari ' + items.length + '. Scroll untuk melihat antrean lainnya.' +
          '</div>'
        );
      }
    }

    function renderPaymentMethods(data) {
      const el = document.getElementById("kasirPaymentMethods");
      const available = data?.payment_methods?.available;
      const items = data?.payment_methods?.items || [];

      if (!available || !items.length) {
        el.innerHTML = `
        <div class="text-center py-3 text-muted" style="font-size:11px;">
          Metode pembayaran belum tersedia pada sumber data kasir.
        </div>
      `;
        return;
      }

      el.innerHTML = items.map((item, index) => `
      <div class="kasir-payment-row">
        <div class="kasir-payment-top">
          <span class="kasir-payment-name">${escapeHtml(item.label || "-")}</span>
          <span class="kasir-payment-value">${Number(item.persentase || 0)}%</span>
        </div>
        <div class="kasir-progress">
          <div class="kasir-progress-bar ${index === 1 ? "blue" : index === 2 ? "green" : index === 3 ? "orange" : ""}"
               style="width:${Math.min(100, Number(item.persentase || 0))}%;"></div>
        </div>
      </div>
    `).join("");
    }

    function renderAlerts(data) {
      const el = document.getElementById("kasirAlertList");
      const items = data?.alerts?.items || [];

      if (!items.length) {
        el.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada informasi.</div>';
        return;
      }

      el.innerHTML = items.map(item => `
      <div class="kasir-alert ${escapeHtml(item.type || "info")}">
        <div class="kasir-alert-icon">
          <iconify-icon icon="${escapeHtml(item.icon || "solar:info-circle-bold")}" width="18"></iconify-icon>
        </div>
        <div class="kasir-alert-content">
          <div class="kasir-alert-title">${escapeHtml(item.title || "-")}</div>
          <div class="kasir-alert-text">${escapeHtml(item.text || "")}</div>
        </div>
      </div>
    `).join("");
    }

    function renderChart(data) {
      const canvas = document.getElementById("kasirRevenueChart");
      if (!canvas || typeof Chart === "undefined") return;

      const items = data?.revenue_chart || [];

      const labels = items.map(item => item.label || item.date || "-");
      const values = items.map(item => Number(item.total || 0));

      if (revenueChart) {
        revenueChart.destroy();
      }

      revenueChart = new Chart(canvas, {
        type: "line",
        data: {
          labels: labels,
          datasets: [{
            label: "Pendapatan",
            data: values,
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
                  return formatRupiah(context.raw);
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
                  if (value >= 1000000) return (value / 1000000) + " Jt";
                  if (value >= 1000) return (value / 1000) + " Rb";
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

    function renderDashboard(data) {
      if (!data || data.status !== true) {
        throw new Error(data?.message || "Data dashboard tidak valid.");
      }

      const kpi = data.kpi || {};
      const summary = data.financial_summary || {};

      document.getElementById("kasirTotalTransaksi").textContent =
        Number(kpi.total_transaksi || 0).toLocaleString("id-ID");

      document.getElementById("kasirMenungguPembayaran").textContent =
        Number(kpi.menunggu_pembayaran || 0).toLocaleString("id-ID");

      document.getElementById("kasirTransaksiLunas").textContent =
        Number(kpi.transaksi_lunas || 0).toLocaleString("id-ID");

      document.getElementById("kasirLunasSub").innerHTML =
        "<strong>" + Number(kpi.persentase_lunas || 0) + "%</strong> dari total transaksi";

      document.getElementById("kasirPendapatan").textContent =
        formatRupiah(kpi.pendapatan || 0);

      document.getElementById("kasirPendapatanSub").textContent =
        "Pendapatan transaksi lunas";

      document.getElementById("kasirRevenueTotal").textContent =
        formatRupiah(kpi.pendapatan || 0);

      document.getElementById("kasirTotalTagihan").textContent =
        formatRupiah(summary.total_tagihan || 0);

      document.getElementById("kasirSudahDibayar").textContent =
        formatRupiah(summary.sudah_dibayar || 0);

      document.getElementById("kasirPiutang").textContent =
        formatRupiah(summary.piutang || 0);

      renderTransactions(data);
      renderQueue(data);
      renderPaymentMethods(data);
      renderAlerts(data);
      renderChart(data);
    }

    async function loadDashboard() {
      const mulai = tanggalMulai.value;
      const selesai = tanggalSelesai.value;

      if (!mulai || !selesai) {
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

      setLoading();
      btnFilter.disabled = true;

      try {
        const url =
          endpoint +
          "&periode=" + encodeURIComponent(periode.value) +
          "&tanggal_mulai=" + encodeURIComponent(mulai) +
          "&tanggal_selesai=" + encodeURIComponent(selesai);

        const response = await fetch(url, {
          method: "GET",
          headers: {
            "Accept": "application/json"
          },
          cache: "no-store"
        });

        const text = await response.text();

        let data;

        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Response bukan JSON:", text);
          throw new Error("Response controller bukan JSON yang valid.");
        }

        renderDashboard(data);

      } catch (error) {
        console.error("Dashboard kasir:", error);

        document.getElementById("kasirTransactionList").innerHTML =
          '<div class="text-center py-4 text-danger">Gagal mengambil data transaksi.</div>';

        document.getElementById("kasirQueueList").innerHTML =
          '<div class="text-center py-4 text-danger">Gagal mengambil data antrean.</div>';

        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "error",
            title: "Gagal Memuat Dashboard",
            text: error.message || "Terjadi kesalahan saat mengambil data."
          });
        }
      } finally {
        btnFilter.disabled = false;
      }
    }

    periode.addEventListener("change", function() {
      const today = new Date();

      if (this.value === "today") {
        const date = formatDate(today);
        tanggalMulai.value = date;
        tanggalSelesai.value = date;

      } else if (this.value === "week") {
        const start = new Date(today);
        const day = today.getDay();
        const diff = day === 0 ? -6 : 1 - day;

        start.setDate(today.getDate() + diff);

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

    btnFilter.addEventListener("click", loadDashboard);

    /*
     * Load pertama kali menggunakan Hari Ini.
     */
    loadDashboard();

  });
</script>