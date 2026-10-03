<?php
$title = 'Registrasi Poliklinik';
require '../../controller/view.php';
date_default_timezone_set('Asia/Jakarta');
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
          <ul class="nav nav-tabs align-items-center" id="myTab" role="tablist"> <!-- Grup kiri -->
            <li class="nav-item" role="presentation"> <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab"> Belum Dilayani </button> </li>
            <li class="nav-item" role="presentation"> <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab"> Sudah Dilayani </button> </li>
            <li class="nav-item" role="presentation"> <button class="nav-link" id="batal-tab" data-bs-toggle="tab" data-bs-target="#batal-tab-pane" type="button" role="tab"> Batal Dilayani </button> </li> <!-- Pemisah 2 garis -->
            <li class="nav-item mx-3 d-flex align-items-center">
              <div style="width:1px;height:24px;background:#dee2e6;"></div>
              <div class="mx-1"></div>
              <div style="width:1px;height:24px;background:#dee2e6;"></div>
            <li class="nav-item" role="presentation"> <button class="nav-link  fw-bold" id="kunjungsehat-tab" data-bs-toggle="tab" data-bs-target="#kunjungsehat-pane" type="button" role="tab"> Kunjungan Sehat </button> </li>
            <li class="nav-item" role="presentation"> <button class="nav-link fw-bold" id="kunjungsehatbatal-tab" data-bs-toggle="tab" data-bs-target="#kunjungsehatbatal-pane" type="button" role="tab"> Kunjungan Sehat Batal</button> </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0"></div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0"></div>
            <div class="tab-pane fade" id="batal-tab-pane" role="tabpanel" aria-labelledby="batal-tab" tabindex="0"></div>
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Registrasi Poliklinik</h5>
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <div class="col-auto">
                        <button type="button" id="btnModalFilter" class="btn btn-dark">
                          <i class="fas fa-filter"></i> Filter
                        </button>
                      </div>
                      <div class="col-auto">
                        <button type="button" id="btnReset" class="btn btn-light">
                          <i class="fas fa-undo"></i> Reset
                        </button>
                      </div>
                      <!-- Tombol kembali -->
                      <div class="d-flex ms-auto">
                        <div class="dropdown">
                          <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus"></i> Tambah
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                              <a class="dropdown-item" href="module/admisi/registrasi-new">
                                <i class="fas fa-user-plus me-2 text-primary"></i> Pasien Baru
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="module/admisi/pendaftaran">
                                <i class="fas fa-stethoscope me-2 text-success"></i> Poliklinik
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item poli-btn" href="javascript:;">
                                <i class="fas fa-stethoscope me-2 text-danger"></i> Tanpa Identitas
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal">No.BPJS</th>
                          <th>Antrian</th>
                          <th class="text-dark fw-normal">Sumber</th>
                          <th class="text-dark fw-normal">Waktu</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Poli</th>
                          <th scope="col" class="text-dark fw-normal">Screening</th>
                          <th scope="col" class="text-dark fw-normal">Jenis Bayar</th>

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
    </div>
  </div>
  <?php
  require 'library.php';
  ?>
  <script src="controller/admisi/helper.js"></script>
</body>


<div class="modal fade" id="detailModal">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow">

      <!-- HEADER -->
      <div class="modal-header  text-white">
        <h5 class="modal-title">
          🩺 Detail Pemeriksaan
        </h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Loading -->
        <div id="detailLoading" class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status"></div>
          <div class="fw-semibold">Memuat data pemeriksaan...</div>
        </div>

        <div id="detailContent" style="display:none;">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <h6 class="fw-bold mb-3">👤 Informasi Pasien</h6>

              <div class="row g-3">
                <div class="col-md-6">
                  <small class="text-muted">Nama Pasien</small>
                  <div class="fw-semibold" id="d_patient_name">-</div>
                </div>

                <div class="col-md-6">
                  <small class="text-muted">Dokter</small>
                  <div id="d_doctor_name">-</div>
                </div>

                <div class="col-md-6">
                  <small class="text-muted">Poliklinik</small>
                  <div id="d_poli_name">-</div>
                </div>

                <div class="col-md-6">
                  <small class="text-muted">Tanggal</small>
                  <span>
                    <div id="d_visit_date">-</div>
                  </span>

                </div>
                <div class="col-md-6">
                  <small class="text-muted">Kondisi Masuk</small>
                  <div id="d_kondisi_masuk">-</div>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Umur</small>
                  <div id="d_umur">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ❤️ VITAL SIGN -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <h6 class="fw-bold mb-3">❤️ Vital Sign</h6>

              <div class="d-flex flex-wrap gap-2">

                <span class="badge bg-light text-dark">
                  🩸 TD: <span id="d_tekanan_darah">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  🌡️ Suhu: <span id="d_suhu">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  ❤️ Nadi: <span id="d_nadi">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  🫁 RR: <span id="d_respirasi">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  🩸 Saturasi: <span id="d_saturasi">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  📏 TB: <span id="d_tinggi">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  ⚖️ BB: <span id="d_berat">-</span>
                </span>

                <span class="badge bg-light text-dark">
                  🏋 BMI: <span id="d_bmi">-</span> Keterangan : <span id="d_bmi_keterangan"></span>
                </span>

              </div>
            </div>
          </div>

          <!-- 🧠 ANAMNESA -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <h6 class="fw-bold mb-2">🧠 Keluhan / Anamnesa</h6>
              <div id="d_anamnesa" class="text-muted">-</div>
            </div>
          </div>

          <!-- <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <h6 class="fw-bold mb-2">📝 Catatan Screening</h6>
              <div id="d_catatan_screening" class="text-muted">-</div>
            </div>
          </div> -->

          <!-- 🔬 DIAGNOSA -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <h6 class="fw-bold mb-2">🔬 Diagnosa</h6>
              <div id="d_diagnosa" class="text-muted">-</div>
            </div>
          </div>

          <!--  TINDAKAN -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="fw-bold mb-2">📝 Tindakan</h6>
              <div id="d_tindakan" class="text-muted">-</div>
            </div>
          </div>

          <!-- 💊 Obat -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="fw-bold mb-2">💊 Obat</h6>
              <div id="d_obat" class="text-muted">-</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="filterModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 mb-3">
            <label for="fromDate" class="form-label mb-0">Dari</label>
            <input type="date" id="fromDate" name="fromDate" class="form-control">
          </div>
          <div class="col-6 mb-3">
            <label for="toDate" class="form-label mb-0">Sampai</label>
            <input type="date" id="toDate" name="toDate" class="form-control">
          </div>
          <div class="col-12 mb-3">
            <label for="doctorSelect" class="form-label mb-0">Dokter</label>
            <select name="doctorSelect" class="form-select" id="doctorSelect">
              <option value="">Semua Dokter</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label for="providerSelect" class="form-label mb-0">Provider</label>
            <select name="providerSelect" class="form-select" id="providerSelect">
              <option value="">Semua Metode Pembayaran</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label for="poliSelect" class="form-label mb-0">Poliklinik</label>
            <select name="poliSelect" class="form-select" id="poliSelect">
              <option value="">Semua Poliklinik</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button class="btn btn-primary" id="btnApplyFilter">Terapkan Filter</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="screeningModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header text-dark">
        <h5 class="modal-title">Vital Sign</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="screening_id_visit">

        <div class="mb-3">
          <label class="form-label">Keluhan</label>
          <textarea id="sc_keluhan" class="form-control"></textarea>
        </div>

        <h5>Pemeriksaan Vital Sign (Perawat)</h5>
        <div class="row g-2">
          <div class="col-md-4">
            <label for="kondisi_masuk" class="form-label">Kondisi Masuk <span class="text-danger">*</span></label>
            <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
              <option value="Baik">Baik</option>
              <option value="Lemah">Lemah</option>
              <option value="Sedang">Sedang</option>
              <option value="Buruk">Buruk</option>
              <option value="Gawat Darurat">Gawat Darurat</option>
              <option value="Tidak Sadar">Tidak Sadar</option>
            </select>
          </div>

          <!-- Input Sistole dan Diastole (Tekanan Darah) -->
          <div class="col-md-4">
            <label class="form-label">Tekanan Darah (mmHg) <span class="text-danger">*</span></label>
            <div class="d-flex gap-2">
              <input type="number" id="sistole" name="sistole" class="form-control" placeholder="Sistole" required>
              <span class="align-self-center">/</span>
              <input type="number" id="diastole" name="diastole" class="form-control" placeholder="Diastole" required>
            </div>
            <!-- hidden untuk backend jika disatukan (opsional) -->
            <input type="hidden" id="tekanan_darah" name="tekanan_darah">
          </div>

          <div class="col-md-4">
            <label for="suhu" class="form-label">Suhu (°C) <span class="text-danger">*</span></label>
            <input type="number" step="0.1" id="suhu" required name="suhu" class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="nadi" class="form-label">Nadi (x/menit) <span class="text-danger">*</span></label>
            <input type="number" id="nadi" name="nadi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="respirasi" class="form-label">Respirasi (x/menit) <span class="text-danger">*</span></label>
            <input type="number" id="respirasi" name="respirasi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="saturasi" class="form-label">Saturasi (Sp02%)</label>
            <input type="number" id="saturasi" name="saturasi" class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="tinggi" class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
            <input type="number" id="tinggi" name="tinggi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="berat" class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
            <input type="number" id="berat" name="berat" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="bmi" class="form-label">BMI <span class="text-danger">*</span></label>
            <input type="number" readonly id="bmi" name="bmi" required class="form-control bg-light">
          </div>
          <div class="col-md-4 mt-2">
            <label class="form-label">Keterangan BMI</label>
            <input type="text" id="bmi_ket" name="bmi_ket" readonly class="form-control bg-light">
          </div>

          <!-- Tambahan Input Lingkar Perut -->
          <div class="col-md-4 mt-2">
            <label for="lingkar_perut" class="form-label">Lingkar Perut (cm)</label>
            <input type="number" id="lingkar_perut" name="lingkar_perut" class="form-control">
          </div>
        </div>

        <div class="mb-3 mt-3">
          <label class="form-label">Catatan Screening</label>
          <textarea id="sc_catatan" class="form-control"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnSaveScreening">Simpan</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="poliModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Registrasi Poliklinik</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPoli">
          <!-- Pasien -->
          <div class="mb-3">
            <label class="form-label">Nama Pasien</label>
            <select name="id_patient_select" id="id_patient_select"
              class="form-select js-example-basic-item" required>
            </select>
          </div>
          <div class="row">
            <div class="col">
              <!-- Tanggal -->
              <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" id="poli_date" class="form-control">
              </div>
            </div>
            <div class="col">
              <!-- Jam -->
              <div class="mb-3">
                <label class="form-label">Jam Kunjungan</label>
                <input type="time" id="poli_time" class="form-control">
              </div>
            </div>
          </div>

          <!-- Poli -->
          <div class="mb-3">
            <label class="form-label">Poliklinik</label>
            <select id="poli_poli" class="form-select"></select>
            <input type="hidden" id="kdPoli" name="kdPoli">
          </div>

          <!-- Dokter -->
          <div class="mb-3">
            <label class="form-label">Dokter</label>
            <select id="poli_doctor" class="form-select"></select>
          </div>

          <!-- Provider -->
          <div class="mb-3">
            <label class="form-label">Provider</label>
            <select id="poli_provider" class="form-select"></select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" id="btnSavePoli">Simpan</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="ttdModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">✍️ Tanda Tangan Pasien</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">

        <input type="hidden" id="ttd_id_visit">

        <canvas id="signaturePad"
          style="border:1px solid #ccc; width:100%; height:400;">
        </canvas>

        <div class="mt-3 d-flex justify-content-between">
          <button class="btn btn-warning" id="clearSignature">🧹 Clear</button>
          <button class="btn btn-primary" id="saveSignature">💾 Simpan</button>
        </div>

      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="editVisitModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">✏️ Edit Visit</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="edit_id_patient">
        <input type="hidden" id="edit_visit_id">

        <div class="mb-3">
          <label>Tanggal</label>
          <input type="date" id="edit_visit_date" class="form-control">
        </div>

        <div class="mb-3">
          <label>Jam</label>
          <input type="time" id="edit_visit_time" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnUpdateVisit">💾 Simpan</button>
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
<div class="modal fade" id="modalRescheduleDoctor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formRescheduleDoctor">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-user-md me-2"></i>Ganti Dokter
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="visit_id" name="visit_id">
          <div class="mb-3">
            <label class="form-label">
              Dokter Saat Ini
            </label>
            <input
              type="text"
              class="form-control"
              id="doctor_now"
              readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">
              Ganti Ke Dokter
            </label>
            <select
              class="form-select"
              id="doctor_new"
              name="doctor_new"
              required>
              <option value="">-- Pilih Dokter --</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Batal
          </button>
          <button
            type="submit"
            class="btn btn-primary">
            <i class="fas fa-save me-1"></i>
            Simpan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  let currentTab = 'belum';
  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {

    const target = $(e.target).attr("id");

    if (target === 'home-tab') {
      currentTab = 'belum';
    } else if (target === 'profile-tab') {
      currentTab = 'selesai';
    } else if (target === 'batal-tab') {
      currentTab = 'batal';
    } else if (target === 'kunjungsehat-tab') {
      currentTab = 'sehat';
    } else if (target === 'kunjungsehatbatal-tab') {
      currentTab = 'sehatbatal';
    }

    console.log("TAB AKTIF:", currentTab);

    $('#periodeTable').DataTable().ajax.reload(null, false);

  });
</script>
<script>
  function hitungBMI() {
    const tinggi = parseFloat(document.getElementById('tinggi').value);
    const berat = parseFloat(document.getElementById('berat').value);

    if (!tinggi || !berat) return;

    const tinggiMeter = tinggi / 100;
    const bmi = berat / (tinggiMeter * tinggiMeter);

    // set nilai BMI (2 decimal)
    document.getElementById('bmi').value = bmi.toFixed(2);

    // kategori BMI
    let ket = '';

    if (bmi < 18.5) {
      ket = 'Kurus';
    } else if (bmi < 25) {
      ket = 'Normal';
    } else if (bmi < 30) {
      ket = 'Gemuk';
    } else {
      ket = 'Obesitas';
    }

    document.getElementById('bmi_ket').value = ket;
  }

  // trigger saat input berubah
  document.getElementById('tinggi').addEventListener('input', hitungBMI);
  document.getElementById('berat').addEventListener('input', hitungBMI);
</script>
<script>
  APP.window = APP.window || {};
  $(document).ready(function() {

    $('#btnModalFilter').on('click', function() {
      $('#filterModal').modal('show');
      loadDoctors();
      loadProviders();
      loadPoli('#poliSelect');
    })


    function loadDoctors() {
      $.ajax({
        url: 'controller/visit/getdoctor',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          let html = '<option value="">Semua Dokter</option>';

          res.forEach(d => {
            html += `<option value="${d.doctor_name}">${d.doctor_name}</option>`;
          });

          $('#doctorSelect').html(html);
        }
      });
    }

    function loadProviders() {
      $.ajax({
        url: 'controller/visit/getprovider',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          let html = '<option value="">Semua Metode Pembayaran</option>';

          res.forEach(p => {
            html += `<option value="${p.id_provider}">${p.provider_name}</option>`;
          });

          $('#providerSelect').html(html);
        }
      });
    }




    $('#btnApplyFilter').on('click', function() {
      table.ajax.reload();
      $('#filterModal').modal('hide');
    });

    var today = new Date().toLocaleDateString("sv-SE", {
      timeZone: "Asia/Jakarta"
    });
    $('#fromDate').val(today);
    $('#fromDate').attr('max', today);

    $('#toDate').val(today);
    const tipePasien = "Poliklinik";
    const apiUrl = 'controller/visit/registrasiController';
    window.table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      scrollCollapse: true,
      order: [],
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctor = $('#doctorSelect').val();
          d.provider = $('#providerSelect').val();
          d.poli = $('#poliSelect').val();
          d.tipe_pasien = tipePasien;
          // 🔥 INI KUNCI TAB
          d.tab = currentTab;
        },
        dataSrc: function(json) {
          return json.data.map(function(row) {

            function hitungUmur(tglLahir, tglKunjungan) {
              if (!tglLahir || !tglKunjungan) return "-";

              const birth = new Date(tglLahir);
              const visit = new Date(tglKunjungan);

              let tahun = visit.getFullYear() - birth.getFullYear();
              let bulan = visit.getMonth() - birth.getMonth();
              let hari = visit.getDate() - birth.getDate();

              if (hari < 0) {
                bulan--;
                const lastMonth = new Date(visit.getFullYear(), visit.getMonth(), 0);
                hari += lastMonth.getDate();
              }

              if (bulan < 0) {
                tahun--;
                bulan += 12;
              }

              return `${tahun} th ${bulan} bln ${hari} hr`;
            }
            let status_sumber = "Non JKN/UMUM";
            if (row.provider_name == 'BPJS KESEHATAN' && row.created_user != "JKNOnsite") {
              status_sumber = "Mobile JKN";
            } else if (row.provider_name == 'BPJS KESEHATAN') {
              status_sumber = "JKN/Onsite";
            }
            let isSelesai = row.visit_status == 4;
            let Batal = row.visit_status == 99;
            return {
              "actions": `
                <div class="text-center">
                  <div class="dropdown">
                  <button class="btn btn-sm btn-primary dropdown-toggle" 
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-boundary="window">
                    Aksi
                  </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow">

                      <li>
                        <a class="dropdown-item detail-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-eye me-2 text-info"></i> Lihat Hasil Pemeriksaan
                        </a>
                      </li>

                      
                      <li>
                        <a class="dropdown-item" href="module/admin/form_sep?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="fas fa-upload me-2 text-info"></i> Upload SEP
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item" href="module/admin/form_fkpp?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="fas fa-upload me-2 text-info"></i> Upload FKPP
                        </a>
                      </li>


                      <li>
                        <a class="dropdown-item screening-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-pencil me-2 text-primary"></i> Vital Sign
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item camera-btn" href="javascript:;" data-id="${row.id_visit}" data-face="${row.patient_face_image ?? ''}">
                          <i class="fas fa-camera me-2 text-success"></i> Ambil Foto
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item ttd-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-signature me-2 text-dark"></i> Tanda Tangan
                        </a>
                      </li>
                    ${!isSelesai ? `
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item btn-reschedule"
                            href="javascript:;"
                            data-id="${row.id_visit}"
                            data-doctor="${row.id_doctor}">
                            <i class="fas fa-user-md me-2 text-dark"></i>
                            Reschedule
                        </a>
                      </li>
                      <li>
                          <a class="dropdown-item edit-visit-btn" 
                              href="javascript:;" 
                              data-visit="${row.visit_ID}"
                              data-date="${row.visit_date}"
                              data-patient="${row.id_patient}"
                              data-time="${row.visit_time}">
                              
                              <i class="fas fa-edit me-2 text-warning"></i>
                              Perubahan Waktu
                          </a>
                      </li>

                      ${!Batal ? `
                          <li>
                              <a class="dropdown-item delete-btn text-danger" 
                                  href="javascript:;" 
                                  data-id="${row.id_visit}" 
                                  data-nokartu="${row.patient_bpjs}" 
                                  data-kdpoli="${row.id_provider}" 
                                  data-tanggal="${row.id_provider}" 
                                  data-prov="${row.id_provider}" 
                                  data-visit="${row.visit_ID}">
                                  
                                  <i class="fas  fa-times-circle me-2"></i>
                                  Batal
                              </a>
                          </li>
                      ` : ''}

                  ` : ''}
                    </ul>

                  </div>
                </div>
              `,
              "registrasi": row.patient_bpjs ?? "-",
              "antrian": row.visit_antrian ?? "-",
              "sumber": status_sumber ?? "-",
              "tanggal": row.visit_time ?? "-",
              "nama": row.patient_name_pcare ?? "-",
              "gender": row.patient_gender ?? "-",
              "dokter": row.id_doctor ?? "-",
              "layanan": row.id_poli ?? "-",
              "screening": row.tekanan_darah ?
                '<span class="badge bg-success">✔️ Sudah</span>' : '<span class="badge bg-danger">❌ Belum</span>',
              "provider": row.provider_name ?? "-",
              "status": row.visit_status === 99 ? '<span class="badge bg-danger text-center d-block">Batal</span>' : row.visit_status === 1 ? '<span class="badge bg-primary text-center d-block">Pemeriksaan</span>' : row.visit_status === 2 ? '<span class="badge bg-secondary text-center d-block">Dipanggil</span>' : row.visit_status === 3 ? '<span class="badge bg-primary text-center d-block">Dilayani</span>' : row.visit_status === 4 ? '<span class="badge bg-success text-center d-block">Selesai</span>' : '<span class="badge bg-dark text-center d-block">Belum Dilayani</span>'
            };
          });
        }
      },
      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        }, {
          data: "status"
        }, {
          data: "registrasi"
        },
        {
          data: "antrian",
          orderable: false
        },
        {
          data: "sumber"
        },
        {
          data: "tanggal"
        },
        {
          data: "nama"
        },
        {
          data: "gender"
        },
        {
          data: "dokter"
        },
        {
          data: "layanan"
        },
        {
          data: "screening"
        },
        {
          data: "provider"
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#periodeTable').on('draw.dt', function() {
      document.querySelectorAll('.dropdown-toggle').forEach(function(el) {
        new bootstrap.Dropdown(el);
      });
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_visit').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_visit').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      let visit = $(this).data('visit');
      let prov = $(this).data('prov');
      Swal.fire({
        title: 'Peringatan?',
        text: 'Masukkan alasan pembatalan',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Tulis alasan...',
        inputAttributes: {
          'aria-label': 'Alasan'
        },
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Kembali',
        preConfirm: (alasan) => {
          if (!alasan) {
            Swal.showValidationMessage('Alasan wajib diisi');
          }
          return alasan;
        }
      }).then((result) => {
        if (result.isConfirmed) {

          let alasan = result.value;
          Swal.fire({
            title: 'Menghapus...',
            text: 'Sedang memproses data',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });
          if (prov == 1) {
            $.ajax({
              url: 'controller/wsbpjs/batalAntrian.php',
              type: "POST",
              data: {
                novisit: visit,
                alasan: alasan,
              },
              dataType: 'json',
              success: function(res) {
                if (res.success) {
                  batalAntrian(visit, alasan, prov);
                } else {
                  Swal.fire({
                    title: "Gagal Hapus",
                    text: res.message,
                    icon: "error"
                  });
                }
              }
            });
          } else {
            batalAntrian(visit, alasan, prov);
          }
        }
      });
    });

    function batalAntrian(visit, alasan, prov) {
      $.ajax({
        url: 'controller/admisi/services/deletePendaftaran.php',
        type: "POST",
        data: {
          novisit: visit,
          alasan: alasan,
          provider: prov
        },
        dataType: 'json',
        success: function(res) {
          Swal.close();
          Swal.fire({
            title: "Berhasil",
            text: res.message,
            icon: "success"
          });
          table.ajax.reload(null, false);
        }
      })
    }
    // filter manual
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      $('#doctorSelect').val('');
      $('#providerSelect').val('');
      $('#poliSelect').val('');
      table.ajax.reload();
    });
    $(document).on('click', '.detail-btn', function() {
      let id_visit = $(this).data('id');
      $('#detailModal').modal('show');
      $('#detailLoading').show();
      $('#detailContent').hide();
      $.ajax({
        url: 'controller/visit/getDetailPemeriksaan',
        type: 'GET',
        data: {
          id: id_visit
        },
        dataType: 'json',
        success: function(res) {
          if (!res.status) {
            $('#detailLoading').html(
              '<div class="alert alert-danger">Data tidak ditemukan.</div>'
            );
            return;
          }
          let d = res.data;
          $('#d_patient_name').text(d.patient_name_pcare ?? '-');
          $('#d_doctor_name').text(d.id_doctor ?? '-');
          $('#d_poli_name').text(d.id_poli ?? '-');
          $('#d_visit_date').text(d.visit_date + ' ' + d.visit_time);
          $('#d_kondisi_masuk').text(
            d.kondisi_masuk && d.kondisi_masuk.trim() !== '' ?
            d.kondisi_masuk :
            'Lemah'
          );


          // ==========================================
          // HITUNG UMUR BERDASARKAN TANGGAL VISIT
          // ==========================================

          if (d.patient_datebirth && d.visit_date) {

            const tanggalLahir = new Date(d.patient_datebirth);
            const tanggalVisit = new Date(d.visit_date);

            let umur = tanggalVisit.getFullYear() - tanggalLahir.getFullYear();

            const bulan =
              tanggalVisit.getMonth() - tanggalLahir.getMonth();

            // Belum ulang tahun pada tahun kunjungan
            if (
              bulan < 0 ||
              (
                bulan === 0 &&
                tanggalVisit.getDate() < tanggalLahir.getDate()
              )
            ) {
              umur--;
            }

            $('#d_umur').text(umur + ' Tahun');

          } else {

            $('#d_umur').text('-');

          }

          $('#d_tekanan_darah').text(d.tekanan_darah ?? '-');
          $('#d_suhu').text(d.suhu ?? '-');
          $('#d_nadi').text(d.nadi ?? '-');
          $('#d_respirasi').text(d.respirasi ?? '-');
          $('#d_saturasi').text(d.saturasi ?? '-');
          $('#d_tinggi').text(d.tinggi_badan ?? '-');
          $('#d_berat').text(d.berat_badan ?? '-');
          $('#d_bmi').text(d.bmi ?? '-');
          $('#d_bmi_keterangan').text(d.bmi_keterangan ?? '-');
          $('#d_anamnesa').text(d.anamnesa ?? '-');
          $('#d_catatan_screening').text(d.catatan_screening ?? '-');

          // ==========================================
          // DIAGNOSA
          // ==========================================

          let diagnosa = '-';

          // --------------------------------------------------
          // PRIORITAS 1
          // Kalau kdDiag1 / nmDiag1 sudah tersedia,
          // langsung gunakan data tersebut.
          // --------------------------------------------------

          if (d.kdDiag1 || d.nmDiag1) {

            let kode = (d.kdDiag1 ?? '').toString().trim();
            let nama = (d.nmDiag1 ?? '').toString().trim();

            if (kode && nama) {

              diagnosa = kode + ' - ' + nama;

            } else if (kode) {

              diagnosa = kode;

            } else if (nama) {

              diagnosa = nama;
            }
          }


          // --------------------------------------------------
          // PRIORITAS 2
          // Kalau kdDiag1 dan nmDiag1 kosong,
          // gunakan pv.diagnosa + hasil LEFT JOIN ICD-10.
          // --------------------------------------------------
          else if (d.diagnosa) {

            let kode = d.diagnosa.toString().trim();

            let nama =
              (d.diagnosa_keterangan_ind ?? '').toString().trim();

            // Kalau keterangan Indonesia tersedia
            if (nama && nama !== 'ICD10_2010') {

              diagnosa = kode + ' - ' + nama;

            }

            // Kalau tidak ada keterangan Indonesia,
            // gunakan keterangan ICD-10 umum
            else if (d.diagnosa_keterangan) {

              diagnosa =
                kode + ' - ' +
                d.diagnosa_keterangan.toString().trim();

            }

            // Kalau hanya kode yang tersedia
            else {

              diagnosa = kode;
            }
          }


          // --------------------------------------------------
          // TAMPILKAN
          // --------------------------------------------------

          $('#d_diagnosa').text(diagnosa);


          // ==========================================
          // TINDAKAN
          // ==========================================

          $('#d_tindakan').text(
            d.tindakan_text &&
            d.tindakan_text !== '' ?
            d.tindakan_text :
            '-'
          );


          // ==========================================
          // OBAT / TERAPI
          // ==========================================

          $('#d_obat').text(
            d.obat_text &&
            d.obat_text !== '' ?
            d.obat_text :
            '-'
          );


          $('#detailLoading').hide();
          $('#detailContent').fadeIn(200);
        },
        error: function() {
          $('#detailLoading').html(`
                <div class="alert alert-danger text-center">
                    Gagal mengambil data pemeriksaan.
                </div>
            `);
        }
      });
    });
  });
</script>
<script>
  $(document).on('click', '.screening-btn', function() {
    let id = $(this).data('id');
    $('#screening_id_visit').val(id);
    fetch(`controller/visit/getDetailPemeriksaan?id=${id}`)
      .then(res => res.json())
      .then(resp => {
        if (resp.status === true) {
          let d = resp.data;
          $('#sc_keluhan').val(d.anamnesa ?? '');
          $('#sc_catatan').val(d.catatan_screening ?? '');
          $('#kondisi_masuk').val(d.kondisi_masuk ?? '');
          $('#suhu').val(d.suhu ?? '');
          $('#nadi').val(d.nadi ?? '');
          $('#respirasi').val(d.respirasi ?? '');
          $('#saturasi').val(d.saturasi ?? '');
          $('#tinggi').val(d.tinggi_badan ?? '');
          $('#berat').val(d.berat_badan ?? '');
          $('#bmi').val(d.bmi ?? '');
          $('#bmi_ket').val(d.bmi_keterangan ?? '');
          $('#lingkar_perut').val(d.lingkar_perut ?? '');
          if (d.tekanan_darah && d.tekanan_darah.includes('/')) {
            const [s, di] = d.tekanan_darah.split('/');
            $('#sistole').val(s);
            $('#diastole').val(di);
          } else {
            $('#sistole').val(d.sistole ?? '');
            $('#diastole').val(d.diastole ?? '');
          }
        } else {
          console.log("Data kosong → mode input baru");
          $('#sc_keluhan, #sc_catatan, #sistole, #diastole, #suhu, #nadi, #respirasi, #saturasi, #tinggi, #berat, #bmi, #bmi_ket, #lingkar_perut').val('');
        }
        $('#screeningModal').modal('show');
      })
      .catch(err => {
        console.error("Terjadi kesalahan fetch:", err);
      });
  });


  // Variabel global untuk menahan agar modal tidak bisa diclose
  let isSubmittingScreening = false;

  // Mencegah modal ditutup (lewat klik di luar modal atau tombol ESC) saat sedang submit
  $('#screeningModal').on('hide.bs.modal', function(e) {
    if (isSubmittingScreening) {
      e.preventDefault(); // Batalkan penutupan modal
    }
  });

  $('#btnSaveScreening').on('click', function() {
    const sistole = $('#sistole').val();
    const diastole = $('#diastole').val();
    const lingkarPerut = $('#lingkar_perut').val();

    // Validasi dengan SweetAlert
    if (!sistole || !diastole) {
      Swal.fire({
        icon: 'warning',
        title: 'Perhatian!',
        text: 'Tekanan darah (Sistole dan Diastole) harus diisi!',
        confirmButtonText: 'Mengerti'
      });
      return;
    }

    // Persiapan Data
    const tekananDarah = `${sistole}/${diastole}`;
    const data = {
      id_visit: $('#screening_id_visit').val(),
      keluhan: $('#sc_keluhan').val(),
      catatan: $('#sc_catatan').val(),
      kondisi_masuk: $('#kondisi_masuk').val(),
      tekanan_darah: tekananDarah,
      sistole: sistole,
      diastole: diastole,
      suhu: $('#suhu').val(),
      nadi: $('#nadi').val(),
      respirasi: $('#respirasi').val(),
      saturasi: $('#saturasi').val(),
      tinggi: $('#tinggi').val(),
      berat: $('#berat').val(),
      bmi: $('#bmi').val(),
      bmi_ket: $('#bmi_ket').val(),
      lingkar_perut: lingkarPerut
    };

    const $btn = $(this);
    const originalBtnText = $btn.html();

    // --- MULAI PROSES LOADING ---
    isSubmittingScreening = true;

    // Ubah tombol jadi loading dan disable tombol Simpan
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

    // Disable juga tombol Batal (X) dan tombol "Batal" agar tidak bisa diklik
    $('#screeningModal .btn-close, #screeningModal .btn-light').prop('disabled', true);

    // Proses AJAX
    $.ajax({
      url: 'controller/visit/saveScreening.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      dataType: 'json',
      success: function(resp) {
        if (resp.status === 'success') {
          // Notifikasi Sukses
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Screening berhasil disimpan.',
            showConfirmButton: false,
            timer: 1500 // Otomatis tertutup setelah 1.5 detik
          }).then(() => {
            // Buka kembali izin close modal lalu tutup modal
            isSubmittingScreening = false;
            $('#screeningModal').modal('hide');

            // Reload datatable
            $('#periodeTable').DataTable().ajax.reload(null, false);
          });
        } else {
          // Notifikasi Gagal dari Backend
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: resp.message || 'Terjadi kesalahan saat menyimpan data.'
          });
        }
      },
      error: function(xhr, status, error) {
        // Notifikasi Error Jaringan/Server
        Swal.fire({
          icon: 'error',
          title: 'Kesalahan Sistem!',
          text: 'Terjadi kesalahan koneksi atau server. Silakan coba lagi.'
        });
        console.error(error);
      },
      complete: function() {
        // --- SELESAI PROSES ---
        // Kembalikan UI ke kondisi semula (hanya jika bukan sukses otomatis)
        // Jika sukses, state tombol akan keriset otomatis saat modal dibuka lagi nanti
        isSubmittingScreening = false;
        $btn.prop('disabled', false).html(originalBtnText);
        $('#screeningModal .btn-close, #screeningModal .btn-light').prop('disabled', false);
      }
    });
  });
  $('.screening-btn').each(function() {
    if ($(this).data('filled')) {
      $(this).text('Edit Screening');
    } else {
      $(this).text('Input Screening');
    }
  });
</script>
<script>
  $(document).on('click', '.poli-btn', function() {
    $('#poliModal').modal('show');

    const now = new Date();
    $('#poli_date').val(now.toISOString().split('T')[0]);
    $('#poli_time').val(now.toTimeString().slice(0, 5));
    loadPoli('#poli_poli');
    loadProvider();
  });

  function loadPoli(poliid) {
    poliSakit = true;
    var select = $(poliid);
    select.empty();
    select.prop('disabled', true);
    select.html('<option value="">Mencari data...</option>');
    select.val('').trigger('change');

    $.ajax({
      url: 'module/admin/get_master_poli.php',
      type: 'POST',
      dataType: 'json',
      success: function(response) {
        if (!response.success) {
          console.log('Gagal load poli');
          return;
        }
        select.empty();
        $.each(response.data, function(index, item) {
          if (item.poliSakit == poliSakit) {
            var option = new Option(item.nmPoli, item.nmPoli, false, false);
            $(option).attr('data-kd', item.kdPoli);
            select.append(option);
          }
        });
        select.prop('disabled', false);
        select.trigger('change');
      },
      error: function(xhr, status, error) {
        console.log(xhr.responseText);
      }
    });
  }
  $('#poli_poli').on('change', function() {
    var kdPoli = $(this).find(':selected').data('kd') || '';
    $('#kdPoli').val(kdPoli);
    loadDokter();
  });

  function loadDokter() {
    const $select = $('#poli_doctor');
    const tgl = $('#poli_date').val();
    const poli = $('#kdPoli').val();
    $select.html('<option value="">Loading...</option>').trigger('change.select2');
    $.ajax({
      url: 'controller/admisi/services/getDokterlocal.php',
      type: 'GET',
      data: {
        kdpoli: poli,
        tanggal: tgl
      },
      dataType: 'json',
      success: function(response) {
        $select.empty();
        if (!response.data || response.data.length === 0) {
          $select.append('<option value="">Tidak ada dokter</option>');
          $select.trigger('change.select2');
          return;
        }

        if (status) {
          $select.append('<option value="">- Pilih -</option>');
        }
        response.data.forEach(function(item) {
          let textExp = item.exp ? '(Sudah Tutup)' : '';
          $select.append(
            '<option value="' + item.kodedokter + '" ' +
            'data-nama="' + item.namadokter + '" ' +
            'data-jam="' + item.jampraktek + '" ' +
            '>' +
            item.namadokter + ' (' + item.jampraktek + ')' + textExp +
            '</option>'
          );
        });
        $select.trigger('change.select2');
      },
      error: function(err) {
        console.error(err);
        $select.html('<option value="">Error loading data</option>').trigger('change.select2');
      }
    });
  }

  function loadProvider() {
    fetch('controller/admisi/services/get_provider')
      .then(res => res.json())
      .then(res => {
        let html = '<option value="">Pilih Provider</option>';
        res.forEach(p => {
          html += `<option value="${p.id}">${p.text}</option>`;
        });
        $('#poli_provider').html(html);
      });
  }
  $('#btnSavePoli').on('click', function() {
    const $btn = $(this);
    const selected = $('#id_patient_select').select2('data')[0];
    const data = {
      id_patient: $('#id_patient_select').val(),
      patient_name_pcare: selected?.patient_name || '',
      id_doctor: $('#poli_doctor').val(),
      doctor_name: $('#poli_doctor option:selected').text(),
      id_poli: $('#poli_poli').val(),
      poli_name: $('#poli_poli option:selected').text(),
      id_provider: $('#poli_provider').val(),
      visit_date: $('#poli_date').val(),
      visit_time: $('#poli_time').val(),
      kdPoli: $('#kdPoli').val()
    };
    if (!data.id_patient || !data.id_doctor || !data.visit_date || !data.visit_time) {
      Swal.fire({
        icon: 'warning',
        title: 'Data Belum Lengkap',
        text: 'Silakan lengkapi semua data yang wajib diisi.'
      });
      return;
    }
    $.ajax({
      url: 'controller/visit/visitController',
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function() {
        $btn.prop('disabled', true)
          .html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
      },
      success: function(resp) {
        if (resp.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Registrasi berhasil.',
            timer: 1800,
            showConfirmButton: false
          });
          $('#poliModal').modal('hide');
          window.location.reload();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: resp.message
          });
        }
      },
      error: function(xhr) {
        let pesan = 'Terjadi kesalahan pada server.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          pesan = xhr.responseJSON.message;
        }
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: pesan
        });
        console.error(xhr.responseText);
      },
      complete: function() {
        $btn.prop('disabled', false)
          .html('Simpan');
      }
    });
  });

  $('#poliModal').on('shown.bs.modal', function() {

    const $select = $('#id_patient_select');

    // destroy kalau sudah ada
    if ($select.hasClass("select2-hidden-accessible")) {
      $select.select2('destroy');
    }
    console.log("INIT SELECT2");
    $select.select2({
      dropdownParent: $('#poliModal'),
      width: '100%',
      placeholder: 'Cari pasien...',
      minimumInputLength: 2,
      ajax: {
        url: 'controller/admisi/patientSearchController',
        type: 'GET',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            search: params.term
          };
        },

        processResults: function(data) {
          let items = data.data ? data.data : data;

          return {
            results: items.map(item => ({
              id: item.id_patient,
              text: `${item.patient_name} (${item.nomor_rm})`, // tampil di UI
              patient_name: item.patient_name // 🔥 simpan asli
            }))
          };
        },
        cache: true
      }
    });
  });
</script>
<script>
  let canvas = document.getElementById('signaturePad');
  let ctx = canvas.getContext('2d');

  let drawing = false;

  // resize canvas biar presisi
  function resizeCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = 400;
  }
  resizeCanvas();

  // start drawing
  canvas.addEventListener('mousedown', () => drawing = true);
  canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('mousemove', draw);

  // support touch (HP)
  canvas.addEventListener('touchstart', (e) => {
    drawing = true;
  });
  canvas.addEventListener('touchend', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('touchmove', drawTouch);

  function draw(e) {
    if (!drawing) return;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
  }

  function drawTouch(e) {
    e.preventDefault();
    if (!drawing) return;

    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];

    const x = touch.clientX - rect.left;
    const y = touch.clientY - rect.top;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
  }

  // clear
  document.getElementById('clearSignature').onclick = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  };

  // open modal
  $(document).on('click', '.ttd-btn', function() {
    let id = $(this).data('id');

    $('#ttd_id_visit').val(id);
    $('#ttdModal').modal('show');

    setTimeout(resizeCanvas, 400);
  });

  // save
  document.getElementById('saveSignature').onclick = function() {
    const image = canvas.toDataURL('image/png');

    fetch('controller/admisi/saveSignature.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_visit: $('#ttd_id_visit').val(),
          image: image
        })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          alert('Tanda tangan berhasil disimpan');
          $('#ttdModal').modal('hide');
        }
      });
  };
</script>
<script src="assets/js/face-api.min.js"></script>
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

  function loadFaceModel() {
    if (!faceModelReady) {
      faceModelReady = Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('models')
      ]);
    }
    return faceModelReady;
  }

  // Ambil descriptor wajah master pasien (foto rekam sebelumnya)
  async function loadMasterDescriptor(path) {
    masterDescriptor = null;
    masterState = "none";
    faceMatched = false;
    if (!path) return;
    masterState = "error";
    try {
      const img = await faceapi.fetchImage(path.replace(/^(\.\.\/)+/, ''));
      const det = await faceapi
        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({inputSize: 416, scoreThreshold: 0.4}))
        .withFaceLandmarks()
        .withFaceDescriptor();
      masterDescriptor = det ? det.descriptor : null;
      masterState = det ? "ok" : "error";
    } catch (e) {
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
    faceMismatch = false;
    setFaceState(false, "Memuat data wajah pasien...");
    await loadMasterDescriptor(currentMasterFace);
    const opts = new faceapi.TinyFaceDetectorOptions({inputSize: 320, scoreThreshold: 0.5});
    let busy = false;
    faceTimer = setInterval(async () => {
      if (busy || !video.videoWidth || video.paused) return;
      busy = true;
      try {
        let q = faceapi.detectSingleFace(video, opts);
        if (masterDescriptor) q = q.withFaceLandmarks().withFaceDescriptor();
        const det = await q;
        if (!faceTimer) return;
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
    currentMasterFace = $(this).attr("data-face") || "";
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

    const captureBtn = document.getElementById("captureBtn");

    captureBtn.disabled = true;
    captureBtn.dataset.saving = "1";

    captureBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1"></span>
        Menyimpan...
    `;

    fetch("controller/admisi/recordFaceVisit.php", {

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

  $(document).on('click', '.edit-visit-btn', function() {
    $('#edit_visit_id').val($(this).data('visit'));
    $('#edit_id_patient').val($(this).data('patient'));
    $('#edit_visit_date').val($(this).data('date'));
    $('#edit_visit_time').val($(this).data('time'));

    $('#editVisitModal').modal('show');

  });

  $('#btnUpdateVisit').on('click', function() {

    const data = {
      visit_ID: $('#edit_visit_id').val(),
      id_patient: $('#edit_id_patient').val(),
      visit_date: $('#edit_visit_date').val(),
      visit_time: $('#edit_visit_time').val()
    };

    fetch('controller/visit/updateVisitTime.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(resp => {

        if (resp.status === 'success') {
          alert('✅ Berhasil update');
          $('#editVisitModal').modal('hide');
          $('#periodeTable').DataTable().ajax.reload(null, false);
        } else {
          alert('❌ ' + resp.message);
        }

      });

  });
</script>
<script>
  $(document).on("click", ".btn-reschedule", function() {
    const idVisit = $(this).data("id");
    const doctorNow = $(this).data("doctor");
    $("#visit_id").val(idVisit);
    $("#doctor_now").val(doctorNow);
    $("#doctor_new").val("").trigger("change");
    $("#modalRescheduleDoctor").modal("show");
    $.ajax({
      url: "module/admin/get_dokter_bpjs.php",
      type: "POST",
      dataType: "json",
      success: function(res) {
        let html = '<option value="">- Pilih Dokter -</option>';
        $.each(res.data, function(i, row) {
          html += `<option value="${row.kdDokter}">${row.nmDokter}</option>`;
        });
        $('#doctor_new').html(html);
        if (selected !== "") {
          $('#doctor_new').val(selected).trigger("change");
        }
      },
      error: function() {
        $('#doctor_new').html('<option value="">Gagal memuat data</option>');
      }
    });
  });
  $("#formRescheduleDoctor").submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: "module/admin/serviceRescheduleDoctor.php",
      type: "POST",
      data: $(this).serialize(),
      dataType: "json",
      success: function(res) {
        if (res.success) {
          $("#modalRescheduleDoctor").modal("hide");
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: res.message,
            confirmButtonText: "OK"
          }).then(() => {
            table.ajax.reload(null, false);
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Gagal",
            text: res.message
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Terjadi kesalahan pada server."
        });
      }
    });
  });
</script>

</html>