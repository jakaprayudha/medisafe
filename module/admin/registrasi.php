<?php
$title = 'Registrasi Poliklinik';
// HTML halaman ini ~150 KB (banyak modal & skrip) -> kompres bila server belum mengompres
if (!ini_get('zlib.output_compression') && extension_loaded('zlib') && !headers_sent()) ob_start('ob_gzhandler');
require '../../controller/view.php';
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../../controller/visit/registrasiService.php';
// data awal (tab Menunggu hari ini) + pilihan filter langsung disisipkan:
// dulu butuh 4 request tambahan setelah halaman tampil, masing-masing membuka session & koneksi DB baru
$rgAwal = null;
$rgOpsi = ['dokter' => [], 'penjamin' => [], 'poli' => []];
try {
  $rgIdc = (int) ($_SESSION['id_customer'] ?? 0);
  if ($rgIdc > 0) {
    $rgAwal = rgDaftar($koneksi, $rgIdc, ['tab' => 'belum', 'tipe_pasien' => 'Poliklinik']);
    $rgOpsi = rgOpsi($koneksi, $rgIdc);
  }
} catch (Throwable $e) {
  error_log('[registrasi] data awal: ' . $e->getMessage());
  $rgAwal = null;
}
session_write_close();
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style id="fixcss">
    .dropdown-menu {
      z-index: 999999 !important;
      min-width: 200px;
      max-width: 250px;
      width: auto;
      white-space: normal;
    }

    /* wrapper utama */
    .dataTables_wrapper {
      overflow: visible !important;
    }

    /* scroll container */
    .dataTables_scroll {
      overflow: visible !important;
    }

    /* biarkan scroll tetap jalan */
    .dataTables_scrollBody {
      overflow-x: auto !important;
      overflow-y: hidden !important;
    }



    /* responsive table */
    .table-responsive {
      overflow: visible !important;
    }

    /* card container */
    .card {
      overflow: visible !important;
    }

    .dropup .dropdown-menu {
      top: auto !important;
      bottom: 100% !important;
      margin-bottom: 5px;
      transform: none !important;
    }

    #cameraModal .modal-body {
      position: relative;
    }

    #cameraModal video {
      width: 100%;
    }

    #cameraModal canvas {
      position: absolute;
      top: 0;
      left: 0;
    }

    /* 🔥 freeze kolom pertama */
    #periodeTable th:first-child,
    #periodeTable td:first-child {
      position: sticky;
      left: 0;
      z-index: 5;
      background: #fff;
    }

    /* header lebih tinggi z-index */
    #periodeTable thead th:first-child {
      z-index: 6;
    }

    /* =========================================================
   FACE CAMERA
   ========================================================= */

    .face-camera-wrapper {
      position: relative;
      width: 100%;
      max-width: 640px;
      margin: 0 auto;

      background: #000;

      overflow: hidden;
      border-radius: 14px;

      /* supaya kamera tidak terlalu tinggi */
      aspect-ratio: 4 / 3;
    }


    /* =========================================================
   VIDEO
   ========================================================= */

    #video {
      width: 100%;
      height: 100%;

      display: block;

      object-fit: cover;

      /* Mirror seperti kamera depan */
      transform: scaleX(-1);
    }


    /* =========================================================
   OVERLAY
   ========================================================= */

    .face-overlay {

      position: absolute;

      inset: 0;

      display: flex;

      align-items: center;
      justify-content: center;

      pointer-events: none;

      /*
     * Background transparan.
     * Gelapnya dibuat oleh box-shadow
     * pada face-frame.
     */

    }


    /* =========================================================
   FACE FRAME
   Bentuk menyerupai kepala / wajah
   ========================================================= */

    .face-circle {

      position: relative;

      width: 250px;
      height: 320px;

      /*
     * Bentuk wajah:
     * - bagian kepala membulat
     * - sisi pipi rounded
     * - bagian bawah mengecil seperti dagu
     */

      border-radius:
        48% 48% 44% 44% / 38% 38% 62% 62%;

      border: 4px solid #00ff88;

      background: transparent;

      /*
     * Membuat area luar frame menjadi gelap
     * tetapi bagian wajah tetap terlihat jelas.
     */

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 18px rgba(0, 255, 136, 0.65),

        inset 0 0 15px rgba(0, 255, 136, 0.05);

      transition:
        border-color 0.3s ease,
        box-shadow 0.3s ease;

    }


    /* =========================================================
   CORNER MARKER
   ========================================================= */

    .face-corner {

      position: absolute;

      width: 30px;
      height: 30px;

      border-color: #ffffff;

      pointer-events: none;

    }


    /* TOP LEFT */

    .corner-tl {

      top: -4px;
      left: -4px;

      border-top: 5px solid;
      border-left: 5px solid;

      border-radius: 5px 0 0 0;

    }


    /* TOP RIGHT */

    .corner-tr {

      top: -4px;
      right: -4px;

      border-top: 5px solid;
      border-right: 5px solid;

      border-radius: 0 5px 0 0;

    }


    /* BOTTOM LEFT */

    .corner-bl {

      bottom: -4px;
      left: -4px;

      border-bottom: 5px solid;
      border-left: 5px solid;

      border-radius: 0 0 0 5px;

    }


    /* BOTTOM RIGHT */

    .corner-br {

      bottom: -4px;
      right: -4px;

      border-bottom: 5px solid;
      border-right: 5px solid;

      border-radius: 0 0 5px 0;

    }


    /* =========================================================
   TEXT PANDUAN
   ========================================================= */

    .face-guide-text {

      font-size: 16px;

      font-weight: 600;

      color: #333;

      line-height: 1.5;

    }


    .face-guide-text i {

      color: #198754;

      margin-right: 5px;

    }


    .face-guide-small {

      font-size: 13px;

      color: #777;

      margin-top: 4px;

      line-height: 1.5;

    }


    /* =========================================================
   BUTTON
   ========================================================= */

    #captureBtn {

      min-width: 160px;

      font-weight: 600;

      border-radius: 8px;

    }


    /* =========================================================
   MODAL CAMERA
   ========================================================= */

    #cameraModal .modal-body {

      padding: 20px;

    }


    #cameraModal .modal-header {

      border-bottom: 1px solid #eee;

    }


    #cameraModal .modal-title {

      font-weight: 600;

    }


    /* =========================================================
   CAMERA STATUS
   ========================================================= */

    .face-status {

      display: inline-flex;

      align-items: center;

      gap: 6px;

      margin-top: 8px;

      font-size: 13px;

      font-weight: 500;

    }


    /* =========================================================
   STATUS WARNA
   ========================================================= */

    .face-frame-success {

      border-color: #00ff88 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(0, 255, 136, 0.85),

        inset 0 0 20px rgba(0, 255, 136, 0.08);

    }


    .face-frame-warning {

      border-color: #ffc107 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(255, 193, 7, 0.75);

    }


    .face-frame-danger {

      border-color: #dc3545 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(220, 53, 69, 0.75);

    }


    /* =========================================================
   ANIMATION
   ========================================================= */

    @keyframes facePulse {

      0% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 12px rgba(0, 255, 136, 0.5);

      }

      50% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 25px rgba(0, 255, 136, 0.85);

      }

      100% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 12px rgba(0, 255, 136, 0.5);

      }

    }


    .face-circle {

      animation: facePulse 2s infinite;

    }


    /* =========================================================
   DESKTOP
   ========================================================= */

    @media (min-width: 768px) {

      .face-camera-wrapper {

        max-width: 640px;

      }

      .face-circle {

        width: 250px;
        height: 320px;

      }

    }


    /* =========================================================
   TABLET
   ========================================================= */

    @media (max-width: 767px) {

      .face-camera-wrapper {

        max-width: 100%;

      }

      .face-circle {

        width: 220px;
        height: 285px;

      }

    }


    /* =========================================================
   MOBILE
   ========================================================= */

    @media (max-width: 576px) {

      .face-camera-wrapper {

        aspect-ratio: 3 / 4;

        border-radius: 12px;

      }

      .face-circle {

        width: 205px;
        height: 270px;

        border-width: 3px;

      }

      .face-corner {

        width: 25px;
        height: 25px;

      }

      .face-guide-text {

        font-size: 14px;

      }

      .face-guide-small {

        font-size: 12px;

      }

      #captureBtn {

        width: 100%;

        max-width: 240px;

      }

    }


    /* =========================================================
   VERY SMALL MOBILE
   ========================================================= */

    @media (max-width: 380px) {

      .face-circle {

        width: 185px;
        height: 245px;

      }

    }


    /* =========================================================
   REDUCE MOTION
   ========================================================= */

    @media (prefers-reduced-motion: reduce) {

      .face-circle {

        animation: none;

      }

    }

    /* ===== Registrasi poliklinik (tampilan baru) ===== */
    .min-w-0 { min-width: 0; }
    .rg-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .rg-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .rg-label { font-size: .72rem; color: #6c757d; }
    .rg-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .rg-sub { font-size: .7rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rg-prog { height: 6px; border-radius: 3px; background: #eef1f4; overflow: hidden; }
    .rg-prog > div { height: 100%; transition: width .3s; }
    .rg-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .rg-tabs .btn .badge { background: #eef1f4; color: #5a6a85; font-weight: 600; margin-left: 4px; }
    .rg-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .rg-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
    .rg-sep { width: 1px; background: #e5eaef; margin: 4px 6px; }
    .rg-antri { font-weight: 700; font-size: .95rem; color: #2a3547; }
    .rg-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 5px; color: #334155; }
    .rg-nama { max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rg-dok { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rg-src { font-size: .64rem; font-weight: 600; padding: .1em .5em; border-radius: 10px; }
    .rg-src-mjkn { background: #ecfdf5; color: #059669; }
    .rg-src-onsite { background: #f1f5f9; color: #64748b; }
    #periodeTable td { vertical-align: middle; }
    #periodeTable .rg-aksi { padding: .2rem .55rem; }
    .rg-cepat .btn { font-size: .78rem; }
    #periodeTable td.rg-dd-buka { z-index: 1060 !important; position: relative; }
    #periodeTable td:first-child.rg-dd-buka { position: sticky; }
    #periodeTable .dropdown-menu { z-index: 1070 !important; }
    /* modal hasil pemeriksaan */
    .dt-ava { width: 46px; height: 46px; border-radius: 50%; background: #ecf2ff; color: #5d87ff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .dt-ava.dt-ava-p { background: #fdeef5; color: #e05297; }
    .dt-chip { font-size: .72rem; background: #f1f5f9; color: #475569; border-radius: 20px; padding: .25rem .65rem; }
    .dt-sec { border: 1px solid #edf0f4; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; background: #fff; }
    .dt-sec-h { font-weight: 600; font-size: .85rem; color: #2a3547; margin-bottom: 8px; }
    .dt-vital { border-radius: 10px; background: #f8fafc; padding: 8px 10px; height: 100%; }
    .dt-vital.kosong { opacity: .55; }
    .dt-vital-l { font-size: .68rem; color: #64748b; }
    .dt-vital-v { font-weight: 700; color: #2a3547; font-size: 1rem; }
    .dt-vital-v small { font-weight: 400; color: #94a3b8; font-size: .68rem; }
    .dt-icd { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 600; font-size: .78rem; background: #ecfdf5; color: #059669; border-radius: 5px; padding: 0 6px; }
    .dt-obat th { font-weight: 500; color: #64748b; font-size: .72rem; background: #f8fafc; }
    .dt-obat td { font-size: .82rem; }
    #detailModal .modal-body { background: #fafbfc; }
    .dt-sk { border-radius: 10px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: dtsk 1.2s infinite; }
    @keyframes dtsk { to { background-position: -200% 0; } }
    /* modal vital sign */
    .vs-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .vs-grid .vs-td { grid-column: span 2; }
    .vs-field { border: 1px solid #edf0f4; border-radius: 10px; padding: 8px 10px; background: #fafbfc; }
    .vs-field .form-label { font-size: .72rem; color: #64748b; margin-bottom: 4px; }
    .vs-field .form-control { font-weight: 600; }
    .vs-field .form-control.is-invalid { background-image: none; padding-right: .5rem; }
    .vs-bmi { font-weight: 700; font-size: 1rem; color: #2a3547; padding-top: 2px; }
    @media (max-width: 767.98px) { .vs-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    /* modal tanda tangan */
    .ttd-pad-wrap { position: relative; border: 1px solid #d6dde6; border-radius: 12px; background: #fff; overflow: hidden; touch-action: none; }
    #signaturePad { display: block; width: 100%; height: 280px; cursor: crosshair; }
    .ttd-pad-line { position: absolute; left: 8%; right: 8%; bottom: 56px; border-bottom: 1.5px dashed #cbd5e1; pointer-events: none; }
    .ttd-pad-hint { position: absolute; left: 8%; bottom: 32px; font-size: .72rem; color: #94a3b8; pointer-events: none; }
    .ttd-lama-box { border: 1px dashed #d6dde6; border-radius: 12px; background: #fafbfc; height: 200px; display: flex; align-items: center; justify-content: center; }
    .ttd-lama-box img { max-height: 180px; max-width: 90%; object-fit: contain; }
    /* modal dokumen SEP / FKPP */
    #dokModal .nav-link { font-weight: 600; font-size: .85rem; color: #64748b; }
    #dokModal .nav-link.active { color: #5d87ff; }
    .dk-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; margin-left: 4px; vertical-align: middle; }
    .dk-dot.ada { background: #13deb9; }
    .dk-dot.hilang { background: #ffae1f; }
    .dk-drop { border: 1.5px dashed #b9c8e6; background: #f7faff; border-radius: 12px; padding: 34px 16px; text-align: center; cursor: pointer; }
    .dk-drop.over, .dk-drop:hover { border-color: #5d87ff; background: #ecf2ff; }
    .dk-view { border: 1px solid #e5eaef; border-radius: 10px; overflow: hidden; background: #f1f5f9; }
    .dk-view iframe { width: 100%; height: 68vh; border: 0; display: block; }
    .dk-view img { max-width: 100%; display: block; margin: 0 auto; }
    .dk-kosong { padding: 24px 0; }
    /* ganti dokter & ubah jadwal */
    .jw-now { background: #f8fafc; border: 1px solid #edf0f4; border-radius: 10px; padding: 10px 12px; }
    .jw-ringkas { background: #ecf2ff; border-radius: 10px; padding: 10px 12px; font-size: .85rem; }
    #jwCepatTgl .btn.active { background: #5d87ff; color: #fff; }
    .dr-list { max-height: 46vh; overflow-y: auto; border: 1px solid #edf0f4; border-radius: 10px; }
    .dr-grup { position: sticky; top: 0; z-index: 1; background: #f8fafc; font-size: .7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .03em; padding: 6px 12px; border-bottom: 1px solid #edf0f4; }
    .dr-item { display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 12px; background: #fff; border: 0; border-bottom: 1px solid #f1f5f9; }
    .dr-item:hover:not(:disabled) { background: #f7faff; }
    .dr-item:disabled { opacity: .6; cursor: default; }
    .dr-item.pilih { background: #ecf2ff; }
    .dr-item .dr-cek { color: #d6dde6; }
    .dr-item.pilih .dr-cek { color: #5d87ff; }
    .dr-ava { width: 34px; height: 34px; border-radius: 50%; background: #ecf2ff; color: #5d87ff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; flex-shrink: 0; }
    /* modal tanpa identitas */
    .tid-seg { display: flex; gap: 6px; }
    .tid-seg input { display: none; }
    .tid-seg label { flex: 1; border: 1px solid #e5eaef; border-radius: 10px; padding: 9px 12px; cursor: pointer; background: #fff; font-size: .82rem; }
    .tid-seg label b { display: block; color: #2a3547; font-size: .86rem; }
    .tid-seg label small { color: #94a3b8; }
    .tid-seg input:checked + label { border-color: #dc3545; background: #fff5f5; }
    .tid-seg input:checked + label b { color: #dc3545; }
    .tid-box { background: #fafbfc; border: 1px solid #edf0f4; border-radius: 10px; padding: 10px 12px; }
    .tid-dokter { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; }
    .tid-dk { border: 1px solid #e5eaef; border-radius: 10px; padding: 7px 10px; background: #fff; display: flex; gap: 8px; align-items: center; width: 100%; }
    .tid-dk:hover:not(:disabled) { border-color: #b9c8e6; }
    .tid-dk.pilih { border-color: #5d87ff; background: #ecf2ff; }
    .tid-dk:disabled { opacity: .5; cursor: not-allowed; }
    .tid-ava { width: 30px; height: 30px; border-radius: 50%; background: #ecf2ff; color: #5d87ff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .7rem; flex-shrink: 0; }
    .tid-sk { height: 44px; border-radius: 10px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: dtsk 1.2s infinite; }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3 rg-head">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="rg-icon bg-primary-subtle text-primary"><i class="fas fa-clipboard-list"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Registrasi Poliklinik</h5>
                  <div class="text-muted small" id="rgPeriodeTeks">Pendaftaran pasien hari ini</div>
                </div>
                <div class="form-check form-switch mb-0 small" title="Muat ulang otomatis tiap 60 detik">
                  <input class="form-check-input" type="checkbox" id="rgAuto" checked>
                  <label class="form-check-label text-muted" for="rgAuto">Auto refresh</label>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="rgRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <div class="dropdown">
                  <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" type="button">
                    <i class="fas fa-plus me-1"></i> Daftar
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="module/admisi/pendaftaran"><i class="fas fa-stethoscope me-2 text-success"></i> Pasien lama ke poliklinik</a></li>
                    <li><a class="dropdown-item" href="module/admisi/registrasi-new"><i class="fas fa-user-plus me-2 text-primary"></i> Pasien baru</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item poli-btn" href="javascript:;"><i class="fas fa-user-secret me-2 text-danger"></i> Tanpa identitas</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Pendaftaran</div><div class="rg-val" id="kAktif">-</div><div class="rg-sub">tidak termasuk batal</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Menunggu dilayani</div><div class="rg-val text-primary" id="kBelum">-</div><div class="rg-sub" id="kCheckin">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Sudah dilayani</div><div class="rg-val text-success" id="kSelesai">-</div><div class="rg-prog mt-2"><div id="kSelesaiBar" class="bg-success" style="width:0"></div></div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Belum vital sign</div><div class="rg-val" id="kVital">-</div><div class="rg-sub">dari pasien menunggu</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Batal</div><div class="rg-val text-danger" id="kBatal">-</div><div class="rg-sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rg-kpi"><div class="rg-label">Pasien BPJS</div><div class="rg-val" id="kBpjs">-</div><div class="rg-sub" id="kBpjsSub">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm w-100">
            <div class="card-body p-3 p-lg-4">
              <!-- FILTER -->
              <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                <div class="btn-group btn-group-sm rg-cepat" role="group">
                  <button type="button" class="btn btn-outline-primary active" data-r="0">Hari ini</button>
                  <button type="button" class="btn btn-outline-primary" data-r="1">Kemarin</button>
                  <button type="button" class="btn btn-outline-primary" data-r="7">7 hari</button>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <input type="date" id="fromDate" class="form-control form-control-sm" style="width:140px" aria-label="Dari tanggal">
                  <span class="text-muted small">–</span>
                  <input type="date" id="toDate" class="form-control form-control-sm" style="width:140px" aria-label="Sampai tanggal">
                </div>
                <select id="poliSelect" class="form-select form-select-sm" style="width:auto;max-width:190px" aria-label="Poli"><option value="">Semua poli</option></select>
                <select id="doctorSelect" class="form-select form-select-sm" style="width:auto;max-width:200px" aria-label="Dokter"><option value="">Semua dokter</option></select>
                <select id="providerSelect" class="form-select form-select-sm" style="width:auto;max-width:180px" aria-label="Penjamin"><option value="">Semua penjamin</option></select>
                <button type="button" id="btnReset" class="btn btn-sm btn-light" title="Reset filter"><i class="fas fa-undo"></i></button>
                <div class="position-relative ms-lg-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="customSearch" class="form-control form-control-sm ps-4" placeholder="Cari nama, RM, no. BPJS, antrian…">
                </div>
              </div>

              <!-- TAB -->
              <div class="rg-tabs d-flex flex-wrap gap-1 mb-3" id="rgTabs" role="tablist">
                <button type="button" class="btn active" data-tab="belum">Menunggu <span class="badge" data-n="belum">0</span></button>
                <button type="button" class="btn" data-tab="selesai">Sudah dilayani <span class="badge" data-n="selesai">0</span></button>
                <button type="button" class="btn" data-tab="batal">Batal <span class="badge" data-n="batal">0</span></button>
                <button type="button" class="btn" data-tab="checkin" title="Booking Mobile JKN yang belum check-in">Belum check-in <span class="badge" data-n="checkin">0</span></button>
                <span class="rg-sep"></span>
                <button type="button" class="btn" data-tab="sehat">Kunjungan sehat <span class="badge" data-n="sehat">0</span></button>
                <button type="button" class="btn" data-tab="sehatbatal">Kunjungan sehat batal <span class="badge" data-n="sehatbatal">0</span></button>
              </div>

              <div class="alert alert-warning small py-2 d-none" id="rgTerpotong">Data lebih dari 5.000 baris — persempit rentang tanggal.</div>
              <div class="table-responsive">
                <table class="table align-middle table-custom mb-0 w-100" id="periodeTable">
                  <thead>
                    <tr>
                      <th class="text-dark fw-normal text-center" style="width:52px">Aksi</th>
                      <th class="text-dark fw-normal">Antrian</th>
                      <th class="text-dark fw-normal">Pasien</th>
                      <th class="text-dark fw-normal">Poli / Dokter</th>
                      <th class="text-dark fw-normal">Penjamin</th>
                      <th class="text-dark fw-normal text-center">Vital sign</th>
                      <th class="text-dark fw-normal text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  require 'library.php';
  ?>
  <script src="controller/admisi/helper.js"></script>



  <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header align-items-start">
          <div class="d-flex align-items-center gap-3 min-w-0">
            <span class="dt-ava" id="dtAva">-</span>
            <div class="min-w-0">
              <h5 class="modal-title mb-0 text-truncate" id="dtNama">Hasil pemeriksaan</h5>
              <div class="small text-muted" id="dtSub">&nbsp;</div>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="px-3 pt-2 pb-2 border-bottom d-flex flex-wrap gap-1" id="dtChips"></div>
        <div class="modal-body" id="dtBody"></div>
        <div class="modal-footer py-2">
          <span class="small text-muted me-auto" id="dtVisit"></span>
          <button type="button" class="btn btn-light btn-sm screening-btn d-none" id="dtVitalBtn" data-bs-dismiss="modal"><i class="fas fa-heart-pulse me-1 text-danger"></i>Isi vital sign</button>
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="screeningModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-heart-pulse text-danger me-2"></i>Vital sign</h5>
            <div class="small text-muted text-truncate" id="vsSub">&nbsp;</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="screening_id_visit">
          <div id="vsMuat" class="text-center py-5 d-none"><div class="spinner-border text-primary"></div><div class="small text-muted mt-2">Memuat data…</div></div>
          <div id="vsGagal" class="text-center py-5 d-none"></div>
          <form id="vsForm" novalidate autocomplete="off">
            <div class="mb-3">
              <label class="form-label small fw-semibold" for="sc_keluhan">Keluhan utama</label>
              <textarea id="sc_keluhan" class="form-control" rows="2" placeholder="Keluhan yang disampaikan pasien"></textarea>
            </div>

            <div class="vs-grid">
              <div class="vs-field vs-td">
                <label class="form-label" for="sistole">Tekanan darah <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                  <input type="number" inputmode="numeric" id="sistole" class="form-control" placeholder="120" data-min="50" data-max="300" data-label="Sistole" required>
                  <span class="input-group-text px-2">/</span>
                  <input type="number" inputmode="numeric" id="diastole" class="form-control" placeholder="80" data-min="20" data-max="200" data-label="Diastole" required>
                  <span class="input-group-text">mmHg</span>
                </div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="nadi">Nadi <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="nadi" class="form-control" placeholder="80" data-min="20" data-max="250" data-label="Nadi" required><span class="input-group-text">x/mnt</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="suhu">Suhu <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="suhu" class="form-control" placeholder="36.5" data-min="30" data-max="45" data-label="Suhu" required><span class="input-group-text">°C</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="respirasi">Respirasi <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="respirasi" class="form-control" placeholder="20" data-min="4" data-max="80" data-label="Respirasi" required><span class="input-group-text">x/mnt</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="saturasi">SpO₂</label>
                <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="saturasi" class="form-control" placeholder="98" data-min="50" data-max="100" data-label="Saturasi"><span class="input-group-text">%</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="tinggi">Tinggi badan <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="tinggi" class="form-control" placeholder="160" data-min="30" data-max="250" data-label="Tinggi badan" required><span class="input-group-text">cm</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="berat">Berat badan <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="berat" class="form-control" placeholder="60" data-min="0.5" data-max="350" data-label="Berat badan" required><span class="input-group-text">kg</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label" for="lingkar_perut">Lingkar perut <span class="text-danger" id="vsLpWajib">*</span></label>
                <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="lingkar_perut" class="form-control" placeholder="80" data-min="20" data-max="250" data-label="Lingkar perut"><span class="input-group-text">cm</span></div>
              </div>
              <div class="vs-field">
                <label class="form-label">BMI <span class="text-muted fw-normal">(otomatis)</span></label>
                <div class="vs-bmi" id="vsBmi">—</div>
              </div>
            </div>
            <div class="small text-danger mt-2 d-none" id="vsError"></div>

            <div class="row g-3 mt-1">
              <div class="col-md-5">
                <label class="form-label small fw-semibold" for="kondisi_masuk">Kondisi masuk <span class="text-danger">*</span></label>
                <select id="kondisi_masuk" class="form-select form-select-sm">
                  <option value="">— Pilih —</option>
                  <option value="Baik">Baik</option>
                  <option value="Sedang">Sedang</option>
                  <option value="Lemah">Lemah</option>
                  <option value="Buruk">Buruk</option>
                  <option value="Gawat Darurat">Gawat darurat</option>
                  <option value="Tidak Sadar">Tidak sadar</option>
                </select>
                <div class="form-text d-none" id="vsPrognosa"></div>
              </div>
              <div class="col-md-7">
                <label class="form-label small fw-semibold" for="sc_catatan">Catatan skrining</label>
                <textarea id="sc_catatan" class="form-control form-control-sm" rows="2"></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer py-2">
          <span class="small text-muted me-auto d-none d-md-inline">Ctrl + Enter untuk simpan</span>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="btnSaveScreening"><i class="fas fa-save me-1"></i>Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="poliModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-user-secret text-danger me-2"></i>Pendaftaran tanpa identitas</h5>
            <div class="small text-muted">Untuk pasien yang belum bisa diidentifikasi (tidak sadar, korban, tanpa KTP/BPJS). Didaftarkan sebagai pasien umum.</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <form id="formPoli" novalidate autocomplete="off">
            <div class="tid-seg mb-3" id="tiMode">
              <input type="radio" name="ti_mode" id="tiBaru" value="baru" checked><label for="tiBaru"><b><i class="fas fa-user-plus me-1"></i>Pasien baru tanpa identitas</b><small>Buat RM sementara, data dilengkapi kemudian</small></label>
              <input type="radio" name="ti_mode" id="tiLama" value="lama"><label for="tiLama"><b><i class="fas fa-magnifying-glass me-1"></i>Pasien sudah terdaftar</b><small>Daftar cepat tanpa cek BPJS</small></label>
            </div>

            <!-- pasien baru -->
            <div id="tiBaruBox" class="tid-box mb-3">
              <div class="row g-2">
                <div class="col-md-5">
                  <label class="form-label small fw-semibold">Jenis kelamin <span class="text-danger">*</span></label>
                  <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="ti_jk" id="tiJkL" value="L" checked><label class="btn btn-outline-primary btn-sm" for="tiJkL">Laki-laki</label>
                    <input type="radio" class="btn-check" name="ti_jk" id="tiJkP" value="P"><label class="btn btn-outline-primary btn-sm" for="tiJkP">Perempuan</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-semibold" for="tiUmur">Perkiraan umur</label>
                  <div class="input-group input-group-sm"><input type="number" id="tiUmur" class="form-control" min="0" max="120" placeholder="±"><span class="input-group-text">th</span></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold" for="tiNama">Nama sementara</label>
                  <input type="text" id="tiNama" class="form-control form-control-sm" maxlength="150">
                </div>
                <div class="col-12">
                  <label class="form-label small fw-semibold" for="tiCiri">Ciri-ciri / keterangan</label>
                  <input type="text" id="tiCiri" class="form-control form-control-sm" maxlength="500" placeholder="mis. korban KLL di Jl. ..., diantar warga, baju biru">
                </div>
              </div>
            </div>

            <!-- pasien lama -->
            <div id="tiLamaBox" class="mb-3 d-none">
              <label class="form-label small fw-semibold">Cari pasien <span class="text-danger">*</span></label>
              <select id="id_patient_select" class="form-select"></select>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold" for="poli_poli">Poli <span class="text-danger">*</span></label>
                <select id="poli_poli" class="form-select form-select-sm"></select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold" for="poli_date">Tanggal</label>
                <input type="date" id="poli_date" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold" for="poli_time">Jam datang</label>
                <input type="time" id="poli_time" class="form-control form-control-sm">
              </div>
            </div>

            <label class="form-label small fw-semibold mb-1">Dokter <span class="text-danger">*</span> <span class="text-muted fw-normal" id="tiHari"></span></label>
            <div class="tid-dokter mb-3" id="tiDokter"><div class="small text-muted">Pilih poli terlebih dahulu.</div></div>

            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label small fw-semibold" for="poli_provider">Penjamin <span class="text-danger">*</span></label>
                <select id="poli_provider" class="form-select form-select-sm"></select>
              </div>
              <div class="col-md-7">
                <label class="form-label small fw-semibold" for="tiKeluhan">Keluhan / kondisi</label>
                <input type="text" id="tiKeluhan" class="form-control form-control-sm" maxlength="500" placeholder="mis. tidak sadar, luka di kepala">
              </div>
            </div>
            <div class="small text-danger mt-2 d-none" id="tiError"></div>
          </form>
        </div>
        <div class="modal-footer py-2">
          <span class="small text-muted me-auto" id="tiRingkas"></span>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" id="btnSavePoli"><i class="fas fa-check me-1"></i>Daftarkan</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="ttdModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-signature me-2 text-primary"></i>Tanda tangan pasien / keluarga</h5>
            <div class="small text-muted text-truncate" id="ttdSub">&nbsp;</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="ttd_id_visit">
          <div id="ttdLama" class="d-none">
            <div class="ttd-lama-box"><img id="ttdLamaImg" alt="Tanda tangan tersimpan"></div>
            <div class="d-flex align-items-center gap-2 mt-2">
              <span class="small text-success"><i class="fas fa-circle-check me-1"></i>Tanda tangan sudah tersimpan</span>
              <button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="ttdUlang"><i class="fas fa-pen me-1"></i>Buat ulang</button>
            </div>
          </div>
          <div id="ttdBaru">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="small text-muted">Minta pasien / keluarga menandatangani di area bawah.</span>
              <div class="ms-auto d-flex gap-1">
                <button type="button" class="btn btn-light btn-sm" id="ttdUndo" disabled><i class="fas fa-undo me-1"></i>Urungkan</button>
                <button type="button" class="btn btn-light btn-sm" id="clearSignature" disabled><i class="fas fa-eraser me-1"></i>Bersihkan</button>
              </div>
            </div>
            <div class="ttd-pad-wrap">
              <canvas id="signaturePad"></canvas>
              <div class="ttd-pad-line"></div>
              <div class="ttd-pad-hint">Tanda tangan di atas garis</div>
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary" id="saveSignature" disabled><i class="fas fa-save me-1"></i>Simpan tanda tangan</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="dokModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-file-medical text-primary me-2"></i>Dokumen SEP &amp; FKPP</h5>
            <div class="small text-muted text-truncate" id="dkSub">&nbsp;</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="px-3 pt-2 border-bottom">
          <ul class="nav nav-tabs border-0" role="tablist">
            <li class="nav-item"><button class="nav-link active" type="button" data-dk="sep">SEP <span class="dk-dot" data-dk-dot="sep"></span></button></li>
            <li class="nav-item"><button class="nav-link" type="button" data-dk="fkpp">FKPP <span class="dk-dot" data-dk-dot="fkpp"></span></button></li>
          </ul>
        </div>
        <div class="modal-body" id="dkBody"></div>
        <div class="modal-footer py-2">
          <span class="small text-muted me-auto">PDF, maksimal 5 MB · tersimpan juga untuk bundle klaim</span>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <input type="file" id="dkFile" accept="application/pdf,.pdf" hidden>

  <div class="modal fade" id="editVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-clock text-warning me-2"></i>Ubah jadwal kunjungan</h5>
            <div class="small text-muted text-truncate" id="jwSub">&nbsp;</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_id_patient">
          <input type="hidden" id="edit_visit_id">
          <div class="jw-now mb-3">
            <div class="small text-muted">Jadwal saat ini</div>
            <div class="fw-semibold" id="jwLama">-</div>
          </div>
          <label class="form-label small fw-semibold">Tanggal baru</label>
          <div class="d-flex flex-wrap gap-1 mb-2" id="jwCepatTgl">
            <button type="button" class="btn btn-sm btn-outline-primary" data-h="0">Hari ini</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-h="1">Besok</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-h="2">Lusa</button>
          </div>
          <input type="date" id="edit_visit_date" class="form-control mb-3">
          <label class="form-label small fw-semibold">Jam</label>
          <div class="d-flex gap-2 align-items-center mb-1">
            <input type="time" id="edit_visit_time" class="form-control" style="max-width:160px">
            <button type="button" class="btn btn-sm btn-light" id="jwSekarang"><i class="fas fa-clock-rotate-left me-1"></i>Jam sekarang</button>
          </div>
          <div class="small text-danger d-none mt-2" id="jwError"></div>
          <div class="jw-ringkas mt-3 d-none" id="jwRingkas"></div>
          <div class="alert alert-warning small py-2 mt-3 mb-0 d-none" id="jwBpjs"><i class="fas fa-triangle-exclamation me-1"></i>Perubahan hanya di sistem klinik. Jadwal antrean di Mobile JKN / PCare tidak ikut berubah.</div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="btnUpdateVisit" disabled><i class="fas fa-save me-1"></i>Simpan jadwal</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Ambil Wajah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">

          <div class="face-camera-wrapper">

            <video id="video" autoplay playsinline></video>

            <!-- Overlay kamera -->
            <div class="face-overlay">

              <!-- Lingkaran wajah -->
              <div class="face-circle">
                <div class="face-corner corner-tl"></div>
                <div class="face-corner corner-tr"></div>
                <div class="face-corner corner-bl"></div>
                <div class="face-corner corner-br"></div>
              </div>

            </div>

          </div>

          <div class="face-guide-text mt-3">
            <i class="fas fa-user-circle"></i>
            Posisikan wajah tepat di dalam lingkaran
          </div>

          <div class="face-guide-small">
            Pastikan wajah terlihat jelas, menghadap kamera dan tidak terpotong.
            Foto tersimpan otomatis setelah wajah sesuai dengan master pasien dan posisi sudah pas.
          </div>

          <div id="faceMatchInfo" class="text-center mb-1"></div>
          <div id="faceStatus" class="face-status text-warning">
            <i class="fas fa-exclamation-circle"></i>
            <span>Memuat deteksi wajah...</span>
          </div>

          <canvas id="canvas" style="display:none;"></canvas>

          <div class="mt-3">
            <button id="captureBtn" class="btn btn-success px-4">
              <i class="fas fa-camera"></i>
              Ambil Wajah
            </button>
          </div>

        </div>

      </div>
    </div>
  </div>
  <div class="modal fade" id="modalRescheduleDoctor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <form id="formRescheduleDoctor" class="d-flex flex-column" style="min-height:0">
          <div class="modal-header">
            <div class="min-w-0">
              <h5 class="modal-title mb-0"><i class="fas fa-user-md text-primary me-2"></i>Ganti dokter</h5>
              <div class="small text-muted text-truncate" id="drSub">&nbsp;</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="visit_id" name="visit_id">
            <input type="hidden" id="doctor_new" name="doctor_new">
            <div class="jw-now mb-3 d-flex align-items-center gap-2">
              <span class="dr-ava" id="drNowAva">-</span>
              <div class="min-w-0"><div class="small text-muted">Dokter saat ini</div><div class="fw-semibold text-truncate" id="doctor_now">-</div></div>
            </div>
            <div class="position-relative mb-2">
              <i class="fas fa-search position-absolute text-muted" style="left:11px;top:50%;transform:translateY(-50%);font-size:.78rem"></i>
              <input type="search" id="drCari" class="form-control form-control-sm ps-5" placeholder="Cari dokter / tenaga…" autocomplete="off">
            </div>
            <div id="drList" class="dr-list"></div>
            <div class="alert alert-warning small py-2 mt-3 mb-0 d-none" id="drBpjs"><i class="fas fa-triangle-exclamation me-1"></i>Perubahan hanya di sistem klinik. Bila pasien BPJS sudah terdaftar di PCare / antrean, sesuaikan juga di sana.</div>
          </div>
          <div class="modal-footer py-2">
            <span class="small text-muted me-auto text-truncate" id="drPilihan"></span>
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSimpanReschedule" disabled><i class="fas fa-save me-1"></i>Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
<script>
  APP.window = APP.window || {};
  // tab aktif dipakai juga oleh skrip lain di halaman ini
  let currentTab = 'belum';
  $(document).ready(function() {
    const apiUrl = 'controller/visit/registrasiController';
    const tipePasien = "Poliklinik";
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmt = n => Number(n || 0).toLocaleString('id-ID');
    const tglJkt = (geser) => { const d = new Date(Date.now() - (geser || 0) * 86400000); return d.toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' }); };
    const tglIndo = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
    const today = tglJkt(0);

    function umur(lahir, pada) {
      const a = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/), b = String(pada || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (!a || !b || a[1] < '1900') return '';
      let th = b[1] - a[1], bl = b[2] - a[2];
      if (b[3] < a[3]) bl--;
      if (bl < 0) { th--; bl += 12; }
      if (th < 0) return '';
      return th > 0 ? th + ' th' : bl + ' bln';
    }
    function sumber(r) {
      const u = String(r.created_user || '');
      if (u === 'MobileJKN') return ['Mobile JKN', 'rg-src-mjkn'];
      if (u === 'JKNSehat') return ['JKN Sehat', 'rg-src-mjkn'];
      if (u === 'JKNOnsite') return ['JKN Onsite', 'rg-src-onsite'];
      return ['Onsite', 'rg-src-onsite'];
    }
    const STATUS = {
      0: ['Menunggu', 'secondary'], 1: ['Pemeriksaan', 'primary'], 2: ['Dipanggil', 'info'], 3: ['Dilayani', 'primary'],
      4: ['Selesai', 'success'], 10: ['Belum check-in', 'warning'], 99: ['Batal', 'danger']
    };

    // ---------------- filter ----------------
    $('#fromDate').val(today).attr('max', today);
    $('#toDate').val(today);
    // pilihan filter disisipkan server (dulu 3 request: getdoctor, getprovider, get_master_poli)
    (function isiOpsi(o) {
      $('#doctorSelect').html('<option value="">Semua dokter</option>' + (o.dokter || []).map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join(''));
      $('#providerSelect').html('<option value="">Semua penjamin</option>' + (o.penjamin || []).map(p => `<option value="${esc(p.id_provider)}">${esc(p.provider_name)}</option>`).join(''));
      $('#poliSelect').html('<option value="">Semua poli</option>' + (o.poli || []).map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join(''));
    })(<?= json_encode($rgOpsi, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>);
    // data tabel pertama ikut di HTML -> tabel langsung tampil tanpa menunggu request
    let dataAwal = <?= $rgAwal ? json_encode($rgAwal, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) : 'null' ?>;

    function teksPeriode() {
      const f = $('#fromDate').val(), t = $('#toDate').val();
      $('#rgPeriodeTeks').text(f === t ? (f === today ? 'Pendaftaran pasien hari ini' : 'Pendaftaran ' + tglIndo(f)) : 'Pendaftaran ' + tglIndo(f) + ' – ' + tglIndo(t));
    }
    $('.rg-cepat .btn').on('click', function() {
      const r = +this.dataset.r;
      $('#fromDate').val(tglJkt(r === 7 ? 6 : r));
      $('#toDate').val(r === 1 ? tglJkt(1) : today);
      $('.rg-cepat .btn').removeClass('active');
      $(this).addClass('active');
      muatUlang(true);
    });
    $('#fromDate, #toDate').on('change', function() {
      if ($('#fromDate').val() > $('#toDate').val()) $('#toDate').val($('#fromDate').val());
      $('.rg-cepat .btn').removeClass('active');
      muatUlang(true);
    });
    $('#poliSelect, #doctorSelect, #providerSelect').on('change', () => muatUlang(true));
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      $('#doctorSelect, #providerSelect, #poliSelect').val('');
      $('#customSearch').val('');
      window.table.search('');
      $('.rg-cepat .btn').removeClass('active').first().addClass('active');
      muatUlang(true);
    });

    // ---------------- tab ----------------
    $('#rgTabs').on('click', '.btn[data-tab]', function() {
      currentTab = this.dataset.tab;
      $('#rgTabs .btn').removeClass('active');
      $(this).addClass('active');
      muatUlang(true);
    });

    function tampilHitung(h) {
      if (!h) return;
      $('#rgTabs [data-n]').each(function() { this.textContent = fmt(h[this.dataset.n]); });
      const sel = (h.selesai || 0), aktif = h.aktif || 0;
      $('#kAktif').text(fmt(aktif));
      $('#kBelum').text(fmt(h.belum));
      $('#kCheckin').text(h.checkin ? fmt(h.checkin) + ' booking belum check-in' : ' ');
      $('#kSelesai').text(fmt(sel));
      $('#kSelesaiBar').css('width', (aktif ? sel / aktif * 100 : 0) + '%');
      $('#kVital').text(fmt(h.tanpa_vital)).toggleClass('text-warning', h.tanpa_vital > 0);
      $('#kBatal').text(fmt(h.batal));
      $('#kBpjs').text(aktif ? Math.round(h.bpjs / aktif * 100) + '%' : '-');
      $('#kBpjsSub').text(fmt(h.bpjs) + ' pasien');
    }

    // ---------------- tabel ----------------
    function aksiHtml(row) {
      const selesai = row.visit_status === 4 || row.visit_status === 3, batal = row.visit_status === 99;
      const sudahTtd = !!row.signature_path, sudahFoto = !!row.ada_foto;
      return `<div class="dropdown">
        <button class="btn btn-sm btn-light border rg-aksi" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-label="Aksi"><i class="fas fa-ellipsis-h"></i></button>
        <ul class="dropdown-menu shadow">
          <li><a class="dropdown-item detail-btn" href="javascript:;" data-id="${row.id_visit}"><i class="fas fa-eye me-2 text-info"></i>Hasil pemeriksaan</a></li>
          <li><a class="dropdown-item screening-btn" href="javascript:;" data-id="${row.id_visit}"><i class="fas fa-heart-pulse me-2 text-danger"></i>${row.tekanan_darah ? 'Ubah' : 'Isi'} vital sign</a></li>
          <li><a class="dropdown-item camera-btn" href="javascript:;" data-id="${row.id_visit}" data-face="${esc(row.patient_face_image || '')}"><i class="fas fa-camera me-2 text-success"></i>${sudahFoto ? 'Ulangi foto' : 'Ambil foto'}</a></li>
          <li><a class="dropdown-item ttd-btn" href="javascript:;" data-id="${row.id_visit}"><i class="fas fa-signature me-2 text-dark"></i>${sudahTtd ? 'Ulangi tanda tangan' : 'Tanda tangan'}</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item dok-btn" href="javascript:;" data-visit="${esc(row.visit_ID)}" data-jenis="sep">${row.ada_sep ? '<i class="fas fa-file-circle-check me-2 text-success"></i>Lihat SEP' : '<i class="fas fa-file-arrow-up me-2 text-info"></i>Upload SEP'}</a></li>
          <li><a class="dropdown-item dok-btn" href="javascript:;" data-visit="${esc(row.visit_ID)}" data-jenis="fkpp">${row.ada_fkpp ? '<i class="fas fa-file-circle-check me-2 text-success"></i>Lihat FKPP' : '<i class="fas fa-file-arrow-up me-2 text-info"></i>Upload FKPP'}</a></li>
          ${!selesai && !batal ? `<li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item btn-reschedule" href="javascript:;" data-id="${row.id_visit}" data-doctor="${esc(row.id_doctor)}"><i class="fas fa-user-md me-2 text-dark"></i>Ganti dokter</a></li>
          <li><a class="dropdown-item edit-visit-btn" href="javascript:;" data-visit="${esc(row.visit_ID)}" data-date="${esc(row.visit_date)}" data-patient="${esc(row.id_patient)}" data-time="${esc(row.visit_time)}"><i class="fas fa-clock me-2 text-warning"></i>Ubah waktu</a></li>
          <li><a class="dropdown-item delete-btn text-danger" href="javascript:;" data-id="${row.id_visit}" data-prov="${esc(row.id_provider)}" data-visit="${esc(row.visit_ID)}" data-nama="${esc(row.patient_name || row.patient_name_pcare || '')}"><i class="fas fa-times-circle me-2"></i>Batalkan pendaftaran</a></li>` : ''}
        </ul></div>`;
    }

    let terakhirMuat = 0, xhrAktif = null;
    window.table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      scrollCollapse: true,
      deferRender: true,
      autoWidth: false,
      order: [],
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100, 250],
      dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
      language: {
        processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…',
        emptyTable: 'Tidak ada pendaftaran pada periode ini.',
        zeroRecords: 'Tidak ada data yang cocok.',
        info: '_START_–_END_ dari _TOTAL_ pasien',
        infoEmpty: '0 pasien',
        infoFiltered: '(disaring dari _MAX_)',
        thousands: '.',
        lengthMenu: 'Tampil _MENU_',
        paginate: { previous: '‹', next: '›' }
      },
      ajax: function(_d, callback) {
        const terima = function(json) {
          terakhirMuat = Date.now();
          if (!json || json.status !== 'success') {
            Swal.fire('Gagal memuat data', (json && json.message) || 'Silakan coba lagi.', 'error');
            callback({ data: [] });
            return;
          }
          tampilHitung(json.hitung);
          $('#rgTerpotong').toggleClass('d-none', !json.terpotong);
          callback({ data: json.data });
        };
        if (dataAwal) {
          const j = dataAwal;
          dataAwal = null;
          terima(j);
          return;
        }
        if (xhrAktif && xhrAktif.readyState !== 4) xhrAktif.abort();
        xhrAktif = $.ajax({
          url: apiUrl,
          type: 'GET',
          dataType: 'json',
          data: {
            v: 2,
            fromDate: $('#fromDate').val(),
            toDate: $('#toDate').val(),
            doctor: $('#doctorSelect').val(),
            provider: $('#providerSelect').val(),
            poli: $('#poliSelect').val(),
            tipe_pasien: tipePasien,
            tab: currentTab
          }
        }).done(terima).fail(function(x, s) {
          if (s === 'abort') return;
          callback({ data: [] });
          if (x.status === 401) {
            Swal.fire('Sesi berakhir', 'Silakan login kembali.', 'warning').then(() => location.href = './');
          } else {
            Swal.fire('Gagal memuat data', 'Periksa koneksi lalu coba lagi.', 'error');
          }
        });
      },
      columns: [{ data: null, orderable: false, searchable: false, className: 'text-center', render: (v, t, r) => t === 'display' ? aksiHtml(r) : '' },
        {
          data: 'visit_antrian',
          render: (v, t, r) => t === 'display' ? `<div class="rg-antri">${esc(v || '-')}</div><div class="small text-muted">${esc(String(r.visit_time || '').slice(0, 5))}${$('#fromDate').val() !== $('#toDate').val() ? ' · ' + esc(tglIndo(r.visit_date)) : ''}</div>` : (v || '')
        },
        {
          data: null,
          render: (v, t, r) => {
            const nama = r.patient_name || r.patient_name_pcare || '-';
            if (t !== 'display') return [nama, r.nomor_rm, r.patient_bpjs, r.visit_antrian, r.visit_ID].join(' ');
            const jk = String(r.patient_gender || '').charAt(0).toUpperCase();
            const u = umur(r.patient_datebirth, r.visit_date);
            return `<div class="fw-semibold rg-nama" title="${esc(nama)}">${esc(nama)}</div>
              <div class="small text-muted"><span class="rg-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + (jk === 'L' ? 'L' : 'P') : ''}${u ? ' · ' + u : ''}${r.patient_bpjs ? ' · <span class="font-monospace" title="No. kartu BPJS">' + esc(r.patient_bpjs) + '</span>' : ''}</div>`;
          }
        },
        {
          data: 'id_poli',
          render: (v, t, r) => t === 'display' ? `<div class="small fw-semibold">${esc(v || '-')}</div><div class="small text-muted rg-dok">${esc(r.id_doctor || '-')}</div>` : (v || '') + ' ' + (r.id_doctor || '')
        },
        {
          data: 'provider_name',
          render: (v, t, r) => { const s = sumber(r); return t === 'display' ? `<div class="small text-nowrap">${esc(String(v || '-').replace('BPJS KESEHATAN', 'BPJS Kesehatan'))}</div><span class="rg-src ${s[1]}">${s[0]}</span>` : (v || '') + ' ' + s[0]; }
        },
        {
          data: 'tekanan_darah', className: 'text-center',
          render: (v, t) => t === 'display' ? (v ? `<span class="badge bg-success-subtle text-success" title="TD ${esc(v)}"><i class="fas fa-check me-1"></i>${esc(v)}</span>` : '<span class="badge bg-danger-subtle text-danger">Belum</span>') : (v ? 1 : 0)
        },
        {
          data: 'visit_status', className: 'text-center',
          render: (v, t) => { const s = STATUS[v] || ['Menunggu', 'secondary']; return t === 'display' ? `<span class="badge bg-${s[1]}-subtle text-${s[1]}">${s[0]}</span>` : v; }
        },
      ]
    });

    // menu Aksi: sel yang menunya terbuka dinaikkan (kolom pertama sticky -> tiap sel punya lapisan sendiri,
    // sehingga menu tertimpa tombol baris berikutnya), dan menu memakai posisi fixed agar tidak terpotong scroll tabel
    $('#periodeTable').on('show.bs.dropdown', function(e) {
      $(e.target).closest('td').addClass('rg-dd-buka').closest('tr').addClass('rg-dd-buka');
    }).on('hidden.bs.dropdown', function(e) {
      $(e.target).closest('td').removeClass('rg-dd-buka').closest('tr').removeClass('rg-dd-buka');
    });
    // tutup menu yang masih terbuka saat tabel di-scroll / digambar ulang (posisi fixed tidak ikut bergeser)
    $('#periodeTable').on('preDraw.dt', function() {
      document.querySelectorAll('#periodeTable [data-bs-toggle="dropdown"]').forEach(b => { const i = bootstrap.Dropdown.getInstance(b); if (i) i.hide(); });
    });
    $(document).on('scroll.rgdd', '.dataTables_scrollBody', function() {
      document.querySelectorAll('#periodeTable .dropdown-menu.show').forEach(m => { const i = bootstrap.Dropdown.getInstance(m.previousElementSibling); if (i) i.hide(); });
    });

    function muatUlang(reset) {
      teksPeriode();
      window.table.ajax.reload(null, !reset);
    }
    $('#rgRefresh').on('click', () => muatUlang(false));
    let tCari;
    $('#customSearch').on('input', function() {
      clearTimeout(tCari);
      const v = this.value;
      tCari = setTimeout(() => window.table.search(v).draw(), 200);
    });

    // muat ulang otomatis (hanya saat melihat hari ini, tab terlihat, dan tidak ada modal/menu terbuka)
    setInterval(function() {
      if (!$('#rgAuto').is(':checked') || document.hidden) return;
      if ($('#toDate').val() !== today || Date.now() - terakhirMuat < 55000) return;
      if ($('.modal.show').length || $('#periodeTable .dropdown-menu.show').length || (window.Swal && Swal.isVisible())) return;
      window.table.ajax.reload(null, false);
    }, 15000);

    // 🔹 Batal pendaftaran
    $(document).on('click', '.delete-btn', function() {
      const visit = $(this).data('visit');
      const prov = String($(this).data('prov'));
      const nama = $(this).data('nama') || '';
      Swal.fire({
        title: 'Batalkan pendaftaran?',
        html: nama ? `<div class="small mb-2">${esc(nama)} · ${esc(visit)}</div>` : '',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Tulis alasan pembatalan…',
        inputAttributes: { 'aria-label': 'Alasan' },
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        preConfirm: (alasan) => {
          if (!alasan || !alasan.trim()) Swal.showValidationMessage('Alasan wajib diisi');
          return alasan && alasan.trim();
        }
      }).then((result) => {
        if (!result.isConfirmed) return;
        const alasan = result.value;
        Swal.fire({ title: 'Membatalkan…', text: 'Sedang memproses data', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        if (prov === '1') {
          $.ajax({
            url: 'controller/wsbpjs/batalAntrian.php',
            type: 'POST',
            data: { novisit: visit, alasan: alasan },
            dataType: 'json'
          }).done(function(res) {
            if (res && res.success) batalAntrian(visit, alasan, prov);
            else Swal.fire('Gagal membatalkan', (res && res.message) || 'Antrean BPJS gagal dibatalkan.', 'error');
          }).fail(() => Swal.fire('Gagal membatalkan', 'Layanan antrean BPJS tidak merespons. Coba lagi.', 'error'));
        } else {
          batalAntrian(visit, alasan, prov);
        }
      });
    });

    function batalAntrian(visit, alasan, prov) {
      $.ajax({
        url: 'controller/admisi/services/deletePendaftaran.php',
        type: 'POST',
        data: { novisit: visit, alasan: alasan, provider: prov },
        dataType: 'json'
      }).done(function(res) {
        // dulu selalu menampilkan "Berhasil" walau server gagal
        if (res && res.success) {
          Swal.fire({ title: 'Pendaftaran dibatalkan', text: res.peringatan || res.message, icon: res.peringatan ? 'warning' : 'success' });
          window.table.ajax.reload(null, false);
        } else {
          Swal.fire('Gagal membatalkan', (res && res.message) || 'Terjadi kesalahan.', 'error');
        }
      }).fail(function(x) {
        Swal.fire('Gagal membatalkan', (x.responseJSON && x.responseJSON.message) || 'Terjadi kesalahan server.', 'error');
      });
    }
    teksPeriode();
    /* ================= DETAIL HASIL PEMERIKSAAN =================
     * - modal langsung tampil dengan identitas dari baris tabel, isi menyusul (skeleton)
     * - detail di-prefetch saat menu Aksi dibuka & disimpan 60 detik
     * - respons basi (modal sudah ganti pasien) diabaikan
     * Dulu: spinner diganti pesan error permanen setelah sekali gagal, kondisi masuk kosong ditampilkan "Lemah",
     * nama dari patient_name_pcare (kosong utk non-BPJS), obat tampil "Obat ID 123".
     */
    const detailCache = new Map();
    function ambilDetail(id) {
      const c = detailCache.get(id);
      if (c && Date.now() - c.t < 60000) return c.p;
      const p = $.ajax({ url: 'controller/visit/getDetailPemeriksaan', data: { id: id }, dataType: 'json' })
        .then(res => {
          if (!res || res.status !== true) return $.Deferred().reject({ responseJSON: res }).promise();
          return res.data;
        });
      p.fail(() => detailCache.delete(id));
      detailCache.set(id, { t: Date.now(), p: p });
      return p;
    }
    window.rgAmbilDetail = ambilDetail;
    $('#periodeTable').on('show.bs.dropdown', function(e) {
      const id = $(e.target).closest('td').find('.detail-btn').data('id');
      if (id) ambilDetail(id);
    });

    // kosong = null / "" / hanya tanda baca ("-", ",", ...)
    const isi = v => v !== null && v !== undefined && /[0-9A-Za-z\u00C0-\uFFFF]/.test(String(v));
    // kolom kondisi_masuk diisi dua cara: perawat (Baik/Lemah/...) dan dokter (kode prognosa PCare 01-05)
    const PROGNOSA = { '01': 'Sanam (sembuh)', '02': 'Bonam (baik)', '03': 'Malam (buruk)', '04': 'Dubia ad sanam (cenderung sembuh)', '05': 'Dubia ad malam (cenderung buruk)' };
    function kondisiHtml(v) {
      if (!isi(v)) return '';
      v = String(v).trim();
      return PROGNOSA[v] ? `<span class="dt-chip"><i class="fas fa-chart-line me-1"></i>Prognosa: ${esc(PROGNOSA[v])}</span>`
        : `<span class="dt-chip"><i class="fas fa-person me-1"></i>Kondisi masuk: ${esc(v)}</span>`;
    }
    const teks = v => isi(v) ? esc(String(v).trim()).replace(/\n/g, '<br>') : '';
    const kosong = t => `<span class="text-muted fst-italic small">${t || 'Belum diisi'}</span>`;
    function inisial(n) { return String(n || '?').replace(/^\s*(an|by|ny|tn|nn|sdr|sdri)\.?\s+/i, '').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?'; }
    function umurLengkap(lahir, pada) {
      const a = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/), b = String(pada || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (!a || !b || a[1] < '1900') return '';
      let th = b[1] - a[1], bl = b[2] - a[2], hr = b[3] - a[3];
      if (hr < 0) { bl--; hr += new Date(+b[1], +b[2] - 1, 0).getDate(); }
      if (bl < 0) { th--; bl += 12; }
      if (th < 0) return '';
      return th >= 5 ? th + ' tahun' : (th ? th + ' th ' : '') + bl + ' bln' + (th ? '' : ' ' + hr + ' hr');
    }
    function kepala(r) {
      const nama = r.patient_name || r.patient_name_pcare || '-';
      const jk = String(r.patient_gender || '').charAt(0).toUpperCase();
      $('#dtAva').text(inisial(nama)).toggleClass('dt-ava-p', jk === 'P');
      $('#dtNama').text(nama).attr('title', nama);
      const u = umurLengkap(r.patient_datebirth, r.visit_date);
      $('#dtSub').html([`<span class="rg-rm">${esc(r.nomor_rm || '-')}</span>`, jk ? (jk === 'L' ? 'Laki-laki' : 'Perempuan') : '', u, r.patient_bpjs ? 'BPJS ' + esc(r.patient_bpjs) : ''].filter(Boolean).join(' · '));
      const st = STATUS[r.visit_status] || null;
      $('#dtChips').html([
        isi(r.id_poli) ? `<span class="dt-chip"><i class="fas fa-hospital me-1"></i>${esc(r.id_poli)}</span>` : '',
        isi(r.id_doctor) ? `<span class="dt-chip"><i class="fas fa-user-md me-1"></i>${esc(r.id_doctor)}</span>` : '',
        `<span class="dt-chip"><i class="far fa-calendar me-1"></i>${esc(tglIndo(r.visit_date))}${r.visit_time ? ' · ' + esc(String(r.visit_time).slice(0, 5)) : ''}</span>`,
        isi(r.provider_name) ? `<span class="dt-chip"><i class="fas fa-wallet me-1"></i>${esc(r.provider_name)}</span>` : '',
        st ? `<span class="badge bg-${st[1]}-subtle text-${st[1]} align-self-center">${st[0]}</span>` : ''
      ].join(''));
      $('#dtVisit').text(r.visit_ID || '');
    }
    function skeleton() {
      return '<div class="row g-2 mb-3">' + '<div class="col-6 col-md-3"><div class="dt-sk" style="height:64px"></div></div>'.repeat(8) + '</div>' +
        '<div class="dt-sk mb-3" style="height:70px"></div><div class="dt-sk mb-3" style="height:70px"></div><div class="dt-sk" style="height:110px"></div>';
    }
    function bagian(ikon, judul, isiHtml, ekstra) {
      return `<div class="dt-sec"><div class="dt-sec-h"><i class="${ikon} me-2"></i>${judul}${ekstra || ''}</div>${isiHtml}</div>`;
    }
    function diagnosaHtml(d) {
      const baris = [];
      const tambah = (kode, nama, utama) => {
        kode = isi(kode) ? String(kode).trim() : '';
        nama = isi(nama) && String(nama).trim() !== 'ICD10_2010' ? String(nama).trim() : '';
        if (!kode && !nama) return;
        if (baris.some(b => b.kode && b.kode === kode)) return;
        baris.push({ kode, nama, utama });
      };
      if (isi(d.kdDiag1) || isi(d.nmDiag1)) tambah(d.kdDiag1, d.nmDiag1, true);
      else if (isi(d.diagnosa)) tambah(d.diagnosa, isi(d.diagnosa_keterangan_ind) && d.diagnosa_keterangan_ind !== 'ICD10_2010' ? d.diagnosa_keterangan_ind : d.diagnosa_keterangan, true);
      tambah(d.kdDiag2, d.nmDiag2, false);
      tambah(d.kdDiag3, d.nmDiag3, false);
      let h = baris.map(b => `<div class="d-flex align-items-start gap-2 mb-1">${b.kode ? `<span class="dt-icd">${esc(b.kode)}</span>` : ''}<span>${esc(b.nama || '')}</span>${b.utama ? '<span class="badge bg-primary-subtle text-primary ms-auto">Utama</span>' : '<span class="badge bg-light text-muted ms-auto">Sekunder</span>'}</div>`).join('');
      if (isi(d.diagnosa_sekunder)) h += `<div class="small text-muted mt-1">Diagnosa sekunder: ${teks(d.diagnosa_sekunder)}</div>`;
      return h || kosong();
    }
    function vitalHtml(d) {
      const td = isi(d.tekanan_darah) ? d.tekanan_darah : (isi(d.sistole) && isi(d.diastole) ? d.sistole + '/' + d.diastole : '');
      const T = [
        ['Tekanan darah', td, 'mmHg', 'fa-heart-pulse text-danger'],
        ['Nadi', d.nadi, 'x/mnt', 'fa-wave-square text-danger'],
        ['Suhu', d.suhu, '°C', 'fa-temperature-half text-warning'],
        ['Respirasi', d.respirasi, 'x/mnt', 'fa-lungs text-info'],
        ['SpO₂', d.saturasi, '%', 'fa-droplet text-primary'],
        ['Tinggi', d.tinggi_badan, 'cm', 'fa-ruler-vertical text-secondary'],
        ['Berat', d.berat_badan, 'kg', 'fa-weight-scale text-secondary'],
        ['BMI', d.bmi, isi(d.bmi_keterangan) ? d.bmi_keterangan : '', 'fa-chart-simple text-success'],
      ];
      if (isi(d.lingkar_perut)) T.push(['Lingkar perut', d.lingkar_perut, 'cm', 'fa-ruler-horizontal text-secondary']);
      const terisi = T.filter(t => isi(t[1])).length;
      return {
        terisi,
        html: '<div class="row g-2">' + T.map(t => `<div class="col-6 col-md-3"><div class="dt-vital ${isi(t[1]) ? '' : 'kosong'}">
          <div class="dt-vital-l"><i class="fas ${t[3]} me-1"></i>${t[0]}</div>
          <div class="dt-vital-v">${isi(t[1]) ? esc(t[1]) : '—'} <small>${isi(t[1]) ? esc(t[2]) : ''}</small></div></div></div>`).join('') + '</div>'
      };
    }
    function obatHtml(obat) {
      if (!obat || !obat.length) return kosong('Tidak ada resep');
      const grup = {};
      obat.forEach(o => { (grup[o.id_permintaan_farmasi] = grup[o.id_permintaan_farmasi] || []).push(o); });
      return Object.values(grup).map(g => {
        const p = g[0], racik = String(p.tipe_obat || '').trim().toLowerCase() === 'racikan', bmhp = String(p.tipe_obat || '').trim().toUpperCase() === 'BMHP';
        return `<div class="table-responsive"><table class="table table-sm align-middle mb-2 dt-obat"><thead><tr><th>${bmhp ? 'BMHP' : 'Obat'}${racik ? ' <span class="badge bg-warning-subtle text-warning">Racikan</span>' : ''}</th><th>Aturan pakai</th><th class="text-end">Jumlah</th></tr></thead><tbody>
          ${g.map(o => `<tr><td>${esc(o.nama_obat || ('Obat ID ' + o.id_pharmacy))}${isi(o.catatan) ? `<div class="small text-muted">${esc(o.catatan)}</div>` : ''}</td>
            <td>${isi(o.signa) ? esc(o.signa) : (racik && isi(p.rck_signa) ? esc(p.rck_signa) : '-')}</td>
            <td class="text-end text-nowrap">${isi(o.qty) ? esc(o.qty) : (isi(o.qty_racik) ? esc(o.qty_racik) : '-')} ${esc(o.pharmacy_unit || '')}</td></tr>`).join('')}
          ${racik && isi(p.rck_jumlah) ? `<tr><td colspan="3" class="small text-muted">Dibuat ${esc(p.rck_jumlah)} ${esc(p.rck_satuan || '')}${isi(p.rck_signa) ? ' · ' + esc(p.rck_signa) : ''}</td></tr>` : ''}
          </tbody></table></div>`;
      }).join('');
    }
    function isiDetail(d) {
      kepala(d);
      const v = vitalHtml(d);
      const tindakan = (d.tindakan || []).filter(t => isi(t.billing_item));
      $('#dtVitalBtn').attr('data-id', d.id_visit).data('id', d.id_visit).toggleClass('d-none', v.terisi > 0);
      $('#dtBody').html(
        (isi(d.kondisi_masuk) ? `<div class="mb-2">${kondisiHtml(d.kondisi_masuk)}</div>` : '') +
        bagian('fas fa-heart-pulse text-danger', 'Vital sign', v.terisi ? v.html : kosong('Vital sign belum diisi')) +
        bagian('fas fa-comment-medical text-primary', 'Keluhan / anamnesa',
          (isi(d.anamnesa) ? `<div>${teks(d.anamnesa)}</div>` : kosong()) +
          (isi(d.keluhan_penyerta) ? `<div class="small mt-1"><span class="text-muted">Keluhan penyerta:</span> ${teks(d.keluhan_penyerta)}</div>` : '') +
          (isi(d.alergi_obat) ? `<div class="small mt-1 text-danger"><i class="fas fa-triangle-exclamation me-1"></i>Alergi obat: ${teks(d.alergi_obat)}</div>` : '')) +
        (isi(d.pemeriksaan_fisik) ? bagian('fas fa-stethoscope text-info', 'Pemeriksaan fisik', `<div>${teks(d.pemeriksaan_fisik)}</div>`) : '') +
        bagian('fas fa-file-medical text-success', 'Diagnosa', diagnosaHtml(d)) +
        bagian('fas fa-syringe text-warning', 'Tindakan', tindakan.length ? '<ul class="mb-0 ps-3">' + tindakan.map(t => `<li>${esc(t.billing_item)}${+t.billing_qty > 1 ? ' <span class="text-muted">x' + esc(t.billing_qty) + '</span>' : ''}</li>`).join('') + '</ul>' : kosong('Tidak ada tindakan')) +
        bagian('fas fa-pills text-primary', 'Obat', obatHtml(d.obat), d.obat && d.obat.length ? ` <span class="badge bg-light text-muted ms-1">${d.obat.length} item</span>` : '') +
        (isi(d.rencana_tindak_lanjut) ? bagian('fas fa-route text-secondary', 'Rencana tindak lanjut', `<div>${teks(d.rencana_tindak_lanjut)}</div>`) : '') +
        (isi(d.catatan_screening) ? bagian('fas fa-note-sticky text-secondary', 'Catatan skrining', `<div>${teks(d.catatan_screening)}</div>`) : '')
      );
    }

    let detailAktif = null;
    $(document).on('click', '.detail-btn', function() { bukaDetail($(this).data('id')); });
    function bukaDetail(id) {
      detailAktif = id;
      const baris = window.table.rows().data().toArray().find(r => r.id_visit === id);
      if (baris) kepala(baris);
      else { $('#dtNama').text('Hasil pemeriksaan'); $('#dtSub, #dtChips, #dtVisit').html(''); }
      $('#dtVitalBtn').addClass('d-none');
      $('#dtBody').html(skeleton());
      bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();
      ambilDetail(id).done(d => { if (detailAktif === id) isiDetail(d); })
        .fail(x => {
          if (detailAktif !== id) return;
          const pesan = (x && x.responseJSON && x.responseJSON.message) || 'Gagal mengambil data pemeriksaan.';
          $('#dtBody').html(`<div class="text-center py-5"><i class="fas fa-circle-exclamation fa-2x text-danger mb-2 d-block"></i><div class="mb-3">${esc(pesan)}</div>
            <button type="button" class="btn btn-sm btn-primary" id="dtUlang" data-id="${esc(id)}"><i class="fas fa-rotate-right me-1"></i>Coba lagi</button></div>`);
        });
    }
    $(document).on('click', '#dtUlang', function() {
      const id = $(this).data('id');
      detailCache.delete(id);
      bukaDetail(id);
    });
    // data detail berubah setelah vital sign disimpan
    $(document).on('hidden.bs.modal', '#screeningModal, #ttdModal', () => detailCache.clear());
  });
</script>
<script>
  /* ================= PENDAFTARAN TANPA IDENTITAS =================
   * Dulu: hanya bisa memilih pasien yang sudah terdaftar (tidak bisa membuat pasien anonim); dokter pertama terpilih
   * diam-diam (variabel "status" tidak terdefinisi); jam praktik diambil dari teks dropdown sehingga dokter yang
   * "(Sudah Tutup)" tersimpan dengan nama & jam salah; halaman di-reload penuh setelah simpan.
   */
  (function() {
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
    const jamJkt = () => new Date().toLocaleTimeString('en-GB', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit' });
    const inisial = n => String(n || '?').replace(/^\s*(drg?)\.?\s*/i, '').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
    const modal = bootstrap.Modal.getOrCreateInstance($id('poliModal'));
    let poliList = null, provList = null, dokter = [], dokterPilih = null, simpanJalan = false, dokterXhr = null;

    const mode = () => (document.querySelector('input[name=ti_mode]:checked') || {}).value || 'baru';
    function namaDefault() {
      const u = $id('tiUmur').value, jk = (document.querySelector('input[name=ti_jk]:checked') || {}).value;
      return (u !== '' && +u < 17 ? 'An. ' : (jk === 'P' ? 'Ny. ' : 'Tn. ')) + 'X (Tanpa Identitas)';
    }
    let namaDiubah = false;
    function segarkanNama() { if (!namaDiubah) $id('tiNama').value = namaDefault(); ringkas(); }
    $id('tiNama').addEventListener('input', () => { namaDiubah = $id('tiNama').value.trim() !== ''; ringkas(); });
    ['tiUmur', 'tiJkL', 'tiJkP'].forEach(i => $id(i).addEventListener(i === 'tiUmur' ? 'input' : 'change', segarkanNama));
    $id('tiMode').addEventListener('change', () => {
      const b = mode() === 'baru';
      $id('tiBaruBox').classList.toggle('d-none', !b);
      $id('tiLamaBox').classList.toggle('d-none', b);
      if (!b) initCari();
      ringkas();
    });

    $(document).on('click', '.poli-btn', function() {
      $id('formPoli').reset();
      namaDiubah = false;
      $id('tiBaru').checked = true;
      $id('tiBaruBox').classList.remove('d-none');
      $id('tiLamaBox').classList.add('d-none');
      $id('poli_date').value = tglJkt(0);
      $id('poli_date').min = tglJkt(-7);
      $id('poli_date').max = tglJkt(0);
      $id('poli_time').value = jamJkt();
      $id('tiError').classList.add('d-none');
      dokterPilih = null;
      segarkanNama();
      muatPoli();
      muatProvider();
      modal.show();
    });

    function muatPoli() {
      const isi = () => {
        const sakit = poliList.filter(p => p.poliSakit == true || p.poliSakit === '1' || p.poliSakit === 1);
        $id('poli_poli').innerHTML = '<option value="">— Pilih —</option>' + sakit.map(p => `<option value="${esc(p.nmPoli)}" data-kd="${esc(p.kdPoli)}">${esc(p.nmPoli)}</option>`).join('');
        const ugd = sakit.find(p => /ugd|igd|gawat/i.test(p.nmPoli)) || sakit.find(p => /umum/i.test(p.nmPoli));
        if (ugd) $id('poli_poli').value = ugd.nmPoli;
        gantiPoli();
      };
      if (poliList) return isi();
      $id('poli_poli').innerHTML = '<option value="">Memuat…</option>';
      $.ajax({ url: 'module/admin/get_master_poli.php', type: 'POST', dataType: 'json' })
        .done(r => { poliList = (r && r.data) || []; isi(); })
        .fail(() => { $id('poli_poli').innerHTML = '<option value="">Gagal memuat poli</option>'; });
    }
    function muatProvider() {
      const isi = () => {
        $id('poli_provider').innerHTML = provList.map(p => `<option value="${esc(p.id)}">${esc(p.text)}</option>`).join('');
        const tunai = provList.find(p => /tunai|umum/i.test(p.text));
        if (tunai) $id('poli_provider').value = tunai.id;
      };
      if (provList) return isi();
      // pasien tanpa identitas tidak bisa didaftarkan ke BPJS -> daftar penjamin non-BPJS
      $.getJSON('controller/admisi/services/get_provider.php', { type: 'UMUM' }).done(r => { provList = r || []; isi(); });
    }
    function gantiPoli() {
      dokterPilih = null;
      const kd = ($id('poli_poli').selectedOptions[0] || {}).dataset ? $id('poli_poli').selectedOptions[0].dataset.kd : '';
      if (dokterXhr) dokterXhr.abort();
      if (!kd) { $id('tiDokter').innerHTML = '<div class="small text-muted">Pilih poli terlebih dahulu.</div>'; $id('tiHari').textContent = ''; ringkas(); return; }
      $id('tiDokter').innerHTML = '<div class="tid-sk"></div><div class="tid-sk"></div>';
      dokterXhr = $.getJSON('controller/admisi/services/getDokterlocal.php', { kdpoli: kd, tanggal: $id('poli_date').value }).done(r => {
        dokter = ((r && r.data) || []).map(d => ({ kode: String(d.kodedokter), nama: d.namadokter, jam: d.jampraktek, tutup: !!d.exp }));
        $id('tiHari').textContent = r && r.hari ? '· jadwal hari ' + r.hari : '';
        if (!dokter.length) {
          // kondisi darurat: tidak ada jadwal -> tampilkan semua dokter / tenaga klinik
          $id('tiHari').innerHTML = '· <span class="text-warning-emphasis">tidak ada jadwal di poli ini, menampilkan semua tenaga</span>';
          $.ajax({ url: 'module/admin/get_dokter_bpjs.php', type: 'POST', dataType: 'json' }).done(x => {
            dokter = ((x && x.data) || []).map(d => ({ kode: String(d.kdDokter), nama: d.nmDokter, jam: '', tutup: false }));
            if (!dokter.length) { $id('tiDokter').innerHTML = '<div class="small text-danger">Belum ada data dokter di master.</div>'; return; }
            renderDokter();
          });
          return;
        }
        const buka = dokter.filter(d => !d.tutup);
        if (buka.length === 1) dokterPilih = buka[0];
        renderDokter();
      }).fail((x, s) => { if (s !== 'abort') $id('tiDokter').innerHTML = '<div class="small text-danger">Gagal memuat dokter.</div>'; });
    }
    function renderDokter() {
      $id('tiDokter').innerHTML = dokter.map(d => `<button type="button" class="tid-dk ${dokterPilih && dokterPilih.kode === d.kode ? 'pilih' : ''}" data-kode="${esc(d.kode)}" ${d.tutup ? 'disabled title="Jam praktik sudah selesai"' : ''}>
        <span class="tid-ava">${esc(inisial(d.nama))}</span><span class="min-w-0 text-start"><span class="d-block fw-semibold small text-truncate">${esc(d.nama)}</span>
        <span class="d-block small ${d.tutup ? 'text-danger' : 'text-muted'}">${d.jam ? esc(d.jam) : 'tanpa jadwal'}${d.tutup ? ' · sudah tutup' : ''}</span></span></button>`).join('');
      ringkas();
    }
    $id('tiDokter').addEventListener('click', e => { const b = e.target.closest('.tid-dk'); if (b && !b.disabled) { dokterPilih = dokter.find(d => d.kode === b.dataset.kode); renderDokter(); } });
    $id('poli_poli').addEventListener('change', gantiPoli);
    $id('poli_date').addEventListener('change', gantiPoli);

    function initCari() {
      const $s = $('#id_patient_select');
      if ($s.hasClass('select2-hidden-accessible')) return;
      $s.select2({
        dropdownParent: $('#poliModal'), width: '100%', placeholder: 'Ketik nama, RM, NIK, atau no. BPJS…', minimumInputLength: 2,
        ajax: {
          url: 'controller/admisi/patientSearchController', dataType: 'json', delay: 250,
          data: p => ({ search: p.term }),
          processResults: d => ({ results: ((d && d.data) || []).map(i => ({ id: i.id_patient, text: `${i.patient_name} (${i.nomor_rm})`, patient_name: i.patient_name, rm: i.nomor_rm, nik: i.patient_nik })) })
        },
        templateResult: d => d.loading ? d.text : $(`<div><div class="fw-semibold">${esc(d.patient_name || d.text)}</div><div class="small text-muted">RM ${esc(d.rm || '-')}${d.nik ? ' · NIK ' + esc(d.nik) : ''}</div></div>`),
        language: { inputTooShort: () => 'Ketik minimal 2 karakter', noResults: () => 'Pasien tidak ditemukan', searching: () => 'Mencari…' }
      }).on('change', ringkas);
    }

    function ringkas() {
      const nama = mode() === 'baru' ? ($id('tiNama').value || namaDefault()) : (($('#id_patient_select').select2 && $('#id_patient_select').hasClass('select2-hidden-accessible') ? ($('#id_patient_select').select2('data')[0] || {}).patient_name : '') || '');
      $id('tiRingkas').innerHTML = [nama, $id('poli_poli').value, dokterPilih ? dokterPilih.nama : ''].filter(Boolean).map(esc).join(' · ');
    }

    $id('btnSavePoli').addEventListener('click', function(e) { kirim(false); });
    function kirim(paksa) {
      if (simpanJalan) return;
      const err = [];
      const baru = mode() === 'baru';
      const idp = baru ? '' : $('#id_patient_select').val();
      if (!baru && !idp) err.push('pilih pasien');
      if (!$id('poli_poli').value) err.push('poli');
      if (!dokterPilih) err.push('dokter');
      if (!$id('poli_provider').value) err.push('penjamin');
      const t = $id('poli_date').value;
      if (!t || t < tglJkt(-7) || t > tglJkt(0)) err.push('tanggal (hari ini s.d. 7 hari ke belakang)');
      if (!$id('poli_time').value) err.push('jam datang');
      const u = $id('tiUmur').value;
      if (baru && u !== '' && (+u < 0 || +u > 120)) err.push('perkiraan umur 0–120');
      $id('tiError').textContent = err.length ? 'Lengkapi: ' + err.join(', ') + '.' : '';
      $id('tiError').classList.toggle('d-none', !err.length);
      if (err.length) return;

      const sel = baru ? null : $('#id_patient_select').select2('data')[0];
      const data = {
        pasien_baru: baru ? '1' : '0', id_patient: idp || '', paksa: paksa ? '1' : '0',
        nama: baru ? ($id('tiNama').value.trim() || namaDefault()) : '', jk: (document.querySelector('input[name=ti_jk]:checked') || {}).value || 'L',
        umur: baru ? u : '', ciri: baru ? $id('tiCiri').value.trim() : '',
        patient_name_pcare: sel ? (sel.patient_name || '') : '',
        id_doctor: dokterPilih.kode, nama_dokter: dokterPilih.nama, jampraktek: dokterPilih.jam,
        id_poli: $id('poli_poli').value, poli_name: $id('poli_poli').value, kdPoli: $id('poli_poli').selectedOptions[0].dataset.kd,
        id_provider: $id('poli_provider').value, visit_date: t, visit_time: $id('poli_time').value, keluhan: $id('tiKeluhan').value.trim()
      };
      const btn = $id('btnSavePoli'), label = btn.innerHTML;
      simpanJalan = true;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mendaftarkan…';
      $.ajax({ url: 'controller/visit/visitController', type: 'POST', data, dataType: 'json' })
        .done(r => {
          if (r && r.status === 'success') {
            if (modal._isTransitioning) $id('poliModal').addEventListener('shown.bs.modal', () => modal.hide(), { once: true }); else modal.hide();
            Swal.fire({ icon: 'success', title: 'Pasien terdaftar', html: `<div class="small">${esc(r.data.nama)}${r.data.nomor_rm ? ' · RM ' + esc(r.data.nomor_rm) : ''}</div>` +
              `<div class="mt-2">Nomor antrian</div><div style="font-size:2.2rem;font-weight:800;color:#dc3545">${esc(r.data.antrian || '-')}</div>` +
              (baru ? '<div class="small text-muted">Lengkapi identitas pasien di menu Data Pasien setelah diketahui.</div>' : '') });
            if (window.table) window.table.ajax.reload(null, false);
          } else if (r && r.dobel) {
            Swal.fire({ icon: 'warning', title: 'Sudah terdaftar', text: r.message + ' Tetap daftarkan?', showCancelButton: true, confirmButtonText: 'Ya, daftarkan', cancelButtonText: 'Batal', reverseButtons: true })
              .then(x => { if (x.isConfirmed) kirim(true); });
          } else {
            Swal.fire('Gagal mendaftar', (r && r.message) || 'Terjadi kesalahan.', 'error');
          }
        })
        .fail(x => Swal.fire('Gagal mendaftar', (x.responseJSON && x.responseJSON.message) || 'Terjadi kesalahan pada server.', 'error'))
        .always(() => { simpanJalan = false; btn.disabled = false; btn.innerHTML = label; });
    }
  })();
</script>
<script>
  /* ================= VITAL SIGN & TANDA TANGAN =================
   * Dulu (vital sign): kondisi masuk berisi kode prognosa dokter (01-05) tidak cocok dengan pilihan -> tersimpan kosong
   * dan menimpa prognosa; modal tidak terbuka bila request gagal; tanpa validasi rentang; BMI lama tertinggal.
   * Dulu (tanda tangan): CSS "height:400" tanpa px -> kanvas tampil 150px tapi resolusi 400px sehingga coretan bergeser;
   * ukuran dihitung saat modal tersembunyi; kanvas kosong bisa disimpan; tanpa pesan gagal; tabel tidak diperbarui.
   */
  (function() {
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const PROGNOSA = { '01': 'Sanam (sembuh)', '02': 'Bonam (baik)', '03': 'Malam (buruk)', '04': 'Dubia ad sanam', '05': 'Dubia ad malam' };
    const barisTabel = id => (window.table ? window.table.rows().data().toArray().find(r => +r.id_visit === +id) : null) || null;
    const subJudul = r => r ? [r.patient_name || r.patient_name_pcare, r.nomor_rm, r.visit_antrian ? 'Antrian ' + r.visit_antrian : ''].filter(Boolean).map(esc).join(' · ') : '&nbsp;';
    const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2000 });
    // Bootstrap mengabaikan hide() selama animasi buka berjalan -> tunda sampai modal selesai tampil
    function tutup(m) {
      if (m._isTransitioning) m._element.addEventListener('shown.bs.modal', () => m.hide(), { once: true });
      else m.hide();
    }
    function umurTahun(lahir) {
      const m = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (!m) return null;
      const n = new Date(), b = new Date(+m[1], m[2] - 1, +m[3]);
      let u = n.getFullYear() - b.getFullYear();
      if (n < new Date(n.getFullYear(), b.getMonth(), b.getDate())) u--;
      return u;
    }

    /* ---------------- VITAL SIGN ---------------- */
    const vsModal = bootstrap.Modal.getOrCreateInstance($id('screeningModal'));
    const ANGKA = ['sistole', 'diastole', 'nadi', 'suhu', 'respirasi', 'saturasi', 'tinggi', 'berat', 'lingkar_perut'];
    let vsAktif = null, vsSimpan = false, lpWajib = true;

    function hitungBMI() {
      const tb = parseFloat($id('tinggi').value), bb = parseFloat($id('berat').value);
      const el = $id('vsBmi');
      if (!(tb >= 30 && tb <= 250) || !(bb > 0 && bb <= 350)) { el.textContent = '—'; el.className = 'vs-bmi'; return; }
      const bmi = bb / Math.pow(tb / 100, 2);
      const k = bmi < 18.5 ? ['Kurus', 'warning'] : bmi < 25 ? ['Normal', 'success'] : bmi < 30 ? ['Gemuk', 'warning'] : ['Obesitas', 'danger'];
      el.innerHTML = `${bmi.toFixed(2)} <span class="badge bg-${k[1]}-subtle text-${k[1]}">${k[0]}</span>`;
      el.className = 'vs-bmi';
    }
    window.hitungBMI = hitungBMI;
    ['tinggi', 'berat'].forEach(i => $id(i).addEventListener('input', hitungBMI));
    ANGKA.forEach(i => $id(i).addEventListener('input', function() { this.classList.remove('is-invalid'); $id('vsError').classList.add('d-none'); }));
    $id('kondisi_masuk').addEventListener('change', function() { this.classList.remove('is-invalid'); });

    function kosongkan() {
      $id('vsForm').reset();
      $id('vsForm').querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
      $id('vsError').classList.add('d-none');
      $id('vsPrognosa').classList.add('d-none');
      hitungBMI();
    }
    function isiForm(d) {
      $id('sc_keluhan').value = d.anamnesa || '';
      $id('sc_catatan').value = d.catatan_screening || '';
      let s = d.sistole, di = d.diastole;
      if ((!s || !di) && d.tekanan_darah && String(d.tekanan_darah).includes('/')) [s, di] = String(d.tekanan_darah).split('/');
      const isi = { sistole: s, diastole: di, nadi: d.nadi, suhu: d.suhu, respirasi: d.respirasi, saturasi: d.saturasi, tinggi: d.tinggi_badan, berat: d.berat_badan, lingkar_perut: d.lingkar_perut };
      // nilai 0 (data lama, mis. SpO2 "0") dianggap kosong agar tidak menghalangi simpan
      ANGKA.forEach(k => { const v = isi[k]; const t = v !== null && v !== undefined ? String(v).trim().replace(',', '.') : ''; $id(k).value = /\d/.test(t) && parseFloat(t) !== 0 ? t : ''; });
      // kode prognosa dokter tidak ditimpa: pilihan dibiarkan kosong & tidak dikirim
      const km = String(d.kondisi_masuk || '').trim();
      const ada = [...$id('kondisi_masuk').options].some(o => o.value === km);
      $id('kondisi_masuk').value = ada ? km : '';
      $id('vsPrognosa').classList.toggle('d-none', !PROGNOSA[km]);
      if (PROGNOSA[km]) $id('vsPrognosa').innerHTML = `<i class="fas fa-circle-info me-1"></i>Prognosa dokter tercatat: <b>${esc(PROGNOSA[km])}</b> (tidak diubah bila kondisi masuk dikosongkan)`;
      hitungBMI();
    }
    function tampilVs(state) {
      $id('vsMuat').classList.toggle('d-none', state !== 'muat');
      $id('vsGagal').classList.toggle('d-none', state !== 'gagal');
      $id('vsForm').classList.toggle('d-none', state !== 'form');
      $id('btnSaveScreening').disabled = state !== 'form';
    }

    $(document).on('click', '.screening-btn', function() {
      const id = $(this).data('id');
      if (!id) return;
      vsAktif = id;
      const r = barisTabel(id);
      $id('screening_id_visit').value = id;
      $id('vsSub').innerHTML = subJudul(r);
      const u = r ? umurTahun(r.patient_datebirth) : null;
      lpWajib = u === null || u >= 15; // lingkar perut wajib untuk usia >= 15 th
      $id('vsLpWajib').classList.toggle('d-none', !lpWajib);
      kosongkan();
      tampilVs('muat');
      vsModal.show();
      const ambil = window.rgAmbilDetail ? window.rgAmbilDetail(id) : $.getJSON('controller/visit/getDetailPemeriksaan', { id }).then(x => x && x.status === true ? x.data : $.Deferred().reject({ responseJSON: x }));
      $.when(ambil).done(d => {
        if (vsAktif !== id) return;
        if (!r) $id('vsSub').innerHTML = subJudul(d);
        isiForm(d);
        tampilVs('form');
        setTimeout(() => ($id('sistole').value ? $id('sc_keluhan') : $id('sistole')).focus(), 250);
      }).fail(x => {
        if (vsAktif !== id) return;
        $id('vsGagal').innerHTML = `<i class="fas fa-circle-exclamation fa-2x text-danger d-block mb-2"></i><div class="mb-3">${esc((x && x.responseJSON && x.responseJSON.message) || 'Gagal memuat data kunjungan.')}</div>
          <button type="button" class="btn btn-sm btn-primary" id="vsUlang"><i class="fas fa-rotate-right me-1"></i>Coba lagi</button>`;
        tampilVs('gagal');
      });
    });
    $(document).on('click', '#vsUlang', function() { $('<a class="screening-btn">').data('id', vsAktif).appendTo('body').trigger('click').remove(); });

    function validasi() {
      const err = [];
      let pertama = null;
      const tandai = (el, pesan) => { el.classList.add('is-invalid'); err.push(pesan); if (!pertama) pertama = el; };
      ANGKA.forEach(k => {
        const el = $id(k), v = el.value.trim();
        const wajib = el.hasAttribute('required') || (k === 'lingkar_perut' && lpWajib);
        if (v === '') { if (wajib) tandai(el, el.dataset.label + ' wajib diisi'); return; }
        const n = parseFloat(v.replace(',', '.'));
        if (isNaN(n) || n < +el.dataset.min || n > +el.dataset.max) tandai(el, `${el.dataset.label} ${el.dataset.min}–${el.dataset.max}`);
      });
      const s = +$id('sistole').value, d = +$id('diastole').value;
      if (s && d && s <= d) tandai($id('sistole'), 'Sistole harus lebih besar dari diastole');
      if (!$id('kondisi_masuk').value && $id('vsPrognosa').classList.contains('d-none')) tandai($id('kondisi_masuk'), 'Kondisi masuk wajib dipilih');
      $id('vsError').textContent = err.join(' · ');
      $id('vsError').classList.toggle('d-none', !err.length);
      if (pertama) pertama.focus();
      return !err.length;
    }

    $id('screeningModal').addEventListener('hide.bs.modal', e => { if (vsSimpan) e.preventDefault(); });
    $id('screeningModal').addEventListener('keydown', e => { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); $id('btnSaveScreening').click(); } });
    $id('vsForm').addEventListener('submit', e => e.preventDefault());

    $id('btnSaveScreening').addEventListener('click', async function() {
      if (vsSimpan || !validasi()) return;
      const id = $id('screening_id_visit').value;
      const data = { id_visit: id, keluhan: $id('sc_keluhan').value, catatan: $id('sc_catatan').value, kondisi_masuk: $id('kondisi_masuk').value };
      ANGKA.forEach(k => data[k] = $id(k).value.trim().replace(',', '.'));
      const btn = this, label = btn.innerHTML;
      vsSimpan = true;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
      try {
        const r = await fetch('controller/visit/saveScreening.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data), credentials: 'same-origin' });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal menyimpan (HTTP ' + r.status + ').');
        vsSimpan = false;
        tutup(vsModal);
        toast('success', 'Vital sign tersimpan');
        if (window.table) window.table.ajax.reload(null, false);
      } catch (e) {
        Swal.fire('Gagal menyimpan', e.message, 'error');
      } finally {
        vsSimpan = false;
        btn.disabled = false;
        btn.innerHTML = label;
      }
    });

    /* ---------------- TANDA TANGAN ---------------- */
    const ttdModal = bootstrap.Modal.getOrCreateInstance($id('ttdModal'));
    const pad = $id('signaturePad'), ctx = pad.getContext('2d');
    let goresan = [], aktifG = null;
    function ukuran() {
      const r = pad.getBoundingClientRect();
      if (!r.width) return;
      const dpr = Math.min(window.devicePixelRatio || 1, 2.5);
      pad.width = Math.round(r.width * dpr);
      pad.height = Math.round(r.height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      gambarUlang();
    }
    function gambarG(g) {
      const p = g.p;
      ctx.strokeStyle = ctx.fillStyle = '#111827';
      ctx.lineCap = ctx.lineJoin = 'round';
      if (p.length === 1) { ctx.beginPath(); ctx.arc(p[0][0], p[0][1], 1.5, 0, Math.PI * 2); ctx.fill(); return; }
      for (let i = 1; i < p.length; i++) {
        ctx.lineWidth = 3 * (0.6 + 0.8 * (p[i][2] ?? 0.5));
        const a = p[i - 1], b = p[i];
        const m0 = i > 1 ? [(p[i - 2][0] + a[0]) / 2, (p[i - 2][1] + a[1]) / 2] : a;
        ctx.beginPath();
        ctx.moveTo(m0[0], m0[1]);
        ctx.quadraticCurveTo(a[0], a[1], (a[0] + b[0]) / 2, (a[1] + b[1]) / 2);
        ctx.stroke();
      }
    }
    function gambarUlang() {
      ctx.save(); ctx.setTransform(1, 0, 0, 1, 0, 0); ctx.clearRect(0, 0, pad.width, pad.height); ctx.restore();
      goresan.forEach(gambarG);
      const ada = goresan.length > 0;
      $id('ttdUndo').disabled = $id('clearSignature').disabled = !ada;
      $id('saveSignature').disabled = !ada;
    }
    const titik = e => { const r = pad.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top, e.pointerType === 'pen' && e.pressure ? e.pressure : 0.5]; };
    pad.addEventListener('pointerdown', e => { e.preventDefault(); pad.setPointerCapture(e.pointerId); aktifG = { p: [titik(e)] }; goresan.push(aktifG); gambarUlang(); });
    pad.addEventListener('pointermove', e => { if (!aktifG) return; const ev = e.getCoalescedEvents ? e.getCoalescedEvents() : []; (ev.length ? ev : [e]).forEach(x => aktifG.p.push(titik(x))); gambarUlang(); });
    ['pointerup', 'pointercancel'].forEach(t => pad.addEventListener(t, () => { aktifG = null; }));
    $id('ttdUndo').addEventListener('click', () => { goresan.pop(); gambarUlang(); });
    $id('clearSignature').addEventListener('click', () => { goresan = []; gambarUlang(); });
    window.addEventListener('resize', () => { if ($id('ttdModal').classList.contains('show')) ukuran(); });
    $id('ttdModal').addEventListener('shown.bs.modal', ukuran);

    function modeBaru(baru) {
      $id('ttdLama').classList.toggle('d-none', baru);
      $id('ttdBaru').classList.toggle('d-none', !baru);
      if (baru) { goresan = []; setTimeout(ukuran, 0); } else { $id('saveSignature').disabled = true; }
    }
    $id('ttdUlang').addEventListener('click', () => modeBaru(true));

    $(document).on('click', '.ttd-btn', function() {
      const id = $(this).data('id');
      if (!id) return;
      const r = barisTabel(id);
      $id('ttd_id_visit').value = id;
      $id('ttdSub').innerHTML = subJudul(r);
      goresan = [];
      const lama = r && r.signature_path ? 'uploads/ttd/' + encodeURIComponent(r.signature_path) + '?v=' + Date.now() : '';
      if (lama) $id('ttdLamaImg').src = lama;
      modeBaru(!lama);
      ttdModal.show();
    });

    // kanvas -> PNG dipotong ke area coretan (latar putih agar tetap terbaca di dokumen cetak)
    function hasilPng() {
      const t = document.createElement('canvas');
      t.width = pad.width; t.height = pad.height;
      const c = t.getContext('2d');
      c.fillStyle = '#fff'; c.fillRect(0, 0, t.width, t.height);
      c.drawImage(pad, 0, 0);
      return t.toDataURL('image/png');
    }
    $id('saveSignature').addEventListener('click', async function() {
      if (!goresan.length) return;
      const total = goresan.reduce((a, g) => a + g.p.length, 0);
      if (total < 8) { Swal.fire('Tanda tangan terlalu pendek', 'Minta pasien menandatangani dengan jelas.', 'warning'); return; }
      const btn = this, label = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
      try {
        const r = await fetch('controller/admisi/saveSignature.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
          body: JSON.stringify({ id_visit: $id('ttd_id_visit').value, image: hasilPng() }) });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal menyimpan (HTTP ' + r.status + ').');
        tutup(ttdModal);
        toast('success', 'Tanda tangan tersimpan');
        if (window.table) window.table.ajax.reload(null, false);
      } catch (e) {
        Swal.fire('Gagal menyimpan', e.message, 'error');
      } finally {
        btn.innerHTML = label;
        btn.disabled = !goresan.length;
      }
    });
  })();
</script>
<script>
  /* ================= DOKUMEN SEP & FKPP =================
   * Dulu: menu mengarah ke module/admin/form_sep & form_fkpp yang tidak ada (404).
   * Sekarang modal: bila dokumen sudah ada langsung ditampilkan (pratinjau PDF), bila belum -> area unggah.
   */
  (function() {
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const LABEL = { sep: 'SEP', fkpp: 'FKPP' };
    const PANJANG = { sep: 'Surat Eligibilitas Peserta', fkpp: 'Formulir Klaim / FKPP' };
    const modal = bootstrap.Modal.getOrCreateInstance($id('dokModal'));
    let noVisit = null, jenisAktif = 'sep', data = {}, sedangUnggah = false;
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const waktu = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}:\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] + ', ' + m[4] : ''; };
    const ukuran = b => b == null ? '' : b < 1024 * 1024 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1048576).toFixed(1) + ' MB';

    function titik() {
      document.querySelectorAll('[data-dk-dot]').forEach(el => {
        const i = data[el.dataset.dkDot];
        el.className = 'dk-dot ' + (i ? (i.ada ? 'ada' : 'hilang') : '');
        el.title = i ? (i.ada ? 'Sudah diunggah' : 'Tercatat, file tidak ditemukan') : 'Belum diunggah';
      });
    }
    function areaUnggah(j, ganti) {
      return `<div class="dk-drop" data-jenis="${j}" tabindex="0" role="button">
        <i class="fas fa-cloud-arrow-up fa-2x text-primary mb-2"></i>
        <div class="fw-semibold">${ganti ? 'Ganti' : 'Unggah'} ${LABEL[j]} — klik atau seret file PDF ke sini</div>
        <div class="small text-muted mt-1">${PANJANG[j]} · PDF maks 5 MB</div>
        <div class="progress mt-3 d-none" style="height:6px"><div class="progress-bar" style="width:0"></div></div>
      </div>`;
    }
    function render() {
      document.querySelectorAll('#dokModal [data-dk]').forEach(b => b.classList.toggle('active', b.dataset.dk === jenisAktif));
      titik();
      const j = jenisAktif, i = data[j];
      if (!i) { $id('dkBody').innerHTML = `<div class="dk-kosong"><div class="text-muted small mb-3"><i class="fas fa-circle-info me-1"></i>${LABEL[j]} belum diunggah untuk kunjungan ini.</div>${areaUnggah(j, false)}</div>`; return; }
      if (!i.ada) {
        $id('dkBody').innerHTML = `<div class="alert alert-warning small"><i class="fas fa-triangle-exclamation me-1"></i>${LABEL[j]} tercatat (<code>${esc(i.file)}</code>) tetapi file tidak ditemukan di server. Unggah ulang.</div>${areaUnggah(j, true)}`;
        return;
      }
      $id('dkBody').innerHTML = `
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
          <span class="badge bg-success-subtle text-success"><i class="fas fa-circle-check me-1"></i>${LABEL[j]} tersimpan</span>
          <span class="small text-muted">${esc(waktu(i.waktu))}${i.ukuran ? ' · ' + ukuran(i.ukuran) : ''}</span>
          <div class="ms-auto d-flex gap-1">
            <a class="btn btn-sm btn-light" href="${esc(i.url)}" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square me-1"></i>Buka</a>
            <a class="btn btn-sm btn-light" href="${esc(i.url)}" download><i class="fas fa-download me-1"></i>Unduh</a>
            <button type="button" class="btn btn-sm btn-outline-primary dk-ganti"><i class="fas fa-rotate me-1"></i>Ganti file</button>
          </div>
        </div>
        <div class="dk-ganti-area d-none mb-2">${areaUnggah(j, true)}</div>
        <div class="dk-view">${i.ext === 'pdf' ? `<iframe src="${esc(i.url)}#view=FitH" title="${LABEL[j]}"></iframe>` : `<img src="${esc(i.url)}" alt="${LABEL[j]}">`}</div>`;
    }

    async function muat() {
      $id('dkBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
      try {
        const r = await fetch('controller/visit/dokumenKlaim.php?no=' + encodeURIComponent(noVisit), { credentials: 'same-origin', cache: 'no-store' });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat dokumen.');
        data = { sep: j.sep, fkpp: j.fkpp };
        render();
      } catch (e) {
        $id('dkBody').innerHTML = `<div class="text-center py-5"><i class="fas fa-circle-exclamation fa-2x text-danger d-block mb-2"></i><div class="mb-3">${esc(e.message)}</div><button type="button" class="btn btn-sm btn-primary" id="dkUlang">Coba lagi</button></div>`;
      }
    }

    $(document).on('click', '.dok-btn', function() {
      noVisit = String($(this).data('visit'));
      jenisAktif = $(this).data('jenis') === 'fkpp' ? 'fkpp' : 'sep';
      const r = window.table ? window.table.rows().data().toArray().find(x => x.visit_ID === noVisit) : null;
      $id('dkSub').innerHTML = r ? [r.patient_name || r.patient_name_pcare, r.nomor_rm, r.patient_bpjs ? 'BPJS ' + r.patient_bpjs : '', r.visit_ID].filter(Boolean).map(esc).join(' · ') : esc(noVisit);
      data = {};
      titik();
      modal.show();
      muat();
    });
    $(document).on('click', '#dkUlang', muat);
    $id('dokModal').addEventListener('click', e => {
      const t = e.target.closest('[data-dk]');
      if (t) { jenisAktif = t.dataset.dk; render(); return; }
      if (e.target.closest('.dk-ganti')) { $id('dkBody').querySelector('.dk-ganti-area').classList.toggle('d-none'); return; }
      const d = e.target.closest('.dk-drop');
      if (d && !sedangUnggah) $id('dkFile').click();
    });
    $id('dokModal').addEventListener('keydown', e => { const d = e.target.closest && e.target.closest('.dk-drop'); if (d && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); $id('dkFile').click(); } });
    $id('dokModal').addEventListener('dragover', e => { const d = e.target.closest('.dk-drop'); if (d) { e.preventDefault(); d.classList.add('over'); } });
    $id('dokModal').addEventListener('dragleave', e => { const d = e.target.closest('.dk-drop'); if (d) d.classList.remove('over'); });
    $id('dokModal').addEventListener('drop', e => { const d = e.target.closest('.dk-drop'); if (!d) return; e.preventDefault(); d.classList.remove('over'); unggah(e.dataTransfer.files[0]); });
    $id('dkFile').addEventListener('change', function() { unggah(this.files[0]); this.value = ''; });
    $id('dokModal').addEventListener('hide.bs.modal', e => { if (sedangUnggah) e.preventDefault(); });

    function unggah(f) {
      if (!f || sedangUnggah) return;
      if (!/\.pdf$/i.test(f.name) && f.type !== 'application/pdf') { Swal.fire('Format tidak didukung', 'File harus PDF.', 'warning'); return; }
      if (f.size > 5 * 1024 * 1024) { Swal.fire('File terlalu besar', 'Maksimal 5 MB.', 'warning'); return; }
      const j = jenisAktif, drop = $id('dkBody').querySelector('.dk-drop');
      const bar = drop && drop.querySelector('.progress');
      if (bar) bar.classList.remove('d-none');
      sedangUnggah = true;
      const fd = new FormData();
      fd.append('no', noVisit);
      fd.append('jenis', j);
      fd.append('file', f);
      // XHR agar ada progres unggah
      const x = new XMLHttpRequest();
      x.open('POST', 'controller/visit/dokumenKlaim.php');
      x.upload.onprogress = ev => { if (bar && ev.lengthComputable) bar.firstElementChild.style.width = Math.round(ev.loaded / ev.total * 100) + '%'; };
      x.onload = () => {
        sedangUnggah = false;
        let res = null;
        try { res = JSON.parse(x.responseText); } catch (e) {}
        if (x.status !== 200 || !res || res.status !== 'success') {
          if (bar) bar.classList.add('d-none');
          Swal.fire('Gagal mengunggah', (res && res.message) || 'Terjadi kesalahan (HTTP ' + x.status + ').', 'error');
          return;
        }
        data[j] = res.info;
        render();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
        if (window.table) window.table.ajax.reload(null, false);
      };
      x.onerror = () => { sedangUnggah = false; if (bar) bar.classList.add('d-none'); Swal.fire('Gagal mengunggah', 'Koneksi terputus. Coba lagi.', 'error'); };
      x.send(fd);
    }
  })();
</script>
<script>
  /* ================= GANTI DOKTER & UBAH JADWAL =================
   * Dulu: alert() polos, tanpa validasi, nama dokter disisipkan tanpa escape, dokter yang sama bisa dipilih,
   * modal ganti dokter memakai z-index 10000000 sehingga dialog SweetAlert tertutup modal.
   */
  (function() {
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
    const jamJkt = () => new Date().toLocaleTimeString('en-GB', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit' });
    const tglPanjang = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return '-'; const d = new Date(+m[1], m[2] - 1, +m[3]); return HARI[d.getDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1]; };
    const baris = (k, v) => window.table ? window.table.rows().data().toArray().find(r => String(r[k]) === String(v)) : null;
    const sub = r => r ? [r.patient_name || r.patient_name_pcare, r.nomor_rm, r.visit_antrian ? 'Antrian ' + r.visit_antrian : '', r.id_poli].filter(Boolean).map(esc).join(' · ') : '&nbsp;';
    const inisial = n => String(n || '?').replace(/^\s*(drg?|bd|ns)\.?\s*/i, '').replace(/[^A-Za-z\s]/g, ' ').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
    const toast = t => Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: t, showConfirmButton: false, timer: 2200 });
    function tutup(m) {
      if (m._isTransitioning) m._element.addEventListener('shown.bs.modal', () => m.hide(), { once: true });
      else m.hide();
    }

    /* ---------------- UBAH JADWAL ---------------- */
    const jwModal = bootstrap.Modal.getOrCreateInstance($id('editVisitModal'));
    let jwLama = { tgl: '', jam: '' };
    function jwCek() {
      const t = $id('edit_visit_date').value, j = $id('edit_visit_time').value;
      const min = tglJkt(-7), max = tglJkt(90);
      let err = '';
      if (!t || !j) err = 'Tanggal dan jam wajib diisi.';
      else if (t < min || t > max) err = 'Tanggal harus antara 7 hari ke belakang dan 90 hari ke depan.';
      const sama = t === jwLama.tgl && j === jwLama.jam;
      $id('jwError').textContent = err;
      $id('jwError').classList.toggle('d-none', !err || (!t && !j));
      document.querySelectorAll('#jwCepatTgl .btn').forEach(b => b.classList.toggle('active', tglJkt(+b.dataset.h) === t));
      const r = $id('jwRingkas');
      if (!err && !sama) {
        r.innerHTML = `<div class="d-flex align-items-center gap-2 flex-wrap"><span class="text-muted text-decoration-line-through">${esc(tglPanjang(jwLama.tgl))} ${esc(jwLama.jam)}</span><i class="fas fa-arrow-right text-primary"></i><b>${esc(tglPanjang(t))} ${esc(j)}</b></div>` +
          (t !== tglJkt(0) ? `<div class="small text-muted mt-1">${t > tglJkt(0) ? 'Pasien akan pindah ke daftar tanggal tersebut.' : 'Tanggal lampau: hanya untuk koreksi data.'}</div>` : '');
        r.classList.remove('d-none');
      } else r.classList.add('d-none');
      $id('btnUpdateVisit').disabled = !!err || sama;
    }
    $(document).on('click', '.edit-visit-btn', function() {
      const no = $(this).data('visit');
      const r = baris('visit_ID', no);
      $id('edit_visit_id').value = no;
      $id('edit_id_patient').value = $(this).data('patient');
      jwLama = { tgl: String($(this).data('date') || '').slice(0, 10), jam: String($(this).data('time') || '').slice(0, 5) };
      $id('edit_visit_date').value = jwLama.tgl;
      $id('edit_visit_date').min = tglJkt(-7);
      $id('edit_visit_date').max = tglJkt(90);
      $id('edit_visit_time').value = jwLama.jam;
      $id('jwSub').innerHTML = sub(r);
      $id('jwLama').textContent = tglPanjang(jwLama.tgl) + (jwLama.jam ? ' · ' + jwLama.jam : '');
      $id('jwBpjs').classList.toggle('d-none', !(r && String(r.id_provider) === '1'));
      jwCek();
      jwModal.show();
    });
    document.querySelectorAll('#jwCepatTgl .btn').forEach(b => b.addEventListener('click', () => { $id('edit_visit_date').value = tglJkt(+b.dataset.h); jwCek(); }));
    $id('jwSekarang').addEventListener('click', () => { $id('edit_visit_time').value = jamJkt(); jwCek(); });
    ['input', 'change'].forEach(t => { $id('edit_visit_date').addEventListener(t, jwCek); $id('edit_visit_time').addEventListener(t, jwCek); });

    $id('btnUpdateVisit').addEventListener('click', async function() {
      jwCek();
      if (this.disabled) return;
      const btn = this, label = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
      try {
        const r = await fetch('controller/visit/updateVisitTime.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
          body: JSON.stringify({ visit_ID: $id('edit_visit_id').value, id_patient: $id('edit_id_patient').value, visit_date: $id('edit_visit_date').value, visit_time: $id('edit_visit_time').value }) });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal menyimpan (HTTP ' + r.status + ').');
        tutup(jwModal);
        toast('Jadwal kunjungan diperbarui');
        if (window.table) window.table.ajax.reload(null, false);
      } catch (e) {
        Swal.fire('Gagal menyimpan', e.message, 'error');
      } finally {
        btn.innerHTML = label;
        jwCek();
      }
    });

    /* ---------------- GANTI DOKTER ---------------- */
    const drModal = bootstrap.Modal.getOrCreateInstance($id('modalRescheduleDoctor'));
    let daftarDokter = null, drNow = '', drPoli = '', drPilih = '';
    function muatDokter() {
      if (daftarDokter) return Promise.resolve(daftarDokter);
      return $.ajax({ url: 'module/admin/get_dokter_bpjs.php', type: 'POST', dataType: 'json' })
        .then(res => (daftarDokter = (res && res.data) || []));
    }
    const norm = s => String(s || '').toLowerCase().replace(/[^a-z0-9]/g, '');
    function renderDokter() {
      if (!daftarDokter) return;
      const q = norm($id('drCari').value);
      const ini = norm(drNow), poli = String(drPoli || '').toUpperCase().trim();
      const cocokPoli = d => !!poli && [d.nmPoli].concat(d.poli_list || []).some(p => p && String(p).toUpperCase().trim() === poli);
      const list = daftarDokter.filter(d => !q || norm(d.nmDokter).includes(q) || norm(d.nmPoli).includes(q))
        .sort((a, b) => (cocokPoli(b) - cocokPoli(a)) || String(a.nmDokter).localeCompare(String(b.nmDokter)));
      if (!list.length) { $id('drList').innerHTML = '<div class="text-center text-muted small py-4">Tidak ada yang cocok.</div>'; return; }
      let grup = null;
      $id('drList').innerHTML = list.map(d => {
        const sekarang = norm(d.nmDokter) === ini, g = cocokPoli(d) ? drPoli + ' · poli pasien' : 'Dokter / tenaga lain';
        const head = g !== grup ? `<div class="dr-grup">${esc(g)}</div>` : '';
        grup = g;
        return head + `<button type="button" class="dr-item ${d.kdDokter === drPilih ? 'pilih' : ''}" data-kd="${esc(d.kdDokter)}" data-nm="${esc(d.nmDokter)}" ${sekarang ? 'disabled' : ''}>
          <span class="dr-ava">${esc(inisial(d.nmDokter))}</span>
          <span class="min-w-0 flex-grow-1 text-start"><span class="d-block fw-semibold text-truncate">${esc(d.nmDokter)}</span><span class="d-block small text-muted text-truncate">${esc((d.poli_list && d.poli_list.length ? d.poli_list.join(', ') : d.nmPoli) || 'Belum ada kunjungan 30 hari terakhir')}</span></span>
          ${!sekarang ? `<span class="badge ${d.menunggu ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'} me-1" title="Pasien menunggu hari ini">${d.menunggu} menunggu</span>` : ''}
          ${sekarang ? '<span class="badge bg-light text-muted">Saat ini</span>' : '<i class="fas fa-circle-check dr-cek"></i>'}
        </button>`;
      }).join('');
    }
    $(document).on('click', '.btn-reschedule', function(e) {
      e.preventDefault();
      const id = $(this).data('id');
      const r = baris('id_visit', id);
      drNow = String($(this).data('doctor') || '');
      drPoli = r ? String(r.id_poli || '') : '';
      drPilih = '';
      $id('visit_id').value = id;
      $id('doctor_new').value = '';
      $id('doctor_now').textContent = drNow || '-';
      $id('drNowAva').textContent = inisial(drNow);
      $id('drSub').innerHTML = sub(r);
      $id('drCari').value = '';
      $id('drPilihan').textContent = '';
      $id('btnSimpanReschedule').disabled = true;
      $id('drBpjs').classList.toggle('d-none', !(r && String(r.id_provider) === '1'));
      $id('drList').innerHTML = daftarDokter ? '' : '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
      drModal.show();
      muatDokter().then(renderDokter).catch(() => {
        daftarDokter = null;
        $id('drList').innerHTML = '<div class="text-center text-danger small py-4">Gagal memuat daftar dokter. <a href="javascript:;" id="drUlang">Coba lagi</a></div>';
      });
    });
    $(document).on('click', '#drUlang', () => muatDokter().then(renderDokter));
    $id('drCari').addEventListener('input', renderDokter);
    $id('modalRescheduleDoctor').addEventListener('shown.bs.modal', () => $id('drCari').focus());
    $id('drList').addEventListener('click', e => {
      const b = e.target.closest('.dr-item');
      if (!b || b.disabled) return;
      drPilih = b.dataset.kd;
      $id('doctor_new').value = drPilih;
      $id('drPilihan').innerHTML = 'Pindah ke <b>' + esc(b.dataset.nm) + '</b>';
      $id('btnSimpanReschedule').disabled = false;
      $id('drList').querySelectorAll('.dr-item').forEach(x => x.classList.toggle('pilih', x === b));
    });
    $id('formRescheduleDoctor').addEventListener('submit', async function(e) {
      e.preventDefault();
      if (!$id('doctor_new').value) return;
      const btn = $id('btnSimpanReschedule'), label = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
      try {
        const r = await fetch('module/admin/serviceRescheduleDoctor.php', { method: 'POST', credentials: 'same-origin', body: new URLSearchParams(new FormData(this)) });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || !j.success) throw new Error((j && j.message) || 'Gagal menyimpan (HTTP ' + r.status + ').');
        tutup(drModal);
        toast(j.message);
        if (window.table) window.table.ajax.reload(null, false);
      } catch (err) {
        Swal.fire('Gagal mengganti dokter', err.message, 'error');
      } finally {
        btn.innerHTML = label;
        btn.disabled = !$id('doctor_new').value;
      }
    });
  })();
</script>
<script>
  let currentPatientId = null;
  let currentMasterFace = "";
  let stream = null;
  let faceTimer = null;
  let faceModelReady = null;
  let masterDescriptor = null;
  let faceMismatch = false;
  let faceMatched = false;
  let masterState = "loading";
  const FACE_MATCH_THRESHOLD = 0.5;
  let faceTooClose = false;
  let faceDetectionSession = 0;
  let autoCaptureAttempted = false;

  // face-api.min.js (1,1 MB) dulu dimuat setiap halaman dibuka; sekarang hanya saat kamera dipakai
  function muatFaceApi() {
    if (window.faceapi) return Promise.resolve();
    if (!window.__faceApiP) {
      window.__faceApiP = new Promise(function(ok, gagal) {
        const sc = document.createElement('script');
        sc.src = 'assets/js/face-api.min.js';
        sc.onload = ok;
        sc.onerror = function() {
          window.__faceApiP = null;
          gagal(new Error('face-api gagal dimuat'));
        };
        document.head.appendChild(sc);
      });
    }
    return window.__faceApiP;
  }

  function loadFaceModel() {
    if (!faceModelReady) {
      faceModelReady = muatFaceApi().then(() => Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('models')
      ])).catch(function(e) {
        faceModelReady = null;
        throw e;
      });
    }
    return faceModelReady;
  }

  // Ambil descriptor wajah master pasien (foto rekam sebelumnya)
  async function loadMasterDescriptor(path, session) {
    masterDescriptor = null;
    masterState = "none";
    faceMatched = false;
    if (!path) return;
    masterState = "error";
    try {
      const img = await faceapi.fetchImage(path.replace(/^(\.\.\/)+/, ''));
      const det = await faceapi
        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({
          inputSize: 416,
          scoreThreshold: 0.4
        }))
        .withFaceLandmarks()
        .withFaceDescriptor();
      if (session !== faceDetectionSession) return;
      masterDescriptor = det ? det.descriptor : null;
      masterState = det ? "ok" : "error";
    } catch (e) {
      if (session !== faceDetectionSession) return;
      console.error("Gagal memuat wajah master", e);
    }
  }

  function setFaceState(ok, message, tooClose = false) {
    faceTooClose = tooClose;
    const circle = document.querySelector(".face-circle");
    const status = document.getElementById("faceStatus");
    const btn = document.getElementById("captureBtn");
    circle.classList.toggle("face-frame-danger", faceMismatch);
    circle.classList.toggle("face-frame-warning", !ok && !faceMismatch);
    circle.classList.toggle("face-frame-success", ok && !faceMismatch);
    status.className = "face-status " + (faceMismatch ? "text-danger" : ok ? "text-success" : "text-warning");
    status.innerHTML = `<i class="fas ${faceMismatch ? 'fa-times-circle' : ok ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
    const info = document.getElementById("faceMatchInfo");
    if (info) {
      if (masterState === "none") info.innerHTML = '<span class="badge bg-secondary">Wajah belum direkam di master data pasien</span>';
      else if (masterState === "error") info.innerHTML = '<span class="badge bg-secondary">Foto master tidak terbaca / wajah tidak terdeteksi</span>';
      else if (faceMismatch) info.innerHTML = '<span class="badge bg-danger">Wajah BUKAN pasien ini</span>';
      else if (masterState === "ok" && faceMatched) info.innerHTML = '<span class="badge bg-success">Wajah SESUAI master pasien</span>';
      else info.innerHTML = '<span class="badge bg-warning text-dark">Memverifikasi wajah...</span>';
    }
  }

  // Cek apakah wajah (koordinat video asli) berada pas di dalam lingkaran
  function evaluateFace(box, video) {
    const vr = video.getBoundingClientRect();
    const cr = document.querySelector(".face-circle").getBoundingClientRect();
    const scale = Math.max(vr.width / video.videoWidth, vr.height / video.videoHeight);
    const offX = (vr.width - video.videoWidth * scale) / 2;
    const offY = (vr.height - video.videoHeight * scale) / 2;

    // video di-mirror (scaleX(-1))
    const fw = box.width * scale;
    const fh = box.height * scale;
    const fx = vr.width - (box.x * scale + offX + fw);
    const fy = box.y * scale + offY;

    const faceCx = fx + fw / 2;
    const faceCy = fy + fh / 2;
    const cCx = cr.left - vr.left + cr.width / 2;
    const cCy = cr.top - vr.top + cr.height / 2;

    const dx = Math.abs(faceCx - cCx) / (cr.width / 2);
    const dy = Math.abs(faceCy - cCy) / (cr.height / 2);
    const ratio = fw / cr.width;

    if (ratio < 0.5) return {
      ok: false,
      msg: "Dekatkan wajah ke kamera"
    };
    if (ratio > 0.95) return {
      ok: false,
      tooClose: true,
      msg: "Terlalu dekat, mundurkan sedikit"
    };
    if (dx > 0.2 || dy > 0.2) return {
      ok: false,
      msg: "Posisikan wajah di tengah lingkaran"
    };
    return {
      ok: true,
      msg: "Wajah pas, siap diambil"
    };
  }

  async function startFaceDetection() {
    const video = document.getElementById("video");
    stopFaceDetection();
    const session = faceDetectionSession;
    faceTooClose = false;
    setFaceState(false, "Memuat deteksi wajah...");
    try {
      await loadFaceModel();
      if (session !== faceDetectionSession) return;
    } catch (e) {
      if (session !== faceDetectionSession) return;
      console.error(e);
      setFaceState(false, "Gagal memuat model deteksi wajah");
      return;
    }
    faceMismatch = false;
    setFaceState(false, "Memuat data wajah pasien...");
    await loadMasterDescriptor(currentMasterFace, session);
    if (session !== faceDetectionSession) return;
    const opts = new faceapi.TinyFaceDetectorOptions({
      inputSize: 320,
      scoreThreshold: 0.5
    });
    let busy = false;
    faceTimer = setInterval(async () => {
      if (busy || document.getElementById("captureBtn").dataset.saving || !video.videoWidth || video.paused) return;
      busy = true;
      try {
        let q = faceapi.detectSingleFace(video, opts);
        if (masterDescriptor) q = q.withFaceLandmarks().withFaceDescriptor();
        const det = await q;
        if (session !== faceDetectionSession || !faceTimer) return;
        if (!det) {
          faceMismatch = false;
          faceMatched = false;
          setFaceState(false, "Wajah tidak terdeteksi");
        } else {
          const box = det.detection ? det.detection.box : det.box;
          const r = evaluateFace(box, video);
          faceMismatch = false;
          faceMatched = false;
          if (masterDescriptor && det.descriptor) {
            const dist = faceapi.euclideanDistance(masterDescriptor, det.descriptor);
            if (dist > FACE_MATCH_THRESHOLD) {
              faceMismatch = true;
              setFaceState(false, "Wajah TIDAK sesuai dengan data master pasien", !!r.tooClose);
              return;
            }
            faceMatched = true;
          }
          setFaceState(r.ok, r.ok && masterDescriptor ? "Wajah sesuai pasien, siap diambil" : r.msg, !!r.tooClose);
          if (r.ok && faceMatched && masterState === "ok" && !autoCaptureAttempted) {
            autoCaptureAttempted = true;
            await captureVisitFace();
          }
        }
      } catch (e) {
        if (session !== faceDetectionSession) return;
        console.error(e);
        setFaceState(false, "Gagal mendeteksi wajah. Silakan tutup dan buka kembali kamera.");
      } finally {
        busy = false;
      }
    }, 250);
  }

  function stopFaceDetection() {
    faceDetectionSession++;
    if (faceTimer) {
      clearInterval(faceTimer);
      faceTimer = null;
    }
  }

  $(document).on("click", ".camera-btn", async function() {
    if (document.getElementById("captureBtn").dataset.saving) return;
    stopFaceDetection();
    autoCaptureAttempted = false;
    faceMatched = false;
    faceMismatch = false;
    // WAJIB: ambil id dari tombol
    currentPatientId = $(this).data("id");
    currentMasterFace = $(this).attr("data-face") || "";
    console.log("📌 ID PATIENT:", currentPatientId);
    const modalEl = document.getElementById("cameraModal");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    const session = faceDetectionSession;
    setFaceState(false, "Menyiapkan kamera...");

    try {
      const cameraStream = await navigator.mediaDevices.getUserMedia({
        video: true
      });
      if (session !== faceDetectionSession) {
        cameraStream.getTracks().forEach(track => track.stop());
        return;
      }
      stream = cameraStream;

      const video = document.getElementById("video");
      video.srcObject = stream;

      await video.play();
      if (session !== faceDetectionSession) return;
      startFaceDetection();

    } catch (err) {
      alert("Kamera tidak bisa diakses");
      console.error(err);
    }

  });
  document.getElementById("captureBtn").addEventListener("click", captureVisitFace);

  function captureVisitFace() {
    const captureBtn = document.getElementById("captureBtn");
    if (captureBtn.disabled || captureBtn.dataset.saving) return;
    if (faceMismatch) {
      Swal.fire("Wajah tidak sesuai", "Wajah di kamera bukan wajah pasien pada data master.", "error");
      return;
    }
    if (faceTooClose) {
      Swal.fire("Terlalu dekat", "Mundurkan wajah sedikit dari kamera.", "warning");
      return;
    }

    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");

    if (!video.videoWidth || !video.videoHeight) {
      Swal.fire(
        "Kamera belum siap",
        "Tunggu sampai kamera aktif.",
        "warning"
      );
      return;
    }

    /*
     * =====================================================
     * UKURAN AREA WAJAH
     * =====================================================
     */

    const circleElement = document.querySelector(".face-circle");

    const circleSize = circleElement.offsetWidth;

    const videoDisplayWidth = video.clientWidth;
    const videoDisplayHeight = video.clientHeight;

    /*
     * Rasio antara ukuran video asli
     * dengan ukuran video yang tampil di layar
     */
    const scaleX = video.videoWidth / videoDisplayWidth;
    const scaleY = video.videoHeight / videoDisplayHeight;

    /*
     * Posisi tengah video
     */
    const centerX = video.videoWidth / 2;
    const centerY = video.videoHeight / 2;

    /*
     * Ukuran crop mengikuti lingkaran
     */
    const cropWidth = circleSize * scaleX;
    const cropHeight = circleSize * scaleY;

    /*
     * Posisi crop dari video asli
     */
    const cropX = centerX - (cropWidth / 2);
    const cropY = centerY - (cropHeight / 2);

    /*
     * =====================================================
     * CANVAS HASIL CAPTURE
     * =====================================================
     */

    canvas.width = cropWidth;
    canvas.height = cropHeight;

    const ctx = canvas.getContext("2d");

    /*
     * Karena preview kamera dibuat mirror,
     * hasil capture juga dibuat mirror agar sesuai preview.
     */
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);

    ctx.drawImage(
      video,

      cropX,
      cropY,
      cropWidth,
      cropHeight,

      0,
      0,
      cropWidth,
      cropHeight
    );

    /*
     * Reset transform
     */
    ctx.setTransform(1, 0, 0, 1, 0, 0);

    /*
     * Convert ke base64
     */
    const imageData = canvas.toDataURL(
      "image/jpeg",
      0.90
    );

    /*
     * =====================================================
     * KIRIM KE SERVER
     * =====================================================
     */

    captureBtn.disabled = true;
    captureBtn.dataset.saving = "1";

    captureBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1"></span>
        Menyimpan...
    `;

    return fetch("controller/admisi/recordFaceVisit.php", {

        method: "POST",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify({
          id: currentPatientId,
          image: imageData
        })

      })
      .then(async res => {
        const result = await res.json();
        if (!res.ok || result.status !== "success") {
          throw new Error(result.message || "Wajah gagal disimpan.");
        }
        return result;
      })

      .then(res => {

        if (res.status === "success") {
          stopFaceDetection();

          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Foto wajah berhasil direkam.",
            timer: 1500,
            showConfirmButton: false
          });

          const modalEl = document.getElementById("cameraModal");

          const modal = bootstrap.Modal.getInstance(modalEl);

          if (modal) {
            modal.hide();
          }

          setTimeout(() => {
            table.ajax.reload(null, false);
          }, 500);

        }

      })

      .catch(error => {

        console.error(error);

        Swal.fire(
          "Error!",
          error.message || "Terjadi kesalahan saat menyimpan wajah.",
          "error"
        );

      })

      .finally(() => {

        delete captureBtn.dataset.saving;
        captureBtn.disabled = false;

        captureBtn.innerHTML = `
            <i class="fas fa-camera"></i>
            Ambil Wajah
        `;

      });

  }

  /* =========================
     CLEANUP (INI YANG PENTING)
  ========================= */
  document.getElementById("cameraModal")
    .addEventListener("hidden.bs.modal", function() {

      console.log("🛑 Stop camera");
      stopFaceDetection();
      faceMismatch = false;

      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }

      const video = document.getElementById("video");
      if (video) {
        video.srcObject = null; // penting
      }

    });
</script>

<script>
  $(document).on('show.bs.dropdown', '.dropdown', function(e) {
    let $toggle = $(this).find('.dropdown-toggle');
    if ($toggle.length === 0) {
      return;
    }
    let $menu = $(this).find('.dropdown-menu');
    if ($menu.length === 0) return;
    $menu.data('original-parent', $(this));
    $('body').append($menu);
    let offset = $toggle[0].getBoundingClientRect();
    $menu.css({
      position: 'fixed',
      top: offset.bottom,
      left: offset.left,
      zIndex: 999999
    });
  });

  $(document).on('hide.bs.dropdown', function() {
    $('body > .dropdown-menu').each(function() {
      let $parent = $(this).data('original-parent');
      if ($parent && $parent.length) {
        $parent.append($(this));
      }
    });
  });

  $(document).on('hide.bs.dropdown', '.dropdown', function() {
    let $menu = $(this).find('.dropdown-menu');

    // balikin ke tempat asal
    $(this).append($menu);

    $menu.removeAttr('style');
  });

</script>

</html>