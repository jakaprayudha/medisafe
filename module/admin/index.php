<?php
$title = 'Dashboard';
require '../../controller/view.php';
$checkvisit = mysqli_query($koneksi, "SELECT visit_ID FROM pasien_visit");
$totalorder = mysqli_num_rows($checkvisit);
$checkbilling = mysqli_query($koneksi, "SELECT SUM(billing_price * billing_qty - billing_discount) AS total FROM pasien_billing");
$data = mysqli_fetch_assoc($checkbilling);
$amount = $data['total'] ?? 0; // fallback ke 0 jika hasilnya NULL
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
          $check = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
          $data = mysqli_fetch_array($check);
          if ($data == NULL) { ?>
            <div class="alert alert-danger" role="alert">
              Klinik Anda Belum Dibuat , maka silahkan lakukan pengaturan terlebih dahulu di menu <a href="module/admin/setting">
                <strong>Pengaturan Klinik</strong>
              </a>
            </div>
          <?php   }
          ?>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Selamat Datang, <?= $_SESSION['fullname'] ?></h5>
            <span><?= date('l, d F Y') ?></span>
          </div>
          <!--  Row 1 -->
          <div class="row">
            <div class="col-lg-8 d-flex align-items-strech">
              <div class="card w-100">
                <div class="card-body">
                  <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                    <div class="mb-3 mb-sm-0">
                      <h5 class="card-title fw-semibold">Revenue Forecast </h5>
                    </div>
                    <div>
                      <select class="form-select">
                        <option value="1">March 2024</option>
                        <option value="2">April 2024</option>
                        <option value="3">May 2024</option>
                        <option value="4">June 2024</option>
                      </select>
                    </div>
                  </div>
                  <div id="revenue-forecast"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-6 mb-4 pb-3">
                        <span
                          class="round-48 d-flex align-items-center justify-content-center rounded bg-secondary-subtle">
                          <iconify-icon icon="solar:user-outline" class="fs-6 text-secondary"> </iconify-icon>
                        </span>
                        <h6 class="mb-0 fs-4">Transaction Order</h6>
                      </div>
                      <div class="row">
                        <div class="col-12">
                          <h4><?= number_format($totalorder) ?></h4>
                          <span class="fs-11 text-success fw-semibold">+18%</span> bulan ini
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-6 mb-4">
                        <span
                          class="round-48 d-flex align-items-center justify-content-center rounded bg-danger-subtle">
                          <iconify-icon icon="solar:box-linear" class="fs-6 text-danger"></iconify-icon>
                        </span>
                        <h6 class="mb-0 fs-4">Total Income</h6>
                      </div>
                      <div class="row">
                        <div class="col-12">
                          <h4>IDR <?= number_format($amount) ?></h4>
                          <span class="fs-11 text-success fw-semibold">+27%</span> bulan ini
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php
          require '../../assets/template/footer.php';
          ?>
        </div>
      </div>
    </div>
  </div>
  <?php
  require 'library.php';
  ?>
</body>

</html>