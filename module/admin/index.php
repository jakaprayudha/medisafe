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
          <?php 
          if($role == 'admin'){
            require '../dashboard/dashboard-admin.php';
          }else if($role == 'receptionis'){
            require '../dashboard/dashboard-admisi.php';
          }else if($role == 'dokter'){
            require '../dashboard/dashboard-dokter.php';
          }else if($role == 'apoteker'){
            require '../dashboard/dashboard-farmasi.php';
          }else if($role == 'kasir'){
            require '../dashboard/dashboard-kasir.php';
          } ?>
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