<?php
$title = 'Pasien';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
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
    /* =========================================================
   DAFTAR PASIEN
   ========================================================= */
    .min-w-0 {
      min-width: 0;
    }

    .pt-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .pt-stat.pt-click {
      cursor: pointer;
      transition: border-color .15s;
    }

    .pt-stat.pt-click:hover,
    .pt-stat.pt-click.active {
      border-color: #5d87ff;
    }

    .pt-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .pt-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .pt-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .pt-bar {
      height: 5px;
      border-radius: 5px;
      background: #eef2f6;
      overflow: hidden;
      margin-top: 8px;
    }

    .pt-bar span {
      display: block;
      height: 100%;
      border-radius: 5px;
    }

    .pt-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .pt-search {
      width: 320px;
    }

    .pt-search .form-control {
      font-size: .875rem;
    }

    @media (max-width: 767.98px) {

      .pt-tools,
      .pt-search {
        width: 100%;
      }

      .pt-search {
        flex: 1;
      }
    }

    #periodeTable {
      width: 100% !important;
    }

    #periodeTable thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    #periodeTable td {
      font-size: .85rem;
      vertical-align: middle;
      padding-top: .6rem;
      padding-bottom: .6rem;
    }

    #periodeTable tbody tr:hover {
      background: #fafcff;
    }

    .pt-ava {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: .85rem;
      overflow: hidden;
    }

    .pt-ava img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .pt-ava.l {
      background: #ecf2ff;
      color: #5d87ff;
    }

    .pt-ava.p {
      background: #fdebf4;
      color: #e0569b;
    }

    .pt-ava.x {
      background: #f2f5f9;
      color: #7c8fac;
    }

    .pt-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pt-sub {
      font-size: .74rem;
      color: #7c8fac;
    }

    .pt-rm {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      font-size: .74rem;
      background: #f2f5f9;
      color: #2a3547;
      padding: .1em .45em;
      border-radius: 5px;
    }

    .pt-id {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      font-size: .78rem;
      color: #2a3547;
    }

    .pt-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .68rem;
      font-weight: 600;
      padding: .2em .55em;
      border-radius: 6px;
    }

    .pt-act {
      white-space: nowrap;
    }

    .pt-act .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .pt-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .pt-sec {
      font-size: .74rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 4px 0 12px;
    }

    .pt-sec::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e5eaef;
    }

    .gen-opt input {
      display: none;
    }

    .gen-opt label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 7px 10px;
      cursor: pointer;
      width: 100%;
      text-align: center;
      font-weight: 600;
      font-size: .82rem;
      margin: 0;
    }

    .gen-opt input:checked+label {
      border-color: #5d87ff;
      background: #ecf2ff;
      color: #5d87ff;
    }

    .gen-opt input.p:checked+label {
      border-color: #e0569b;
      background: #fdebf4;
      color: #e0569b;
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Data Pasien</h4>
              <div class="text-muted small">Cari, daftarkan dan kelola data pasien faskes.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-user-plus me-1"></i> Pasien Baru</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pt-stat pt-click" data-filter="">
                <div class="d-flex gap-3 align-items-center">
                  <span class="pt-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
                  <div class="min-w-0">
                    <div class="pt-val" id="stTotal">-</div>
                    <div class="pt-label">Total pasien terdaftar</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pt-stat pt-click" data-filter="bpjs">
                <div class="d-flex gap-3 align-items-center">
                  <span class="pt-icon bg-success-subtle text-success"><i class="fas fa-id-card"></i></span>
                  <div class="flex-grow-1">
                    <div class="pt-val" id="stBpjs">-</div>
                    <div class="pt-label" id="stBpjsSub">Peserta BPJS</div>
                  </div>
                </div>
                <div class="pt-bar"><span id="stBpjsBar" style="width:0;background:#13deb9"></span></div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pt-stat">
                <div class="d-flex gap-3 align-items-center">
                  <span class="pt-icon bg-info-subtle text-info"><i class="fas fa-heartbeat"></i></span>
                  <div class="flex-grow-1">
                    <div class="pt-val" id="stIhs">-</div>
                    <div class="pt-label" id="stIhsSub">Terhubung Satu Sehat</div>
                  </div>
                </div>
                <div class="pt-bar"><span id="stIhsBar" style="width:0;background:#49beff"></span></div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pt-stat pt-click" data-filter="wajah">
                <div class="d-flex gap-3 align-items-center">
                  <span class="pt-icon bg-warning-subtle text-warning"><i class="fas fa-camera"></i></span>
                  <div class="flex-grow-1">
                    <div class="pt-val" id="stWajah">-</div>
                    <div class="pt-label" id="stWajahSub">Wajah terekam</div>
                  </div>
                </div>
                <div class="pt-bar"><span id="stWajahBar" style="width:0;background:#ffae1f"></span></div>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card position-relative">
            <div class="pt-loading" id="ptLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="pt-chips d-flex flex-wrap gap-2" id="ptChips">
                  <button class="btn btn-primary" data-filter="">Semua</button>
                  <button class="btn btn-light" data-filter="bpjs">BPJS</button>
                  <button class="btn btn-light" data-filter="umum">Non-BPJS</button>
                  <button class="btn btn-light" data-filter="wajah">Wajah terekam</button>
                  <button class="btn btn-light" data-filter="tanpa_wajah">Belum rekam wajah</button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center pt-tools">
                  <select id="ptSort" class="form-select form-select-sm" style="width:auto">
                    <option value="baru">Terbaru didaftarkan</option>
                    <option value="lama">Terlama didaftarkan</option>
                    <option value="nama">Nama A-Z</option>
                    <option value="rm">No. RM</option>
                  </select>
                  <div class="input-group input-group-sm pt-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="ptSearch" class="form-control" placeholder="Nama, No. RM, NIK, No. BPJS atau HP" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0" id="periodeTable">
                  <thead>
                    <tr>
                      <th>Pasien</th>
                      <th>NIK / BPJS</th>
                      <th>Kontak</th>
                      <th class="text-center">Kunjungan</th>
                      <th class="text-center">Wajah</th>
                      <th class="text-center" style="width:150px">Aksi</th>
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
  require '../admin/library.php';
  ?>


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
          </div>

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
</body>

<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-user-plus text-primary"></i> <span id="ptModalTitle">Pasien Baru</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_patient" id="id_patient">
        <input type="hidden" name="patient_provinsi" id="provinsi_text">
        <input type="hidden" name="patient_kabupaten" id="kabupaten_text">
        <input type="hidden" name="patient_kecamatan" id="kecamatan_text">
        <input type="hidden" name="patient_kelurahan" id="kelurahan_text">

        <div class="alert alert-light border small d-flex gap-2 align-items-start py-2" id="ptRmInfo">
          <i class="fas fa-info-circle text-primary mt-1"></i>
          <div>Nomor rekam medis dibuat otomatis saat disimpan. Isi <b>NIK</b> atau <b>No. BPJS</b> lalu klik <i class="fas fa-search"></i> untuk mengambil data dari BPJS.</div>
        </div>

        <div class="pt-sec">Identitas</div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="patient_nik">NIK</label>
            <div class="input-group">
              <input type="text" id="patient_nik" name="patient_nik" class="form-control" inputmode="numeric" maxlength="16" placeholder="16 digit NIK">
              <button class="btn btn-outline-primary" type="button" id="btncarinik" title="Cari di BPJS">
                <span class="btn-text"><i class="fas fa-search"></i></span>
                <span class="btn-loading d-none spinner-border spinner-border-sm"></span>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="patient_bpjs">No. BPJS</label>
            <div class="input-group">
              <input type="text" id="patient_bpjs" name="patient_bpjs" class="form-control" inputmode="numeric" maxlength="13" placeholder="13 digit No. Kartu">
              <button class="btn btn-outline-primary" type="button" id="btncaribpjs" title="Cari di BPJS">
                <span class="btn-text"><i class="fas fa-search"></i></span>
                <span class="btn-loading d-none spinner-border spinner-border-sm"></span>
              </button>
            </div>
          </div>
        </div>

        <div class="pt-sec">Data Diri</div>
        <div class="row g-3 mb-3">
          <div class="col-md-8">
            <label class="form-label required" for="patient_name">Nama Pasien</label>
            <input type="text" id="patient_name" name="patient_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label required">Jenis Kelamin</label>
            <select name="patient_gender" class="d-none" id="patient_gender" required>
              <option value="">PILIH</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
            <div class="row g-2">
              <div class="col-6 gen-opt"><input type="radio" name="_gender_ui" id="genL" value="Laki-laki"><label for="genL"><i class="fas fa-mars me-1"></i>Laki-laki</label></div>
              <div class="col-6 gen-opt"><input type="radio" name="_gender_ui" id="genP" value="Perempuan" class="p"><label for="genP"><i class="fas fa-venus me-1"></i>Perempuan</label></div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label required" for="patient_place">Tempat Lahir</label>
            <input type="text" id="patient_place" name="patient_place" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label required" for="patient_datebirth">Tanggal Lahir</label>
            <input type="date" id="patient_datebirth" name="patient_datebirth" class="form-control" required>
            <div class="form-text" id="ptUmur"></div>
          </div>
          <div class="col-md-4">
            <label class="form-label required" for="patient_religion">Agama</label>
            <select name="patient_religion" class="form-select" id="patient_religion" required>
              <option value="">PILIH</option>
              <option value="Islam">Islam</option>
              <option value="Kristen">Kristen</option>
              <option value="Katolik">Katolik</option>
              <option value="Hindu">Hindu</option>
              <option value="Budha">Budha</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="patient_phone">No. Handphone</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
              <input type="tel" id="patient_phone" name="patient_phone" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
          </div>
        </div>

        <div class="pt-sec">Alamat</div>
        <div class="small text-muted mb-2 d-none" id="ptWilayahNow"></div>
        <div class="row g-3">
          <div class="col-md-6 col-lg-3">
            <label class="form-label" for="provinsi">Provinsi</label>
            <select id="provinsi" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>
          <div class="col-md-6 col-lg-3">
            <label class="form-label" for="kabupaten">Kabupaten/Kota</label>
            <select id="kabupaten" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>
          <div class="col-md-6 col-lg-3">
            <label class="form-label" for="kecamatan">Kecamatan</label>
            <select id="kecamatan" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>
          <div class="col-md-6 col-lg-3">
            <label class="form-label" for="kelurahan">Kelurahan</label>
            <select id="kelurahan" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="patient_address">Alamat Lengkap</label>
            <textarea name="patient_address" id="patient_address" class="form-control" rows="2" placeholder="Jalan, nomor rumah, RT/RW"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="ptSave"><i class="fas fa-save me-1"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
  const apiUrl = 'controller/master/patientContrroller';
  let table;
  // dipanggil juga oleh skrip kamera setelah foto tersimpan
  let muatStatistik = () => {};

  (function() {
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    } [c]));
    const fmt = n => (Number(n) || 0).toLocaleString('id-ID');
    const toast = (icon, title) => Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 2200,
      timerProgressBar: true
    });
    const fmtTgl = t => t ? new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    }) : '';
    const fotoUrl = f => String(f || '').replace(/^(\.\.\/)+/, '').replace(/^\/+/, '');
    const umur = tgl => {
      if (!/^\d{4}-\d{2}-\d{2}/.test(tgl || '')) return '';
      const b = new Date(tgl.slice(0, 10) + 'T00:00:00'),
        n = new Date();
      if (isNaN(b) || b > n) return '';
      let th = n.getFullYear() - b.getFullYear(),
        bl = n.getMonth() - b.getMonth();
      if (n.getDate() < b.getDate()) bl--;
      if (bl < 0) {
        th--;
        bl += 12;
      }
      return th > 0 ? `${th} th` : `${bl} bln`;
    };
    const inisial = n => {
      const k = String(n || '').trim().split(/\s+/).filter(Boolean);
      return ((k[0] || '?')[0] + (k.length > 1 ? k[k.length - 1][0] : '')).toUpperCase();
    };
    const waLink = hp => {
      let n = String(hp || '').replace(/\D/g, '');
      if (n.startsWith('0')) n = '62' + n.slice(1);
      return n.length >= 9 ? `https://wa.me/${n}` : '';
    };
    const pct = (a, b) => b ? Math.round(a / b * 100) : 0;

    let filter = '';

    // ---------- statistik ----------
    muatStatistik = function() {
      fetch(apiUrl + '?stats=1').then(r => r.json()).then(r => {
        if (r.status !== 'success') return;
        const d = r.data;
        $('#stTotal').text(fmt(d.total));
        $('#stBpjs').text(fmt(d.bpjs));
        $('#stBpjsSub').text(`Peserta BPJS · ${pct(d.bpjs, d.total)}%`);
        $('#stBpjsBar').css('width', pct(d.bpjs, d.total) + '%');
        $('#stIhs').text(fmt(d.ihs));
        $('#stIhsSub').text(`Terhubung Satu Sehat · ${pct(d.ihs, d.total)}%`);
        $('#stIhsBar').css('width', pct(d.ihs, d.total) + '%');
        $('#stWajah').text(fmt(d.wajah));
        $('#stWajahSub').text(`Wajah terekam · ${pct(d.wajah, d.total)}%`);
        $('#stWajahBar').css('width', pct(d.wajah, d.total) + '%');
      }).catch(() => {});
    };

    // ---------- tabel ----------
    function kolomPasien(r) {
      const g = /^l/i.test(r.patient_gender || '') ? 'l' : /^p/i.test(r.patient_gender || '') ? 'p' : 'x';
      const foto = fotoUrl(r.face_image);
      const u = umur(r.patient_datebirth);
      const ava = foto ?
        `<a href="${esc(foto)}" target="_blank" class="pt-ava ${g}" title="Lihat foto"><img src="${esc(foto)}" loading="lazy" alt=""></a>` :
        `<div class="pt-ava ${g}">${esc(inisial(r.patient_name))}</div>`;
      return `<div class="d-flex align-items-center gap-3">${ava}
        <div class="min-w-0">
          <a href="module/admin/patient_details?pt=${r.id_patient}" class="pt-name d-block text-reset" title="${esc(r.patient_name)}">${esc((r.patient_name || '-').trim())}</a>
          <div class="pt-sub d-flex flex-wrap gap-2 align-items-center mt-1">
            <span class="pt-rm" title="Nomor rekam medis">${esc((r.nomor_rm || '-').trim())}</span>
            ${g === 'l' ? '<span><i class="fas fa-mars text-primary"></i> L</span>' : g === 'p' ? '<span><i class="fas fa-venus" style="color:#e0569b"></i> P</span>' : ''}
            ${u ? `<span title="${esc(fmtTgl(r.patient_datebirth.slice(0, 10)))}">${u}</span>` : ''}
          </div>
        </div></div>`;
    }

    function kolomIdentitas(r) {
      const nik = r.patient_nik ? `<div class="pt-id" title="NIK">${esc(r.patient_nik)}</div>` : '<div class="pt-sub">NIK belum diisi</div>';
      const bpjs = r.patient_bpjs ?
        `<span class="pt-badge bg-success-subtle text-success mt-1" title="No. BPJS"><i class="fas fa-id-card"></i>${esc(r.patient_bpjs)}</span>` :
        '<span class="pt-badge bg-secondary-subtle text-secondary mt-1">Non-BPJS</span>';
      const ihs = r.idsh ? `<span class="pt-badge bg-info-subtle text-info mt-1" title="ID IHS Satu Sehat: ${esc(r.idsh)}"><i class="fas fa-heartbeat"></i>IHS</span>` : '';
      return `${nik}<div class="d-flex gap-1" style="white-space:nowrap">${bpjs}${ihs}</div>`;
    }

    function kolomKontak(r) {
      const wa = waLink(r.patient_phone);
      const hp = r.patient_phone ?
        (wa ? `<a href="${wa}" target="_blank" rel="noopener" class="text-reset">${esc(r.patient_phone)} <i class="fab fa-whatsapp text-success"></i></a>` : esc(r.patient_phone)) :
        '<span class="pt-sub">-</span>';
      const lokasi = r.patient_kabupaten || r.patient_address || '';
      return `<div>${hp}</div>${lokasi ? `<div class="pt-sub text-truncate" style="max-width:200px" title="${esc(r.patient_address || '')}">${esc(lokasi)}</div>` : ''}`;
    }

    function kolomKunjungan(r) {
      if (!+r.jml_kunjungan) return '<span class="pt-sub">Belum ada</span>';
      return `<div class="fw-semibold">${fmt(r.jml_kunjungan)}x</div><div class="pt-sub">${esc(fmtTgl(r.kunjungan_terakhir))}</div>`;
    }

    function kolomAksi(r) {
      const dipakai = +r.jml_kunjungan > 0;
      return `<div class="pt-act d-flex gap-1 justify-content-center">
        <button class="btn btn-light text-warning camera-btn" data-id="${r.id_patient}" title="${r.face_image ? 'Rekam ulang wajah' : 'Rekam wajah'}"><i class="fas fa-camera"></i></button>
        <a class="btn btn-light text-info" href="module/admin/patient_details?pt=${r.id_patient}" title="Detail"><i class="fas fa-id-badge"></i></a>
        <button class="btn btn-light text-primary edit-btn" data-id="${r.id_patient}" title="Ubah"><i class="fas fa-pen"></i></button>
        ${dipakai
          ? `<span class="d-inline-block" tabindex="0" title="Tidak bisa dihapus: pasien sudah memiliki ${fmt(r.jml_kunjungan)} kunjungan"><button class="btn btn-light text-danger" disabled style="pointer-events:none"><i class="fas fa-trash"></i></button></span>`
          : `<button class="btn btn-light text-danger delete-btn" data-id="${r.id_patient}" data-nama="${esc(r.patient_name)}" title="Hapus"><i class="fas fa-trash"></i></button>`}
      </div>`;
    }

    table = $('#periodeTable').DataTable({
      processing: false,
      serverSide: true,
      ordering: false,
      searching: true,
      autoWidth: false,
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"<"d-flex align-items-center gap-2"li>p>',
      language: {
        info: 'Menampilkan _START_-_END_ dari _TOTAL_ pasien',
        infoEmpty: 'Tidak ada pasien',
        infoFiltered: '(dari _MAX_)',
        lengthMenu: '_MENU_',
        emptyTable: '<div class="py-4 text-muted"><i class="fas fa-user-injured fa-2x mb-2 d-block"></i>Belum ada pasien. Data pasien juga otomatis bertambah saat faskes mendaftarkan pasien dari PCare BPJS.</div>',
        zeroRecords: '<div class="py-4 text-muted"><i class="fas fa-search fa-2x mb-2 d-block"></i>Pasien tidak ditemukan.</div>',
        paginate: {
          previous: '‹',
          next: '›'
        }
      },
      ajax: {
        url: apiUrl,
        type: 'GET',
        data: d => ({
          draw: d.draw,
          start: d.start,
          length: d.length,
          q: $('#ptSearch').val().trim(),
          filter,
          sort: $('#ptSort').val()
        }),
        error: () => {
          $('#ptLoading').hide();
          toast('error', 'Gagal memuat data pasien.');
        }
      },
      columns: [{
        data: null,
        render: (d, t, r) => kolomPasien(r)
      }, {
        data: null,
        render: (d, t, r) => kolomIdentitas(r)
      }, {
        data: null,
        render: (d, t, r) => kolomKontak(r)
      }, {
        data: null,
        className: 'text-center',
        render: (d, t, r) => kolomKunjungan(r)
      }, {
        data: null,
        className: 'text-center',
        render: (d, t, r) => r.face_image ?
          '<span class="pt-badge bg-success-subtle text-success"><i class="fas fa-check-circle"></i>Terekam</span>' : '<span class="pt-badge bg-warning-subtle text-warning"><i class="fas fa-camera"></i>Belum</span>'
      }, {
        data: null,
        className: 'text-center',
        render: (d, t, r) => kolomAksi(r)
      }]
    }).on('preXhr.dt', () => $('#ptLoading').css('display', 'flex'))
      .on('xhr.dt', () => $('#ptLoading').hide());

    // cari: tunggu user berhenti mengetik
    let tCari;
    $('#ptSearch').on('input', function() {
      clearTimeout(tCari);
      tCari = setTimeout(() => table.ajax.reload(), 400);
    }).on('keydown', function(e) {
      if (e.key === 'Enter') {
        clearTimeout(tCari);
        table.ajax.reload();
      }
    });
    $('#customSearch').on('keyup', function() {
      $('#ptSearch').val(this.value).trigger('input');
    });
    $('#ptSort').on('change', () => table.ajax.reload());

    function setFilter(f) {
      filter = f;
      $('#ptChips .btn').each(function() {
        const on = String($(this).data('filter')) === f;
        $(this).toggleClass('btn-primary', on).toggleClass('btn-light', !on);
      });
      $('.pt-stat.pt-click').each(function() {
        $(this).toggleClass('active', f !== '' && String($(this).data('filter')) === f);
      });
      table.ajax.reload();
    }
    $('#ptChips').on('click', '.btn', function() {
      setFilter(String($(this).data('filter')));
    });
    $('.pt-stat.pt-click').on('click', function() {
      setFilter(String($(this).data('filter')));
    });
    $('#btnRefresh').on('click', () => {
      muatStatistik();
      table.ajax.reload(null, false);
    });

    // ---------- form ----------
    function setGender(v) {
      $('#patient_gender').val(v || '');
      $('input[name="_gender_ui"]').prop('checked', false);
      if (v) $(`input[name="_gender_ui"][value="${v}"]`).prop('checked', true);
    }
    $('input[name="_gender_ui"]').on('change', function() {
      $('#patient_gender').val(this.value);
    });
    // hasil cari BPJS men-set select gender lewat trigger('change')
    $('#patient_gender').on('change', function() {
      setGender(this.value);
    });
    $('#patient_datebirth').on('change input', function() {
      const u = umur(this.value);
      $('#ptUmur').text(u ? `Umur ${u}` : '');
    });

    function resetWilayah() {
      $('#provinsi').val('');
      $('#kabupaten, #kecamatan, #kelurahan').html('<option value="">PILIH</option>');
    }

    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset();
      $('#id_patient').val('');
      $('#provinsi_text, #kabupaten_text, #kecamatan_text, #kelurahan_text').val('');
      resetWilayah();
      setGender('');
      $('#ptUmur').text('');
      $('#ptRmInfo').removeClass('d-none');
      $('#ptWilayahNow').addClass('d-none');
      $('#ptModalTitle').text('Pasien Baru');
      $('#programModal').modal('show');
    });
    $('#programModal').on('shown.bs.modal', () => {
      if (!$('#id_patient').val()) $('#patient_nik').trigger('focus');
    });

    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      if (!$('#patient_gender').val()) return Swal.fire('Lengkapi data', 'Pilih jenis kelamin.', 'warning');
      if (!this.checkValidity()) {
        this.reportValidity();
        return;
      }
      const formData = $(this).serializeArray().filter(f => f.name !== '_gender_ui');
      const id = $('#id_patient').val();
      if (id) formData.push({
        name: '_method',
        value: 'PUT'
      });
      const $btn = $('#ptSave').prop('disabled', true);
      $.ajax({
        url: apiUrl,
        type: 'POST',
        data: $.param(formData),
        success: function(res) {
          const data = typeof res === 'string' ? JSON.parse(res) : res;
          if (data.status === 'success') {
            $('#programModal').modal('hide');
            if (!id && data.nomor_rm) {
              Swal.fire('Pasien terdaftar', `Nomor rekam medis: <b>${esc(data.nomor_rm)}</b>`, 'success');
            } else {
              toast('success', data.message || 'Data pasien tersimpan');
            }
            muatStatistik();
            table.ajax.reload(null, !!id ? false : true);
          } else {
            Swal.fire('Gagal', data.message || 'Gagal menyimpan.', 'error');
          }
        },
        error: () => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'),
        complete: () => $btn.prop('disabled', false)
      });
    });

    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status !== 'success') return Swal.fire('Gagal', resp.message || 'Data tidak ditemukan.', 'error');
          const d = resp.data;
          $('#programForm')[0].reset();
          resetWilayah();
          for (const key in d) {
            $(`#programForm [name="${key}"]`).val(d[key] ?? '');
          }
          if (d.patient_datebirth) $('#patient_datebirth').val(String(d.patient_datebirth).slice(0, 10)).trigger('change');
          setGender(d.patient_gender);
          const wil = [d.patient_kelurahan, d.patient_kecamatan, d.patient_kabupaten, d.patient_provinsi].filter(Boolean).join(', ');
          $('#ptWilayahNow').toggleClass('d-none', !wil).html(wil ? `<i class="fas fa-map-marker-alt me-1"></i>Wilayah saat ini: <b>${esc(wil)}</b>. Pilih ulang di bawah hanya jika ingin mengubah.` : '');
          $('#ptRmInfo').addClass('d-none');
          $('#ptModalTitle').text(`Ubah Pasien · ${(d.nomor_rm || '').trim()}`);
          $('#programModal').modal('show');
        });
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      Swal.fire({
        title: 'Hapus pasien?',
        html: `<b>${esc($(this).data('nama') || '')}</b> belum memiliki kunjungan dan akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#fa896b'
      }).then(result => {
        if (!result.isConfirmed) return;
        fetch(apiUrl + `?id=${id}`, {
            method: 'DELETE'
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              toast('success', data.message);
              muatStatistik();
              table.ajax.reload(null, false);
            } else {
              Swal.fire('Gagal', data.message || 'Gagal menghapus.', 'error');
            }
          });
      });
    });

    $(document).on('click', '#btncarinik', function() {
      cariDataPatient('nik', '#btncarinik');
    });
    $(document).on('click', '#btncaribpjs', function() {
      cariDataPatient('noka', '#btncaribpjs');
    });

    function cariDataPatient(type, id) {
      const btn = $(id);
      const nomor = (type === 'nik' ? $('#patient_nik').val() : $('#patient_bpjs').val()).trim();
      if (!nomor) return Swal.fire('Isi nomor', type === 'nik' ? 'Isi NIK terlebih dahulu.' : 'Isi No. BPJS terlebih dahulu.', 'info');
      $.ajax({
        type: 'GET',
        url: 'controller/admisi/services/getInfoPatient.php',
        data: {
          type,
          nomor
        },
        dataType: 'json',
        beforeSend: function() {
          btn.prop('disabled', true);
          btn.find('.btn-text').addClass('d-none');
          btn.find('.btn-loading').removeClass('d-none');
        },
        complete: function() {
          btn.prop('disabled', false);
          btn.find('.btn-text').removeClass('d-none');
          btn.find('.btn-loading').addClass('d-none');
        },
        success: function(response) {
          if (!response || !response.nama) {
            return Swal.fire('Tidak ditemukan', 'Data peserta tidak ditemukan di BPJS.', 'info');
          }
          if (type === 'nik') {
            $('#patient_bpjs').val(response.noKartu);
          } else {
            $('#patient_nik').val(response.nik);
          }
          $('#patient_name').val(response.nama);
          $('#patient_datebirth').val(response.tglLahir).trigger('change');
          $('#patient_phone').val(response.noHP);
          $('#patient_gender').val(response.sex == 'L' ? 'Laki-laki' : 'Perempuan').trigger('change');
          toast('success', 'Data peserta BPJS ditemukan');
        },
        error: function(xhr, status, error) {
          console.log(error);
          Swal.fire('Gagal', 'Tidak dapat mengambil data dari BPJS.', 'error');
        }
      });
    }

    muatStatistik();
  })();
</script>
<script src="assets/js/face-api.min.js"></script>
<script>
  let currentPatientId = null;
  let stream = null;
  let faceTimer = null;
  let faceModelReady = null;
  let faceTooClose = false;

  function loadFaceModel() {
    if (!faceModelReady) {
      faceModelReady = faceapi.nets.tinyFaceDetector.loadFromUri('models');
    }
    return faceModelReady;
  }

  function setFaceState(ok, message, tooClose = false) {
    faceTooClose = tooClose;
    const circle = document.querySelector(".face-circle");
    const status = document.getElementById("faceStatus");
    const btn = document.getElementById("captureBtn");
    circle.classList.toggle("face-frame-success", ok);
    circle.classList.toggle("face-frame-warning", !ok);
    status.className = "face-status " + (ok ? "text-success" : "text-warning");
    status.innerHTML = `<i class="fas ${ok ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
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

    if (ratio < 0.5) return {ok: false, msg: "Dekatkan wajah ke kamera"};
    if (ratio > 0.95) return {ok: false, tooClose: true, msg: "Terlalu dekat, mundurkan sedikit"};
    if (dx > 0.2 || dy > 0.2) return {ok: false, msg: "Posisikan wajah di tengah lingkaran"};
    return {ok: true, msg: "Wajah pas, siap diambil"};
  }

  async function startFaceDetection() {
    const video = document.getElementById("video");
    stopFaceDetection();
    faceTooClose = false;
    setFaceState(false, "Memuat deteksi wajah...");
    try {
      await loadFaceModel();
    } catch (e) {
      console.error(e);
      setFaceState(false, "Gagal memuat model deteksi wajah");
      return;
    }
    const opts = new faceapi.TinyFaceDetectorOptions({inputSize: 320, scoreThreshold: 0.5});
    let busy = false;
    faceTimer = setInterval(async () => {
      if (busy || !video.videoWidth || video.paused) return;
      busy = true;
      try {
        const det = await faceapi.detectSingleFace(video, opts);
        if (!faceTimer) return;
        if (!det) setFaceState(false, "Wajah tidak terdeteksi");
        else {
          const r = evaluateFace(det.box, video);
          setFaceState(r.ok, r.msg, !!r.tooClose);
        }
      } catch (e) {
        console.error(e);
      } finally {
        busy = false;
      }
    }, 250);
  }

  function stopFaceDetection() {
    if (faceTimer) {
      clearInterval(faceTimer);
      faceTimer = null;
    }
  }

  $(document).on("click", ".camera-btn", async function() {
    // WAJIB: ambil id dari tombol
    currentPatientId = $(this).data("id");
    console.log("📌 ID PATIENT:", currentPatientId);
    const modalEl = document.getElementById("cameraModal");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: true
      });

      const video = document.getElementById("video");
      video.srcObject = stream;

      await video.play();
      startFaceDetection();

    } catch (err) {
      alert("Kamera tidak bisa diakses");
      console.error(err);
    }

  });
  document.getElementById("captureBtn").addEventListener("click", function() {
    if (this.disabled) return;
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

    const captureBtn = document.getElementById("captureBtn");

    captureBtn.disabled = true;
    captureBtn.dataset.saving = "1";

    captureBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1"></span>
        Menyimpan...
    `;

    fetch("controller/admisi/recordFace.php", {

        method: "POST",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify({
          id: currentPatientId,
          image: imageData
        })

      })
      .then(res => res.json())

      .then(res => {

        if (res.status === "success") {

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
            muatStatistik();
          }, 500);

        } else {

          Swal.fire(
            "Gagal!",
            res.message || "Wajah gagal disimpan.",
            "error"
          );

        }

      })

      .catch(error => {

        console.error(error);

        Swal.fire(
          "Error!",
          "Terjadi kesalahan saat menyimpan wajah.",
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

  });

  /* =========================
     CLEANUP (INI YANG PENTING)
  ========================= */
  document.getElementById("cameraModal")
    .addEventListener("hidden.bs.modal", function() {

      console.log("🛑 Stop camera");
      stopFaceDetection();

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
  const apiWilayah = "controller/master/wilayah.php";

  // 🔥 LOAD PROVINSI
  function loadProvinsi() {
    fetch(`${apiWilayah}?type=provinsi`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#provinsi').html(html);
      });
  }

  // 🔥 LOAD KABUPATEN
  $('#provinsi').on('change', function() {
    let id = $(this).val();

    $('#kabupaten').html('<option>Loading...</option>');
    $('#kecamatan').html('<option>PILIH</option>');
    $('#kelurahan').html('<option>PILIH</option>');

    fetch(`${apiWilayah}?type=kabupaten&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kabupaten').html(html);
      });
  });

  // 🔥 LOAD KECAMATAN
  $('#kabupaten').on('change', function() {
    let id = $(this).val();

    $('#kecamatan').html('<option>Loading...</option>');
    $('#kelurahan').html('<option>PILIH</option>');

    fetch(`${apiWilayah}?type=kecamatan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kecamatan').html(html);
      });
  });

  // 🔥 LOAD KELURAHAN
  $('#kecamatan').on('change', function() {
    let id = $(this).val();

    $('#kelurahan').html('<option>Loading...</option>');

    fetch(`${apiWilayah}?type=kelurahan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kelurahan').html(html);
      });
  });

  // 🔥 INIT
  $(document).ready(function() {
    loadProvinsi();
  });
</script>

<script>
  $('#provinsi').on('change', function() {
    $('#provinsi_text').val($(this).find('option:selected').text());
  });

  $('#kabupaten').on('change', function() {
    $('#kabupaten_text').val($(this).find('option:selected').text());
  });

  $('#kecamatan').on('change', function() {
    $('#kecamatan_text').val($(this).find('option:selected').text());
  });

  $('#kelurahan').on('change', function() {
    $('#kelurahan_text').val($(this).find('option:selected').text());
  });
</script>

</html>