<style>
   .lab-dashboard {
      --lab-primary: #635bff;
      --lab-primary-soft: #eeecff;
      --lab-text: #273444;
      --lab-muted: #7b8494;
      --lab-border: #edf0f5;

      --lab-green: #16a34a;
      --lab-green-soft: #eaf8ef;

      --lab-red: #dc2626;
      --lab-red-soft: #fff0f0;

      --lab-orange: #d97706;
      --lab-orange-soft: #fff7e8;

      --lab-blue: #1687d9;
      --lab-blue-soft: #edf7ff;
   }

   .lab-dashboard {
      color: var(--lab-text);
   }

   /* =========================================================
   FILTER
========================================================= */

   .lab-filter {
      background: #fff;
      border: 1px solid var(--lab-border);
      border-radius: 18px;
      padding: 16px 18px;
      margin-bottom: 16px;
   }

   .lab-filter-label {
      font-size: 10px;
      font-weight: 800;
      color: var(--lab-muted);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .35px;
   }

   .lab-filter .form-control,
   .lab-filter .form-select {
      min-height: 40px;
      border-radius: 11px;
      border: 1px solid var(--lab-border);
      font-size: 12px;
      box-shadow: none;
   }

   .lab-filter .form-control:focus,
   .lab-filter .form-select:focus {
      border-color: var(--lab-primary);
      box-shadow: 0 0 0 3px rgba(99, 91, 255, .08);
   }

   .lab-filter-btn {
      width: 100%;
      min-height: 40px;
      border: 0;
      border-radius: 11px;
      background: var(--lab-primary);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
   }

   .lab-filter-btn:hover {
      background: #5149e8;
      color: #fff;
   }


   /* =========================================================
   HEADER
========================================================= */

   .lab-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
   }

   .lab-title {
      font-size: 18px;
      font-weight: 800;
      line-height: 1.2;
   }

   .lab-subtitle {
      margin-top: 4px;
      font-size: 11px;
      color: var(--lab-muted);
   }

   .lab-status {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 7px 12px;
      border-radius: 999px;
      background: var(--lab-green-soft);
      color: var(--lab-green);
      font-size: 10px;
      font-weight: 800;
   }

   .lab-status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--lab-green);
   }


   /* =========================================================
   KPI
========================================================= */

   .lab-kpi {
      height: 100%;
      background: #fff;
      border: 1px solid var(--lab-border);
      border-radius: 18px;
      padding: 18px;
      transition: .2s ease;
   }

   .lab-kpi:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(39, 52, 68, .06);
   }

   .lab-kpi-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
   }

   .lab-kpi-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--lab-primary-soft);
      color: var(--lab-primary);
   }

   .lab-kpi-icon.green {
      background: var(--lab-green-soft);
      color: var(--lab-green);
   }

   .lab-kpi-icon.orange {
      background: var(--lab-orange-soft);
      color: var(--lab-orange);
   }

   .lab-kpi-icon.blue {
      background: var(--lab-blue-soft);
      color: var(--lab-blue);
   }

   .lab-kpi-label {
      font-size: 10px;
      color: var(--lab-muted);
      margin-bottom: 4px;
   }

   .lab-kpi-value {
      font-size: 24px;
      font-weight: 800;
      line-height: 1.15;
   }

   .lab-kpi-sub {
      margin-top: 5px;
      font-size: 9px;
      color: var(--lab-muted);
   }


   /* =========================================================
   CARD
========================================================= */

   .lab-card {
      height: 100%;
      background: #fff;
      border: 1px solid var(--lab-border);
      border-radius: 18px;
      padding: 19px;
   }

   .lab-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 17px;
   }

   .lab-card-title {
      font-size: 14px;
      font-weight: 800;
   }

   .lab-card-subtitle {
      margin-top: 3px;
      font-size: 10px;
      color: var(--lab-muted);
   }

   .lab-link {
      color: var(--lab-primary);
      text-decoration: none;
      font-size: 10px;
      font-weight: 700;
   }


   /* =========================================================
   QUEUE
========================================================= */

   .lab-queue {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 11px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .lab-queue:last-child {
      border-bottom: 0;
   }

   .lab-queue-number {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--lab-primary-soft);
      color: var(--lab-primary);
      font-size: 11px;
      font-weight: 800;
      flex-shrink: 0;
   }

   .lab-queue-info {
      flex: 1;
      min-width: 0;
   }

   .lab-queue-name {
      font-size: 11px;
      font-weight: 800;
   }

   .lab-queue-meta {
      font-size: 9px;
      color: var(--lab-muted);
      margin-top: 2px;
   }

   .lab-queue-status {
      padding: 5px 8px;
      border-radius: 8px;
      font-size: 8px;
      font-weight: 800;
      white-space: nowrap;
   }

   .lab-waiting {
      background: var(--lab-orange-soft);
      color: var(--lab-orange);
   }

   .lab-process {
      background: var(--lab-blue-soft);
      color: var(--lab-blue);
   }

   .lab-ready {
      background: var(--lab-green-soft);
      color: var(--lab-green);
   }

   .lab-critical {
      background: var(--lab-red-soft);
      color: var(--lab-red);
   }


   /* =========================================================
   PROCESS
========================================================= */

   .lab-process-item {
      margin-bottom: 15px;
   }

   .lab-process-item:last-child {
      margin-bottom: 0;
   }

   .lab-process-top {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
   }

   .lab-process-name {
      font-size: 10px;
      font-weight: 700;
   }

   .lab-process-value {
      font-size: 10px;
      font-weight: 800;
   }

   .lab-progress {
      height: 7px;
      background: #f0f1f5;
      border-radius: 99px;
      overflow: hidden;
   }

   .lab-progress-bar {
      height: 100%;
      border-radius: 99px;
      background: var(--lab-primary);
   }

   .lab-progress-bar.green {
      background: var(--lab-green);
   }

   .lab-progress-bar.orange {
      background: var(--lab-orange);
   }

   .lab-progress-bar.blue {
      background: var(--lab-blue);
   }


   /* =========================================================
   RESULT
========================================================= */

   .lab-result {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 11px 0;
      border-bottom: 1px solid #f1f2f5;
   }

   .lab-result:last-child {
      border-bottom: 0;
   }

   .lab-result-icon {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      background: var(--lab-blue-soft);
      color: var(--lab-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .lab-result-info {
      flex: 1;
      min-width: 0;
   }

   .lab-result-name {
      font-size: 11px;
      font-weight: 800;
   }

   .lab-result-meta {
      font-size: 9px;
      color: var(--lab-muted);
      margin-top: 2px;
   }

   .lab-result-status {
      padding: 5px 8px;
      border-radius: 8px;
      font-size: 8px;
      font-weight: 800;
   }


   /* =========================================================
   ALERT
========================================================= */

   .lab-alert {
      display: flex;
      gap: 11px;
      padding: 12px;
      border-radius: 13px;
      margin-bottom: 10px;
   }

   .lab-alert:last-child {
      margin-bottom: 0;
   }

   .lab-alert-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .lab-alert-content {
      flex: 1;
   }

   .lab-alert-title {
      font-size: 11px;
      font-weight: 800;
   }

   .lab-alert-text {
      margin-top: 2px;
      font-size: 9px;
      line-height: 1.5;
   }

   .lab-alert.warning {
      background: var(--lab-orange-soft);
   }

   .lab-alert.warning .lab-alert-icon {
      background: #ffeac2;
      color: var(--lab-orange);
   }

   .lab-alert.warning .lab-alert-text {
      color: #8a5a0a;
   }

   .lab-alert.danger {
      background: var(--lab-red-soft);
   }

   .lab-alert.danger .lab-alert-icon {
      background: #ffdada;
      color: var(--lab-red);
   }

   .lab-alert.danger .lab-alert-text {
      color: #9f2424;
   }

   .lab-alert.info {
      background: var(--lab-blue-soft);
   }

   .lab-alert.info .lab-alert-icon {
      background: #d9efff;
      color: var(--lab-blue);
   }

   .lab-alert.info .lab-alert-text {
      color: #17648f;
   }


   /* =========================================================
   QUICK ACCESS
========================================================= */

   .lab-quick {
      height: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      border: 1px solid var(--lab-border);
      border-radius: 13px;
      text-decoration: none;
      color: var(--lab-text);
      transition: .2s ease;
   }

   .lab-quick:hover {
      color: var(--lab-primary);
      background: #faf9ff;
      border-color: #dcd9ff;
      transform: translateY(-2px);
   }

   .lab-quick-icon {
      width: 40px;
      height: 40px;
      border-radius: 11px;
      background: var(--lab-primary-soft);
      color: var(--lab-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .lab-quick-title {
      font-size: 10px;
      font-weight: 800;
   }

   .lab-quick-desc {
      font-size: 9px;
      color: var(--lab-muted);
      margin-top: 2px;
   }


   /* =========================================================
   RESPONSIVE
========================================================= */

   @media (max-width: 767.98px) {

      .lab-filter {
         padding: 14px;
      }

      .lab-card {
         padding: 15px;
      }

      .lab-kpi-value {
         font-size: 21px;
      }

      .lab-header {
         align-items: flex-start;
         gap: 10px;
      }

      .lab-status {
         white-space: nowrap;
      }
   }
</style>


<div class="lab-dashboard">

   <!-- =====================================================
         FILTER
    ====================================================== -->

   <div class="lab-filter">

      <div class="row g-2 align-items-end">

         <div class="col-xl-3 col-md-6">

            <div class="lab-filter-label">
               Periode
            </div>

            <select
               class="form-select"
               id="labPeriode">

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

            <div class="lab-filter-label">
               Tanggal Mulai
            </div>

            <input
               type="date"
               class="form-control"
               id="labTanggalMulai"
               value="<?= date('Y-m-d') ?>">

         </div>


         <div class="col-xl-3 col-md-6">

            <div class="lab-filter-label">
               Tanggal Selesai
            </div>

            <input
               type="date"
               class="form-control"
               id="labTanggalSelesai"
               value="<?= date('Y-m-d') ?>">

         </div>


         <div class="col-xl-3 col-md-6">

            <button
               type="button"
               class="lab-filter-btn"
               id="btnFilterLab">

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

   <div class="lab-header">

      <div>

         <div class="lab-title">
            Dashboard Laboratorium
         </div>

         <div class="lab-subtitle">
            Monitoring pemeriksaan, spesimen, hasil laboratorium dan validasi
         </div>

      </div>


      <div class="lab-status">

         <span class="lab-status-dot"></span>

         Laboratorium Aktif

      </div>

   </div>


   <!-- =====================================================
         KPI
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- PERMINTAAN -->

      <div class="col-xl-3 col-md-6">

         <div class="lab-kpi">

            <div class="lab-kpi-top">

               <div class="lab-kpi-icon">

                  <iconify-icon
                     icon="solar:test-tube-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="lab-kpi-label">
               Permintaan Pemeriksaan
            </div>

            <div class="lab-kpi-value">
               86
            </div>

            <div class="lab-kpi-sub">
               Pemeriksaan hari ini
            </div>

         </div>

      </div>


      <!-- MENUNGGU -->

      <div class="col-xl-3 col-md-6">

         <div class="lab-kpi">

            <div class="lab-kpi-top">

               <div class="lab-kpi-icon orange">

                  <iconify-icon
                     icon="solar:clock-circle-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="lab-kpi-label">
               Menunggu Pemeriksaan
            </div>

            <div class="lab-kpi-value">
               18
            </div>

            <div class="lab-kpi-sub">
               Spesimen belum diproses
            </div>

         </div>

      </div>


      <!-- PROSES -->

      <div class="col-xl-3 col-md-6">

         <div class="lab-kpi">

            <div class="lab-kpi-top">

               <div class="lab-kpi-icon blue">

                  <iconify-icon
                     icon="solar:refresh-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="lab-kpi-label">
               Sedang Diproses
            </div>

            <div class="lab-kpi-value">
               24
            </div>

            <div class="lab-kpi-sub">
               Pemeriksaan aktif
            </div>

         </div>

      </div>


      <!-- HASIL -->

      <div class="col-xl-3 col-md-6">

         <div class="lab-kpi">

            <div class="lab-kpi-top">

               <div class="lab-kpi-icon green">

                  <iconify-icon
                     icon="solar:check-circle-bold"
                     width="24">
                  </iconify-icon>

               </div>

            </div>


            <div class="lab-kpi-label">
               Hasil Selesai
            </div>

            <div class="lab-kpi-value">
               44
            </div>

            <div class="lab-kpi-sub">
               38 sudah divalidasi
            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         ROW 1
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- ANTREAN PEMERIKSAAN -->

      <div class="col-xl-7">

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Antrean Pemeriksaan
                  </div>

                  <div class="lab-card-subtitle">
                     Permintaan pemeriksaan yang masuk
                  </div>

               </div>

               <a href="#" class="lab-link">
                  Lihat Semua
               </a>

            </div>


            <!-- QUEUE 1 -->

            <div class="lab-queue">

               <div class="lab-queue-number">
                  L-021
               </div>

               <div class="lab-queue-info">

                  <div class="lab-queue-name">
                     Ahmad Fauzan
                  </div>

                  <div class="lab-queue-meta">
                     RM-000128 • Hematologi • Darah
                  </div>

               </div>

               <span class="lab-queue-status lab-waiting">
                  MENUNGGU
               </span>

            </div>


            <!-- QUEUE 2 -->

            <div class="lab-queue">

               <div class="lab-queue-number">
                  L-022
               </div>

               <div class="lab-queue-info">

                  <div class="lab-queue-name">
                     Siti Rahma
                  </div>

                  <div class="lab-queue-meta">
                     RM-000127 • Kimia Klinik • Serum
                  </div>

               </div>

               <span class="lab-queue-status lab-process">
                  PROSES
               </span>

            </div>


            <!-- QUEUE 3 -->

            <div class="lab-queue">

               <div class="lab-queue-number">
                  L-023
               </div>

               <div class="lab-queue-info">

                  <div class="lab-queue-name">
                     Budi Santoso
                  </div>

                  <div class="lab-queue-meta">
                     RM-000126 • Urinalisa • Urine
                  </div>

               </div>

               <span class="lab-queue-status lab-process">
                  PROSES
               </span>

            </div>


            <!-- QUEUE 4 -->

            <div class="lab-queue">

               <div class="lab-queue-number">
                  L-024
               </div>

               <div class="lab-queue-info">

                  <div class="lab-queue-name">
                     Nur Aisyah
                  </div>

                  <div class="lab-queue-meta">
                     RM-000125 • Hematologi • Darah
                  </div>

               </div>

               <span class="lab-queue-status lab-ready">
                  SELESAI
               </span>

            </div>


            <!-- QUEUE 5 -->

            <div class="lab-queue">

               <div class="lab-queue-number">
                  L-025
               </div>

               <div class="lab-queue-info">

                  <div class="lab-queue-name">
                     Dedi Irawan
                  </div>

                  <div class="lab-queue-meta">
                     RM-000124 • Imunologi • Serum
                  </div>

               </div>

               <span class="lab-queue-status lab-critical">
                  PRIORITAS
               </span>

            </div>

         </div>

      </div>


      <!-- PROGRESS -->

      <div class="col-xl-5">

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Progress Pemeriksaan
                  </div>

                  <div class="lab-card-subtitle">
                     Status proses laboratorium
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Penerimaan Spesimen
                  </span>

                  <span class="lab-process-value">
                     94%
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar green"
                     style="width:94%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Pemeriksaan
                  </span>

                  <span class="lab-process-value">
                     78%
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar"
                     style="width:78%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Validasi Hasil
                  </span>

                  <span class="lab-process-value">
                     86%
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar blue"
                     style="width:86%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Hasil Terkirim
                  </span>

                  <span class="lab-process-value">
                     91%
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar green"
                     style="width:91%;">
                  </div>

               </div>

            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         ROW 2
    ====================================================== -->

   <div class="row g-3 mb-3">


      <!-- HASIL TERBARU -->

      <div class="col-xl-7">

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Hasil Laboratorium Terbaru
                  </div>

                  <div class="lab-card-subtitle">
                     Hasil pemeriksaan yang baru selesai
                  </div>

               </div>

               <a href="#" class="lab-link">
                  Lihat Semua
               </a>

            </div>


            <div class="lab-result">

               <div class="lab-result-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="lab-result-info">

                  <div class="lab-result-name">
                     Ahmad Fauzan
                  </div>

                  <div class="lab-result-meta">
                     Hemoglobin • 13.8 g/dL • 08:42
                  </div>

               </div>

               <span
                  class="lab-result-status lab-ready">
                  VALID
               </span>

            </div>


            <div class="lab-result">

               <div class="lab-result-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="lab-result-info">

                  <div class="lab-result-name">
                     Siti Rahma
                  </div>

                  <div class="lab-result-meta">
                     Glukosa Darah • 126 mg/dL • 08:37
                  </div>

               </div>

               <span
                  class="lab-result-status lab-ready">
                  VALID
               </span>

            </div>


            <div class="lab-result">

               <div class="lab-result-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="lab-result-info">

                  <div class="lab-result-name">
                     Budi Santoso
                  </div>

                  <div class="lab-result-meta">
                     Leukosit • 12.500 /µL • 08:30
                  </div>

               </div>

               <span
                  class="lab-result-status lab-critical">
                  ABNORMAL
               </span>

            </div>


            <div class="lab-result">

               <div class="lab-result-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="19">
                  </iconify-icon>

               </div>

               <div class="lab-result-info">

                  <div class="lab-result-name">
                     Nur Aisyah
                  </div>

                  <div class="lab-result-meta">
                     Kreatinin • 0.9 mg/dL • 08:24
                  </div>

               </div>

               <span
                  class="lab-result-status lab-ready">
                  VALID
               </span>

            </div>

         </div>

      </div>


      <!-- JENIS PEMERIKSAAN -->

      <div class="col-xl-5">

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Jenis Pemeriksaan
                  </div>

                  <div class="lab-card-subtitle">
                     Distribusi pemeriksaan hari ini
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Hematologi
                  </span>

                  <span class="lab-process-value">
                     32
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar"
                     style="width:72%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Kimia Klinik
                  </span>

                  <span class="lab-process-value">
                     24
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar blue"
                     style="width:58%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Urinalisa
                  </span>

                  <span class="lab-process-value">
                     14
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar green"
                     style="width:36%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Imunologi
                  </span>

                  <span class="lab-process-value">
                     9
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar orange"
                     style="width:23%;">
                  </div>

               </div>

            </div>


            <div class="lab-process-item">

               <div class="lab-process-top">

                  <span class="lab-process-name">
                     Lainnya
                  </span>

                  <span class="lab-process-value">
                     7
                  </span>

               </div>

               <div class="lab-progress">

                  <div
                     class="lab-progress-bar"
                     style="width:18%;">
                  </div>

               </div>

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

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Perlu Perhatian
                  </div>

                  <div class="lab-card-subtitle">
                     Pemeriksaan dan hasil yang membutuhkan tindak lanjut
                  </div>

               </div>

            </div>


            <div class="lab-alert danger">

               <div class="lab-alert-icon">

                  <iconify-icon
                     icon="solar:danger-triangle-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="lab-alert-content">

                  <div class="lab-alert-title">
                     Hasil Abnormal
                  </div>

                  <div class="lab-alert-text">
                     Terdapat 3 hasil pemeriksaan dengan nilai di luar reference range.
                  </div>

               </div>

            </div>


            <div class="lab-alert warning">

               <div class="lab-alert-icon">

                  <iconify-icon
                     icon="solar:clock-circle-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="lab-alert-content">

                  <div class="lab-alert-title">
                     Spesimen Menunggu
                  </div>

                  <div class="lab-alert-text">
                     18 spesimen belum masuk ke proses pemeriksaan.
                  </div>

               </div>

            </div>


            <div class="lab-alert info">

               <div class="lab-alert-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="18">
                  </iconify-icon>

               </div>

               <div class="lab-alert-content">

                  <div class="lab-alert-title">
                     Hasil Belum Divalidasi
                  </div>

                  <div class="lab-alert-text">
                     Terdapat 6 hasil pemeriksaan yang masih menunggu validasi analis.
                  </div>

               </div>

            </div>

         </div>

      </div>


      <!-- STATISTIK -->

      <div class="col-xl-5">

         <div class="lab-card">

            <div class="lab-card-header">

               <div>

                  <div class="lab-card-title">
                     Ringkasan Laboratorium
                  </div>

                  <div class="lab-card-subtitle">
                     Statistik pelayanan hari ini
                  </div>

               </div>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:11px 0;
                    border-bottom:1px solid var(--lab-border);">

               <span style="
                        font-size:10px;
                        color:var(--lab-muted);">
                  Turn Around Time
               </span>

               <strong style="font-size:11px;">
                  47 Menit
               </strong>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:11px 0;
                    border-bottom:1px solid var(--lab-border);">

               <span style="
                        font-size:10px;
                        color:var(--lab-muted);">
                  Validasi Hasil
               </span>

               <strong style="
                        font-size:11px;
                        color:var(--lab-green);">
                  86%
               </strong>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:11px 0;
                    border-bottom:1px solid var(--lab-border);">

               <span style="
                        font-size:10px;
                        color:var(--lab-muted);">
                  Hasil Abnormal
               </span>

               <strong style="
                        font-size:11px;
                        color:var(--lab-red);">
                  3
               </strong>

            </div>


            <div style="
                    display:flex;
                    justify-content:space-between;
                    padding:11px 0;">

               <span style="
                        font-size:10px;
                        color:var(--lab-muted);">
                  Pemeriksaan Selesai
               </span>

               <strong style="font-size:11px;">
                  44
               </strong>

            </div>

         </div>

      </div>

   </div>


   <!-- =====================================================
         QUICK ACCESS
    ====================================================== -->

   <div class="lab-card mb-3">

      <div class="lab-card-header">

         <div>

            <div class="lab-card-title">
               Akses Cepat Laboratorium
            </div>

            <div class="lab-card-subtitle">
               Menu utama pelayanan laboratorium
            </div>

         </div>

      </div>


      <div class="row g-2">


         <!-- PERMINTAAN -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:clipboard-list-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Permintaan
                  </div>

                  <div class="lab-quick-desc">
                     Pemeriksaan
                  </div>

               </div>

            </a>

         </div>


         <!-- SPESIMEN -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:test-tube-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Spesimen
                  </div>

                  <div class="lab-quick-desc">
                     Penerimaan
                  </div>

               </div>

            </a>

         </div>


         <!-- PEMERIKSAAN -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:flask-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Pemeriksaan
                  </div>

                  <div class="lab-quick-desc">
                     Proses lab
                  </div>

               </div>

            </a>

         </div>


         <!-- HASIL -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:document-text-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Hasil
                  </div>

                  <div class="lab-quick-desc">
                     Pemeriksaan
                  </div>

               </div>

            </a>

         </div>


         <!-- VALIDASI -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:check-circle-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Validasi
                  </div>

                  <div class="lab-quick-desc">
                     Hasil lab
                  </div>

               </div>

            </a>

         </div>


         <!-- LAPORAN -->

         <div class="col-xl-2 col-md-4 col-6">

            <a href="#" class="lab-quick">

               <div class="lab-quick-icon">

                  <iconify-icon
                     icon="solar:chart-2-bold"
                     width="20">
                  </iconify-icon>

               </div>

               <div>

                  <div class="lab-quick-title">
                     Laporan
                  </div>

                  <div class="lab-quick-desc">
                     Laboratorium
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
         document.getElementById("labPeriode");

      const tanggalMulai =
         document.getElementById("labTanggalMulai");

      const tanggalSelesai =
         document.getElementById("labTanggalSelesai");

      const btnFilter =
         document.getElementById("btnFilterLab");


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
            "Filter Laboratorium:",
            mulai,
            "sampai",
            selesai
         );

      });

   });
</script>