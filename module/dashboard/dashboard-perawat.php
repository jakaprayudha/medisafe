<style>
   .perawat-dashboard {
      --nurse-primary: #635bff;
      --nurse-primary-soft: #eeecff;
      --nurse-text: #273444;
      --nurse-muted: #7b8494;
      --nurse-border: #edf0f5;
      --nurse-green: #16a34a;
      --nurse-green-soft: #eaf8ef;
      --nurse-red: #dc2626;
      --nurse-red-soft: #fff0f0;
      --nurse-orange: #d97706;
      --nurse-orange-soft: #fff7e8;
      --nurse-blue: #1687d9;
      --nurse-blue-soft: #edf7ff;
   }

   .perawat-dashboard {
      color: var(--nurse-text);
   }

   /* =========================
   FILTER
========================= */
   .perawat-filter {
      background: #fff;
      border: 1px solid var(--nurse-border);
      border-radius: 18px;
      padding: 16px 18px;
      margin-bottom: 16px;
   }

   .perawat-filter-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--nurse-muted);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .3px;
   }

   .perawat-filter .form-control,
   .perawat-filter .form-select {
      border: 1px solid var(--nurse-border);
      border-radius: 11px;
      min-height: 40px;
      font-size: 13px;
      box-shadow: none;
   }

   .perawat-filter .form-control:focus,
   .perawat-filter .form-select:focus {
      border-color: var(--nurse-primary);
      box-shadow: 0 0 0 3px rgba(99, 91, 255, .08);
   }

   .perawat-filter-btn {
      min-height: 40px;
      border: 0;
      border-radius: 11px;
      background: var(--nurse-primary);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      padding: 0 17px;
   }

   .perawat-filter-btn:hover {
      background: #5149e8;
      color: #fff;
   }

   /* =========================
   STATUS
========================= */
   .perawat-status {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 7px 11px;
      border-radius: 999px;
      background: var(--nurse-green-soft);
      color: var(--nurse-green);
      font-size: 11px;
      font-weight: 700;
   }

   .perawat-status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--nurse-green);
   }

   /* =========================
   KPI
========================= */
   .perawat-kpi {
      height: 100%;
      background: #fff;
      border: 1px solid var(--nurse-border);
      border-radius: 18px;
      padding: 18px;
      transition: .2s ease;
   }

   .perawat-kpi:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(39, 52, 68, .06);
   }

   .perawat-kpi-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
   }

   .perawat-kpi-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--nurse-primary-soft);
      color: var(--nurse-primary);
   }

   .perawat-kpi-icon.green {
      background: var(--nurse-green-soft);
      color: var(--nurse-green);
   }

   .perawat-kpi-icon.orange {
      background: var(--nurse-orange-soft);
      color: var(--nurse-orange);
   }

   .perawat-kpi-icon.blue {
      background: var(--nurse-blue-soft);
      color: var(--nurse-blue);
   }

   .perawat-kpi-label {
      font-size: 11px;
      color: var(--nurse-muted);
      margin-bottom: 4px;
   }

   .perawat-kpi-value {
      font-size: 24px;
      line-height: 1.15;
      font-weight: 800;
   }

   .perawat-kpi-sub {
      font-size: 10px;
      color: var(--nurse-muted);
      margin-top: 5px;
   }

   /* =========================
   CARD
========================= */
   .perawat-card {
      height: 100%;
      background: #fff;
      border: 1px solid var(--nurse-border);
      border-radius: 18px;
      padding: 19px;
   }

   .perawat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 17px;
   }

   .perawat-card-title {
      margin: 0;
      font-size: 14px;
      font-weight: 800;
   }

   .perawat-card-subtitle {
      margin-top: 3px;
      font-size: 10px;
      color: var(--nurse-muted);
   }

   .perawat-link {
      font-size: 11px;
      color: var(--nurse-primary);
      text-decoration: none;
      font-weight: 700;
   }

   /* =========================
   PATIENT LIST
========================= */
   .perawat-patient {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .perawat-patient:last-child {
      border-bottom: 0;
   }

   .perawat-avatar {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: var(--nurse-primary-soft);
      color: var(--nurse-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 800;
      flex-shrink: 0;
   }

   .perawat-patient-info {
      flex: 1;
      min-width: 0;
   }

   .perawat-patient-name {
      font-size: 12px;
      font-weight: 800;
   }

   .perawat-patient-meta {
      font-size: 10px;
      color: var(--nurse-muted);
      margin-top: 2px;
   }

   .perawat-patient-status {
      padding: 5px 8px;
      border-radius: 8px;
      font-size: 9px;
      font-weight: 800;
      white-space: nowrap;
   }

   .patient-stable {
      background: var(--nurse-green-soft);
      color: var(--nurse-green);
   }

   .patient-monitor {
      background: var(--nurse-orange-soft);
      color: var(--nurse-orange);
   }

   .patient-critical {
      background: var(--nurse-red-soft);
      color: var(--nurse-red);
   }

   /* =========================
   TASK
========================= */
   .perawat-task {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 11px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .perawat-task:last-child {
      border-bottom: 0;
   }

   .perawat-task-icon {
      width: 37px;
      height: 37px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: var(--nurse-primary-soft);
      color: var(--nurse-primary);
   }

   .perawat-task-icon.green {
      background: var(--nurse-green-soft);
      color: var(--nurse-green);
   }

   .perawat-task-icon.orange {
      background: var(--nurse-orange-soft);
      color: var(--nurse-orange);
   }

   .perawat-task-icon.red {
      background: var(--nurse-red-soft);
      color: var(--nurse-red);
   }

   .perawat-task-info {
      flex: 1;
   }

   .perawat-task-title {
      font-size: 11px;
      font-weight: 800;
   }

   .perawat-task-meta {
      font-size: 9px;
      color: var(--nurse-muted);
      margin-top: 2px;
   }

   .perawat-task-time {
      font-size: 10px;
      font-weight: 800;
      color: var(--nurse-muted);
   }

   /* =========================
   VITAL
========================= */
   .perawat-vital {
      padding: 13px;
      border: 1px solid var(--nurse-border);
      border-radius: 13px;
      text-align: center;
   }

   .perawat-vital-label {
      font-size: 9px;
      color: var(--nurse-muted);
   }

   .perawat-vital-value {
      margin-top: 4px;
      font-size: 18px;
      font-weight: 800;
   }

   .perawat-vital-unit {
      font-size: 9px;
      color: var(--nurse-muted);
   }

   /* =========================
   ALERT
========================= */
   .perawat-alert {
      display: flex;
      gap: 11px;
      padding: 12px;
      border-radius: 13px;
      margin-bottom: 10px;
   }

   .perawat-alert:last-child {
      margin-bottom: 0;
   }

   .perawat-alert-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .perawat-alert-content {
      flex: 1;
   }

   .perawat-alert-title {
      font-size: 11px;
      font-weight: 800;
      margin-bottom: 2px;
   }

   .perawat-alert-text {
      font-size: 10px;
      line-height: 1.45;
   }

   .perawat-alert.warning {
      background: var(--nurse-orange-soft);
   }

   .perawat-alert.warning .perawat-alert-icon {
      background: #ffeac2;
      color: var(--nurse-orange);
   }

   .perawat-alert.warning .perawat-alert-text {
      color: #8a5a0a;
   }

   .perawat-alert.danger {
      background: var(--nurse-red-soft);
   }

   .perawat-alert.danger .perawat-alert-icon {
      background: #ffdada;
      color: var(--nurse-red);
   }

   .perawat-alert.danger .perawat-alert-text {
      color: #9f2424;
   }

   .perawat-alert.info {
      background: var(--nurse-blue-soft);
   }

   .perawat-alert.info .perawat-alert-icon {
      background: #d9efff;
      color: var(--nurse-blue);
   }

   .perawat-alert.info .perawat-alert-text {
      color: #17648f;
   }

   /* =========================
   QUICK ACCESS
========================= */
   .perawat-quick {
      height: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      border: 1px solid var(--nurse-border);
      border-radius: 13px;
      text-decoration: none;
      color: var(--nurse-text);
      transition: .2s ease;
   }

   .perawat-quick:hover {
      color: var(--nurse-primary);
      background: #faf9ff;
      border-color: #dcd9ff;
      transform: translateY(-2px);
   }

   .perawat-quick-icon {
      width: 40px;
      height: 40px;
      border-radius: 11px;
      background: var(--nurse-primary-soft);
      color: var(--nurse-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .perawat-quick-title {
      font-size: 11px;
      font-weight: 800;
   }

   .perawat-quick-desc {
      font-size: 9px;
      color: var(--nurse-muted);
      margin-top: 2px;
   }

   /* =========================
   PROGRESS
========================= */
   .perawat-progress-item {
      margin-bottom: 15px;
   }

   .perawat-progress-item:last-child {
      margin-bottom: 0;
   }

   .perawat-progress-top {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
   }

   .perawat-progress-name {
      font-size: 10px;
      font-weight: 700;
   }

   .perawat-progress-value {
      font-size: 10px;
      font-weight: 800;
   }

   .perawat-progress {
      height: 7px;
      border-radius: 99px;
      background: #f0f1f5;
      overflow: hidden;
   }

   .perawat-progress-bar {
      height: 100%;
      border-radius: 99px;
      background: var(--nurse-primary);
   }

   .perawat-progress-bar.green {
      background: var(--nurse-green);
   }

   .perawat-progress-bar.orange {
      background: var(--nurse-orange);
   }

   .perawat-progress-bar.blue {
      background: var(--nurse-blue);
   }

   /* =========================
   RESPONSIVE
========================= */
   @media (max-width: 767.98px) {

      .perawat-filter {
         padding: 14px;
      }

      .perawat-filter-btn {
         width: 100%;
      }

      .perawat-card {
         padding: 15px;
      }

      .perawat-kpi-value {
         font-size: 21px;
      }
   }
</style>


<div class="perawat-dashboard">

   <!-- =========================
         FILTER PERIODE
    ========================== -->
   <div class="perawat-filter">

      <div class="row g-2 align-items-end">

         <div class="col-xl-3 col-md-6">

            <div class="perawat-filter-label">
               Periode
            </div>

            <select class="form-select" id="perawatPeriode">

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

            <div class="perawat-filter-label">
               Tanggal Mulai
            </div>

            <input
               type="date"
               class="form-control"
               id="perawatTanggalMulai"
               value="<?= date('Y-m-d') ?>">

         </div>

         <div class="col-xl-3 col-md-6">

            <div class="perawat-filter-label">
               Tanggal Selesai
            </div>

            <input
               type="date"
               class="form-control"
               id="perawatTanggalSelesai"
               value="<?= date('Y-m-d') ?>">

         </div>

         <div class="col-xl-3 col-md-6">

            <button
               type="button"
               class="perawat-filter-btn w-100"
               id="btnFilterPerawat">

               <iconify-icon
                  icon="solar:filter-bold"
                  width="16">
               </iconify-icon>

               Terapkan Filter

            </button>

         </div>

      </div>

   </div>


   <!-- =========================
         HEADER
    ========================== -->
   <div class="d-flex justify-content-between align-items-center mb-3">

      <div>

         <div style="
                font-size:18px;
                font-weight:800;">
            Dashboard Perawat
         </div>

         <div style="
                font-size:11px;
                color:var(--nurse-muted);">
            Monitoring pelayanan dan asuhan keperawatan pasien
         </div>

      </div>

      <div class="perawat-status">

         <span class="perawat-status-dot"></span>

         Shift Aktif

      </div>

   </div>


   <!-- =========================
         KPI
    ========================== -->
   <div class="row g-3 mb-3">

      <!-- TOTAL PASIEN -->
      <div class="col-xl-3 col-md-6">

         <div class="perawat-kpi">

            <div class="perawat-kpi-top">

               <div class="perawat-kpi-icon">

                  <iconify-icon
                     icon="solar:bed-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>

            <div class="perawat-kpi-label">
               Total Pasien Dirawat
            </div>

            <div class="perawat-kpi-value">
               42
            </div>

            <div class="perawat-kpi-sub">
               38 pasien stabil
            </div>

         </div>

      </div>


      <!-- PASIEN BARU -->
      <div class="col-xl-3 col-md-6">

         <div class="perawat-kpi">

            <div class="perawat-kpi-top">

               <div class="perawat-kpi-icon blue">

                  <iconify-icon
                     icon="solar:user-plus-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>

            <div class="perawat-kpi-label">
               Pasien Masuk
            </div>

            <div class="perawat-kpi-value">
               8
            </div>

            <div class="perawat-kpi-sub">
               Hari ini
            </div>

         </div>

      </div>


      <!-- TINDAKAN -->
      <div class="col-xl-3 col-md-6">

         <div class="perawat-kpi">

            <div class="perawat-kpi-top">

               <div class="perawat-kpi-icon orange">

                  <iconify-icon
                     icon="solar:stethoscope-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>

            <div class="perawat-kpi-label">
               Tindakan Menunggu
            </div>

            <div class="perawat-kpi-value">
               11
            </div>

            <div class="perawat-kpi-sub">
               Perlu segera ditangani
            </div>

         </div>

      </div>


      <!-- OBAT -->
      <div class="col-xl-3 col-md-6">

         <div class="perawat-kpi">

            <div class="perawat-kpi-top">

               <div class="perawat-kpi-icon green">

                  <iconify-icon
                     icon="solar:pills-3-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>

            <div class="perawat-kpi-label">
               Pemberian Obat
            </div>

            <div class="perawat-kpi-value">
               36
            </div>

            <div class="perawat-kpi-sub">
               32 sudah diberikan
            </div>

         </div>

      </div>

   </div>


   <!-- =========================
         PASIEN + TASK
    ========================== -->
   <div class="row g-3 mb-3">

      <!-- DAFTAR PASIEN -->
      <div class="col-xl-7">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Pasien Dalam Perawatan
                  </div>

                  <div class="perawat-card-subtitle">
                     Daftar pasien yang menjadi tanggung jawab shift
                  </div>

               </div>

               <a href="#" class="perawat-link">
                  Lihat Semua
               </a>

            </div>


            <!-- PASIEN 1 -->
            <div class="perawat-patient">

               <div class="perawat-avatar">
                  AF
               </div>

               <div class="perawat-patient-info">

                  <div class="perawat-patient-name">
                     Ahmad Fauzan
                  </div>

                  <div class="perawat-patient-meta">
                     RM-000128 • Kamar 201 • Bed A
                  </div>

               </div>

               <span class="perawat-patient-status patient-stable">
                  Stabil
               </span>

            </div>


            <!-- PASIEN 2 -->
            <div class="perawat-patient">

               <div class="perawat-avatar">
                  SR
               </div>

               <div class="perawat-patient-info">

                  <div class="perawat-patient-name">
                     Siti Rahma
                  </div>

                  <div class="perawat-patient-meta">
                     RM-000127 • Kamar 203 • Bed B
                  </div>

               </div>

               <span class="perawat-patient-status patient-monitor">
                  Monitor
               </span>

            </div>


            <!-- PASIEN 3 -->
            <div class="perawat-patient">

               <div class="perawat-avatar">
                  BS
               </div>

               <div class="perawat-patient-info">

                  <div class="perawat-patient-name">
                     Budi Santoso
                  </div>

                  <div class="perawat-patient-meta">
                     RM-000126 • Kamar 205 • Bed A
                  </div>

               </div>

               <span class="perawat-patient-status patient-stable">
                  Stabil
               </span>

            </div>


            <!-- PASIEN 4 -->
            <div class="perawat-patient">

               <div class="perawat-avatar">
                  NA
               </div>

               <div class="perawat-patient-info">

                  <div class="perawat-patient-name">
                     Nur Aisyah
                  </div>

                  <div class="perawat-patient-meta">
                     RM-000125 • Kamar 206 • Bed B
                  </div>

               </div>

               <span class="perawat-patient-status patient-critical">
                  Perhatian
               </span>

            </div>


            <!-- PASIEN 5 -->
            <div class="perawat-patient">

               <div class="perawat-avatar">
                  DI
               </div>

               <div class="perawat-patient-info">

                  <div class="perawat-patient-name">
                     Dedi Irawan
                  </div>

                  <div class="perawat-patient-meta">
                     RM-000124 • Kamar 208 • Bed A
                  </div>

               </div>

               <span class="perawat-patient-status patient-stable">
                  Stabil
               </span>

            </div>

         </div>

      </div>


      <!-- TUGAS -->
      <div class="col-xl-5">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Tugas Keperawatan
                  </div>

                  <div class="perawat-card-subtitle">
                     Aktivitas yang perlu dilakukan
                  </div>

               </div>

            </div>


            <div class="perawat-task">

               <div class="perawat-task-icon orange">

                  <iconify-icon
                     icon="solar:temperature-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="perawat-task-info">

                  <div class="perawat-task-title">
                     Monitoring Tanda Vital
                  </div>

                  <div class="perawat-task-meta">
                     5 pasien
                  </div>

               </div>

               <div class="perawat-task-time">
                  09:00
               </div>

            </div>


            <div class="perawat-task">

               <div class="perawat-task-icon">

                  <iconify-icon
                     icon="solar:pills-3-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="perawat-task-info">

                  <div class="perawat-task-title">
                     Pemberian Obat
                  </div>

                  <div class="perawat-task-meta">
                     8 pasien
                  </div>

               </div>

               <div class="perawat-task-time">
                  10:00
               </div>

            </div>


            <div class="perawat-task">

               <div class="perawat-task-icon green">

                  <iconify-icon
                     icon="solar:medical-kit-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="perawat-task-info">

                  <div class="perawat-task-title">
                     Perawatan Luka
                  </div>

                  <div class="perawat-task-meta">
                     3 pasien
                  </div>

               </div>

               <div class="perawat-task-time">
                  11:00
               </div>

            </div>


            <div class="perawat-task">

               <div class="perawat-task-icon red">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="perawat-task-info">

                  <div class="perawat-task-title">
                     Catatan Keperawatan
                  </div>

                  <div class="perawat-task-meta">
                     4 catatan belum lengkap
                  </div>

               </div>

               <div class="perawat-task-time">
                  Prioritas
               </div>

            </div>

         </div>

      </div>

   </div>


   <!-- =========================
         VITAL + PROGRESS
    ========================== -->
   <div class="row g-3 mb-3">

      <!-- VITAL -->
      <div class="col-xl-7">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Monitoring Tanda Vital
                  </div>

                  <div class="perawat-card-subtitle">
                     Ringkasan pemeriksaan terakhir
                  </div>

               </div>

               <span style="
                        font-size:10px;
                        color:var(--nurse-muted);">
                  Update terakhir 08:45
               </span>

            </div>


            <div class="row g-2">

               <div class="col-6 col-md-3">

                  <div class="perawat-vital">

                     <div class="perawat-vital-label">
                        Tekanan Darah
                     </div>

                     <div class="perawat-vital-value">
                        120/80
                     </div>

                     <div class="perawat-vital-unit">
                        mmHg
                     </div>

                  </div>

               </div>


               <div class="col-6 col-md-3">

                  <div class="perawat-vital">

                     <div class="perawat-vital-label">
                        Nadi
                     </div>

                     <div class="perawat-vital-value">
                        78
                     </div>

                     <div class="perawat-vital-unit">
                        bpm
                     </div>

                  </div>

               </div>


               <div class="col-6 col-md-3">

                  <div class="perawat-vital">

                     <div class="perawat-vital-label">
                        Suhu
                     </div>

                     <div class="perawat-vital-value">
                        36.7
                     </div>

                     <div class="perawat-vital-unit">
                        °C
                     </div>

                  </div>

               </div>


               <div class="col-6 col-md-3">

                  <div class="perawat-vital">

                     <div class="perawat-vital-label">
                        SpO₂
                     </div>

                     <div class="perawat-vital-value">
                        98
                     </div>

                     <div class="perawat-vital-unit">
                        %
                     </div>

                  </div>

               </div>

            </div>

         </div>

      </div>


      <!-- PROGRESS -->
      <div class="col-xl-5">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Penyelesaian Pelayanan
                  </div>

                  <div class="perawat-card-subtitle">
                     Progress tugas shift
                  </div>

               </div>

            </div>


            <div class="perawat-progress-item">

               <div class="perawat-progress-top">

                  <span class="perawat-progress-name">
                     Tanda Vital
                  </span>

                  <span class="perawat-progress-value">
                     85%
                  </span>

               </div>

               <div class="perawat-progress">

                  <div
                     class="perawat-progress-bar"
                     style="width:85%;">
                  </div>

               </div>

            </div>


            <div class="perawat-progress-item">

               <div class="perawat-progress-top">

                  <span class="perawat-progress-name">
                     Pemberian Obat
                  </span>

                  <span class="perawat-progress-value">
                     89%
                  </span>

               </div>

               <div class="perawat-progress">

                  <div
                     class="perawat-progress-bar green"
                     style="width:89%;">
                  </div>

               </div>

            </div>


            <div class="perawat-progress-item">

               <div class="perawat-progress-top">

                  <span class="perawat-progress-name">
                     Tindakan Keperawatan
                  </span>

                  <span class="perawat-progress-value">
                     72%
                  </span>

               </div>

               <div class="perawat-progress">

                  <div
                     class="perawat-progress-bar orange"
                     style="width:72%;">
                  </div>

               </div>

            </div>


            <div class="perawat-progress-item">

               <div class="perawat-progress-top">

                  <span class="perawat-progress-name">
                     Dokumentasi RME
                  </span>

                  <span class="perawat-progress-value">
                     64%
                  </span>

               </div>

               <div class="perawat-progress">

                  <div
                     class="perawat-progress-bar blue"
                     style="width:64%;">
                  </div>

               </div>

            </div>

         </div>

      </div>

   </div>


   <!-- =========================
         ALERT
    ========================== -->
   <div class="row g-3 mb-3">

      <div class="col-xl-7">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Perlu Perhatian
                  </div>

                  <div class="perawat-card-subtitle">
                     Pasien dan dokumentasi yang perlu ditindaklanjuti
                  </div>

               </div>

            </div>


            <div class="perawat-alert danger">

               <div class="perawat-alert-icon">

                  <iconify-icon
                     icon="solar:danger-triangle-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="perawat-alert-content">

                  <div class="perawat-alert-title">
                     Pasien Memerlukan Monitoring Ketat
                  </div>

                  <div class="perawat-alert-text">
                     1 pasien memiliki status vital yang membutuhkan pemantauan lebih lanjut.
                  </div>

               </div>

            </div>


            <div class="perawat-alert warning">

               <div class="perawat-alert-icon">

                  <iconify-icon
                     icon="solar:clock-circle-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="perawat-alert-content">

                  <div class="perawat-alert-title">
                     4 Tindakan Belum Didokumentasikan
                  </div>

                  <div class="perawat-alert-text">
                     Pastikan seluruh tindakan keperawatan dicatat pada RME pasien.
                  </div>

               </div>

            </div>


            <div class="perawat-alert info">

               <div class="perawat-alert-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="perawat-alert-content">

                  <div class="perawat-alert-title">
                     Dokumentasi Asuhan Belum Lengkap
                  </div>

                  <div class="perawat-alert-text">
                     Terdapat beberapa catatan keperawatan yang masih membutuhkan kelengkapan data.
                  </div>

               </div>

            </div>

         </div>

      </div>


      <!-- SHIFT -->
      <div class="col-xl-5">

         <div class="perawat-card">

            <div class="perawat-card-header">

               <div>

                  <div class="perawat-card-title">
                     Informasi Shift
                  </div>

                  <div class="perawat-card-subtitle">
                     Jadwal dan pembagian pelayanan
                  </div>

               </div>

            </div>


            <div style="
                    padding:15px;
                    background:var(--nurse-primary-soft);
                    border-radius:14px;
                    margin-bottom:10px;">

               <div style="
                        font-size:10px;
                        color:var(--nurse-muted);">
                  Shift Saat Ini
               </div>

               <div style="
                        font-size:20px;
                        font-weight:800;
                        color:var(--nurse-primary);
                        margin-top:3px;">
                  Pagi
               </div>

               <div style="
                        font-size:10px;
                        color:var(--nurse-muted);
                        margin-top:2px;">
                  07:00 - 14:00
               </div>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:10px 0;
                    border-bottom:1px solid var(--nurse-border);">

               <span style="
                        font-size:10px;
                        color:var(--nurse-muted);">
                  Perawat Bertugas
               </span>

               <strong style="font-size:11px;">
                  6 Orang
               </strong>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:10px 0;
                    border-bottom:1px solid var(--nurse-border);">

               <span style="
                        font-size:10px;
                        color:var(--nurse-muted);">
                  Pasien
               </span>

               <strong style="font-size:11px;">
                  42 Pasien
               </strong>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:10px 0;">

               <span style="
                        font-size:10px;
                        color:var(--nurse-muted);">
                  Rasio
               </span>

               <strong style="font-size:11px;">
                  1 : 7
               </strong>

            </div>

         </div>

      </div>

   </div>


   <!-- =========================
         QUICK ACCESS
    ========================== -->
   <div class="perawat-card mb-3">

      <div class="perawat-card-header">

         <div>

            <div class="perawat-card-title">
               Akses Cepat
            </div>

            <div class="perawat-card-subtitle">
               Menu yang sering digunakan dalam pelayanan keperawatan
            </div>

         </div>

      </div>


      <div class="row g-2">

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:users-group-rounded-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Pasien
                  </div>

                  <div class="perawat-quick-desc">
                     Daftar pasien
                  </div>

               </div>

            </a>

         </div>


         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:heart-pulse-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Tanda Vital
                  </div>

                  <div class="perawat-quick-desc">
                     Monitoring
                  </div>

               </div>

            </a>

         </div>


         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:pills-3-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Pemberian Obat
                  </div>

                  <div class="perawat-quick-desc">
                     Medication
                  </div>

               </div>

            </a>

         </div>


         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:medical-kit-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Tindakan
                  </div>

                  <div class="perawat-quick-desc">
                     Keperawatan
                  </div>

               </div>

            </a>

         </div>


         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Catatan
                  </div>

                  <div class="perawat-quick-desc">
                     Keperawatan
                  </div>

               </div>

            </a>

         </div>


         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="perawat-quick">

               <div class="perawat-quick-icon">

                  <iconify-icon
                     icon="solar:chart-2-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="perawat-quick-title">
                     Laporan
                  </div>

                  <div class="perawat-quick-desc">
                     Keperawatan
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
         document.getElementById("perawatPeriode");

      const tanggalMulai =
         document.getElementById("perawatTanggalMulai");

      const tanggalSelesai =
         document.getElementById("perawatTanggalSelesai");

      const btnFilter =
         document.getElementById("btnFilterPerawat");


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

            if (typeof Swal !== "undefined") {

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

            if (typeof Swal !== "undefined") {

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
            "Filter dashboard perawat:",
            mulai,
            "sampai",
            selesai
         );

      });

   });
</script>