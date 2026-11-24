<?php
$title = 'Pemeriksaan';
/*
 * RME satu halaman (View RME Baru). Perbaikan: wajib ?rm sama persis dengan nomor RM (format berbeda -> halaman
 * ditolak 404); flatpickr dimuat dua kali (sudah ada di head.php); akordeon hanya bisa membuka 1 bagian (isi vital,
 * anamnesa, diagnosa harus buka-tutup bergantian); Saturasi tanpa batas (0% lolos); bagian Vaksin/Tindakan/Riwayat/
 * Rawat Inap hanya teks tanpa fungsi; kotak keterangan dobel di atas komponen; tombol simpan jauh di bawah.
 */
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = $_GET['no'] ?? '';
$rm = $_GET['rm'] ?? '';
if (!is_string($no) || !is_string($rm) || $no === '') {
  http_response_code(400);
  exit('Nomor kunjungan wajib diisi.');
}
$cycleVisitStmt = $koneksi->prepare("SELECT v.id_patient, p.nomor_rm FROM pasien_visit v
  JOIN ms_patient p ON p.id_patient = v.id_patient
  WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
$cycleVisitStmt->bind_param('si', $no, $_SESSION['id_customer']);
$cycleVisitStmt->execute();
$cycleVisit = $cycleVisitStmt->get_result()->fetch_assoc();
if (!$cycleVisit) {
  http_response_code(404);
  exit('Kunjungan tidak ditemukan pada klinik ini.');
}
$id_patient = $cycleVisit['id_patient'];
$rm = (string) ($cycleVisit['nomor_rm'] ?? $rm);   // nomor RM dari database
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$_SESSION['rme_cycle_token'] ??= bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .rmx-bar { position: sticky; top: 92px; z-index: 7; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 10px; margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; box-shadow: 0 2px 8px rgba(15, 23, 42, .06); }
    .rmx-bar a.chip { border: 1px solid #e2e8f0; border-radius: 999px; padding: 3px 10px; font-size: .76rem; font-weight: 600; color: #1f2937; text-decoration: none; white-space: nowrap; }
    .rmx-bar a.chip:hover { border-color: #94a3b8; background: #f8fafc; }
    .rmx-bar .ctl { margin-left: auto; display: flex; gap: 6px; }
    .rmx-bar .ctl .btn { padding: 3px 10px; font-size: .76rem; font-weight: 600; }
    .pemeriksaan-accordion .accordion-item { scroll-margin-top: 150px; }
    .rmx-link { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 14px 16px; background: #f8fafc; }
    .rmx-link .ic { width: 40px; height: 40px; border-radius: 10px; background: #e0e7ff; color: #3730a3; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    .rmx-embed { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; min-height: 120px; position: relative; }
    .rmx-embed iframe { display: block; width: 100%; border: 0; height: 200px; }
    .rmx-embed-muat { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #1f2937; font-size: .86rem; background: #fff; }
    .rmx-link p { margin: 0; color: #1f2937; font-size: .86rem; }
    /* teks abu-abu di dalam RME (keterangan, placeholder komponen) dibuat gelap agar terbaca */
    #accordionPemeriksaan .text-muted, #accordionPemeriksaan .form-text, #accordionPemeriksaan .text-secondary, #accordionPemeriksaan small:not(.badge) { color: #1f2937 !important; }
    #accordionPemeriksaan .farmasi-placeholder, #accordionPemeriksaan .farmasi-placeholder p { color: #1f2937 !important; }
    #accordionPemeriksaan ::placeholder { color: #6b7280; opacity: 1; }
    .rmx-save { position: sticky; bottom: 0; z-index: 7; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 10px 12px; margin-top: 14px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; box-shadow: 0 -4px 12px rgba(15, 23, 42, .06); }
    .rmx-save #printActions { margin: 0 !important; }
    .rmx-save #simpan_pemeriksaan { margin-left: auto; min-width: 240px; font-weight: 600; }
  </style>

</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
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
          <div class="row">
            <?php
            require 'menu_rme.php';
            ?>
            <form id="formPemeriksaan" method="POST"></form>
            <div id="rmeCycleFields" class="w-100">
              <input type="hidden" name="rme_cycle_token" value="<?= htmlspecialchars($_SESSION['rme_cycle_token'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="nomor_rm" value="<?= htmlspecialchars($rm, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="nomor_visit" value="<?= htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="id_patient" id="id_patient" value="<?= htmlspecialchars((string) $id_patient, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" id="terapiObat" name="terapiObat">
              <input type="hidden" id="terapiNonObat" name="terapiNonObat">
              <input type="hidden" id="bmhp" name="bmhp">
              <input type="hidden" id="tglDaftar" name="tglDaftar">
              <input type="hidden" name="typeRujukan" id="typeRujukan" value="normal">
              <input type="hidden" name="noKartu" id="noKartu">
              <input type="hidden" name="kdPoli" id="kode_poli">
              <input type="hidden" name="nmPoli" id="nama_poli">
              <input type="hidden" name="kdDokter" id="kdDokter">
              <input type="hidden" name="nmDokter" id="nmDokter">
              <input type="hidden" name="noKunjungan" id="noKunjungan">
              <input type="hidden" name="kdTkp" value="10">
              <input type="hidden" name="status_pasien" id="status_pasien">
            <!-- =========================================================
     ACCORDION PEMERIKSAAN
========================================================= -->

            <nav class="rmx-bar" aria-label="Bagian RME">
              <a class="chip" href="#collapsePasien" data-buka>Pasien</a>
              <a class="chip" href="#collapseVital" data-buka>Vital sign</a>
              <a class="chip" href="#collapseDokter" data-buka>Anamnesa</a>
              <a class="chip" href="#collapseDiagnosa" data-buka>Diagnosa</a>
              <a class="chip" href="#collapseOdontogram" data-buka>Odontogram</a>
              <a class="chip" href="#collapseFarmasi" data-buka>Farmasi</a>
              <a class="chip" href="#collapseResepLuar" data-buka>Resep luar</a>
              <a class="chip" href="#collapseLab" data-buka>Lab</a>
              <a class="chip" href="#collapseVaksin" data-buka>Vaksin</a>
              <a class="chip" href="#collapseTindakan" data-buka>Tindakan</a>
              <a class="chip" href="#collapseRiwayatPengobatan" data-buka>Riwayat</a>
              <a class="chip" href="#collapseRawatInap" data-buka>Rawat inap</a>
              <a class="chip" href="#collapsePulang" data-buka>Status pulang</a>
              <span class="ctl">
                <button type="button" class="btn btn-outline-dark" id="rmxBukaSemua">Buka semua</button>
                <button type="button" class="btn btn-outline-dark" id="rmxTutupSemua">Tutup semua</button>
              </span>
            </nav>
            <div class="accordion pemeriksaan-accordion" id="accordionPemeriksaan">

              <!-- =====================================================
         1. INFORMASI PASIEN
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingPasien">

                  <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapsePasien"
                    aria-expanded="true"
                    aria-controls="collapsePasien">

                    <span class="accordion-icon">
                      <i class="bi bi-person-vcard-fill"></i>
                    </span>

                    <span>
                      <strong>Informasi Pasien</strong>
                      <small>Identitas pasien dan informasi kunjungan</small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapsePasien"
                  class="accordion-collapse collapse show"
                  aria-labelledby="headingPasien"
                  >

                  <div class="accordion-body">

                    <?php
                    require 'card-pasien.php';
                    ?>

                  </div>

                </div>

              </div>



              <!-- =====================================================
         2. CATATAN SCREENING
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingScreening">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseScreening"
                    aria-expanded="false"
                    aria-controls="collapseScreening">

                    <span class="accordion-icon screening">
                      <i class="bi bi-clipboard2-pulse-fill"></i>
                    </span>

                    <span>
                      <strong>Catatan Screening</strong>
                      <small>Informasi awal hasil screening pasien</small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseScreening"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingScreening"
                  >

                  <div class="accordion-body">

                    <div class="section-description">
                      <i class="bi bi-info-circle"></i>

                      Catatan yang diperoleh dari proses screening sebelum pemeriksaan medis.
                    </div>


                    <div class="mb-3">

                      <label
                        for="visit_notes"
                        class="form-label fw-semibold">

                        Catatan Screening

                      </label>

                      <input
                        type="text"
                        id="visit_notes"
                        name="visit_notes"
                        class="form-control bg-light"
                        readonly>

                    </div>

                  </div>

                </div>

              </div>



              <!-- =====================================================
         3. VITAL SIGN PERAWAT
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingVital">

                  <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseVital"
                    aria-expanded="true"
                    aria-controls="collapseVital">

                    <span class="accordion-icon perawat">
                      <i class="bi bi-heart-pulse-fill"></i>
                    </span>

                    <span>

                      <strong>
                        Vital Sign
                      </strong>

                      <small>
                        Pemeriksaan awal oleh perawat
                      </small>

                    </span>

                  </button>

                </h2>


                <div
                  id="collapseVital"
                  class="accordion-collapse collapse show"
                  aria-labelledby="headingVital"
                  >

                  <div class="accordion-body">

                    <div class="section-description">

                      <i class="bi bi-person-badge-fill"></i>

                      Pemeriksaan tanda-tanda vital dan antropometri pasien.

                    </div>


                    <div class="row g-3">


                      <!-- TINGGI -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="tinggiBadan"
                          class="form-label">

                          Tinggi Badan
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="tinggiBadan"
                            id="tinggiBadan"
                            min="30"
                            max="250"
                            step="0.1"
                            required>

                          <span class="input-group-text">
                            CM
                          </span>

                        </div>

                      </div>


                      <!-- BERAT -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="beratBadan"
                          class="form-label">

                          Berat Badan
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="beratBadan"
                            id="beratBadan"
                            min="1"
                            max="300"
                            step="0.1"
                            required>

                          <span class="input-group-text">
                            Kg
                          </span>

                        </div>

                      </div>


                      <!-- LINGKAR PERUT -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="lingkarPerut"
                          class="form-label">

                          Lingkar Perut

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="lingkarPerut"
                            id="lingkarPerut"
                            min="30"
                            max="200"
                            step="0.1">

                          <span class="input-group-text">
                            CM
                          </span>

                        </div>

                      </div>


                      <!-- SISTOLE -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="sistole"
                          class="form-label">

                          Sistole
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="sistole"
                            id="sistole"
                            min="50"
                            max="300"
                            step="1"
                            required>

                          <span class="input-group-text">
                            mmHg
                          </span>

                        </div>

                      </div>


                      <!-- DIASTOLE -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="diastole"
                          class="form-label">

                          Diastole
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="diastole"
                            id="diastole"
                            min="30"
                            max="200"
                            step="1"
                            required>

                          <span class="input-group-text">
                            mmHg
                          </span>

                        </div>

                      </div>


                      <!-- RESPIRATORY -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="respRate"
                          class="form-label">

                          Respiratory Rate
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="respRate"
                            id="respRate"
                            min="5"
                            max="60"
                            step="1"
                            required>

                          <span class="input-group-text">
                            /Minute
                          </span>

                        </div>

                      </div>


                      <!-- HEART RATE -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="heartRate"
                          class="form-label">

                          Heart Rate
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="heartRate"
                            id="heartRate"
                            min="30"
                            max="220"
                            step="1"
                            required>

                          <span class="input-group-text">
                            BPM
                          </span>

                        </div>

                      </div>


                      <!-- SUHU -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="suhu"
                          class="form-label">

                          Suhu
                          <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            class="form-control"
                            name="suhu"
                            id="suhu"
                            min="25"
                            max="45"
                            step="0.1">

                          <span class="input-group-text">
                            ℃
                          </span>

                        </div>

                      </div>


                      <!-- SPO2 -->

                      <div class="col-xl-2 col-md-4">

                        <label
                          for="saturasi"
                          class="form-label">

                          Saturasi

                        </label>

                        <div class="input-group">

                          <input
                            type="number"
                            id="saturasi"
                            min="50"
                            max="100"
                            step="1"
                            name="saturasi"
                            class="form-control">

                          <span class="input-group-text">
                            SpO2 %
                          </span>

                        </div>

                      </div>


                      <!-- BMI -->

                      <div class="col-xl-3 col-md-6">

                        <label
                          for="bmi"
                          class="form-label">

                          BMI

                        </label>

                        <div class="input-group">

                          <input
                            type="text"
                            id="bmi"
                            name="bmi"
                            readonly
                            class="form-control bg-light">

                          <span class="input-group-text">
                            kg/m²
                          </span>

                        </div>

                      </div>


                      <!-- BMI KETERANGAN -->

                      <div class="col-xl-3 col-md-6">

                        <label
                          for="bmiKet"
                          class="form-label">

                          Keterangan BMI

                        </label>

                        <div class="input-group">

                          <input
                            type="text"
                            id="bmiKet"
                            name="bmi_keterangan"
                            readonly
                            class="form-control bg-light">

                          <span class="input-group-text">
                            Ket
                          </span>

                        </div>

                      </div>


                      <!-- PROGNOSA -->

                      <div class="col-md-6 statuspasien">

                        <label
                          for="kondisi_masuk"
                          class="form-label">

                          Prognosa
                          <span class="text-danger">*</span>

                        </label>

                        <select
                          name="kondisi_masuk"
                          id="kondisi_masuk"
                          class="form-select"
                          required>

                        </select>

                      </div>


                      <!-- KESADARAN -->

                      <div class="col-md-6 statuspasien">

                        <label
                          for="kdSadar"
                          class="form-label">

                          Kesadaran
                          <span class="text-danger">*</span>

                        </label>

                        <select
                          class="form-select"
                          name="kdSadar"
                          id="kdSadar"
                          required>

                        </select>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- =====================================================
         4. PEMERIKSAAN DOKTER
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingDokter">

                  <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseDokter"
                    aria-expanded="true"
                    aria-controls="collapseDokter">

                    <span class="accordion-icon dokter">
                      <i class="bi bi-stethoscope"></i>
                    </span>

                    <span>

                      <strong>
                        Pemeriksaan Dokter
                      </strong>

                      <small>
                        Anamnesis, keluhan dan alergi pasien
                      </small>

                    </span>

                  </button>

                </h2>


                <div
                  id="collapseDokter"
                  class="accordion-collapse collapse show"
                  aria-labelledby="headingDokter"
                  >

                  <div class="accordion-body">


                    <!-- KELUHAN -->

                    <div class="mb-3">

                      <label
                        for="keluhan_penyerta"
                        class="form-label required">

                        Keluhan Utama

                      </label>

                      <textarea
                        id="keluhan_penyerta"
                        name="keluhan_penyerta"
                        rows="3"
                        class="form-control"
                        required></textarea>

                    </div>


                    <!-- ANAMNESA -->

                    <div class="mb-4">

                      <label
                        for="keluhan_utama"
                        class="form-label required">

                        Anamnesa

                      </label>

                      <textarea
                        class="form-control"
                        id="keluhan_utama"
                        name="keluhan_utama"
                        rows="4"
                        required></textarea>

                    </div>


                    <!-- ALERGI -->

                    <div class="medical-subtitle">

                      <i class="bi bi-exclamation-triangle-fill"></i>

                      Riwayat Alergi

                    </div>


                    <div
                      class="row g-3"
                      id="formalergi">


                      <!-- ALERGI MAKAN -->

                      <div class="col-md-4">

                        <label
                          class="form-label">

                          Alergi Makan

                        </label>

                        <select
                          class="form-select mb-2"
                          name="alergiMakan"
                          id="alergiMakan">

                        </select>

                        <textarea
                          class="form-control"
                          name="ketAlergiMakan"
                          id="ketAlergiMakan"
                          rows="2"
                          placeholder="Keterangan alergi..."
                          disabled></textarea>

                      </div>


                      <!-- ALERGI UDARA -->

                      <div class="col-md-4">

                        <label
                          class="form-label">

                          Alergi Udara

                        </label>

                        <select
                          class="form-select mb-2"
                          name="alergiUdara"
                          id="alergiUdara">

                        </select>

                        <textarea
                          class="form-control"
                          name="ketAlergiUdara"
                          id="ketAlergiUdara"
                          rows="2"
                          placeholder="Keterangan alergi..."
                          disabled></textarea>

                      </div>


                      <!-- ALERGI OBAT -->

                      <div class="col-md-4">

                        <label
                          class="form-label">

                          Alergi Obat

                        </label>

                        <select
                          class="form-select mb-2"
                          name="alergiObat"
                          id="alergiObat">

                        </select>

                        <textarea
                          class="form-control"
                          name="ketAlergiObat"
                          id="ketAlergiObat"
                          rows="2"
                          placeholder="Keterangan alergi..."
                          disabled></textarea>

                      </div>

                    </div>

                  </div>

                </div>

              </div>



              <!-- =====================================================
         5. DIAGNOSA & RENCANA
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingDiagnosa">

                  <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseDiagnosa"
                    aria-expanded="true"
                    aria-controls="collapseDiagnosa">

                    <span class="accordion-icon diagnosa">
                      <i class="bi bi-clipboard2-check-fill"></i>
                    </span>

                    <span>

                      <strong>
                        Diagnosa & Rencana
                      </strong>

                      <small>
                        Diagnosa medis, terapi dan rencana pelayanan
                      </small>

                    </span>

                  </button>

                </h2>


                <div
                  id="collapseDiagnosa"
                  class="accordion-collapse collapse show"
                  aria-labelledby="headingDiagnosa"
                  >

                  <div class="accordion-body">


                    <!-- DIAGNOSA -->

                    <div class="medical-subtitle">

                      <i class="bi bi-file-medical-fill"></i>

                      Diagnosa

                    </div>


                    <div class="row g-3 mb-4">


                      <!-- UTAMA -->

                      <div class="col-12">

                        <label
                          class="form-label fw-semibold">

                          Diagnosa Utama
                          <span class="text-danger">*</span>

                        </label>

                        <select
                          id="diag1"
                          name="diag1"
                          class="form-select"
                          required></select>

                        <input
                          type="hidden"
                          id="nmDiag1"
                          name="nmDiag1">

                        <input
                          type="hidden"
                          id="kdnonSpesialis1">

                      </div>


                      <!-- SEKUNDER 1 -->

                      <div class="col-md-6">

                        <label
                          class="form-label">

                          Diagnosa Sekunder 1

                        </label>

                        <select
                          id="diag2"
                          name="diag2"
                          class="form-select"></select>

                        <input
                          type="hidden"
                          id="nmDiag2"
                          name="nmDiag2">

                        <input
                          type="hidden"
                          id="kdnonSpesialis2">

                      </div>


                      <!-- SEKUNDER 2 -->

                      <div class="col-md-6">

                        <label
                          class="form-label">

                          Diagnosa Sekunder 2

                        </label>

                        <select
                          id="diag3"
                          name="diag3"
                          class="form-select"></select>

                        <input
                          type="hidden"
                          id="nmDiag3"
                          name="nmDiag3">

                        <input
                          type="hidden"
                          id="kdnonSpesialis3">

                      </div>

                    </div>


                    <!-- RENCANA -->

                    <div class="medical-subtitle">

                      <i class="bi bi-journal-medical"></i>

                      Tindakan / Terapi / Rencana

                    </div>


                    <div class="mb-3">

                      <label
                        for="tindakan"
                        class="form-label">

                        Tindakan / Terapi / Instruksi / Rencana Rawat

                      </label>

                      <textarea
                        id="tindakan"
                        name="tindakan"
                        rows="4"
                        class="form-control"><?= @$data['tindakan'] ?></textarea>

                    </div>

                  </div>

                </div>

              </div>



              <!-- =====================================================
     6. ODONTOGRAM
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingOdontogram">
                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseOdontogram"
                    aria-expanded="false"
                    aria-controls="collapseOdontogram">

                    <span class="accordion-icon odontogram">
                      <i class="bi bi-grid-3x3-gap"></i>
                    </span>

                    <span>
                      <strong>Odontogram</strong>
                      <small>
                        Pemeriksaan dan kondisi gigi pasien
                      </small>
                    </span>

                  </button>
                </h2>

                <div
                  id="collapseOdontogram"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingOdontogram"
                  >

                  <div class="accordion-body">

                    
                    <?php require 'component_odontogram.php'; ?>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     7. PERMINTAAN FARMASI
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingFarmasi">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFarmasi"
                    aria-expanded="false"
                    aria-controls="collapseFarmasi">

                    <span class="accordion-icon farmasi">
                      <i class="bi bi-capsule-pill"></i>
                    </span>

                    <span>
                      <strong>Permintaan Farmasi</strong>

                      <small>
                        Resep dan permintaan obat pasien
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseFarmasi"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingFarmasi"
                  >

                  <div class="accordion-body">

                    <div class="farmasi-placeholder">

                      <div class="farmasi-placeholder-icon">
                        <i class="bi bi-capsule-pill"></i>
                      </div>

                      <div>

                        <strong>Permintaan Farmasi</strong>

                        <p>
                          Resep disimpan sebagai draft terlebih dahulu. Setelah
                              Simpan Pemeriksaan berhasil, resep otomatis dikirim ke farmasi.
                        </p>

                      </div>

                    </div>

                    <?php
                    require 'component_farmasi.php';
                    ?>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     8. RESEP LUAR
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingResepLuar">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseResepLuar"
                    aria-expanded="false"
                    aria-controls="collapseResepLuar">

                    <span class="accordion-icon resep-luar">
                      <i class="bi bi-prescription2"></i>
                    </span>

                    <span>
                      <strong>Resep Luar</strong>

                      <small>
                        Resep obat yang ditebus di luar fasilitas
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseResepLuar"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingResepLuar"
                  >

                  <div class="accordion-body">

                    
                    <?php
                    require 'component_resep.php';
                    ?>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     9. PEMERIKSAAN LABORATORIUM
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingLab">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseLab"
                    aria-expanded="false"
                    aria-controls="collapseLab">

                    <span class="accordion-icon lab">
                      <i class="bi bi-eyedropper"></i>
                    </span>

                    <span>
                      <strong>Pemeriksaan Laboratorium</strong>

                      <small>
                        Permintaan dan hasil pemeriksaan laboratorium
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseLab"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingLab"
                  >

                  <div class="accordion-body">

                    

                    <?php
                    require 'component_penunjang.php';
                    ?>



                  </div>

                </div>

              </div>


              <!-- =====================================================
     10. VAKSIN
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingVaksin">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseVaksin"
                    aria-expanded="false"
                    aria-controls="collapseVaksin">

                    <span class="accordion-icon vaksin">
                      <i class="bi bi-shield-plus"></i>
                    </span>

                    <span>
                      <strong>Vaksin</strong>

                      <small>
                        Pemberian dan riwayat vaksinasi pasien
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseVaksin"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingVaksin"
                  >

                  <div class="accordion-body">

                    <div class="rmx-embed" data-src="module/admin/vaksin?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'a', 'embed' => 1])) ?>" data-judul="Vaksinasi">
                      <div class="rmx-embed-muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat vaksinasi…</div>
                    </div>
                    <div class="text-end mt-1"><button type="button" class="btn btn-link btn-sm p-0 rmx-reload"><i class="bi bi-arrow-clockwise me-1"></i>Muat ulang</button></div>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     11. TINDAKAN
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingTindakan">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseTindakan"
                    aria-expanded="false"
                    aria-controls="collapseTindakan">

                    <span class="accordion-icon tindakan">
                      <i class="bi bi-bandaid"></i>
                    </span>

                    <span>
                      <strong>Tindakan</strong>

                      <small>
                        Tindakan medis yang diberikan kepada pasien
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseTindakan"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingTindakan"
                  >

                  <div class="accordion-body">

                    <div class="rmx-embed" data-src="module/admin/billing?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'a', 'embed' => 1])) ?>" data-judul="Tindakan">
                      <div class="rmx-embed-muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat tindakan…</div>
                    </div>
                    <div class="text-end mt-1"><button type="button" class="btn btn-link btn-sm p-0 rmx-reload"><i class="bi bi-arrow-clockwise me-1"></i>Muat ulang</button></div>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     12. RIWAYAT PENGOBATAN
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingRiwayatPengobatan">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseRiwayatPengobatan"
                    aria-expanded="false"
                    aria-controls="collapseRiwayatPengobatan">

                    <span class="accordion-icon riwayat">
                      <i class="bi bi-clock-history"></i>
                    </span>

                    <span>
                      <strong>Riwayat Pengobatan</strong>

                      <small>
                        Riwayat terapi dan pengobatan pasien
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseRiwayatPengobatan"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingRiwayatPengobatan"
                  >

                  <div class="accordion-body">

                    <div class="rmx-embed" data-src="module/admin/riwayat?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'a', 'embed' => 1])) ?>" data-judul="Riwayat pengobatan">
                      <div class="rmx-embed-muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat riwayat pengobatan…</div>
                    </div>
                    <div class="text-end mt-1"><button type="button" class="btn btn-link btn-sm p-0 rmx-reload"><i class="bi bi-arrow-clockwise me-1"></i>Muat ulang</button></div>

                  </div>

                </div>

              </div>


              <!-- =====================================================
     13. RAWAT INAP
====================================================== -->
              <div class="accordion-item">

                <h2 class="accordion-header" id="headingRawatInap">

                  <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseRawatInap"
                    aria-expanded="false"
                    aria-controls="collapseRawatInap">

                    <span class="accordion-icon rawat-inap">
                      <i class="bi bi-hospital"></i>
                    </span>

                    <span>
                      <strong>Rawat Inap</strong>

                      <small>
                        Perencanaan dan permintaan rawat inap
                      </small>
                    </span>

                  </button>

                </h2>


                <div
                  id="collapseRawatInap"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingRawatInap"
                  >

                  <div class="accordion-body">

                    <div class="rmx-embed" data-src="module/admin/rawat_inap?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'a', 'embed' => 1])) ?>" data-judul="Rawat inap">
                      <div class="rmx-embed-muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat rawat inap…</div>
                    </div>
                    <div class="text-end mt-1"><button type="button" class="btn btn-link btn-sm p-0 rmx-reload"><i class="bi bi-arrow-clockwise me-1"></i>Muat ulang</button></div>

                  </div>

                </div>

              </div>



              <!-- =====================================================
         7. STATUS PULANG & RUJUKAN
    ====================================================== -->

              <div class="accordion-item">

                <h2 class="accordion-header" id="headingPulang">

                  <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapsePulang"
                    aria-expanded="true"
                    aria-controls="collapsePulang">

                    <span class="accordion-icon pulang">
                      <i class="bi bi-door-open-fill"></i>
                    </span>

                    <span>

                      <strong>
                        Status Pulang & Rujukan
                      </strong>

                      <small>
                        Status akhir kunjungan dan rujukan pasien
                      </small>

                    </span>

                  </button>

                </h2>


                <div
                  id="collapsePulang"
                  class="accordion-collapse collapse show"
                  aria-labelledby="headingPulang"
                  >

                  <div class="accordion-body">


                    <!-- STATUS PULANG -->

                    <div class="row mb-4">

                      <div class="col-md-12">

                        <label
                          for="kdStatusPulang"
                          class="form-label fw-semibold">

                          Status Pulang
                          <span class="text-danger">*</span>

                        </label>

                        <select
                          class="form-select"
                          id="kdStatusPulang"
                          name="kdStatusPulang">

                        </select>

                      </div>

                    </div>



                    <!-- RUJUKAN VERTIKAL -->

                    <div
                      class="d-none"
                      id="rujukvertikal">


                      <hr>


                      <div class="medical-subtitle">

                        <i class="bi bi-hospital-fill"></i>

                        Rujukan Vertikal

                      </div>


                      <!-- STATUS RUJUKAN -->

                      <div class="row mb-3">

                        <div class="col-md-3">

                          <label
                            class="form-label fw-semibold">

                            Status Rujukan
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-9">

                          <div class="row g-2">


                            <div class="col-md-6">

                              <input
                                type="radio"
                                class="btn-check"
                                name="kdStatusRujuk"
                                id="rujukSpesialis"
                                value="SP"
                                required>

                              <label
                                class="btn btn-outline-primary w-100 text-start p-3"
                                for="rujukSpesialis">

                                <strong>
                                  Rujukan Spesialis
                                </strong>

                                <br>

                                <small>
                                  Rujukan ke sub spesialis sesuai indikasi medis
                                </small>

                              </label>

                            </div>


                            <div class="col-md-6">

                              <input
                                type="radio"
                                class="btn-check"
                                name="kdStatusRujuk"
                                id="rujukKhusus"
                                value="KH"
                                required>

                              <label
                                class="btn btn-outline-success w-100 text-start p-3"
                                for="rujukKhusus">

                                <strong>
                                  Rujukan Khusus
                                </strong>

                                <br>

                                <small>
                                  Rujukan dengan kriteria atau program khusus
                                </small>

                              </label>

                            </div>

                          </div>

                        </div>

                      </div>



                      <!-- KATEGORI -->

                      <div class="row mb-3">

                        <div class="col-md-3">

                          <label
                            for="kdKategori"
                            class="form-label fw-semibold">

                            Kategori
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-5">

                          <label
                            class="form-label">

                            Spesialis

                          </label>

                          <select
                            class="form-select"
                            id="kdKategori"
                            name="kdKategori"
                            required>

                          </select>

                          <input
                            type="hidden"
                            id="nmKategori"
                            name="nmKategori">

                        </div>


                        <div
                          class="col-md-2 d-none"
                          id="subspesialis">

                          <label
                            class="form-label">

                            Sub Spesialis

                          </label>

                          <select
                            class="form-select"
                            id="kdsubspesialis"
                            name="kdSubSpesialis1">

                          </select>

                          <input
                            type="hidden"
                            id="nmSubSpesialis1"
                            name="nmSubSpesialis1">

                        </div>


                        <div
                          class="col-md-3 d-none"
                          id="sarana">

                          <div class="form-check mb-2">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              id="useSarana"
                              value="1">

                            <label
                              class="form-check-label fw-semibold"
                              for="useSarana">

                              Sarana

                            </label>

                          </div>


                          <select
                            class="form-select"
                            id="kdSarana">

                          </select>


                          <input
                            type="hidden"
                            name="kdSarana"
                            id="kdSaranaHidden"
                            value="0">

                        </div>


                        <div class="col-md-5">

                          <label
                            class="form-label fw-semibold">

                            Tgl Kunjung

                          </label>


                          <div class="input-group">

                            <input
                              type="date"
                              class="form-control"
                              id="tglRujukan"
                              name="tglRujukan"
                              required>

                            <button
                              type="button"
                              id="btnCariFaskes"
                              class="btn btn-primary">

                              <i class="bi bi-search"></i>

                              Cari Faskes

                            </button>

                          </div>

                        </div>

                      </div>



                      <!-- ALASAN RUJUKAN -->

                      <div
                        class="row mb-3 d-none"
                        id="alasanrujuk">

                        <div class="col-md-3">

                          <label
                            for="alasanRujukan"
                            class="form-label fw-semibold">

                            Alasan
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-9">

                          <textarea
                            class="form-control"
                            id="alasanRujukan"
                            name="alasanRujukan"
                            rows="3"
                            placeholder="Masukkan alasan rujukan..."
                            required></textarea>

                        </div>

                      </div>



                      <!-- FASKES -->

                      <div class="row mb-3">

                        <div class="col-md-3">

                          <label
                            class="form-label fw-semibold">

                            Faskes
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-9">

                          <input
                            type="text"
                            id="nmfaskes"
                            name="nmfaskes"
                            class="form-control"
                            readonly>

                          <input
                            type="hidden"
                            name="kdppk"
                            id="kdfaskes">

                          <input
                            type="hidden"
                            name="jadwal"
                            id="jadwal">

                        </div>

                      </div>

                    </div>



                    <!-- =================================================
                     TACC
                ================================================== -->

                    <div
                      class="d-none"
                      id="formTacc">

                      <hr>


                      <div class="row mb-3">

                        <div class="col-md-3">

                          <label
                            class="form-label fw-semibold">

                            TACC
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-3">

                          <label
                            class="form-label">

                            TACC

                          </label>

                          <select
                            class="form-select"
                            id="kdTacc"
                            name="kdTacc">

                            <option value="-1">
                              Tanpa TACC
                            </option>

                            <option value="1">
                              Time
                            </option>

                            <option value="2">
                              Age
                            </option>

                            <option value="3">
                              Complication
                            </option>

                            <option value="4">
                              Comorbidity
                            </option>

                          </select>

                        </div>


                        <div class="col-md-6">

                          <label
                            for="alasanTacc"
                            class="form-label">

                            Alasan

                          </label>

                          <input
                            type="text"
                            class="form-control"
                            id="alasanTacc"
                            name="alasanTacc">

                        </div>

                      </div>

                    </div>



                    <!-- =================================================
                     NOMOR LAPORAN POLISI
                ================================================== -->

                    <div
                      class="d-none"
                      id="noLaporanPolisi">

                      <div class="row mb-3">

                        <div class="col-md-3">

                          <label
                            class="form-label fw-semibold">

                            Nomor LP
                            <span class="text-danger">*</span>

                          </label>

                        </div>


                        <div class="col-md-9">

                          <input
                            type="text"
                            class="form-control"
                            placeholder="Masukan Nomor Laporan Polisi Disini..."
                            id="nomorLp"
                            name="nomorLp">

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </div>


            <!-- =========================================================
     PRINT ACTIONS
========================================================= -->

            <div class="rmx-save">
              <div
              class="d-flex flex-wrap align-items-center gap-2"
              id="printActions">


              <button
                type="button"
                class="btn btn-outline-dark btn-printkunjungan d-none"
                id="btn-printkunjungan">

                <i class="bi bi-printer-fill me-2"></i>

                Print Kunjungan

              </button>


              <button
                type="button"
                class="btn btn-outline-dark btn-print d-none"
                id="btn-print">

                <i class="bi bi-file-earmark-text-fill me-2"></i>

                Print Rujukan

              </button>

            </div>
              <button
                type="button"
                id="simpan_pemeriksaan"
                class="btn btn-primary">

                <i class="bi bi-save2-fill me-2"></i>

                Simpan Pemeriksaan

              </button>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalFaskes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
      <div class="modal-content shadow-lg rounded-3 border-0">
        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-hospital fs-3"></i>
            Daftar Fasilitas Kesehatan
          </h5>
          <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle text-center" id="tableRujukan">
              <thead class="table-dark text-white">
                <tr>
                  <th>No</th>
                  <th>Faskes</th>
                  <th>Kelas</th>
                  <th>Kantor Cabang</th>
                  <th>Alamat</th>
                  <th>Telp</th>
                  <th>Jarak</th>
                  <th>Total Rujukan</th>
                  <th>Kapasitas</th>
                  <th>%</th>
                  <th>Jadwal</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data akan diisi JS -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
  require 'modal_farmasi.php';
  ?>
  <?php require 'library.php'; ?>
  <script src="controller/admisi/helper.js?v=<?= filemtime(__DIR__ . '/../../controller/admisi/helper.js') ?>"></script>
  <script src="controller/doctor/rmeCycle.js?v=<?= filemtime(__DIR__ . '/../../controller/doctor/rmeCycle.js') ?>"></script>
  <script>
    // navigasi akordeon: klik chip -> buka bagian & gulir; buka/tutup semua
    (function() {
      const buka = el => bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
      document.querySelectorAll('.rmx-bar [data-buka]').forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(a.getAttribute('href'));
        if (!el) return;
        buka(el);
        setTimeout(() => el.closest('.accordion-item').scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
      }));
      // bagian Vaksin / Tindakan / Riwayat / Rawat Inap tampil di halaman ini (mode sematan), dimuat saat dibuka
      function muatSematan(box, paksa) {
        let fr = box.querySelector('iframe');
        if (fr && !paksa) return;
        if (fr) fr.remove();
        const muat = box.querySelector('.rmx-embed-muat');
        muat.style.display = 'flex';
        fr = document.createElement('iframe');
        fr.title = box.dataset.judul;
        fr.loading = 'eager';
        fr.src = box.dataset.src;
        fr.addEventListener('load', () => {
          muat.style.display = 'none';
          const doc = fr.contentDocument;
          if (!doc) return;
          const atur = () => {
            // tinggi isi (body), bukan dokumen — dokumen minimal setinggi iframe sehingga tidak bisa mengecil
            const tinggi = doc.body ? doc.body.scrollHeight : 200;
            fr.style.height = Math.min(tinggi + 4, Math.round(window.innerHeight * 0.8)) + 'px';
          };
          atur();
          try { new fr.contentWindow.ResizeObserver(atur).observe(doc.body); } catch (e) { setInterval(atur, 1000); }
        });
        box.appendChild(fr);
      }
      document.querySelectorAll('.rmx-embed').forEach(box => {
        const col = box.closest('.accordion-collapse');
        col.addEventListener('shown.bs.collapse', () => muatSematan(box, false));
        if (col.classList.contains('show')) muatSematan(box, false);
        box.parentElement.querySelector('.rmx-reload').addEventListener('click', () => muatSematan(box, true));
      });
      document.getElementById('rmxBukaSemua').addEventListener('click', () => document.querySelectorAll('#accordionPemeriksaan .accordion-collapse').forEach(buka));
      document.getElementById('rmxTutupSemua').addEventListener('click', () => document.querySelectorAll('#accordionPemeriksaan .accordion-collapse').forEach(el => bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide()));
    })();
  </script>
  <script src="controller/doctor/kunjunganrj.js?v=<?= filemtime(__DIR__ . '/../../controller/doctor/kunjunganrj.js') ?>"></script>
</body>




</html>