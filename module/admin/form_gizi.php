<?php
$title = 'Foto Gizi & Nutrisi';
require '../../database/connect.php';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$patient = mysqli_query($koneksi, "SELECT nomor_rm, id_patient FROM ms_patient WHERE nomor_rm='$rm'");
$datapatient = mysqli_fetch_array($patient);
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
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
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <!-- =========================================================
     RME GIZI & NUTRISI
========================================================= -->

            <div class="col-lg-12 d-flex align-items-stretch">

              <div class="card w-100 shadow-sm">

                <div class="card-body p-4">

                  <!-- =====================================================
           HEADER
      ====================================================== -->

                  <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                      <h5 class="card-title fw-semibold mb-1">
                        RME Gizi & Nutrisi
                      </h5>

                      <small class="text-muted">
                        Asesmen Gizi, Diagnosis, Intervensi Diet & Monitoring
                      </small>

                    </div>


                    <div class="d-flex ms-auto gap-2">

                      <a
                        href="module/admin/print/formulir_gizi?no=<?= $no ?>&rm=<?= $rm ?>"
                        target="_blank"
                        class="btn btn-outline-primary">

                        <i class="fas fa-print me-1"></i>
                        Cetak

                      </a>

                      <button
                        type="button"
                        class="btn btn-primary"
                        id="btnSimpanGizi">

                        <i class="fas fa-save me-1"></i>
                        Simpan

                      </button>

                    </div>

                  </div>


                  <!-- =====================================================
           IDENTITAS PASIEN
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-user me-2"></i>
                        Identitas Nutrisionis
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">



                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Tanggal Asesmen
                          </label>

                          <input
                            type="date"
                            class="form-control"
                            id="gizi_tanggal_asesmen"
                            value="<?= date('Y-m-d') ?>">

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Nutrisionis / Dietisien
                          </label>

                          <input
                            type="text"
                            class="form-control"
                            id="gizi_nutrisionis">

                        </div>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           A. SCREENING RISIKO GIZI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-filter me-2"></i>
                        A. Skrining Risiko Gizi
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Penurunan berat badan tidak direncanakan?
                          </label>

                          <div>

                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="penurunan_bb"
                                value="Tidak">

                              <label class="form-check-label">
                                Tidak
                              </label>

                            </div>


                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="penurunan_bb"
                                value="Ya">

                              <label class="form-check-label">
                                Ya
                              </label>

                            </div>

                          </div>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Penurunan asupan makan?
                          </label>

                          <div>

                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="penurunan_asupan"
                                value="Tidak">

                              <label class="form-check-label">
                                Tidak
                              </label>

                            </div>


                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="penurunan_asupan"
                                value="Ya">

                              <label class="form-check-label">
                                Ya
                              </label>

                            </div>

                          </div>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Kondisi yang meningkatkan kebutuhan gizi?
                          </label>

                          <div>

                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="kebutuhan_gizi"
                                value="Tidak">

                              <label class="form-check-label">
                                Tidak
                              </label>

                            </div>


                            <div class="form-check form-check-inline">

                              <input
                                class="form-check-input"
                                type="radio"
                                name="kebutuhan_gizi"
                                value="Ya">

                              <label class="form-check-label">
                                Ya
                              </label>

                            </div>

                          </div>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Risiko Gizi
                          </label>

                          <select
                            class="form-select"
                            id="risiko_gizi">

                            <option value="">
                              Pilih Risiko
                            </option>

                            <option value="Rendah">
                              Risiko Rendah
                            </option>

                            <option value="Sedang">
                              Risiko Sedang
                            </option>

                            <option value="Tinggi">
                              Risiko Tinggi
                            </option>

                          </select>

                        </div>


                        <div class="col-12">

                          <label class="form-label">
                            Catatan Skrining
                          </label>

                          <textarea
                            class="form-control"
                            id="catatan_skrining"
                            rows="2"></textarea>

                        </div>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           B. ANTROPOMETRI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-weight-scale me-2"></i>
                        B. Asesmen Antropometri
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Berat Badan
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="bb"
                              placeholder="0">

                            <span class="input-group-text">
                              kg
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Tinggi Badan
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="tb"
                              placeholder="0">

                            <span class="input-group-text">
                              cm
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            IMT
                          </label>

                          <div class="input-group">

                            <input
                              type="text"
                              class="form-control"
                              id="imt"
                              readonly>

                            <span class="input-group-text">
                              kg/m²
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Kategori IMT
                          </label>

                          <input
                            type="text"
                            class="form-control"
                            id="kategori_imt"
                            readonly>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Berat Badan Ideal
                          </label>

                          <div class="input-group">

                            <input
                              type="text"
                              class="form-control"
                              id="bb_ideal"
                              readonly>

                            <span class="input-group-text">
                              kg
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Berat Badan Biasanya
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="bb_biasa">

                            <span class="input-group-text">
                              kg
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Perubahan BB
                          </label>

                          <div class="input-group">

                            <input
                              type="text"
                              class="form-control"
                              id="perubahan_bb"
                              readonly>

                            <span class="input-group-text">
                              kg
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Persentase Perubahan BB
                          </label>

                          <div class="input-group">

                            <input
                              type="text"
                              class="form-control"
                              id="persen_perubahan_bb"
                              readonly>

                            <span class="input-group-text">
                              %
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Lingkar Lengan Atas
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="llla">

                            <span class="input-group-text">
                              cm
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Lingkar Pinggang
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="lingkar_pinggang">

                            <span class="input-group-text">
                              cm
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Lingkar Pinggul
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="lingkar_pinggul">

                            <span class="input-group-text">
                              cm
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3 mb-3">

                          <label class="form-label">
                            Periode Perubahan BB
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="periode_perubahan_bb">

                            <span class="input-group-text">
                              bulan
                            </span>

                          </div>

                        </div>


                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           C. BIOKIMIA
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-flask me-2"></i>
                        C. Biokimia / Laboratorium
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="table-responsive">

                        <table
                          class="table table-bordered align-middle mb-0">

                          <thead class="table-light">

                            <tr>

                              <th>
                                Pemeriksaan
                              </th>

                              <th width="180">
                                Hasil
                              </th>

                              <th width="130">
                                Satuan
                              </th>

                              <th width="180">
                                Tanggal
                              </th>

                              <th>
                                Keterangan
                              </th>

                            </tr>

                          </thead>


                          <tbody>


                            <tr>

                              <td>
                                Hemoglobin
                              </td>

                              <td>

                                <input
                                  type="number"
                                  step="0.1"
                                  class="form-control"
                                  id="lab_hb">

                              </td>

                              <td>
                                g/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_hb_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_hb_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Gula Darah
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_gula">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_gula_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_gula_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                HbA1c
                              </td>

                              <td>

                                <input
                                  type="number"
                                  step="0.1"
                                  class="form-control"
                                  id="lab_hba1c">

                              </td>

                              <td>
                                %
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_hba1c_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_hba1c_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Kolesterol Total
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_kolesterol">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_kolesterol_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_kolesterol_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                LDL
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_ldl">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_ldl_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_ldl_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                HDL
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_hdl">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_hdl_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_hdl_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Trigliserida
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_trigliserida">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_trigliserida_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_trigliserida_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Albumin
                              </td>

                              <td>

                                <input
                                  type="number"
                                  step="0.1"
                                  class="form-control"
                                  id="lab_albumin">

                              </td>

                              <td>
                                g/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_albumin_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_albumin_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Ureum
                              </td>

                              <td>

                                <input
                                  type="number"
                                  class="form-control"
                                  id="lab_ureum">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_ureum_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_ureum_keterangan">

                              </td>

                            </tr>


                            <tr>

                              <td>
                                Kreatinin
                              </td>

                              <td>

                                <input
                                  type="number"
                                  step="0.1"
                                  class="form-control"
                                  id="lab_kreatinin">

                              </td>

                              <td>
                                mg/dL
                              </td>

                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  id="lab_kreatinin_tanggal">

                              </td>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  id="lab_kreatinin_keterangan">

                              </td>

                            </tr>


                          </tbody>

                        </table>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           D. PEMERIKSAAN KLINIS / FISIK
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-stethoscope me-2"></i>
                        D. Pemeriksaan Klinis / Fisik
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Nafsu Makan
                          </label>

                          <select
                            class="form-select"
                            id="nafsu_makan">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Baik">
                              Baik
                            </option>

                            <option value="Menurun">
                              Menurun
                            </option>

                            <option value="Meningkat">
                              Meningkat
                            </option>

                          </select>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Kesadaran
                          </label>

                          <select
                            class="form-select"
                            id="kesadaran_gizi">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Compos Mentis">
                              Compos Mentis
                            </option>

                            <option value="Somnolen">
                              Somnolen
                            </option>

                            <option value="Sopor">
                              Sopor
                            </option>

                            <option value="Koma">
                              Koma
                            </option>

                          </select>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Kondisi Umum
                          </label>

                          <select
                            class="form-select"
                            id="kondisi_umum">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Baik">
                              Baik
                            </option>

                            <option value="Sedang">
                              Sedang
                            </option>

                            <option value="Lemah">
                              Lemah
                            </option>

                          </select>

                        </div>


                        <div class="col-md-12 mb-3">

                          <label class="form-label">
                            Keluhan / Gejala Terkait Gizi
                          </label>


                          <div class="row">


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Mual">

                                <label class="form-check-label">
                                  Mual
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Muntah">

                                <label class="form-check-label">
                                  Muntah
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Diare">

                                <label class="form-check-label">
                                  Diare
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Konstipasi">

                                <label class="form-check-label">
                                  Konstipasi
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Sulit Menelan">

                                <label class="form-check-label">
                                  Sulit Menelan
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Gangguan Mengunyah">

                                <label class="form-check-label">
                                  Gangguan Mengunyah
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Kembung">

                                <label class="form-check-label">
                                  Kembung
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="keluhan_gizi[]"
                                  value="Nyeri Abdomen">

                                <label class="form-check-label">
                                  Nyeri Abdomen
                                </label>

                              </div>

                            </div>


                          </div>

                        </div>


                        <div class="col-md-12 mb-3">

                          <label class="form-label">
                            Kondisi Fisik Khusus
                          </label>


                          <div class="row">


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="kondisi_fisik[]"
                                  value="Edema">

                                <label class="form-check-label">
                                  Edema
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="kondisi_fisik[]"
                                  value="Asites">

                                <label class="form-check-label">
                                  Asites
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="kondisi_fisik[]"
                                  value="Dehidrasi">

                                <label class="form-check-label">
                                  Dehidrasi
                                </label>

                              </div>

                            </div>


                            <div class="col-md-3">

                              <div class="form-check">

                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="kondisi_fisik[]"
                                  value="Kehilangan Massa Otot">

                                <label class="form-check-label">
                                  Kehilangan Massa Otot
                                </label>

                              </div>

                            </div>


                          </div>

                        </div>


                        <div class="col-md-12">

                          <label class="form-label">
                            Catatan Klinis
                          </label>

                          <textarea
                            class="form-control"
                            id="catatan_klinis"
                            rows="3"></textarea>

                        </div>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           E. RIWAYAT GIZI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-utensils me-2"></i>
                        E. Riwayat Gizi / Dietary History
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Makan Utama
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="frekuensi_makan">

                            <span class="input-group-text">
                              kali/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Snack
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="frekuensi_snack">

                            <span class="input-group-text">
                              kali/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Pola Makan
                          </label>

                          <select
                            class="form-select"
                            id="pola_makan">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Teratur">
                              Teratur
                            </option>

                            <option value="Tidak Teratur">
                              Tidak Teratur
                            </option>

                          </select>

                        </div>


                        <!-- =============================================
                 RECALL 24 JAM
            ============================================== -->

                        <div class="col-12 mt-2">

                          <div class="d-flex justify-content-between align-items-center mb-2">

                            <h6 class="fw-semibold mb-0">
                              Recall 24 Jam
                            </h6>

                            <button
                              type="button"
                              class="btn btn-sm btn-outline-primary"
                              id="btnTambahRecall">

                              <i class="fas fa-plus me-1"></i>
                              Tambah

                            </button>

                          </div>


                          <div class="table-responsive">

                            <table
                              class="table table-bordered align-middle"
                              id="tableRecall">

                              <thead class="table-light">

                                <tr>

                                  <th width="130">
                                    Waktu
                                  </th>

                                  <th>
                                    Makanan / Minuman
                                  </th>

                                  <th width="130">
                                    Jumlah
                                  </th>

                                  <th width="130">
                                    URT
                                  </th>

                                  <th width="60">
                                    #
                                  </th>

                                </tr>

                              </thead>


                              <tbody id="recallBody">

                                <tr>

                                  <td>

                                    <input
                                      type="time"
                                      class="form-control"
                                      name="recall_waktu[]">

                                  </td>


                                  <td>

                                    <input
                                      type="text"
                                      class="form-control"
                                      name="recall_makanan[]"
                                      placeholder="Contoh: Nasi, ayam, sayur">

                                  </td>


                                  <td>

                                    <input
                                      type="text"
                                      class="form-control"
                                      name="recall_jumlah[]">

                                  </td>


                                  <td>

                                    <input
                                      type="text"
                                      class="form-control"
                                      name="recall_urt[]"
                                      placeholder="Piring / potong / gelas">

                                  </td>


                                  <td class="text-center">

                                    <button
                                      type="button"
                                      class="btn btn-sm btn-outline-danger btnHapusRecall">

                                      <i class="fas fa-trash"></i>

                                    </button>

                                  </td>

                                </tr>

                              </tbody>

                            </table>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Estimasi Energi
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="asupan_energi">

                            <span class="input-group-text">
                              kcal
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Estimasi Protein
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="asupan_protein">

                            <span class="input-group-text">
                              g
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Cairan
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="asupan_cairan">

                            <span class="input-group-text">
                              mL/hari
                            </span>

                          </div>

                        </div>


                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           F. RIWAYAT PERSONAL
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-notes-medical me-2"></i>
                        F. Riwayat Personal
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Pekerjaan
                          </label>

                          <input
                            type="text"
                            class="form-control"
                            id="pekerjaan">

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Aktivitas Fisik
                          </label>

                          <select
                            class="form-select"
                            id="aktivitas_fisik">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Sangat Ringan">
                              Sangat Ringan
                            </option>

                            <option value="Ringan">
                              Ringan
                            </option>

                            <option value="Sedang">
                              Sedang
                            </option>

                            <option value="Berat">
                              Berat
                            </option>

                          </select>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Merokok
                          </label>

                          <select
                            class="form-select"
                            id="merokok">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Tidak">
                              Tidak
                            </option>

                            <option value="Ya">
                              Ya
                            </option>

                          </select>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Alergi Makanan
                          </label>

                          <textarea
                            class="form-control"
                            id="alergi_makanan"
                            rows="3"></textarea>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Intoleransi Makanan
                          </label>

                          <textarea
                            class="form-control"
                            id="intoleransi_makanan"
                            rows="3"></textarea>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Diet Sebelumnya
                          </label>

                          <textarea
                            class="form-control"
                            id="diet_sebelumnya"
                            rows="3"></textarea>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Pantangan / Makanan yang Dihindari
                          </label>

                          <textarea
                            class="form-control"
                            id="pantangan_makanan"
                            rows="3"></textarea>

                        </div>


                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           G. KESIMPULAN ASESMEN
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-file-medical me-2"></i>
                        G. Kesimpulan Asesmen Gizi
                      </strong>

                    </div>


                    <div class="card-body">

                      <textarea
                        class="form-control"
                        id="kesimpulan_asesmen"
                        rows="5"
                        placeholder="Ringkasan hasil asesmen gizi..."></textarea>

                    </div>

                  </div>


                  <!-- =====================================================
           H. DIAGNOSIS GIZI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-diagnoses me-2"></i>
                        H. Diagnosis Gizi
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Domain
                          </label>

                          <select
                            class="form-select"
                            id="diagnosis_domain">

                            <option value="">
                              Pilih Domain
                            </option>

                            <option value="Intake">
                              Intake
                            </option>

                            <option value="Clinical">
                              Clinical
                            </option>

                            <option value="Behavioral / Environmental">
                              Behavioral / Environmental
                            </option>

                          </select>

                        </div>


                        <div class="col-md-8 mb-3">

                          <label class="form-label">
                            Problem
                          </label>

                          <input
                            type="text"
                            class="form-control"
                            id="diagnosis_problem"
                            placeholder="Contoh: Asupan energi tidak adekuat">

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Etiologi
                          </label>

                          <textarea
                            class="form-control"
                            id="diagnosis_etiologi"
                            rows="3"
                            placeholder="Berkaitan dengan..."></textarea>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Signs / Symptoms
                          </label>

                          <textarea
                            class="form-control"
                            id="diagnosis_tanda"
                            rows="3"
                            placeholder="Dibuktikan dengan..."></textarea>

                        </div>


                        <div class="col-12">

                          <label class="form-label fw-semibold">
                            Diagnosis Gizi / PES
                          </label>

                          <textarea
                            class="form-control"
                            id="diagnosis_pes"
                            rows="4"
                            readonly></textarea>

                        </div>


                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           I. INTERVENSI GIZI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-bowl-food me-2"></i>
                        I. Intervensi Gizi & Preskripsi Diet
                      </strong>

                    </div>


                    <div class="card-body">


                      <!-- TUJUAN -->

                      <div class="mb-4">

                        <label class="form-label fw-semibold">
                          Tujuan Intervensi
                        </label>


                        <div class="row">


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Meningkatkan Asupan Energi">

                              <label class="form-check-label">
                                Meningkatkan Asupan Energi
                              </label>

                            </div>

                          </div>


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Meningkatkan Berat Badan">

                              <label class="form-check-label">
                                Meningkatkan Berat Badan
                              </label>

                            </div>

                          </div>


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Menurunkan Berat Badan">

                              <label class="form-check-label">
                                Menurunkan Berat Badan
                              </label>

                            </div>

                          </div>


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Kontrol Gula Darah">

                              <label class="form-check-label">
                                Kontrol Gula Darah
                              </label>

                            </div>

                          </div>


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Kontrol Tekanan Darah">

                              <label class="form-check-label">
                                Kontrol Tekanan Darah
                              </label>

                            </div>

                          </div>


                          <div class="col-md-4">

                            <div class="form-check">

                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="tujuan_intervensi[]"
                                value="Perbaikan Status Gizi">

                              <label class="form-check-label">
                                Perbaikan Status Gizi
                              </label>

                            </div>

                          </div>


                        </div>

                      </div>


                      <!-- PRESKRIPSI DIET -->

                      <div class="row">


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Jenis Diet
                          </label>

                          <select
                            class="form-select"
                            id="jenis_diet">

                            <option value="">
                              Pilih Jenis Diet
                            </option>

                            <option value="Diet Biasa">
                              Diet Biasa
                            </option>

                            <option value="Diet Lunak">
                              Diet Lunak
                            </option>

                            <option value="Diet Saring">
                              Diet Saring
                            </option>

                            <option value="Diet Cair">
                              Diet Cair
                            </option>

                            <option value="Diet DM">
                              Diet DM
                            </option>

                            <option value="Diet Rendah Garam">
                              Diet Rendah Garam
                            </option>

                            <option value="Diet Rendah Lemak">
                              Diet Rendah Lemak
                            </option>

                            <option value="Diet Rendah Protein">
                              Diet Rendah Protein
                            </option>

                            <option value="Diet Tinggi Protein">
                              Diet Tinggi Protein
                            </option>

                            <option value="Diet Ginjal">
                              Diet Ginjal
                            </option>

                            <option value="Diet Hati">
                              Diet Hati
                            </option>

                            <option value="Diet Jantung">
                              Diet Jantung
                            </option>

                            <option value="Diet Tinggi Kalori Tinggi Protein">
                              Tinggi Kalori Tinggi Protein
                            </option>

                            <option value="Diet Lainnya">
                              Diet Lainnya
                            </option>

                          </select>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Energi
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="target_energi">

                            <span class="input-group-text">
                              kcal/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Protein
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="target_protein">

                            <span class="input-group-text">
                              g/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Lemak
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="target_lemak">

                            <span class="input-group-text">
                              g/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Karbohidrat
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              step="0.1"
                              class="form-control"
                              id="target_karbohidrat">

                            <span class="input-group-text">
                              g/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Cairan
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="target_cairan">

                            <span class="input-group-text">
                              mL/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Bentuk Makanan
                          </label>

                          <select
                            class="form-select"
                            id="bentuk_makanan">

                            <option value="">
                              Pilih
                            </option>

                            <option value="Biasa">
                              Biasa
                            </option>

                            <option value="Lunak">
                              Lunak
                            </option>

                            <option value="Saring">
                              Saring
                            </option>

                            <option value="Cair">
                              Cair
                            </option>

                          </select>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Frekuensi Makan Utama
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="target_frekuensi_makan">

                            <span class="input-group-text">
                              kali/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-md-4 mb-3">

                          <label class="form-label">
                            Snack
                          </label>

                          <div class="input-group">

                            <input
                              type="number"
                              class="form-control"
                              id="target_frekuensi_snack">

                            <span class="input-group-text">
                              kali/hari
                            </span>

                          </div>

                        </div>


                        <div class="col-12 mb-3">

                          <label class="form-label">
                            Pembatasan / Modifikasi Diet
                          </label>

                          <textarea
                            class="form-control"
                            id="modifikasi_diet"
                            rows="3"
                            placeholder="Contoh: pembatasan gula sederhana, natrium, lemak jenuh, cairan, dll."></textarea>

                        </div>


                        <div class="col-12">

                          <label class="form-label">
                            Instruksi Diet
                          </label>

                          <textarea
                            class="form-control"
                            id="instruksi_diet"
                            rows="4"
                            placeholder="Instruksi diet untuk pasien..."></textarea>

                        </div>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           J. EDUKASI GIZI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-chalkboard-user me-2"></i>
                        J. Edukasi Gizi
                      </strong>

                    </div>


                    <div class="card-body">

                      <div class="row">


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Pemilihan Makanan">

                            <label class="form-check-label">
                              Pemilihan Makanan
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Porsi Makan">

                            <label class="form-check-label">
                              Porsi Makan
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Jadwal Makan">

                            <label class="form-check-label">
                              Jadwal Makan
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Diet Penyakit">

                            <label class="form-check-label">
                              Diet Penyakit
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Pembatasan Gula">

                            <label class="form-check-label">
                              Pembatasan Gula
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Pembatasan Garam">

                            <label class="form-check-label">
                              Pembatasan Garam
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Aktivitas Fisik">

                            <label class="form-check-label">
                              Aktivitas Fisik
                            </label>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="form-check">

                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="edukasi_gizi[]"
                              value="Label Pangan">

                            <label class="form-check-label">
                              Membaca Label Pangan
                            </label>

                          </div>

                        </div>


                        <div class="col-12 mt-3">

                          <label class="form-label">
                            Materi / Catatan Edukasi
                          </label>

                          <textarea
                            class="form-control"
                            id="materi_edukasi"
                            rows="4"></textarea>

                        </div>


                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           K. MONITORING & EVALUASI
      ====================================================== -->

                  <div class="card border mb-4">

                    <div class="card-header bg-light">

                      <strong>
                        <i class="fas fa-chart-line me-2"></i>
                        K. Monitoring & Evaluasi Gizi
                      </strong>

                    </div>


                    <div class="card-body">


                      <div class="table-responsive">

                        <table
                          class="table table-bordered align-middle"
                          id="tableMonitoring">

                          <thead class="table-light">

                            <tr>

                              <th>
                                Parameter
                              </th>

                              <th>
                                Target
                              </th>

                              <th>
                                Hasil
                              </th>

                              <th>
                                Tanggal
                              </th>

                              <th>
                                Status
                              </th>

                              <th>
                                Keterangan
                              </th>

                              <th width="60">
                                #
                              </th>

                            </tr>

                          </thead>


                          <tbody id="monitoringBody">

                            <tr>

                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  name="monitoring_parameter[]"
                                  placeholder="Contoh: Berat Badan">

                              </td>


                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  name="monitoring_target[]">

                              </td>


                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  name="monitoring_hasil[]">

                              </td>


                              <td>

                                <input
                                  type="date"
                                  class="form-control"
                                  name="monitoring_tanggal[]">

                              </td>


                              <td>

                                <select
                                  class="form-select"
                                  name="monitoring_status[]">

                                  <option value="">
                                    Pilih
                                  </option>

                                  <option value="Membaik">
                                    Membaik
                                  </option>

                                  <option value="Tetap">
                                    Tetap
                                  </option>

                                  <option value="Memburuk">
                                    Memburuk
                                  </option>

                                </select>

                              </td>


                              <td>

                                <input
                                  type="text"
                                  class="form-control"
                                  name="monitoring_keterangan[]">

                              </td>


                              <td class="text-center">

                                <button
                                  type="button"
                                  class="btn btn-sm btn-outline-danger btnHapusMonitoring">

                                  <i class="fas fa-trash"></i>

                                </button>

                              </td>

                            </tr>

                          </tbody>

                        </table>

                      </div>


                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary mb-3"
                        id="btnTambahMonitoring">

                        <i class="fas fa-plus me-1"></i>
                        Tambah Parameter

                      </button>


                      <div class="row">


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Evaluasi
                          </label>

                          <textarea
                            class="form-control"
                            id="evaluasi_gizi"
                            rows="4"></textarea>

                        </div>


                        <div class="col-md-6 mb-3">

                          <label class="form-label">
                            Rencana Tindak Lanjut
                          </label>

                          <textarea
                            class="form-control"
                            id="rencana_tindak_lanjut"
                            rows="4"></textarea>

                        </div>


                        <div class="col-md-4">

                          <label class="form-label">
                            Rencana Kontrol Gizi
                          </label>

                          <input
                            type="date"
                            class="form-control"
                            id="tanggal_kontrol_gizi">

                        </div>

                      </div>

                    </div>

                  </div>


                  <!-- =====================================================
           FOOTER
      ====================================================== -->

                  <div class="card border-0 bg-light">

                    <div class="card-body">

                      <div class="d-flex justify-content-between align-items-center">

                        <div>

                          <strong>
                            Status Asesmen
                          </strong>

                          <div class="small text-muted">
                            Pastikan asesmen, diagnosis, intervensi dan monitoring telah terdokumentasi.
                          </div>

                        </div>


                        <select
                          class="form-select"
                          id="status_asesmen_gizi"
                          style="width:220px;">

                          <option value="Draft">
                            Draft
                          </option>

                          <option value="Selesai">
                            Selesai
                          </option>

                          <option value="Follow Up">
                            Follow Up
                          </option>

                        </select>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </div>


            <!-- =========================================================
     JAVASCRIPT RME GIZI
========================================================= -->

            <script>
              $(document).ready(function() {


                /* =========================================================
                   HITUNG IMT
                ========================================================= */

                function hitungIMT() {

                  const bb =
                    parseFloat(
                      $('#bb').val()
                    );

                  const tb =
                    parseFloat(
                      $('#tb').val()
                    );


                  if (
                    !bb ||
                    !tb ||
                    tb <= 0
                  ) {

                    $('#imt').val('');
                    $('#kategori_imt').val('');

                    return;

                  }


                  const tinggiMeter =
                    tb / 100;


                  const imt =
                    bb /
                    (
                      tinggiMeter *
                      tinggiMeter
                    );


                  $('#imt').val(
                    imt.toFixed(1)
                  );


                  let kategori = '';


                  /*
                   * Kategori umum dewasa.
                   * Untuk pediatrik sebaiknya menggunakan
                   * kurva pertumbuhan/indikator usia.
                   */

                  if (imt < 18.5) {

                    kategori =
                      'Kurus';

                  } else if (
                    imt < 25
                  ) {

                    kategori =
                      'Normal';

                  } else if (
                    imt < 30
                  ) {

                    kategori =
                      'Overweight';

                  } else {

                    kategori =
                      'Obesitas';

                  }


                  $('#kategori_imt')
                    .val(
                      kategori
                    );

                }


                /* =========================================================
                   BERAT BADAN IDEAL
                ========================================================= */

                function hitungBBIdeal() {

                  const tb =
                    parseFloat(
                      $('#tb').val()
                    );


                  if (
                    !tb ||
                    tb <= 0
                  ) {

                    $('#bb_ideal').val('');

                    return;

                  }


                  /*
                   * Rumus Broca sederhana.
                   * Untuk implementasi klinis lebih lanjut,
                   * formula dapat disesuaikan berdasarkan
                   * jenis kelamin dan kondisi pasien.
                   */

                  const bbIdeal =
                    (
                      tb - 100
                    ) -
                    (
                      0.1 *
                      (
                        tb - 100
                      )
                    );


                  $('#bb_ideal')
                    .val(
                      bbIdeal.toFixed(1)
                    );

                }


                /* =========================================================
                   PERUBAHAN BB
                ========================================================= */

                function hitungPerubahanBB() {

                  const bb =
                    parseFloat(
                      $('#bb').val()
                    );


                  const bbBiasa =
                    parseFloat(
                      $('#bb_biasa').val()
                    );


                  if (
                    isNaN(bb) ||
                    isNaN(bbBiasa) ||
                    bbBiasa <= 0
                  ) {

                    $('#perubahan_bb').val('');
                    $('#persen_perubahan_bb').val('');

                    return;

                  }


                  const perubahan =
                    bb -
                    bbBiasa;


                  const persen =
                    (
                      Math.abs(
                        perubahan
                      ) /
                      bbBiasa
                    ) *
                    100;


                  $('#perubahan_bb')
                    .val(
                      perubahan.toFixed(1)
                    );


                  $('#persen_perubahan_bb')
                    .val(
                      persen.toFixed(1)
                    );

                }


                /* =========================================================
                   EVENT ANTROPOMETRI
                ========================================================= */

                $('#bb, #tb')
                  .on(
                    'input',
                    function() {

                      hitungIMT();

                      hitungBBIdeal();

                      hitungPerubahanBB();

                    }
                  );


                $('#bb_biasa')
                  .on(
                    'input',
                    function() {

                      hitungPerubahanBB();

                    }
                  );


                /* =========================================================
                   PES DIAGNOSIS GENERATOR
                ========================================================= */

                function generatePES() {

                  const problem =
                    $.trim(
                      $('#diagnosis_problem')
                      .val()
                    );


                  const etiologi =
                    $.trim(
                      $('#diagnosis_etiologi')
                      .val()
                    );


                  const tanda =
                    $.trim(
                      $('#diagnosis_tanda')
                      .val()
                    );


                  let hasil = '';


                  if (problem) {

                    hasil +=
                      problem;

                  }


                  if (etiologi) {

                    hasil +=
                      ' berkaitan dengan ' +
                      etiologi;

                  }


                  if (tanda) {

                    hasil +=
                      ' dibuktikan dengan ' +
                      tanda;

                  }


                  $('#diagnosis_pes')
                    .val(
                      hasil
                    );

                }


                $('#diagnosis_problem, #diagnosis_etiologi, #diagnosis_tanda')
                  .on(
                    'input',
                    function() {

                      generatePES();

                    }
                  );


                /* =========================================================
                   TAMBAH RECALL
                ========================================================= */

                $('#btnTambahRecall')
                  .on(
                    'click',
                    function() {


                      const row = `

          <tr>

            <td>

              <input
                type="time"
                class="form-control"
                name="recall_waktu[]">

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="recall_makanan[]"
                placeholder="Makanan / minuman">

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="recall_jumlah[]">

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="recall_urt[]"
                placeholder="URT">

            </td>


            <td class="text-center">

              <button
                type="button"
                class="btn btn-sm btn-outline-danger btnHapusRecall">

                <i class="fas fa-trash"></i>

              </button>

            </td>

          </tr>

        `;


                      $('#recallBody')
                        .append(
                          row
                        );

                    }
                  );


                /* =========================================================
                   HAPUS RECALL
                ========================================================= */

                $(document)
                  .on(
                    'click',
                    '.btnHapusRecall',
                    function() {

                      const jumlah =
                        $('#recallBody tr')
                        .length;


                      if (
                        jumlah <= 1
                      ) {

                        $(this)
                          .closest('tr')
                          .find('input')
                          .val('');

                        return;

                      }


                      $(this)
                        .closest('tr')
                        .remove();

                    }
                  );


                /* =========================================================
                   TAMBAH MONITORING
                ========================================================= */

                $('#btnTambahMonitoring')
                  .on(
                    'click',
                    function() {


                      const row = `

          <tr>

            <td>

              <input
                type="text"
                class="form-control"
                name="monitoring_parameter[]">

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="monitoring_target[]">

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="monitoring_hasil[]">

            </td>


            <td>

              <input
                type="date"
                class="form-control"
                name="monitoring_tanggal[]">

            </td>


            <td>

              <select
                class="form-select"
                name="monitoring_status[]">

                <option value="">
                  Pilih
                </option>

                <option value="Membaik">
                  Membaik
                </option>

                <option value="Tetap">
                  Tetap
                </option>

                <option value="Memburuk">
                  Memburuk
                </option>

              </select>

            </td>


            <td>

              <input
                type="text"
                class="form-control"
                name="monitoring_keterangan[]">

            </td>


            <td class="text-center">

              <button
                type="button"
                class="btn btn-sm btn-outline-danger btnHapusMonitoring">

                <i class="fas fa-trash"></i>

              </button>

            </td>

          </tr>

        `;


                      $('#monitoringBody')
                        .append(
                          row
                        );

                    }
                  );


                /* =========================================================
                   HAPUS MONITORING
                ========================================================= */

                $(document)
                  .on(
                    'click',
                    '.btnHapusMonitoring',
                    function() {

                      const jumlah =
                        $('#monitoringBody tr')
                        .length;


                      if (
                        jumlah <= 1
                      ) {

                        $(this)
                          .closest('tr')
                          .find('input')
                          .val('');

                        return;

                      }


                      $(this)
                        .closest('tr')
                        .remove();

                    }
                  );


                /* =========================================================
                   SIMPAN
                ========================================================= */

                $('#btnSimpanGizi')
                  .on(
                    'click',
                    function() {


                      /*
                       * Untuk sementara kumpulkan data
                       * terlebih dahulu.
                       *
                       * Nanti dapat diarahkan ke:
                       * nutritionAssessmentController.php
                       */

                      const data = {

                        id_visit: '<?= htmlspecialchars($no ?? '') ?>',

                        id_patient: '<?= htmlspecialchars($rm ?? '') ?>',

                        tanggal_asesmen: $('#gizi_tanggal_asesmen')
                          .val(),

                        risiko_gizi: $('#risiko_gizi')
                          .val(),

                        bb: $('#bb')
                          .val(),

                        tb: $('#tb')
                          .val(),

                        imt: $('#imt')
                          .val(),

                        kategori_imt: $('#kategori_imt')
                          .val(),

                        bb_ideal: $('#bb_ideal')
                          .val(),

                        bb_biasa: $('#bb_biasa')
                          .val(),

                        perubahan_bb: $('#perubahan_bb')
                          .val(),

                        persen_perubahan_bb: $('#persen_perubahan_bb')
                          .val(),

                        kesimpulan_asesmen: $('#kesimpulan_asesmen')
                          .val(),

                        diagnosis_domain: $('#diagnosis_domain')
                          .val(),

                        diagnosis_problem: $('#diagnosis_problem')
                          .val(),

                        diagnosis_etiologi: $('#diagnosis_etiologi')
                          .val(),

                        diagnosis_tanda: $('#diagnosis_tanda')
                          .val(),

                        diagnosis_pes: $('#diagnosis_pes')
                          .val(),

                        jenis_diet: $('#jenis_diet')
                          .val(),

                        target_energi: $('#target_energi')
                          .val(),

                        target_protein: $('#target_protein')
                          .val(),

                        target_lemak: $('#target_lemak')
                          .val(),

                        target_karbohidrat: $('#target_karbohidrat')
                          .val(),

                        target_cairan: $('#target_cairan')
                          .val(),

                        bentuk_makanan: $('#bentuk_makanan')
                          .val(),

                        modifikasi_diet: $('#modifikasi_diet')
                          .val(),

                        instruksi_diet: $('#instruksi_diet')
                          .val(),

                        evaluasi_gizi: $('#evaluasi_gizi')
                          .val(),

                        rencana_tindak_lanjut: $('#rencana_tindak_lanjut')
                          .val(),

                        tanggal_kontrol_gizi: $('#tanggal_kontrol_gizi')
                          .val(),

                        status: $('#status_asesmen_gizi')
                          .val()

                      };


                      console.log(
                        'DATA RME GIZI:',
                        data
                      );


                      Swal.fire({

                        icon: 'success',

                        title: 'Data Siap Disimpan',

                        text: 'Form RME Gizi berhasil dikumpulkan. Controller dapat dihubungkan pada tahap berikutnya.',

                        confirmButtonText: 'OK'

                      });

                    }
                  );


              });
            </script>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php
  require 'library.php';
  ?>
</body>

<?php
$id_patient = $datapatient['id_patient'];
?>

</html>