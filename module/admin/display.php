<?php
$title = 'Display';
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
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Display Layanan</h5>
                  </div>

                  <div class="row g-3">

                    <!-- Display Admisi -->
                    <div class="col-md-3">
                      <div class="card border h-100">
                        <div class="card-body text-center">
                          <iconify-icon
                            icon="mdi:monitor"
                            width="40"
                            height="40"
                            class="text-primary mb-3">
                          </iconify-icon>

                          <h6 class="fw-semibold">Display Admisi</h6>
                          <p class="text-muted small">
                            Antrian dan proses pendaftaran pasien
                          </p>

                          <a href="module/display/display-admisi" target="_blank" class="btn btn-outline-primary btn-sm">
                            Buka Display
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- Display Farmasi -->
                    <div class="col-md-3">
                      <div class="card border h-100">
                        <div class="card-body text-center">
                          <iconify-icon
                            icon="mdi:monitor-multiple"
                            width="40"
                            height="40"
                            class="text-success mb-3">
                          </iconify-icon>

                          <h6 class="fw-semibold">Display Farmasi</h6>
                          <p class="text-muted small">
                            Status antrian dan pengambilan obat
                          </p>

                          <a href="module/display/display-farmasi" target="_blank" class="btn btn-outline-success btn-sm">
                            Buka Display
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- Display Poliklinik -->
                    <div class="col-md-3">
                      <div class="card border h-100">
                        <div class="card-body text-center">
                          <iconify-icon
                            icon="mdi:desktop-classic"
                            width="40"
                            height="40"
                            class="text-info mb-3">
                          </iconify-icon>

                          <h6 class="fw-semibold">Display Poliklinik</h6>
                          <p class="text-muted small">
                            Jadwal dokter dan antrian poli
                          </p>

                          <a href="module/display/display-poliklinik" target="_blank" class="btn btn-outline-info btn-sm">
                            Buka Display
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- Display Tempat Tidur -->
                    <div class="col-md-3">
                      <div class="card border h-100">
                        <div class="card-body text-center">
                          <iconify-icon
                            icon="mdi:monitor-dashboard"
                            width="40"
                            height="40"
                            class="text-warning mb-3">
                          </iconify-icon>

                          <h6 class="fw-semibold">Display Tempat Tidur</h6>
                          <p class="text-muted small">
                            Ketersediaan bed rawat inap
                          </p>

                          <a href="module/display/display-applicare" target="_blank" class="btn btn-outline-warning btn-sm">
                            Buka Display
                          </a>
                        </div>
                      </div>
                    </div>

                  </div>
                  <!-- end row -->

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