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

            <div class="adm-filter-label">
               Periode Monitoring
            </div>

            <select
               class="form-select"
               id="admPeriode">

               <option value="today">
                  Hari Ini
               </option>

               <option value="week">
                  Minggu Ini
               </option>

               <option value="month">
                  Bulan Ini
               </option>

               <option value="custom">
                  Custom
               </option>

            </select>

         </div>


         <div class="col-xl-3 col-md-6">

            <div class="adm-filter-label">
               Tanggal Mulai
            </div>

            <input
               type="date"
               class="form-control"
               id="admTanggalMulai"
               value="<?= date('Y-m-d') ?>">

         </div>


         <div class="col-xl-3 col-md-6">

            <div class="adm-filter-label">
               Tanggal Selesai
            </div>

            <input
               type="date"
               class="form-control"
               id="admTanggalSelesai"
               value="<?= date('Y-m-d') ?>">

         </div>


         <div class="col-xl-3 col-md-6">

            <button
               type="button"
               class="adm-filter-btn"
               id="btnFilterAdministrator">

               <iconify-icon
                  icon="solar:filter-bold"
                  width="16">
               </iconify-icon>

               Terapkan Filter

            </button>

         </div>

      </div>

   </div>


   <!-- =====================================================
         HEADER
    ====================================================== -->

   <div class="adm-header">

      <div>

         <div class="adm-title">
            Dashboard Administrator
         </div>

         <div class="adm-subtitle">
            Monitoring platform, fasilitas kesehatan, integrasi dan kualitas data
         </div>

      </div>


      <div class="adm-system-status">

         <span class="adm-system-dot"></span>

         System Operational

      </div>

   </div>


   <!-- =====================================================
         KPI
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- FASKES -->

      <div class="col-xl-3 col-md-6">

         <div class="adm-kpi">

            <div class="adm-kpi-top">

               <div class="adm-kpi-icon">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="24">
                  </iconify-icon>

               </div>

               <iconify-icon
                  icon="solar:arrow-right-up-linear"
                  width="17"
                  style="color:var(--adm-green);">
               </iconify-icon>

            </div>


            <div class="adm-kpi-label">
               Total Faskes
            </div>

            <div class="adm-kpi-value">
               128
            </div>

            <div class="adm-kpi-sub">
               121 aktif • 7 pending
            </div>

         </div>

      </div>


      <!-- DATA PASIEN -->

      <div class="col-xl-3 col-md-6">

         <div class="adm-kpi">

            <div class="adm-kpi-top">

               <div class="adm-kpi-icon blue">

                  <iconify-icon
                     icon="solar:users-group-rounded-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="adm-kpi-label">
               Data Pasien
            </div>

            <div class="adm-kpi-value">
               1,28 Jt
            </div>

            <div class="adm-kpi-sub">
               Terdata pada platform
            </div>

         </div>

      </div>


      <!-- DOKTER -->

      <div class="col-xl-3 col-md-6">

         <div class="adm-kpi">

            <div class="adm-kpi-top">

               <div class="adm-kpi-icon green">

                  <iconify-icon
                     icon="solar:user-id-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="adm-kpi-label">
               IDSH Dokter
            </div>

            <div class="adm-kpi-value">
               8.462
            </div>

            <div class="adm-kpi-sub">
               8.210 terverifikasi
            </div>

         </div>

      </div>


      <!-- IMPORT -->

      <div class="col-xl-3 col-md-6">

         <div class="adm-kpi">

            <div class="adm-kpi-top">

               <div class="adm-kpi-icon orange">

                  <iconify-icon
                     icon="solar:database-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="adm-kpi-label">
               Data Import
            </div>

            <div class="adm-kpi-value">
               96,4%
            </div>

            <div class="adm-kpi-sub">
               Tingkat keberhasilan import
            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         ROW 1
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- MONITORING SISTEM -->

      <div class="col-xl-7">

         <div class="adm-card">

            <div class="adm-card-header">

               <div>

                  <div class="adm-card-title">
                     Monitoring Sistem
                  </div>

                  <div class="adm-card-subtitle">
                     Status layanan utama platform
                  </div>

               </div>

               <span style="
                        font-size:9px;
                        color:var(--adm-muted);">
                  Updated 08:55
               </span>

            </div>


            <!-- SATUSEHAT -->

            <div class="adm-system-item">

               <div class="adm-system-icon green">

                  <iconify-icon
                     icon="solar:link-bold"
                     width="20">
                  </iconify-icon>

               </div>


               <div class="adm-system-info">

                  <div class="adm-system-name">
                     Integrasi SATUSEHAT
                  </div>

                  <div class="adm-system-desc">
                     API connectivity dan pertukaran data
                  </div>

               </div>


               <span class="adm-system-badge adm-online">
                  ONLINE
               </span>

            </div>


            <!-- DATABASE -->

            <div class="adm-system-item">

               <div class="adm-system-icon green">

                  <iconify-icon
                     icon="solar:database-bold"
                     width="20">
                  </iconify-icon>

               </div>


               <div class="adm-system-info">

                  <div class="adm-system-name">
                     Database Platform
                  </div>

                  <div class="adm-system-desc">
                     Database server dan koneksi aplikasi
                  </div>

               </div>


               <span class="adm-system-badge adm-online">
                  NORMAL
               </span>

            </div>


            <!-- LAB -->

            <div class="adm-system-item">

               <div class="adm-system-icon green">

                  <iconify-icon
                     icon="solar:test-tube-bold"
                     width="20">
                  </iconify-icon>

               </div>


               <div class="adm-system-info">

                  <div class="adm-system-name">
                     Laboratory Service
                  </div>

                  <div class="adm-system-desc">
                     Integrasi dan sinkronisasi data laboratorium
                  </div>

               </div>


               <span class="adm-system-badge adm-online">
                  ONLINE
               </span>

            </div>


            <!-- IMPORT -->

            <div class="adm-system-item">

               <div class="adm-system-icon orange">

                  <iconify-icon
                     icon="solar:cloud-upload-bold"
                     width="20">
                  </iconify-icon>

               </div>


               <div class="adm-system-info">

                  <div class="adm-system-name">
                     Data Import Service
                  </div>

                  <div class="adm-system-desc">
                     Terdapat proses import yang sedang berjalan
                  </div>

               </div>


               <span class="adm-system-badge adm-warning">
                  PROCESS
               </span>

            </div>


            <!-- MONITOR RME -->

            <div class="adm-system-item">

               <div class="adm-system-icon green">

                  <iconify-icon
                     icon="solar:monitor-bold"
                     width="20">
                  </iconify-icon>

               </div>


               <div class="adm-system-info">

                  <div class="adm-system-name">
                     Monitoring RME
                  </div>

                  <div class="adm-system-desc">
                     Monitoring kelengkapan data rekam medis elektronik
                  </div>

               </div>


               <span class="adm-system-badge adm-online">
                  NORMAL
               </span>

            </div>

         </div>

      </div>


      <!-- FASKES -->

      <div class="col-xl-5">

         <div class="adm-card">

            <div class="adm-card-header">

               <div>

                  <div class="adm-card-title">
                     Faskes Terbaru
                  </div>

                  <div class="adm-card-subtitle">
                     Aktivitas fasilitas kesehatan
                  </div>

               </div>

               <a href="#" class="adm-link">
                  Lihat Semua
               </a>

            </div>


            <div class="adm-faskes">

               <div class="adm-faskes-avatar">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="adm-faskes-info">

                  <div class="adm-faskes-name">
                     RS Harapan Sehat
                  </div>

                  <div class="adm-faskes-meta">
                     Rumah Sakit • Jakarta
                  </div>

               </div>

               <span
                  class="adm-faskes-status adm-online">
                  AKTIF
               </span>

            </div>


            <div class="adm-faskes">

               <div class="adm-faskes-avatar">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="adm-faskes-info">

                  <div class="adm-faskes-name">
                     Klinik Sehat Sentosa
                  </div>

                  <div class="adm-faskes-meta">
                     Klinik • Bandung
                  </div>

               </div>

               <span
                  class="adm-faskes-status adm-online">
                  AKTIF
               </span>

            </div>


            <div class="adm-faskes">

               <div class="adm-faskes-avatar">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="adm-faskes-info">

                  <div class="adm-faskes-name">
                     Puskesmas Sukamaju
                  </div>

                  <div class="adm-faskes-meta">
                     Puskesmas • Depok
                  </div>

               </div>

               <span
                  class="adm-faskes-status adm-warning">
                  PENDING
               </span>

            </div>


            <div class="adm-faskes">

               <div class="adm-faskes-avatar">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="adm-faskes-info">

                  <div class="adm-faskes-name">
                     Klinik Medika Prima
                  </div>

                  <div class="adm-faskes-meta">
                     Klinik • Bekasi
                  </div>

               </div>

               <span
                  class="adm-faskes-status adm-online">
                  AKTIF
               </span>

            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         ROW 2
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- DATA IMPORT -->

      <div class="col-xl-6">

         <div class="adm-card">

            <div class="adm-card-header">

               <div>

                  <div class="adm-card-title">
                     Data Import
                  </div>

                  <div class="adm-card-subtitle">
                     Status proses import data
                  </div>

               </div>

               <a href="#" class="adm-link">
                  Kelola Import
               </a>

            </div>


            <!-- PASIEN -->

            <div class="adm-import-box">

               <div class="adm-import-top">

                  <div class="adm-import-title">
                     Data Pasien
                  </div>

                  <div class="adm-import-value">
                     98%
                  </div>

               </div>

               <div class="adm-import-desc">
                  12.480 data berhasil diproses
               </div>

               <div class="adm-progress">

                  <div
                     class="adm-progress-bar green"
                     style="width:98%;">
                  </div>

               </div>

            </div>


            <!-- DOKTER -->

            <div class="adm-import-box">

               <div class="adm-import-top">

                  <div class="adm-import-title">
                     Data Dokter
                  </div>

                  <div class="adm-import-value">
                     94%
                  </div>

               </div>

               <div class="adm-import-desc">
                  3.240 data berhasil diproses
               </div>

               <div class="adm-progress">

                  <div
                     class="adm-progress-bar"
                     style="width:94%;">
                  </div>

               </div>

            </div>


            <!-- RME -->

            <div class="adm-import-box">

               <div class="adm-import-top">

                  <div class="adm-import-title">
                     Data RME
                  </div>

                  <div class="adm-import-value">
                     87%
                  </div>

               </div>

               <div class="adm-import-desc">
                  28.650 data berhasil diproses
               </div>

               <div class="adm-progress">

                  <div
                     class="adm-progress-bar orange"
                     style="width:87%;">
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

                  <div class="adm-card-title">
                     Integrasi & Layanan
                  </div>

                  <div class="adm-card-subtitle">
                     Status koneksi layanan eksternal
                  </div>

               </div>

            </div>


            <!-- SATUSEHAT -->

            <div class="adm-integration">

               <div class="adm-integration-left">

                  <div class="adm-integration-icon">

                     <iconify-icon
                        icon="solar:link-bold"
                        width="19">
                     </iconify-icon>

                  </div>

                  <div>

                     <div class="adm-integration-name">
                        SATUSEHAT
                     </div>

                     <div class="adm-integration-desc">
                        Healthcare interoperability
                     </div>

                  </div>

               </div>

               <span class="adm-integration-status adm-online">
                  CONNECTED
               </span>

            </div>


            <!-- LAB -->

            <div class="adm-integration">

               <div class="adm-integration-left">

                  <div class="adm-integration-icon">

                     <iconify-icon
                        icon="solar:test-tube-bold"
                        width="19">
                     </iconify-icon>

                  </div>

                  <div>

                     <div class="adm-integration-name">
                        Laboratory
                     </div>

                     <div class="adm-integration-desc">
                        Laboratory data integration
                     </div>

                  </div>

               </div>

               <span class="adm-integration-status adm-online">
                  CONNECTED
               </span>

            </div>


            <!-- IDSH -->

            <div class="adm-integration">

               <div class="adm-integration-left">

                  <div class="adm-integration-icon">

                     <iconify-icon
                        icon="solar:user-id-bold"
                        width="19">
                     </iconify-icon>

                  </div>

                  <div>

                     <div class="adm-integration-name">
                        IDSH
                     </div>

                     <div class="adm-integration-desc">
                        Identitas dokter & pasien
                     </div>

                  </div>

               </div>

               <span class="adm-integration-status adm-online">
                  ACTIVE
               </span>

            </div>


            <!-- API -->

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
                        API Gateway
                     </div>

                     <div class="adm-integration-desc">
                        Platform API service
                     </div>

                  </div>

               </div>

               <span class="adm-integration-status adm-online">
                  HEALTHY
               </span>

            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         ROW 3
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- ALERT -->

      <div class="col-xl-7">

         <div class="adm-card">

            <div class="adm-card-header">

               <div>

                  <div class="adm-card-title">
                     Perlu Perhatian
                  </div>

                  <div class="adm-card-subtitle">
                     Aktivitas administrator yang membutuhkan tindak lanjut
                  </div>

               </div>

            </div>


            <div class="adm-alert warning">

               <div class="adm-alert-icon">

                  <iconify-icon
                     icon="solar:cloud-upload-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="adm-alert-content">

                  <div class="adm-alert-title">
                     3 Proses Import Berjalan
                  </div>

                  <div class="adm-alert-text">
                     Beberapa fasilitas kesehatan masih melakukan sinkronisasi data.
                  </div>

               </div>

            </div>


            <div class="adm-alert danger">

               <div class="adm-alert-icon">

                  <iconify-icon
                     icon="solar:danger-triangle-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="adm-alert-content">

                  <div class="adm-alert-title">
                     7 Faskes Belum Terverifikasi
                  </div>

                  <div class="adm-alert-text">
                     Data fasilitas kesehatan membutuhkan proses verifikasi administrator.
                  </div>

               </div>

            </div>


            <div class="adm-alert info">

               <div class="adm-alert-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="adm-alert-content">

                  <div class="adm-alert-title">
                     Monitoring RME Menemukan Data Tidak Lengkap
                  </div>

                  <div class="adm-alert-text">
                     Terdapat beberapa data RME yang belum memenuhi kelengkapan yang ditentukan.
                  </div>

               </div>

            </div>

         </div>

      </div>


      <!-- DATA IDSH -->

      <div class="col-xl-5">

         <div class="adm-card">

            <div class="adm-card-header">

               <div>

                  <div class="adm-card-title">
                     IDSH
                  </div>

                  <div class="adm-card-subtitle">
                     Status identitas tenaga medis dan pasien
                  </div>

               </div>

            </div>


            <div class="adm-import-box">

               <div class="adm-import-top">

                  <div class="adm-import-title">
                     IDSH Dokter
                  </div>

                  <div
                     class="adm-import-value"
                     style="color:var(--adm-green);">
                     97%
                  </div>

               </div>

               <div class="adm-import-desc">
                  8.210 dari 8.462 terverifikasi
               </div>

               <div class="adm-progress">

                  <div
                     class="adm-progress-bar green"
                     style="width:97%;">
                  </div>

               </div>

            </div>


            <div class="adm-import-box">

               <div class="adm-import-top">

                  <div class="adm-import-title">
                     IDSH Pasien
                  </div>

                  <div
                     class="adm-import-value"
                     style="color:var(--adm-blue);">
                     91%
                  </div>

               </div>

               <div class="adm-import-desc">
                  1,16 juta dari 1,28 juta terverifikasi
               </div>

               <div class="adm-progress">

                  <div
                     class="adm-progress-bar"
                     style="width:91%;">
                  </div>

               </div>

            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         QUICK ACCESS
    ====================================================== -->

   <div class="adm-card mb-3">

      <div class="adm-card-header">

         <div>

            <div class="adm-card-title">
               Akses Cepat Administrator
            </div>

            <div class="adm-card-subtitle">
               Menu utama untuk pengelolaan platform
            </div>

         </div>

      </div>


      <div class="row g-2">


         <!-- DATA FASKES -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:hospital-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Data Faskes
                  </div>

                  <div class="adm-quick-desc">
                     Kelola faskes
                  </div>

               </div>

            </a>

         </div>


         <!-- IMPORT -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:cloud-upload-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Data Import
                  </div>

                  <div class="adm-quick-desc">
                     Import data
                  </div>

               </div>

            </a>

         </div>


         <!-- MASTER -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:database-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Master Data
                  </div>

                  <div class="adm-quick-desc">
                     Data referensi
                  </div>

               </div>

            </a>

         </div>


         <!-- SATUSEHAT -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:link-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Satu Sehat
                  </div>

                  <div class="adm-quick-desc">
                     Integrasi API
                  </div>

               </div>

            </a>

         </div>


         <!-- LAB -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:test-tube-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Laboratorium
                  </div>

                  <div class="adm-quick-desc">
                     Data laboratorium
                  </div>

               </div>

            </a>

         </div>


         <!-- MONITOR RME -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="adm-quick">

               <div class="adm-quick-icon">

                  <iconify-icon
                     icon="solar:monitor-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="adm-quick-title">
                     Monitoring RME
                  </div>

                  <div class="adm-quick-desc">
                     Kualitas data
                  </div>

               </div>

            </a>

         </div>

      </div>

   </div>


</div>


<script>
   document.addEventListener("DOMContentLoaded", function() {

      const periode =
         document.getElementById("admPeriode");

      const tanggalMulai =
         document.getElementById("admTanggalMulai");

      const tanggalSelesai =
         document.getElementById("admTanggalSelesai");

      const btnFilter =
         document.getElementById(
            "btnFilterAdministrator"
         );


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


      periode.addEventListener("change", function() {

         const today = new Date();


         if (this.value === "today") {

            const date =
               formatDate(today);

            tanggalMulai.value = date;
            tanggalSelesai.value = date;

         } else if (this.value === "week") {

            const start =
               new Date(today);

            start.setDate(
               today.getDate() -
               today.getDay() +
               1
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

      });


      btnFilter.addEventListener("click", function() {

         const mulai =
            tanggalMulai.value;

         const selesai =
            tanggalSelesai.value;


         if (!mulai || !selesai) {

            if (
               typeof Swal !== "undefined"
            ) {

               Swal.fire({
                  icon: "warning",
                  title: "Periode Belum Lengkap",
                  text: "Silakan pilih tanggal mulai dan tanggal selesai."
               });

            } else {

               alert(
                  "Silakan pilih tanggal terlebih dahulu."
               );

            }

            return;
         }


         if (mulai > selesai) {

            if (
               typeof Swal !== "undefined"
            ) {

               Swal.fire({
                  icon: "warning",
                  title: "Periode Tidak Valid",
                  text: "Tanggal mulai tidak boleh lebih besar dari tanggal selesai."
               });

            } else {

               alert(
                  "Tanggal mulai tidak boleh lebih besar dari tanggal selesai."
               );

            }

            return;
         }


         console.log(
            "Filter Administrator:",
            mulai,
            "sampai",
            selesai
         );

      });

   });
</script>