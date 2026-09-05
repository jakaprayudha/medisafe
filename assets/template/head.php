<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Medisafe | <?= $title ?>
</title>
<link rel="shortcut icon" type="image/png" href="assets/images/logos/icon_medisafe.png" />
<link rel="stylesheet" href="assets/css/styles.min.css" />
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
<!-- Tambahkan Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- CSS Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<!-- <script src="assets/js/sweet-alert/sweetalert.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (wajib untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom Calender -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Icon Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>



<style>
   .dataTables_wrapper {
      width: 100% !important;
   }

   table.dataTable {
      width: 100% !important;
   }
</style>
<style>
   /* Biar Select2 sama tinggi dengan form input lainnya */
   .select2-container--default .select2-selection--single {
      height: calc(2.25rem + 2px);
      /* sama dengan Bootstrap's .form-control */
      padding: 0.375rem 0.75rem;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
   }

   .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: normal;
   }

   .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 100%;
      top: 0.3rem;
      right: 0.75rem;
   }

   /* Pastikan lebarnya 100% agar sama dengan form input lainnya */
   .select2-container {
      width: 100% !important;
   }

   .container-fluid {
      max-width: 1400px;
      margin: 0 auto;
   }

   .page-wrapper {
      overflow-x: hidden;
   }

   /* styling untuk label yang required */
   .form-label.required::after {
      content: " *";
      color: red;
   }
</style>
<script type="text/javascript">
   var koneksiinternet = 0;
   setInterval(function() {
      if (koneksiinternet == 0 && navigator.onLine == 0) {
         koneksiinternet = 1
         Swal.fire({
            title: "Offline",
            text: "Koneksi Terputus. Periksa Sambungan Internet Anda",
            icon: "info",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ok"
         }).then((result) => {
            koneksiinternet = 0;
         });
      }
   }, 5000);
</script>

<style>
   /* Biar select2 di modal Bootstrap selalu full */
   .select2-container {
      width: 100% !important;
   }

   .select2-selection {
      height: calc(2.25rem + 2px) !important;
      /* biar sejajar sama form-control bootstrap */
      padding: 0.375rem 0.75rem !important;
      font-size: 1rem;
      border: 1px solid #ced4da !important;
      border-radius: 0.375rem !important;
   }
</style>

<style>
   /* SUBMENU */
   .collapse-menu {
      max-height: 0;
      overflow: hidden;
      transition: all 0.3s ease;
      background: transparent;
      border-radius: 10px;
   }

   /* OPEN STATE */
   .sidebar-item.open .collapse-menu {
      background: #f1f5f9;
      margin-top: 6px;
      padding: 6px 0;
   }

   /* ITEM */
   .collapse-menu li a {
      display: flex;
      align-items: center;
      padding: 8px 14px;
      font-size: 13px;
      color: #374151;
      border-radius: 8px;
      margin: 2px 8px;
      transition: 0.2s;
      position: relative;
   }

   /* DOT */
   .collapse-menu li a::before {
      content: "";
      width: 5px;
      height: 5px;
      background: #9ca3af;
      border-radius: 50%;
      margin-right: 10px;
      transition: 0.2s;
   }

   /* HOVER */
   .collapse-menu li a:hover {
      background: #e0f2fe;
      color: #0f9b8e;
   }

   .collapse-menu li a:hover::before {
      background: #0f9b8e;
   }

   /* ACTIVE */
   .collapse-menu li a.active {
      background: #0f9b8e;
      color: white;
      font-weight: 600;
   }

   .collapse-menu li a.active::before {
      background: white;
   }

   /* ARROW */
   .arrow {
      margin-left: auto;
      transition: 0.3s;
   }

   .sidebar-item.open .arrow {
      transform: rotate(180deg);
   }

   /* PARENT ACTIVE */
   .sidebar-item.open>.sidebar-link {
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      color: white;
      border-radius: 10px;
   }

   .switch {
      position: relative;
      display: inline-block;
      width: 40px;
      height: 20px;
   }

   .switch input {
      display: none;
   }

   .slider {
      position: absolute;
      cursor: pointer;
      background-color: #ccc;
      border-radius: 20px;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      transition: .3s;
   }

   .slider:before {
      position: absolute;
      content: "";
      height: 14px;
      width: 14px;
      left: 3px;
      bottom: 3px;
      background: white;
      border-radius: 50%;
      transition: .3s;
   }

   input:checked+.slider {
      background-color: #22c55e;
   }

   input:checked+.slider:before {
      transform: translateX(18px);
   }

   /* =========================================================
   PEMERIKSAAN ACCORDION
========================================================= */

   .pemeriksaan-accordion {
      border-radius: 16px;
      overflow: hidden;
   }

   .pemeriksaan-accordion .accordion-item {
      border: 1px solid #edf0f5;
      margin-bottom: 10px;
      border-radius: 14px !important;
      overflow: hidden;
      background: #fff;
   }

   .pemeriksaan-accordion .accordion-button {
      min-height: 68px;
      padding: 12px 18px;
      background: #fff;
      color: #273444;
      box-shadow: none !important;
      gap: 12px;
   }

   .pemeriksaan-accordion .accordion-button:not(.collapsed) {
      background: #faf9ff;
      color: #273444;
   }

   .pemeriksaan-accordion .accordion-button::after {
      width: 14px;
      height: 14px;
      background-size: 14px;
   }

   .pemeriksaan-accordion .accordion-button>span:last-of-type {
      display: flex;
      flex-direction: column;
      gap: 2px;
   }

   .pemeriksaan-accordion .accordion-button strong {
      font-size: 13px;
      font-weight: 800;
   }

   .pemeriksaan-accordion .accordion-button small {
      font-size: 10px;
      color: #8a94a6;
      font-weight: 400;
   }

   .accordion-icon {
      width: 40px;
      height: 40px;
      min-width: 40px;
      border-radius: 11px;

      display: flex;
      align-items: center;
      justify-content: center;

      background: #eeecff;
      color: #635bff;

      font-size: 18px;
   }

   .accordion-icon.screening {
      background: #eef7ff;
      color: #1687d9;
   }

   .accordion-icon.perawat {
      background: #eaf8ef;
      color: #16a34a;
   }

   .accordion-icon.dokter {
      background: #fff5e7;
      color: #d97706;
   }

   .accordion-icon.diagnosa {
      background: #f3efff;
      color: #7c3aed;
   }

   .accordion-icon.farmasi {
      background: #fff0f0;
      color: #dc2626;
   }

   .accordion-icon.pulang {
      background: #eef7ff;
      color: #2563eb;
   }

   .pemeriksaan-accordion .accordion-body {
      padding: 20px;
      border-top: 1px solid #edf0f5;
   }

   .section-description {
      display: flex;
      align-items: center;
      gap: 8px;

      padding: 10px 12px;
      margin-bottom: 18px;

      border-radius: 10px;
      background: #f8f9fc;

      color: #6f7887;
      font-size: 10px;
   }

   .section-description i {
      color: #635bff;
      font-size: 14px;
   }

   .medical-subtitle {
      display: flex;
      align-items: center;
      gap: 8px;

      margin-bottom: 12px;

      font-size: 12px;
      font-weight: 800;
      color: #273444;
   }

   .medical-subtitle i {
      color: #635bff;
   }

   .pemeriksaan-accordion .form-label {
      font-size: 11px;
      font-weight: 600;
      color: #4b5563;
      margin-bottom: 5px;
   }

   .pemeriksaan-accordion .form-control,
   .pemeriksaan-accordion .form-select {
      border-radius: 10px;
      border-color: #e5e7eb;
      font-size: 12px;
      min-height: 39px;
      box-shadow: none;
   }

   .pemeriksaan-accordion textarea.form-control {
      min-height: auto;
   }

   .pemeriksaan-accordion .form-control:focus,
   .pemeriksaan-accordion .form-select:focus {
      border-color: #635bff;
      box-shadow: 0 0 0 3px rgba(99, 91, 255, .08);
   }

   .pemeriksaan-accordion .input-group-text {
      border-color: #e5e7eb;
      background: #f8f9fc;
      color: #7b8494;
      font-size: 10px;
      border-radius: 0 10px 10px 0;
   }

   .farmasi-placeholder {
      display: flex;
      align-items: center;
      gap: 14px;

      padding: 18px;

      border: 1px dashed #dcd9ff;
      border-radius: 13px;

      background: #faf9ff;
   }

   .farmasi-placeholder-icon {
      width: 45px;
      height: 45px;
      min-width: 45px;

      border-radius: 12px;

      display: flex;
      align-items: center;
      justify-content: center;

      background: #eeecff;
      color: #635bff;

      font-size: 20px;
   }

   .farmasi-placeholder strong {
      font-size: 12px;
      font-weight: 800;
   }

   .farmasi-placeholder p {
      margin: 3px 0 0;
      color: #7b8494;
      font-size: 10px;
   }

   #simpan_pemeriksaan {
      min-height: 44px;
      border-radius: 11px;
      font-size: 12px;
      font-weight: 700;
   }

   @media (max-width: 767px) {

      .pemeriksaan-accordion .accordion-button {
         padding: 11px 13px;
      }

      .pemeriksaan-accordion .accordion-body {
         padding: 15px;
      }

      .accordion-icon {
         width: 36px;
         height: 36px;
         min-width: 36px;
         font-size: 16px;
      }

   }

   /* =====================================================
   ACCORDION SECTION COLORS
===================================================== */

   /* 🦷 ODONTOGRAM */
   .accordion-icon.odontogram {
      background: #f0e7ff;
      color: #7c3aed;
   }

   /* 💊 FARMASI */
   .accordion-icon.farmasi {
      background: #e7f8ef;
      color: #198754;
   }

   /* 💊 RESEP LUAR */
   .accordion-icon.resep-luar {
      background: #e5f7f7;
      color: #0f8b8d;
   }

   /* 🧪 LABORATORIUM */
   .accordion-icon.lab {
      background: #e7f1ff;
      color: #2563eb;
   }

   /* 💉 VAKSIN */
   .accordion-icon.vaksin {
      background: #fff1df;
      color: #ea7a00;
   }

   /* 🩺 TINDAKAN */
   .accordion-icon.tindakan {
      background: #ffe8e8;
      color: #dc3545;
   }

   /* 📋 RIWAYAT */
   .accordion-icon.riwayat {
      background: #ebe9ff;
      color: #5b5bd6;
   }

   /* 🏥 RAWAT INAP */
   .accordion-icon.rawat-inap {
      background: #e5f6fb;
      color: #0891b2;
   }

   .accordion-item:has(#collapseOdontogram.show) {
      border-color: #c4b5fd !important;
   }

   .accordion-item:has(#collapseFarmasi.show) {
      border-color: #86efac !important;
   }

   .accordion-item:has(#collapseResepLuar.show) {
      border-color: #67e8f9 !important;
   }

   .accordion-item:has(#collapseLab.show) {
      border-color: #93c5fd !important;
   }

   .accordion-item:has(#collapseVaksin.show) {
      border-color: #fdba74 !important;
   }

   .accordion-item:has(#collapseTindakan.show) {
      border-color: #fca5a5 !important;
   }

   .accordion-item:has(#collapseRiwayatPengobatan.show) {
      border-color: #a5b4fc !important;
   }

   .accordion-item:has(#collapseRawatInap.show) {
      border-color: #67e8f9 !important;
   }

   /* =====================================================
   SECTION PLACEHOLDER
===================================================== */

   .section-placeholder {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 18px;
      border: 1px dashed #dfe3eb;
      border-radius: 14px;
      background: #fafbfc;
   }

   .section-placeholder-icon {
      width: 48px;
      height: 48px;
      min-width: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      background: #f0efff;
      color: #635bff;
      font-size: 22px;
   }

   .section-placeholder strong {
      display: block;
      color: #273444;
      margin-bottom: 4px;
   }

   .section-placeholder p {
      color: #7b8494;
      font-size: 14px;
   }


   /* =====================================================
   FARMASI
===================================================== */

   .farmasi-placeholder {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 18px;
      margin-bottom: 18px;
      border: 1px dashed #dfe3eb;
      border-radius: 14px;
      background: #fafbfc;
   }

   .farmasi-placeholder-icon {
      width: 48px;
      height: 48px;
      min-width: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      background: #eefbf3;
      color: #198754;
      font-size: 22px;
   }

   .farmasi-placeholder strong {
      display: block;
      color: #273444;
      margin-bottom: 4px;
   }

   .farmasi-placeholder p {
      margin: 0;
      color: #7b8494;
      font-size: 14px;
   }

   /* =========================================================
   LOG UPDATE
========================================================= */

   .log-update-btn {
      position: relative;
      transition: all .25s ease;
   }

   .log-update-btn.has-update {
      border-color: #6366f1;
      color: #4f46e5;
      background: rgba(99, 102, 241, .06);
      box-shadow: 0 4px 12px rgba(99, 102, 241, .12);
   }

   .log-update-btn.has-update:hover {
      background: #6366f1;
      color: #fff;
      transform: translateY(-1px);
   }

   /* Badge navbar */
   .log-update-badge {
      position: absolute;
      top: -8px;
      right: -8px;

      min-width: 20px;
      height: 20px;

      padding: 0 5px;

      display: flex;
      align-items: center;
      justify-content: center;

      background: #ef4444;
      color: #fff;

      border-radius: 50%;
      border: 2px solid #fff;

      font-size: 10px;
      font-weight: 700;

      box-shadow: 0 3px 10px rgba(239, 68, 68, .35);

      animation: logBadgePulse 1.8s infinite;
   }

   @keyframes logBadgePulse {
      0% {
         transform: scale(1);
      }

      50% {
         transform: scale(1.12);
      }

      100% {
         transform: scale(1);
      }
   }


   /* =========================================================
   MODAL
========================================================= */

   #logUpdateModal .modal-dialog {
      max-width: 850px;
   }

   #logUpdateModal .modal-content {
      border: 0;
      border-radius: 22px;
      overflow: hidden;
      background: #f8fafc;
      box-shadow: 0 25px 70px rgba(15, 23, 42, .22);
   }

   #logUpdateModal .modal-header {
      padding: 28px 32px 24px;
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
   }

   #logUpdateModal .modal-title {
      color: #111827 !important;
      font-size: 24px;
      font-weight: 700;
   }

   #logUpdateModal .modal-header small {
      color: #64748b !important;
      font-size: 14px;
   }

   #logUpdateModal .modal-body {
      padding: 28px 32px 32px;
   }


   /* =========================================================
   UPDATE ITEM
========================================================= */

   .log-update-item {
      position: relative;

      display: flex;
      gap: 18px;

      padding: 22px;

      margin-bottom: 16px;

      background: #ffffff;

      border: 1px solid #e5e7eb;
      border-radius: 16px;

      box-shadow: 0 4px 14px rgba(15, 23, 42, .05);

      transition:
         transform .2s ease,
         box-shadow .2s ease,
         border-color .2s ease;
   }

   .log-update-item:hover {
      transform: translateY(-2px);

      border-color: #c7d2fe;

      box-shadow: 0 10px 28px rgba(15, 23, 42, .10);
   }


   /* Update baru */
   .log-update-item.is-new {
      border-left: 4px solid #6366f1;

      background:
         linear-gradient(90deg,
            rgba(99, 102, 241, .035),
            #ffffff 35%);
   }


   /* Icon */
   .log-update-icon {
      flex: 0 0 46px;

      width: 46px;
      height: 46px;

      display: flex;
      align-items: center;
      justify-content: center;

      border-radius: 13px;

      background: #eef2ff;
      color: #4f46e5;

      font-size: 21px;
   }


   /* Content */
   .log-update-content {
      flex: 1;
      min-width: 0;
   }


   /* Top row */
   .log-update-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 15px;

      margin-bottom: 9px;
   }


   /* Title */
   .log-update-title {
      margin: 0;

      color: #111827 !important;

      font-size: 17px;
      line-height: 1.4;

      font-weight: 700;
   }


   /* Date */
   .log-update-date {
      flex-shrink: 0;

      color: #64748b !important;

      font-size: 12px;
      font-weight: 500;

      white-space: nowrap;
   }


   /* Description */
   .log-update-description {
      margin: 10px 0 14px;

      color: #475569 !important;

      font-size: 14px;
      line-height: 1.65;

      font-weight: 400;
   }


   /* =========================================================
   META
========================================================= */

   .log-update-meta {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
   }


   /* Type */
   .log-update-type {
      display: inline-flex;
      align-items: center;

      padding: 5px 10px;

      border-radius: 7px;

      font-size: 11px;
      font-weight: 700;

      text-transform: uppercase;
      letter-spacing: .3px;
   }


   /* Feature */
   .log-update-type.feature {
      background: #dcfce7;
      color: #166534;
   }


   /* Improvement */
   .log-update-type.improvement {
      background: #fef3c7;
      color: #92400e;
   }


   /* Fix */
   .log-update-type.fix {
      background: #fee2e2;
      color: #991b1b;
   }


   /* Update */
   .log-update-type.update {
      background: #e0e7ff;
      color: #3730a3;
   }


   /* Version */
   .log-update-version {
      display: inline-flex;
      align-items: center;

      padding: 5px 9px;

      border-radius: 7px;

      background: #f1f5f9;
      color: #475569;

      font-size: 11px;
      font-weight: 600;
   }


   /* NEW badge */
   .log-update-new {
      display: inline-flex;
      align-items: center;

      padding: 5px 10px;

      border-radius: 7px;

      background: #f43f5e;
      color: #fff;

      font-size: 10px;
      font-weight: 800;

      letter-spacing: .3px;

      box-shadow: 0 3px 8px rgba(244, 63, 94, .20);
   }


   /* =========================================================
   EMPTY
========================================================= */

   #logUpdateEmpty {
      padding: 60px 20px !important;
   }

   #logUpdateEmpty .empty-icon {
      width: 70px;
      height: 70px;

      margin: 0 auto 18px;

      display: flex;
      align-items: center;
      justify-content: center;

      border-radius: 20px;

      background: #ecfdf5;
      color: #10b981;

      font-size: 30px;
   }

   #logUpdateEmpty h6 {
      color: #1e293b !important;
   }

   #logUpdateEmpty small {
      color: #64748b !important;
   }


   /* =========================================================
   MOBILE
========================================================= */

   @media (max-width: 576px) {

      #logUpdateModal .modal-header {
         padding: 22px 20px;
      }

      #logUpdateModal .modal-body {
         padding: 20px;
      }

      .log-update-item {
         padding: 18px;
         gap: 13px;
      }

      .log-update-icon {
         flex-basis: 40px;
         width: 40px;
         height: 40px;

         font-size: 18px;
      }

      .log-update-top {
         display: block;
      }

      .log-update-date {
         display: block;
         margin-top: 5px;
      }

      .log-update-title {
         font-size: 15px;
      }

      .log-update-description {
         font-size: 13px;
      }
   }

   /* =========================================================
   DETAIL UPDATE
========================================================= */

   .log-update-detail {
      display: none;

      margin-top: 14px;
      padding: 15px 16px;

      background: #f8fafc;

      border: 1px solid #e2e8f0;
      border-radius: 10px;

      color: #334155 !important;

      font-size: 13px;
      line-height: 1.7;
   }

   .log-update-detail.show {
      display: block;
   }


   .log-update-detail-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;

      margin-top: 2px;

      padding: 6px 11px;

      border: 1px solid #e2e8f0;
      border-radius: 8px;

      background: #ffffff;
      color: #475569;

      font-size: 12px;
      font-weight: 600;

      transition: all .2s ease;
   }

   .log-update-detail-btn:hover {
      border-color: #6366f1;
      background: #eef2ff;
      color: #4f46e5;
   }


   .log-update-detail-btn i {
      font-size: 15px;
   }


   /* Saat detail terbuka */
   .log-update-item.detail-open {
      border-color: #c7d2fe;
   }


   .log-update-item.detail-open .log-update-detail-btn {
      border-color: #c7d2fe;
      background: #eef2ff;
      color: #4f46e5;
   }


   /* =========================================================
   MODAL SCROLL
========================================================= */

   #logUpdateModal .modal-body {
      max-height: calc(100vh - 190px);
      overflow-y: auto;
   }


   /* Scrollbar */
   #logUpdateModal .modal-body::-webkit-scrollbar {
      width: 6px;
   }

   #logUpdateModal .modal-body::-webkit-scrollbar-track {
      background: transparent;
   }

   #logUpdateModal .modal-body::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
   }

   #logUpdateModal .modal-body::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
   }
</style>