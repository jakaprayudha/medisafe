<style>
   .administrator-dashboard {
      --adm-primary: #635bff;
      --adm-primary-soft: #eeecff;
      --adm-text: #273444;
      --adm-muted: #7b8494;
      --adm-border: #edf0f5;

      --adm-green: #16a34a;
      --adm-green-soft: #eaf8ef;

      --adm-red: #dc2626;
      --adm-red-soft: #fff0f0;

      --adm-orange: #d97706;
      --adm-orange-soft: #fff7e8;

      --adm-blue: #1687d9;
      --adm-blue-soft: #edf7ff;
   }


   /* =========================================================
   BASE
========================================================= */

   .administrator-dashboard {
      color: var(--adm-text);
   }

   .administrator-dashboard * {
      box-sizing: border-box;
   }


   /* =========================================================
   FILTER
========================================================= */

   .adm-filter-card {
      background: #fff;
      border: 1px solid var(--adm-border);
      border-radius: 18px;
      padding: 16px 18px;
      margin-bottom: 16px;
   }

   .adm-filter-label {
      font-size: 10px;
      font-weight: 800;
      color: var(--adm-muted);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .35px;
   }

   .adm-filter-card .form-control,
   .adm-filter-card .form-select {
      min-height: 40px;
      border-radius: 11px;
      border: 1px solid var(--adm-border);
      font-size: 12px;
      box-shadow: none;
   }

   .adm-filter-card .form-control:focus,
   .adm-filter-card .form-select:focus {
      border-color: var(--adm-primary);
      box-shadow: 0 0 0 3px rgba(99, 91, 255, .08);
   }

   .adm-filter-btn {
      min-height: 40px;
      width: 100%;
      border: 0;
      border-radius: 11px;
      background: var(--adm-primary);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
   }

   .adm-filter-btn:hover {
      background: #5149e8;
      color: #fff;
   }


   /* =========================================================
   HEADER
========================================================= */

   .adm-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
   }

   .adm-title {
      font-size: 18px;
      font-weight: 800;
      line-height: 1.2;
   }

   .adm-subtitle {
      margin-top: 4px;
      font-size: 11px;
      color: var(--adm-muted);
   }

   .adm-system-status {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 7px 12px;
      border-radius: 999px;
      background: var(--adm-green-soft);
      color: var(--adm-green);
      font-size: 10px;
      font-weight: 800;
   }

   .adm-system-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--adm-green);
   }


   /* =========================================================
   KPI
========================================================= */

   .adm-kpi {
      height: 100%;
      background: #fff;
      border: 1px solid var(--adm-border);
      border-radius: 18px;
      padding: 18px;
      transition: .2s ease;
   }

   .adm-kpi:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(39, 52, 68, .06);
   }

   .adm-kpi-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
   }

   .adm-kpi-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--adm-primary-soft);
      color: var(--adm-primary);
   }

   .adm-kpi-icon.green {
      background: var(--adm-green-soft);
      color: var(--adm-green);
   }

   .adm-kpi-icon.blue {
      background: var(--adm-blue-soft);
      color: var(--adm-blue);
   }

   .adm-kpi-icon.orange {
      background: var(--adm-orange-soft);
      color: var(--adm-orange);
   }

   .adm-kpi-label {
      font-size: 10px;
      color: var(--adm-muted);
      margin-bottom: 4px;
   }

   .adm-kpi-value {
      font-size: 24px;
      font-weight: 800;
      line-height: 1.15;
   }

   .adm-kpi-sub {
      margin-top: 5px;
      font-size: 9px;
      color: var(--adm-muted);
   }


   /* =========================================================
   CARD
========================================================= */

   .adm-card {
      height: 100%;
      background: #fff;
      border: 1px solid var(--adm-border);
      border-radius: 18px;
      padding: 19px;
   }

   .adm-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 17px;
   }

   .adm-card-title {
      font-size: 14px;
      font-weight: 800;
   }

   .adm-card-subtitle {
      margin-top: 3px;
      font-size: 10px;
      color: var(--adm-muted);
   }

   .adm-link {
      color: var(--adm-primary);
      text-decoration: none;
      font-size: 10px;
      font-weight: 700;
   }


   /* =========================================================
   SYSTEM MONITOR
========================================================= */

   .adm-system-item {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 12px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .adm-system-item:last-child {
      border-bottom: 0;
   }

   .adm-system-icon {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--adm-primary-soft);
      color: var(--adm-primary);
      flex-shrink: 0;
   }

   .adm-system-icon.green {
      background: var(--adm-green-soft);
      color: var(--adm-green);
   }

   .adm-system-icon.orange {
      background: var(--adm-orange-soft);
      color: var(--adm-orange);
   }

   .adm-system-icon.red {
      background: var(--adm-red-soft);
      color: var(--adm-red);
   }

   .adm-system-info {
      flex: 1;
   }

   .adm-system-name {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-system-desc {
      margin-top: 2px;
      font-size: 9px;
      color: var(--adm-muted);
   }

   .adm-system-badge {
      padding: 5px 8px;
      border-radius: 8px;
      font-size: 8px;
      font-weight: 800;
   }

   .adm-online {
      background: var(--adm-green-soft);
      color: var(--adm-green);
   }

   .adm-warning {
      background: var(--adm-orange-soft);
      color: var(--adm-orange);
   }

   .adm-error {
      background: var(--adm-red-soft);
      color: var(--adm-red);
   }


   /* =========================================================
   FASKES
========================================================= */

   .adm-faskes {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 11px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .adm-faskes:last-child {
      border-bottom: 0;
   }

   .adm-faskes-avatar {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--adm-primary-soft);
      color: var(--adm-primary);
      flex-shrink: 0;
   }

   .adm-faskes-info {
      flex: 1;
   }

   .adm-faskes-name {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-faskes-meta {
      font-size: 9px;
      color: var(--adm-muted);
      margin-top: 2px;
   }

   .adm-faskes-status {
      font-size: 8px;
      font-weight: 800;
      padding: 5px 8px;
      border-radius: 8px;
   }


   /* =========================================================
   IMPORT
========================================================= */

   .adm-import-box {
      padding: 15px;
      border-radius: 14px;
      background: #fafbfc;
      border: 1px solid var(--adm-border);
      margin-bottom: 10px;
   }

   .adm-import-box:last-child {
      margin-bottom: 0;
   }

   .adm-import-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
   }

   .adm-import-title {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-import-value {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-import-desc {
      font-size: 9px;
      color: var(--adm-muted);
      margin-top: 3px;
   }

   .adm-progress {
      height: 7px;
      background: #f0f1f5;
      border-radius: 99px;
      overflow: hidden;
      margin-top: 9px;
   }

   .adm-progress-bar {
      height: 100%;
      border-radius: 99px;
      background: var(--adm-primary);
   }

   .adm-progress-bar.green {
      background: var(--adm-green);
   }

   .adm-progress-bar.orange {
      background: var(--adm-orange);
   }


   /* =========================================================
   INTEGRATION
========================================================= */

   .adm-integration {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px;
      border: 1px solid var(--adm-border);
      border-radius: 13px;
      margin-bottom: 9px;
   }

   .adm-integration:last-child {
      margin-bottom: 0;
   }

   .adm-integration-left {
      display: flex;
      align-items: center;
      gap: 10px;
   }

   .adm-integration-icon {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      background: var(--adm-blue-soft);
      color: var(--adm-blue);
      display: flex;
      align-items: center;
      justify-content: center;
   }

   .adm-integration-name {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-integration-desc {
      font-size: 9px;
      color: var(--adm-muted);
      margin-top: 2px;
   }

   .adm-integration-status {
      font-size: 8px;
      font-weight: 800;
      padding: 5px 8px;
      border-radius: 8px;
   }


   /* =========================================================
   ALERT
========================================================= */

   .adm-alert {
      display: flex;
      gap: 11px;
      padding: 12px;
      border-radius: 13px;
      margin-bottom: 10px;
   }

   .adm-alert:last-child {
      margin-bottom: 0;
   }

   .adm-alert-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .adm-alert-content {
      flex: 1;
   }

   .adm-alert-title {
      font-size: 11px;
      font-weight: 800;
   }

   .adm-alert-text {
      margin-top: 2px;
      font-size: 9px;
      line-height: 1.5;
   }

   .adm-alert.warning {
      background: var(--adm-orange-soft);
   }

   .adm-alert.warning .adm-alert-icon {
      background: #ffeac2;
      color: var(--adm-orange);
   }

   .adm-alert.warning .adm-alert-text {
      color: #8a5a0a;
   }

   .adm-alert.danger {
      background: var(--adm-red-soft);
   }

   .adm-alert.danger .adm-alert-icon {
      background: #ffdada;
      color: var(--adm-red);
   }

   .adm-alert.danger .adm-alert-text {
      color: #9f2424;
   }

   .adm-alert.info {
      background: var(--adm-blue-soft);
   }

   .adm-alert.info .adm-alert-icon {
      background: #d9efff;
      color: var(--adm-blue);
   }

   .adm-alert.info .adm-alert-text {
      color: #17648f;
   }


   /* =========================================================
   QUICK ACCESS
========================================================= */

   .adm-quick {
      height: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      border: 1px solid var(--adm-border);
      border-radius: 13px;
      text-decoration: none;
      color: var(--adm-text);
      transition: .2s ease;
   }

   .adm-quick:hover {
      color: var(--adm-primary);
      background: #faf9ff;
      border-color: #dcd9ff;
      transform: translateY(-2px);
   }

   .adm-quick-icon {
      width: 40px;
      height: 40px;
      border-radius: 11px;
      background: var(--adm-primary-soft);
      color: var(--adm-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .adm-quick-title {
      font-size: 10px;
      font-weight: 800;
   }

   .adm-quick-desc {
      font-size: 9px;
      color: var(--adm-muted);
      margin-top: 2px;
   }


   /* =========================================================
   RESPONSIVE
========================================================= */

   @media (max-width: 767.98px) {

      .adm-filter-card {
         padding: 14px;
      }

      .adm-card {
         padding: 15px;
      }

      .adm-kpi-value {
         font-size: 21px;
      }

      .adm-header {
         align-items: flex-start;
         gap: 10px;
      }

      .adm-system-status {
         white-space: nowrap;
      }
   }
</style>


<div class="administrator-dashboard">

   <!-- =====================================================
         FILTER PERIODE
   ====================================================== -->
   <div class="adm-filter-card">
      <div class="row g-2 align-items-end">

         <div class="col-xl-3 col-md-6">
            <div class="adm-filter-label">Periode Monitoring</div>
            <select class="form-select" id="admPeriode">
               <option value="today">Hari Ini</option>
               <option value="week">Minggu Ini</option>
               <option value="month">Bulan Ini</option>
               <option value="custom">Custom</option>
            </select>
         </div>

         <div class="col-xl-3 col-md-6">
            <div class="adm-filter-label">Tanggal Mulai</div>
            <input type="date" class="form-control" id="admTanggalMulai" value="<?= date('Y-m-d') ?>">
         </div>

         <div class="col-xl-3 col-md-6">
            <div class="adm-filter-label">Tanggal Selesai</div>
            <input type="date" class="form-control" id="admTanggalSelesai" value="<?= date('Y-m-d') ?>">
         </div>

         <div class="col-xl-3 col-md-6">
            <button type="button" class="adm-filter-btn" id="btnFilterAdministrator">
               <iconify-icon icon="solar:filter-bold" width="16"></iconify-icon>
               Terapkan Filter
            </button>
         </div>

      </div>
   </div>

   <!-- HEADER -->
   <div class="adm-header">
      <div>
         <div class="adm-title">Dashboard Administrator</div>
         <div class="adm-subtitle">
            Monitoring platform, fasilitas kesehatan, integrasi dan kualitas data
         </div>
      </div>

      <div class="adm-system-status" id="admSystemStatus">
         <span class="adm-system-dot"></span>
         <span id="admSystemStatusText">System Operational</span>
      </div>
   </div>

   <!-- KPI -->
   <div class="row g-3 mb-3">

      <div class="col-xl-3 col-md-6">
         <div class="adm-kpi">
            <div class="adm-kpi-top">
               <div class="adm-kpi-icon">
                  <iconify-icon icon="solar:hospital-bold" width="24"></iconify-icon>
               </div>
               <iconify-icon icon="solar:arrow-right-up-linear" width="17" style="color:var(--adm-green);"></iconify-icon>
            </div>
            <div class="adm-kpi-label">Total Faskes</div>
            <div class="adm-kpi-value" id="admTotalFaskes">0</div>
            <div class="adm-kpi-sub" id="admFaskesSub">0 aktif • 0 pending</div>
         </div>
      </div>

      <div class="col-xl-3 col-md-6">
         <div class="adm-kpi">
            <div class="adm-kpi-top">
               <div class="adm-kpi-icon blue">
                  <iconify-icon icon="solar:users-group-rounded-bold" width="24"></iconify-icon>
               </div>
            </div>
            <div class="adm-kpi-label">Data Pasien</div>
            <div class="adm-kpi-value" id="admTotalPasien">0</div>
            <div class="adm-kpi-sub">Terdata pada platform</div>
         </div>
      </div>

      <div class="col-xl-3 col-md-6">
         <div class="adm-kpi">
            <div class="adm-kpi-top">
               <div class="adm-kpi-icon green">
                  <iconify-icon icon="solar:user-id-bold" width="24"></iconify-icon>
               </div>
            </div>
            <div class="adm-kpi-label">IDSH Dokter</div>
            <div class="adm-kpi-value" id="admTotalDokter">0</div>
            <div class="adm-kpi-sub" id="admDokterSub">0 terverifikasi</div>
         </div>
      </div>

      <div class="col-xl-3 col-md-6">
         <div class="adm-kpi">
            <div class="adm-kpi-top">
               <div class="adm-kpi-icon orange">
                  <iconify-icon icon="solar:database-bold" width="24"></iconify-icon>
               </div>
            </div>
            <div class="adm-kpi-label">Data Import</div>
            <div class="adm-kpi-value" id="admImportKpi">-</div>
            <div class="adm-kpi-sub" id="admImportKpiSub">Sumber data import belum tersedia</div>
         </div>
      </div>

   </div>

   <!-- ROW 1 -->
   <div class="row g-3 mb-3">

      <!-- MONITORING SISTEM -->
      <div class="col-xl-7">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">Monitoring Sistem</div>
                  <div class="adm-card-subtitle">Status layanan utama platform</div>
               </div>
               <span id="admSystemUpdated" style="font-size:9px;color:var(--adm-muted);">
                  Updated -
               </span>
            </div>

            <div id="admSystemContainer">
               <div class="adm-system-item">
                  <div class="adm-system-info">
                     <div class="adm-system-name">Memuat monitoring sistem...</div>
                  </div>
               </div>
            </div>

         </div>
      </div>

      <!-- FASKES -->
      <div class="col-xl-5">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">Faskes Terbaru</div>
                  <div class="adm-card-subtitle">Aktivitas fasilitas kesehatan</div>
               </div>
               <a href="#" class="adm-link">Lihat Semua</a>
            </div>

            <div id="admFaskesContainer">
               <div class="adm-faskes">
                  <div class="adm-faskes-info">
                     <div class="adm-faskes-name">Memuat data...</div>
                     <div class="adm-faskes-meta">Mohon tunggu</div>
                  </div>
               </div>
            </div>

         </div>
      </div>

   </div>

   <!-- ROW 2 -->
   <div class="row g-3 mb-3">

      <!-- DATA IMPORT -->
      <div class="col-xl-6">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">Data Import</div>
                  <div class="adm-card-subtitle">Status proses import data</div>
               </div>
               <a href="#" class="adm-link">Kelola Import</a>
            </div>

            <div id="admImportContainer">
               <div class="adm-import-box">
                  <div class="adm-import-title">Data import belum tersedia</div>
                  <div class="adm-import-desc">
                     Controller belum menerima sumber tabel/log import.
                  </div>
               </div>
            </div>

         </div>
      </div>

      <!-- INTEGRASI -->
      <div class="col-xl-6">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">Integrasi & Layanan</div>
                  <div class="adm-card-subtitle">Status koneksi layanan eksternal</div>
               </div>
            </div>

            <div id="admIntegrationContainer">
               <div class="adm-integration">
                  <div class="adm-integration-left">
                     <div class="adm-integration-icon">
                        <iconify-icon icon="solar:server-square-bold" width="19"></iconify-icon>
                     </div>
                     <div>
                        <div class="adm-integration-name">Memuat layanan...</div>
                        <div class="adm-integration-desc">Memuat status integrasi</div>
                     </div>
                  </div>
               </div>
            </div>

         </div>
      </div>

   </div>

   <!-- ROW 3 -->
   <div class="row g-3 mb-3">

      <!-- ALERT -->
      <div class="col-xl-7">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">Perlu Perhatian</div>
                  <div class="adm-card-subtitle">
                     Aktivitas administrator yang membutuhkan tindak lanjut
                  </div>
               </div>
            </div>

            <div id="admAlertContainer">
               <div class="adm-alert info">
                  <div class="adm-alert-icon">
                     <iconify-icon icon="solar:refresh-bold" width="18"></iconify-icon>
                  </div>
                  <div class="adm-alert-content">
                     <div class="adm-alert-title">Memuat data...</div>
                     <div class="adm-alert-text">Mohon tunggu.</div>
                  </div>
               </div>
            </div>

         </div>
      </div>

      <!-- IDSH -->
      <div class="col-xl-5">
         <div class="adm-card">

            <div class="adm-card-header">
               <div>
                  <div class="adm-card-title">IDSH</div>
                  <div class="adm-card-subtitle">
                     Status identitas tenaga medis dan pasien
                  </div>
               </div>
            </div>

            <div class="adm-import-box">
               <div class="adm-import-top">
                  <div class="adm-import-title">IDSH Dokter</div>
                  <div class="adm-import-value" id="admIDSHDokterPercent" style="color:var(--adm-green);">0%</div>
               </div>

               <div class="adm-import-desc" id="admIDSHDokterDesc">
                  0 dari 0 terverifikasi
               </div>

               <div class="adm-progress">
                  <div class="adm-progress-bar green" id="admIDSHDokterBar" style="width:0%;"></div>
               </div>
            </div>

            <div class="adm-import-box">
               <div class="adm-import-top">
                  <div class="adm-import-title">IDSH Pasien</div>
                  <div class="adm-import-value" id="admIDSHPasienPercent" style="color:var(--adm-blue);">0%</div>
               </div>

               <div class="adm-import-desc" id="admIDSHPasienDesc">
                  0 dari 0 terverifikasi
               </div>

               <div class="adm-progress">
                  <div class="adm-progress-bar" id="admIDSHPasienBar" style="width:0%;"></div>
               </div>
            </div>

         </div>
      </div>

   </div>

   <!-- QUICK ACCESS -->
   <div class="adm-card mb-3">

      <div class="adm-card-header">
         <div>
            <div class="adm-card-title">Akses Cepat Administrator</div>
            <div class="adm-card-subtitle">
               Menu utama untuk pengelolaan platform
            </div>
         </div>
      </div>

      <div class="row g-2">

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:hospital-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Data Faskes</div>
                  <div class="adm-quick-desc">Kelola faskes</div>
               </div>
            </a>
         </div>

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:cloud-upload-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Data Import</div>
                  <div class="adm-quick-desc">Import data</div>
               </div>
            </a>
         </div>

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:database-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Master Data</div>
                  <div class="adm-quick-desc">Data referensi</div>
               </div>
            </a>
         </div>

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:link-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Satu Sehat</div>
                  <div class="adm-quick-desc">Integrasi API</div>
               </div>
            </a>
         </div>

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:test-tube-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Laboratorium</div>
                  <div class="adm-quick-desc">Data laboratorium</div>
               </div>
            </a>
         </div>

         <div class="col-xl-2 col-md-4 col-6">
            <a href="#" class="adm-quick">
               <div class="adm-quick-icon">
                  <iconify-icon icon="solar:monitor-bold" width="20"></iconify-icon>
               </div>
               <div>
                  <div class="adm-quick-title">Monitoring RME</div>
                  <div class="adm-quick-desc">Kualitas data</div>
               </div>
            </a>
         </div>

      </div>
   </div>

</div>


<script>
   (function() {

      /*
       * =========================================================
       * ADMINISTRATOR DASHBOARD
       * GLOBAL / SELURUH FASKES
       * =========================================================
       */

      function initAdministratorDashboard() {

         const periode =
            document.getElementById("admPeriode");

         const tanggalMulai =
            document.getElementById("admTanggalMulai");

         const tanggalSelesai =
            document.getElementById("admTanggalSelesai");

         const btnFilter =
            document.getElementById("btnFilterAdministrator");

         if (!periode || !tanggalMulai || !tanggalSelesai || !btnFilter) {
            return;
         }


         function formatDate(date) {

            const year =
               date.getFullYear();

            const month =
               String(date.getMonth() + 1)
               .padStart(2, "0");

            const day =
               String(date.getDate())
               .padStart(2, "0");

            return `${year}-${month}-${day}`;
         }


         function formatNumber(value) {

            const number =
               Number(value || 0);

            return new Intl.NumberFormat("id-ID")
               .format(number);
         }


         function escapeHtml(value) {

            return String(value ?? "")
               .replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/"/g, "&quot;")
               .replace(/'/g, "&#039;");
         }


         function showError(message) {

            if (typeof Swal !== "undefined") {

               Swal.fire({
                  icon: "error",
                  title: "Dashboard Gagal Dimuat",
                  text: message
               });

            } else {

               console.error(message);
            }
         }


         function renderKPI(data) {

            const faskes =
               data.kpi?.faskes || {};

            const pasien =
               data.kpi?.pasien || {};

            const dokter =
               data.kpi?.dokter || {};

            const importData =
               data.kpi?.import || {};


            document.getElementById("admTotalFaskes").textContent =
               formatNumber(faskes.total);

            document.getElementById("admFaskesSub").textContent =
               `${formatNumber(faskes.aktif)} aktif • ${formatNumber(faskes.pending)} pending`;


            document.getElementById("admTotalPasien").textContent =
               formatNumber(pasien.total);


            document.getElementById("admTotalDokter").textContent =
               formatNumber(dokter.total);

            document.getElementById("admDokterSub").textContent =
               `${formatNumber(dokter.terverifikasi)} terverifikasi`;


            if (
               importData.available === true &&
               importData.persentase !== null
            ) {

               document.getElementById("admImportKpi").textContent =
                  `${Number(importData.persentase).toLocaleString("id-ID")}%`;

               document.getElementById("admImportKpiSub").textContent =
                  "Tingkat keberhasilan import";

            } else {

               document.getElementById("admImportKpi").textContent =
                  "-";

               document.getElementById("admImportKpiSub").textContent =
                  "Sumber data import belum tersedia";
            }
         }


         function renderFaskes(items) {

            const container =
               document.getElementById("admFaskesContainer");

            if (!container) {
               return;
            }


            if (!Array.isArray(items) || items.length === 0) {

               container.innerHTML = `
               <div class="adm-faskes">
                  <div class="adm-faskes-info">
                     <div class="adm-faskes-name">
                        Belum ada data faskes
                     </div>
                     <div class="adm-faskes-meta">
                        Tidak ada fasilitas kesehatan
                     </div>
                  </div>
               </div>
            `;

               return;
            }


            container.innerHTML =
               items.slice(0, 5).map(function(item) {

                  const statusClass =
                     item.status_class ||
                     (
                        Number(item.status) === 1 ?
                        "adm-online" :
                        "adm-warning"
                     );

                  const statusLabel =
                     item.status_label ||
                     (
                        Number(item.status) === 1 ?
                        "AKTIF" :
                        "PENDING"
                     );

                  const code =
                     item.faskes_code ||
                     `ID Faskes #${item.faskes_code}`;

                  const city =
                     item.faskes_city ||
                     "-";

                  const district =
                     item.faskes_district ||
                     "";

                  const location =
                     district ?
                     `${city} • ${district}` :
                     city;


                  return `
                  <div class="adm-faskes">

                     <div class="adm-faskes-avatar">
                        <iconify-icon
                           icon="solar:hospital-bold"
                           width="19">
                        </iconify-icon>
                     </div>

                     <div class="adm-faskes-info">

                        <div class="adm-faskes-name">
                           ${escapeHtml(code)}
                        </div>

                        <div class="adm-faskes-meta">
                           ${escapeHtml(location)}
                        </div>

                     </div>

                     <span class="adm-faskes-status ${escapeHtml(statusClass)}">
                        ${escapeHtml(statusLabel)}
                     </span>

                  </div>
               `;

               }).join("");
         }


         function renderSystem(items, serverTime) {

            const container =
               document.getElementById("admSystemContainer");

            if (!container) {
               return;
            }


            if (!Array.isArray(items) || items.length === 0) {

               container.innerHTML = `
               <div class="adm-system-item">
                  <div class="adm-system-info">
                     <div class="adm-system-name">
                        Monitoring sistem belum tersedia
                     </div>
                     <div class="adm-system-desc">
                        Belum ada data monitoring service.
                     </div>
                  </div>
               </div>
            `;

            } else {

               container.innerHTML =
                  items.map(function(item) {

                     let iconClass = "";

                     if (item.status_class === "adm-online") {
                        iconClass = "green";
                     } else if (item.status_class === "adm-error") {
                        iconClass = "red";
                     } else if (item.status_class === "adm-warning") {
                        iconClass = "orange";
                     }


                     return `
                     <div class="adm-system-item">

                        <div class="adm-system-icon ${iconClass}">
                           <iconify-icon
                              icon="${escapeHtml(item.icon || "solar:server-bold")}"
                              width="20">
                           </iconify-icon>
                        </div>

                        <div class="adm-system-info">

                           <div class="adm-system-name">
                              ${escapeHtml(item.name)}
                           </div>

                           <div class="adm-system-desc">
                              ${escapeHtml(item.description)}
                           </div>

                        </div>

                        <span class="adm-system-badge ${escapeHtml(item.status_class || "adm-warning")}">
                           ${escapeHtml(item.status_label || "UNKNOWN")}
                        </span>

                     </div>
                  `;

                  }).join("");
            }


            document.getElementById("admSystemUpdated").textContent =
               serverTime ?
               `Updated ${serverTime}` :
               "Updated -";
         }


         function renderImport(data) {

            const container =
               document.getElementById("admImportContainer");

            if (!container) {
               return;
            }


            if (
               !data ||
               data.available !== true ||
               !Array.isArray(data.items) ||
               data.items.length === 0
            ) {

               container.innerHTML = `
               <div class="adm-import-box">

                  <div class="adm-import-top">

                     <div class="adm-import-title">
                        Data Import
                     </div>

                     <div class="adm-import-value">
                        -
                     </div>

                  </div>

                  <div class="adm-import-desc">
                     ${escapeHtml(
                        data?.message ||
                        "Sumber data import belum tersedia."
                     )}
                  </div>

               </div>
            `;

               return;
            }


            container.innerHTML =
               data.items.map(function(item) {

                  const percent =
                     Math.max(
                        0,
                        Math.min(
                           100,
                           Number(item.persentase || 0)
                        )
                     );


                  const barClass =
                     percent >= 95 ?
                     "green" :
                     percent >= 90 ?
                     "" :
                     "orange";


                  return `
                  <div class="adm-import-box">

                     <div class="adm-import-top">

                        <div class="adm-import-title">
                           ${escapeHtml(item.name)}
                        </div>

                        <div class="adm-import-value">
                           ${percent.toLocaleString("id-ID")}%
                        </div>

                     </div>

                     <div class="adm-import-desc">
                        ${escapeHtml(item.description || "-")}
                     </div>

                     <div class="adm-progress">

                        <div
                           class="adm-progress-bar ${barClass}"
                           style="width:${percent}%;">
                        </div>

                     </div>

                  </div>
               `;

               }).join("");
         }


         function renderIntegration(items) {

            const container =
               document.getElementById("admIntegrationContainer");

            if (!container) {
               return;
            }


            if (!Array.isArray(items) || items.length === 0) {

               container.innerHTML = `
               <div class="adm-integration">
                  <div class="adm-integration-left">
                     <div class="adm-integration-icon">
                        <iconify-icon
                           icon="solar:server-square-bold"
                           width="19">
                        </iconify-icon>
                     </div>
                     <div>
                        <div class="adm-integration-name">
                           Tidak ada data layanan
                        </div>
                        <div class="adm-integration-desc">
                           Monitoring integrasi belum tersedia.
                        </div>
                     </div>
                  </div>
               </div>
            `;

               return;
            }


            container.innerHTML =
               items.map(function(item) {

                  return `
                  <div class="adm-integration">

                     <div class="adm-integration-left">

                        <div class="adm-integration-icon">

                           <iconify-icon
                              icon="${escapeHtml(
                                 item.icon ||
                                 "solar:server-square-bold"
                              )}"
                              width="19">
                           </iconify-icon>

                        </div>

                        <div>

                           <div class="adm-integration-name">
                              ${escapeHtml(item.name)}
                           </div>

                           <div class="adm-integration-desc">
                              ${escapeHtml(item.description)}
                           </div>

                        </div>

                     </div>

                     <span class="adm-integration-status ${escapeHtml(item.status_class || "adm-warning")}">
                        ${escapeHtml(item.status_label || "UNKNOWN")}
                     </span>

                  </div>
               `;

               }).join("");
         }


         function renderAlerts(items) {

            const container =
               document.getElementById("admAlertContainer");

            if (!container) {
               return;
            }


            if (!Array.isArray(items) || items.length === 0) {

               container.innerHTML = `
               <div class="adm-alert info">

                  <div class="adm-alert-icon">
                     <iconify-icon
                        icon="solar:check-circle-bold"
                        width="18">
                     </iconify-icon>
                  </div>

                  <div class="adm-alert-content">

                     <div class="adm-alert-title">
                        Tidak Ada Alert Kritis
                     </div>

                     <div class="adm-alert-text">
                        Tidak ditemukan aktivitas yang membutuhkan tindak lanjut segera.
                     </div>

                  </div>

               </div>
            `;

               return;
            }


            container.innerHTML =
               items.map(function(item) {

                  return `
                  <div class="adm-alert ${escapeHtml(item.type || "info")}">

                     <div class="adm-alert-icon">

                        <iconify-icon
                           icon="${escapeHtml(
                              item.icon ||
                              "solar:info-circle-bold"
                           )}"
                           width="18">
                        </iconify-icon>

                     </div>

                     <div class="adm-alert-content">

                        <div class="adm-alert-title">
                           ${escapeHtml(item.title)}
                        </div>

                        <div class="adm-alert-text">
                           ${escapeHtml(item.text)}
                        </div>

                     </div>

                  </div>
               `;

               }).join("");
         }


         function renderIDSH(idsh) {

            const dokter =
               idsh?.dokter || {};

            const pasien =
               idsh?.pasien || {};


            const dokterPercent =
               Number(dokter.persentase || 0);

            const pasienPercent =
               Number(pasien.persentase || 0);


            document.getElementById(
                  "admIDSHDokterPercent"
               ).textContent =
               `${dokterPercent.toLocaleString("id-ID")}%`;


            document.getElementById(
                  "admIDSHDokterDesc"
               ).textContent =
               `${formatNumber(dokter.terverifikasi)} dari ${formatNumber(dokter.total)} terverifikasi`;


            document.getElementById(
                  "admIDSHDokterBar"
               ).style.width =
               `${Math.min(100, Math.max(0, dokterPercent))}%`;


            document.getElementById(
                  "admIDSHPasienPercent"
               ).textContent =
               `${pasienPercent.toLocaleString("id-ID")}%`;


            document.getElementById(
                  "admIDSHPasienDesc"
               ).textContent =
               `${formatNumber(pasien.terverifikasi)} dari ${formatNumber(pasien.total)} terverifikasi`;


            document.getElementById(
                  "admIDSHPasienBar"
               ).style.width =
               `${Math.min(100, Math.max(0, pasienPercent))}%`;
         }


         function renderSystemStatus(items) {

            const status =
               document.getElementById("admSystemStatus");

            const label =
               document.getElementById("admSystemStatusText");


            const hasError =
               Array.isArray(items) &&
               items.some(function(item) {
                  return item.status_class === "adm-error";
               });


            const hasWarning =
               Array.isArray(items) &&
               items.some(function(item) {
                  return item.status_class === "adm-warning";
               });


            if (hasError) {

               status.style.background =
                  "var(--adm-red-soft)";

               status.style.color =
                  "var(--adm-red)";

               label.textContent =
                  "System Attention";

            } else if (hasWarning) {

               status.style.background =
                  "var(--adm-orange-soft)";

               status.style.color =
                  "var(--adm-orange)";

               label.textContent =
                  "System Monitoring";

            } else {

               status.style.background =
                  "var(--adm-green-soft)";

               status.style.color =
                  "var(--adm-green)";

               label.textContent =
                  "System Operational";
            }
         }


         async function loadDashboard() {

            const mulai =
               tanggalMulai.value;

            const selesai =
               tanggalSelesai.value;


            if (!mulai || !selesai) {

               showError(
                  "Tanggal mulai dan tanggal selesai wajib diisi."
               );

               return;
            }


            if (mulai > selesai) {

               showError(
                  "Tanggal mulai tidak boleh lebih besar dari tanggal selesai."
               );

               return;
            }


            btnFilter.disabled = true;

            const originalText =
               btnFilter.innerHTML;

            btnFilter.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1"></span>
            Memuat...
         `;


            try {

               const params =
                  new URLSearchParams({

                     action: "dashboard",

                     periode: periode.value,

                     tanggal_mulai: mulai,

                     tanggal_selesai: selesai

                  });


               const response =
                  await fetch(
                     "controller/dashboard/administratorDashboardController.php?" +
                     params.toString(), {
                        method: "GET",
                        cache: "no-store",
                        headers: {
                           "Accept": "application/json"
                        }
                     }
                  );


               const text =
                  await response.text();


               let data;

               try {

                  data =
                     JSON.parse(text);

               } catch (error) {

                  console.error(
                     "Response controller bukan JSON:",
                     text
                  );

                  throw new Error(
                     "Response controller bukan JSON."
                  );
               }


               if (!data.status) {

                  throw new Error(
                     data.message ||
                     "Gagal mengambil dashboard."
                  );
               }


               renderKPI(data);

               renderFaskes(
                  data.faskes || []
               );

               renderSystem(
                  data.system || [],
                  data.server_time || null
               );

               renderImport(
                  data.import || {}
               );

               renderIntegration(
                  data.integration || []
               );

               renderAlerts(
                  data.alerts || []
               );

               renderIDSH(
                  data.idsh || {}
               );

               renderSystemStatus(
                  data.system || []
               );


            } catch (error) {

               console.error(
                  "Administrator Dashboard:",
                  error
               );

               showError(
                  error.message ||
                  "Terjadi kesalahan saat memuat dashboard."
               );


            } finally {

               btnFilter.disabled = false;

               btnFilter.innerHTML =
                  originalText;
            }
         }


         periode.addEventListener(
            "change",
            function() {

               const today =
                  new Date();


               if (this.value === "today") {

                  const date =
                     formatDate(today);

                  tanggalMulai.value =
                     date;

                  tanggalSelesai.value =
                     date;


               } else if (this.value === "week") {

                  const start =
                     new Date(today);

                  const day =
                     start.getDay();

                  const diff =
                     day === 0 ?
                     6 :
                     day - 1;

                  start.setDate(
                     start.getDate() - diff
                  );


                  tanggalMulai.value =
                     formatDate(start);

                  tanggalSelesai.value =
                     formatDate(today);


               } else if (this.value === "month") {

                  const start =
                     new Date(
                        today.getFullYear(),
                        today.getMonth(),
                        1
                     );


                  tanggalMulai.value =
                     formatDate(start);

                  tanggalSelesai.value =
                     formatDate(today);
               }
            }
         );


         btnFilter.addEventListener(
            "click",
            loadDashboard
         );


         /*
          * Load pertama kali.
          */

         loadDashboard();
      }


      /*
       * Aman untuk halaman normal maupun
       * content yang dimuat melalui AJAX.
       */

      if (
         document.readyState === "loading"
      ) {

         document.addEventListener(
            "DOMContentLoaded",
            initAdministratorDashboard
         );

      } else {

         initAdministratorDashboard();
      }

   })();
</script>