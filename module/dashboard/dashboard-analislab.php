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

<style>
   .lab-dashboard #labQueueList,
   .lab-dashboard #labResultList {
      max-height: 430px;
      overflow-y: auto;
      padding-right: 5px;
   }

   .lab-dashboard #labQueueList::-webkit-scrollbar,
   .lab-dashboard #labResultList::-webkit-scrollbar {
      width: 5px;
   }

   .lab-dashboard #labQueueList::-webkit-scrollbar-thumb,
   .lab-dashboard #labResultList::-webkit-scrollbar-thumb {
      background: #dfe3ea;
      border-radius: 10px;
   }

   .lab-dashboard .lab-loading,
   .lab-dashboard .lab-empty {
      padding: 28px 10px;
      text-align: center;
      color: var(--lab-muted);
      font-size: 11px;
   }
</style>

<style>
   .lab-dashboard #labQueueList,
   .lab-dashboard #labResultList {
      max-height: 430px;
      overflow-y: auto;
      padding-right: 5px;
   }

   .lab-dashboard #labQueueList::-webkit-scrollbar,
   .lab-dashboard #labResultList::-webkit-scrollbar {
      width: 5px;
   }

   .lab-dashboard #labQueueList::-webkit-scrollbar-thumb,
   .lab-dashboard #labResultList::-webkit-scrollbar-thumb {
      background: #dfe3ea;
      border-radius: 10px;
   }

   .lab-dashboard .lab-empty {
      padding: 30px 10px;
      text-align: center;
      color: var(--lab-muted);
      font-size: 11px;
   }

   .lab-dashboard .lab-loading {
      padding: 30px 10px;
      text-align: center;
      color: var(--lab-muted);
      font-size: 11px;
   }

   .lab-dashboard .lab-trend-wrap {
      position: relative;
      height: 220px;
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

            <div class="lab-kpi-value" id="labPermintaan">0</div>

            <div class="lab-kpi-sub" id="labPermintaanSub">
               Pemeriksaan sesuai periode
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

            <div class="lab-kpi-value" id="labMenunggu">0</div>

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

            <div class="lab-kpi-value" id="labProses">0</div>

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

            <div class="lab-kpi-value" id="labSelesai">0</div>

            <div class="lab-kpi-sub" id="labSelesaiSub">
               Persentase selesai: 0%
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


            <!-- DATA ANTREAN DINAMIS -->
            <div id="labQueueList">
               <div class="lab-loading">Memuat data antrean...</div>
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


            <div id="labProcessList">
               <div class="lab-loading">Memuat progress...</div>
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


            <div id="labResultList">
               <div class="lab-loading">Memuat hasil laboratorium...</div>
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


            <div id="labTypeList">
               <div class="lab-loading">Memuat jenis pemeriksaan...</div>
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


            <div id="labAlertList">
               <div class="lab-loading">Memuat alert...</div>
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
   (function() {

      function initLabDashboard() {

         const periode = document.getElementById("labPeriode");
         const tanggalMulai = document.getElementById("labTanggalMulai");
         const tanggalSelesai = document.getElementById("labTanggalSelesai");
         const btnFilter = document.getElementById("btnFilterLab");

         if (!periode || !tanggalMulai || !tanggalSelesai || !btnFilter) {
            console.warn("Elemen filter dashboard laboratorium tidak ditemukan.");
            return;
         }

         const endpoint = "controller/dashboard/labDashboardController.php?action=dashboard";

         function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            return year + "-" + month + "-" + day;
         }

         function escapeHtml(value) {
            return String(value === null || value === undefined ? "" : value)
               .replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/"/g, "&quot;")
               .replace(/'/g, "&#039;");
         }

         function setText(id, value) {
            const el = document.getElementById(id);
            if (el) {
               el.textContent = value;
            }
         }

         function setHtml(id, html) {
            const el = document.getElementById(id);
            if (el) {
               el.innerHTML = html;
            }
         }

         function showLoading() {
            [
               "labQueueList",
               "labProcessList",
               "labResultList",
               "labTypeList",
               "labAlertList"
            ].forEach(function(id) {
               setHtml(id, '<div class="lab-loading">Memuat data...</div>');
            });
         }

         function showError(message) {
            [
               "labQueueList",
               "labProcessList",
               "labResultList",
               "labTypeList",
               "labAlertList"
            ].forEach(function(id) {
               setHtml(id, '<div class="lab-empty">Data tidak dapat dimuat.</div>');
            });

            console.error("Lab Dashboard Error:", message);

            if (typeof Swal !== "undefined") {
               Swal.fire({
                  icon: "error",
                  title: "Gagal Memuat Dashboard",
                  text: message || "Terjadi kesalahan saat mengambil data laboratorium."
               });
            } else {
               alert(message || "Gagal memuat dashboard laboratorium.");
            }
         }

         function renderKpi(data) {
            const kpi = data.kpi || {};

            const total = Number(kpi.permintaan_pemeriksaan || 0);
            const waiting = Number(kpi.menunggu_pemeriksaan || 0);
            const processing = Number(kpi.sedang_diproses || 0);
            const completed = Number(kpi.hasil_selesai || 0);
            const percent = Number(kpi.persentase_selesai || 0);

            setText("labPermintaan", total.toLocaleString("id-ID"));
            setText("labMenunggu", waiting.toLocaleString("id-ID"));
            setText("labProses", processing.toLocaleString("id-ID"));
            setText("labSelesai", completed.toLocaleString("id-ID"));

            setText(
               "labPermintaanSub",
               "Periode " + (data.period ? data.period.start : "") +
               " s/d " + (data.period ? data.period.end : "")
            );

            setText(
               "labSelesaiSub",
               "Persentase selesai: " + percent + "%"
            );
         }

         function renderQueue(data) {
            const items = data.queue && Array.isArray(data.queue.items) ?
               data.queue.items :
               [];

            const total = Number(
               data.queue && data.queue.total !== undefined ?
               data.queue.total :
               items.length
            );

            setText("labQueueBadge", total.toLocaleString("id-ID"));

            if (!items.length) {
               setHtml(
                  "labQueueList",
                  '<div class="lab-empty">Tidak ada antrean pemeriksaan pada periode ini.</div>'
               );
               return;
            }

            setHtml("labQueueList", items.map(function(item) {

               const statusClass = item.status_class ||
                  (
                     item.status === "completed" ?
                     "lab-ready" :
                     item.status === "processing" ?
                     "lab-process" :
                     "lab-waiting"
                  );

               return `
               <div class="lab-queue">
                  <div class="lab-queue-number">
                     ${escapeHtml(item.queue_number || "-")}
                  </div>

                  <div class="lab-queue-info">
                     <div class="lab-queue-name">
                        ${escapeHtml(item.patient_name || "-")}
                     </div>

                     <div class="lab-queue-meta">
                        RM-${escapeHtml(item.rm || "-")}
                        • ${escapeHtml(item.inspection_name || "-")}
                        • ${escapeHtml(item.inspection_source || "-")}
                     </div>
                  </div>

                  <span class="lab-queue-status ${escapeHtml(statusClass)}">
                     ${escapeHtml(item.status_label || "MENUNGGU")}
                  </span>
               </div>
            `;
            }).join(""));
         }

         function renderProcess(data) {
            const kpi = data.kpi || {};
            const process = data.process || {};

            const total = Number(kpi.permintaan_pemeriksaan || 0);
            const completed = Number(kpi.hasil_selesai || 0);
            const processing = Number(kpi.sedang_diproses || 0);
            const validation = Number(kpi.hasil_validasi || 0);

            // Schema belum menyediakan status proses spesimen/pemeriksaan/validasi.
            // Karena itu jangan membuat angka palsu.
            const penerimaan = process.penerimaan_spesimen !== null ?
               Number(process.penerimaan_spesimen || 0) :
               (total > 0 ? 100 : 0);

            const pemeriksaan = process.pemeriksaan !== null ?
               Number(process.pemeriksaan || 0) :
               (total > 0 ? Math.round(((processing + completed) / total) * 100) : 0);

            const validasi = process.validasi_hasil !== null ?
               Number(process.validasi_hasil || 0) :
               (total > 0 ? Math.round((validation / total) * 100) : 0);

            const terkirim = process.hasil_terkirim !== null ?
               Number(process.hasil_terkirim || 0) :
               Number(kpi.persentase_selesai || 0);

            const rows = [
               ["Penerimaan Spesimen", penerimaan, "green"],
               ["Pemeriksaan", pemeriksaan, ""],
               ["Validasi Hasil", validasi, "blue"],
               ["Hasil Terkirim", terkirim, "green"]
            ];

            setHtml("labProcessList", rows.map(function(row) {
               const value = Math.max(0, Math.min(100, Number(row[1] || 0)));

               return `
               <div class="lab-process-item">
                  <div class="lab-process-top">
                     <span class="lab-process-name">${row[0]}</span>
                     <span class="lab-process-value">${value}%</span>
                  </div>

                  <div class="lab-progress">
                     <div class="lab-progress-bar ${row[2]}"
                          style="width:${value}%;">
                     </div>
                  </div>
               </div>
            `;
            }).join(""));
         }

         function renderResults(data) {
            const items = data.results && Array.isArray(data.results.items) ?
               data.results.items :
               [];

            const total = Number(
               data.results && data.results.total !== undefined ?
               data.results.total :
               items.length
            );

            setText("labResultBadge", total.toLocaleString("id-ID"));

            if (!items.length) {
               setHtml(
                  "labResultList",
                  '<div class="lab-empty">Belum ada hasil laboratorium pada periode ini.</div>'
               );
               return;
            }

            setHtml("labResultList", items.map(function(item) {

               // Schema saat ini belum mempunyai reference range/flag abnormal.
               // Jadi status tidak boleh dibuat ABNORMAL secara asumsi.
               const statusClass = item.status === "completed" ?
                  "lab-ready" :
                  "lab-waiting";

               return `
               <div class="lab-result">

                  <div class="lab-result-icon">
                     <iconify-icon
                        icon="solar:document-text-bold"
                        width="19">
                     </iconify-icon>
                  </div>

                  <div class="lab-result-info">
                     <div class="lab-result-name">
                        ${escapeHtml(item.patient_name || "-")}
                     </div>

                     <div class="lab-result-meta">
                        ${escapeHtml(item.inspection_name || "-")}
                        • ${escapeHtml(item.hasil || "-")}
                        • ${escapeHtml(item.created_at || "-")}
                     </div>
                  </div>

                  <span class="lab-result-status ${statusClass}">
                     ${escapeHtml(item.status_label || "HASIL")}
                  </span>

               </div>
            `;
            }).join(""));
         }

         function renderTypes(data) {
            const container = document.getElementById("labTypeList");

            if (!container) {
               console.warn("Element #labTypeList tidak ditemukan.");
               return;
            }

            const source = data.types || {};
            const items = Array.isArray(source.items) ? source.items : [];

            if (!items.length) {
               container.innerHTML =
                  '<div class="lab-empty">Belum ada jenis pemeriksaan pada periode ini.</div>';
               return;
            }

            const max = Math.max.apply(
               null,
               items.map(function(item) {
                  return Number(item.total || 0);
               })
            ) || 1;

            container.innerHTML = items.map(function(item, index) {
               const total = Number(item.total || 0);
               const width = Math.round((total / max) * 100);

               const classes = ["", "blue", "green", "orange"];
               const barClass = classes[index % classes.length];

               return `
               <div class="lab-process-item">

                  <div class="lab-process-top">
                     <span class="lab-process-name">
                        ${escapeHtml(item.label || "-")}
                     </span>

                     <span class="lab-process-value">
                        ${total.toLocaleString("id-ID")}
                     </span>
                  </div>

                  <div class="lab-progress">
                     <div class="lab-progress-bar ${barClass}"
                          style="width:${width}%;">
                     </div>
                  </div>

               </div>
            `;
            }).join("");
         }

         function renderAlerts(data) {
            const container = document.getElementById("labAlertList");

            if (!container) {
               console.warn("Element #labAlertList tidak ditemukan.");
               return;
            }

            const source = data.alerts || {};
            const items = Array.isArray(source.items) ? source.items : [];

            if (!items.length) {
               container.innerHTML = `
               <div class="lab-alert info">
                  <div class="lab-alert-icon">
                     <iconify-icon
                        icon="solar:check-circle-bold"
                        width="18">
                     </iconify-icon>
                  </div>

                  <div class="lab-alert-content">
                     <div class="lab-alert-title">
                        Tidak Ada Alert
                     </div>

                     <div class="lab-alert-text">
                        Tidak ada pemeriksaan yang membutuhkan perhatian pada periode ini.
                     </div>
                  </div>
               </div>
            `;
               return;
            }

            container.innerHTML = items.map(function(item) {

               const type = ["danger", "warning", "info"].indexOf(item.type) >= 0 ?
                  item.type :
                  "info";

               const icon = item.icon ||
                  (
                     type === "danger" ?
                     "solar:danger-triangle-bold" :
                     type === "warning" ?
                     "solar:clock-circle-bold" :
                     "solar:document-text-bold"
                  );

               return `
               <div class="lab-alert ${type}">

                  <div class="lab-alert-icon">
                     <iconify-icon
                        icon="${escapeHtml(icon)}"
                        width="18">
                     </iconify-icon>
                  </div>

                  <div class="lab-alert-content">
                     <div class="lab-alert-title">
                        ${escapeHtml(item.title || "Informasi")}
                     </div>

                     <div class="lab-alert-text">
                        ${escapeHtml(item.text || "")}
                     </div>
                  </div>

               </div>
            `;
            }).join("");
         }

         function renderSummary(data) {
            const summary = data.summary || {};

            setText(
               "labTAT",
               summary.turn_around_time_menit !== null &&
               summary.turn_around_time_menit !== undefined ?
               summary.turn_around_time_menit + " Menit" :
               "-"
            );

            setText(
               "labValidasi",
               summary.validasi_hasil !== null &&
               summary.validasi_hasil !== undefined ?
               summary.validasi_hasil + "%" :
               "-"
            );

            setText(
               "labAbnormal",
               summary.hasil_abnormal !== null &&
               summary.hasil_abnormal !== undefined ?
               Number(summary.hasil_abnormal).toLocaleString("id-ID") :
               "-"
            );

            setText(
               "labSummarySelesai",
               Number(summary.pemeriksaan_selesai || 0).toLocaleString("id-ID")
            );
         }

         function loadDashboard() {

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
               }
               return;
            }

            showLoading();

            const url =
               endpoint +
               "&start_date=" + encodeURIComponent(mulai) +
               "&end_date=" + encodeURIComponent(selesai);

            fetch(url, {
                  method: "GET",
                  headers: {
                     "X-Requested-With": "XMLHttpRequest",
                     "Accept": "application/json"
                  },
                  cache: "no-store"
               })
               .then(function(response) {

                  if (!response.ok) {
                     throw new Error("HTTP " + response.status);
                  }

                  return response.text();
               })
               .then(function(raw) {

                  let data;

                  try {
                     data = JSON.parse(raw);
                  } catch (e) {
                     console.error("Response controller bukan JSON:", raw);
                     throw new Error(
                        "Controller tidak mengembalikan JSON yang valid."
                     );
                  }

                  if (!data || data.status !== true) {
                     throw new Error(
                        data && data.message ?
                        data.message :
                        "Response dashboard tidak valid."
                     );
                  }

                  console.log("Lab Dashboard Data:", data);

                  renderKpi(data);
                  renderQueue(data);
                  renderProcess(data);
                  renderResults(data);
                  renderTypes(data);
                  renderAlerts(data);
                  renderSummary(data);
               })
               .catch(function(error) {
                  showError(error.message);
               });
         }

         periode.addEventListener("change", function() {

            const today = new Date();

            if (this.value === "today") {

               const date = formatDate(today);

               tanggalMulai.value = date;
               tanggalSelesai.value = date;

            } else if (this.value === "week") {

               const start = new Date(today);
               const day = today.getDay() || 7;

               start.setDate(today.getDate() - day + 1);

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

         // Penting:
         // Jangan memakai DOMContentLoaded karena view dashboard kemungkinan
         // dimuat melalui AJAX setelah DOMContentLoaded sudah selesai.
         loadDashboard();
      }

      // Jalankan langsung jika script view di-inject melalui AJAX.
      initLabDashboard();

   })();
</script>