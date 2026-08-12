<?php
$title = 'Data Surat Surat FKTP';
require '../../controller/view.php';
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

          <!-- ==========================================
             HEADER
        =========================================== -->

          <div class="row mb-4">

            <div class="col-12">

              <div class="d-flex align-items-center justify-content-between">

                <div>
                  <h4 class="fw-semibold mb-1">
                    Surat &amp; Dokumen
                  </h4>

                  <p class="text-muted mb-0">
                    Kelola dan cetak surat keterangan pasien berdasarkan pelayanan medis.
                  </p>
                </div>

              </div>

            </div>

          </div>


          <!-- ==========================================
             CARD SURAT
        =========================================== -->

          <div class="row g-4">


            <!-- ======================================
                 1. SURAT KETERANGAN SEHAT
            ======================================= -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                  <div class="d-flex align-items-start mb-4">

                    <div class="d-flex align-items-center gap-3">

                      <!-- ICON -->

                      <div class="rounded-circle bg-success-subtle
                                            d-flex align-items-center justify-content-center"
                        style="
                                        width:52px;
                                        height:52px;
                                        flex:0 0 52px;
                                     ">

                        <iconify-icon
                          icon="material-symbols:health-and-safety-outline"
                          width="28"
                          class="text-success">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">
                          Surat Keterangan Sehat
                        </h5>

                        <span class="badge bg-success-subtle text-success">
                          Kesehatan
                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat yang menerangkan kondisi kesehatan
                    pasien berdasarkan hasil pemeriksaan dokter.

                  </p>


                  <!-- FOOTER -->

                  <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Surat Kesehatan

                    </small>


                    <!-- BUTTON -->

                    <a href="module/letter/form-sks"
                      class="btn btn-success btn-sm"
                      style="
                                    width:145px;
                                    height:46px;
                                    padding:0 15px;
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    white-space:nowrap;
                               ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- ======================================
                 2. SURAT KETERANGAN SAKIT
            ======================================= -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                  <div class="d-flex align-items-start mb-4">

                    <div class="d-flex align-items-center gap-3">

                      <!-- ICON -->

                      <div class="rounded-circle bg-danger-subtle
                                            d-flex align-items-center justify-content-center"
                        style="
                                        width:52px;
                                        height:52px;
                                        flex:0 0 52px;
                                     ">

                        <iconify-icon
                          icon="material-symbols:sick-outline"
                          width="28"
                          class="text-danger">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">
                          Surat Keterangan Sakit
                        </h5>

                        <span class="badge bg-danger-subtle text-danger">
                          Istirahat
                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat keterangan yang menyatakan pasien
                    memerlukan waktu istirahat berdasarkan
                    pemeriksaan dokter.

                  </p>


                  <!-- FOOTER -->

                  <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Surat Medis

                    </small>


                    <!-- BUTTON -->

                    <a href="module/letter/form-sick"
                      class="btn btn-danger btn-sm"
                      style="
                                    width:145px;
                                    height:46px;
                                    padding:0 15px;
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    white-space:nowrap;
                               ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- ======================================
                 3. SURAT KETERANGAN BEROBAT
            ======================================= -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                  <div class="d-flex align-items-start mb-4">

                    <div class="d-flex align-items-center gap-3">

                      <!-- ICON -->

                      <div class="rounded-circle bg-primary-subtle
                                            d-flex align-items-center justify-content-center"
                        style="
                                        width:52px;
                                        height:52px;
                                        flex:0 0 52px;
                                     ">

                        <iconify-icon
                          icon="material-symbols:medical-information-outline"
                          width="28"
                          class="text-primary">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">
                          Surat Keterangan Berobat
                        </h5>

                        <span class="badge bg-primary-subtle text-primary">
                          Kunjungan
                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat yang menerangkan bahwa pasien telah
                    datang dan mendapatkan pelayanan kesehatan
                    pada faskes.

                  </p>


                  <!-- FOOTER -->

                  <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Bukti Kunjungan

                    </small>


                    <!-- BUTTON -->

                    <a href="module/admisi/form-letter/treatment"
                      class="btn btn-primary btn-sm"
                      style="
                                    width:145px;
                                    height:46px;
                                    padding:0 15px;
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    white-space:nowrap;
                               ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- ======================================
                 4. CATATAN KEMATIAN
            ======================================= -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                  <div class="d-flex align-items-start mb-4">

                    <div class="d-flex align-items-center gap-3">

                      <!-- ICON -->

                      <div class="rounded-circle bg-dark-subtle
                                            d-flex align-items-center justify-content-center"
                        style="
                                        width:52px;
                                        height:52px;
                                        flex:0 0 52px;
                                     ">

                        <iconify-icon
                          icon="material-symbols:deceased-outline"
                          width="28"
                          class="text-dark">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">
                          Catatan Kematian
                        </h5>

                        <span class="badge bg-dark-subtle text-dark">
                          Kematian
                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Dokumen catatan kematian yang memuat
                    identitas dan informasi waktu serta tempat
                    kematian pasien.

                  </p>


                  <!-- FOOTER -->

                  <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Catatan Medis

                    </small>


                    <!-- BUTTON -->

                    <a href="module/admisi/form-letter/death"
                      class="btn btn-dark btn-sm"
                      style="
                                    width:145px;
                                    height:46px;
                                    padding:0 15px;
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    white-space:nowrap;
                               ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- ======================================
                 5. PEMERIKSAAN MATA
            ======================================= -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                  <div class="d-flex align-items-start mb-4">

                    <div class="d-flex align-items-center gap-3">

                      <!-- ICON -->

                      <div class="rounded-circle bg-warning-subtle
                                            d-flex align-items-center justify-content-center"
                        style="
                                        width:52px;
                                        height:52px;
                                        flex:0 0 52px;
                                     ">

                        <iconify-icon
                          icon="material-symbols:visibility-outline"
                          width="28"
                          class="text-warning">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">
                          Pemeriksaan Mata
                        </h5>

                        <span class="badge bg-warning-subtle text-warning">
                          Pemeriksaan
                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Hasil pemeriksaan mata meliputi visus,
                    refraksi, pemeriksaan mata, tanda vital,
                    serta pemeriksaan laboratorium penunjang.

                  </p>


                  <!-- FOOTER -->

                  <div class="d-flex align-items-center justify-content-between">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:visibility-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Hasil Pemeriksaan

                    </small>


                    <!-- BUTTON -->

                    <a href="module/admisi/form-letter/eye"
                      class="btn btn-warning btn-sm"
                      style="
                                    width:145px;
                                    height:46px;
                                    padding:0 15px;
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                    white-space:nowrap;
                               ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

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
</body>


</html>