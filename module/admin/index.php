<?php
$title = 'Dashboard';
require '../../controller/view.php';
// Dulu di sini ada COUNT(*) seluruh pasien_visit & SUM seluruh pasien_billing (214 rb+ baris) untuk SEMUA role di tiap
// buka dashboard, padahal hasilnya ($totalorder, $amount) tidak dipakai di mana pun -> dihapus.
$idcDash = (int) ($_SESSION['id_customer'] ?? 0);
// peringatan "klinik belum dibuat" hanya relevan untuk akun faskes (superadmin id_customer=0 tidak punya klinik);
// dulu mengambil baris setting_clinic MANA PUN (tanpa filter faskes) sehingga peringatan tidak pernah muncul.
$klinikAda = true;
if ($idcDash > 0) {
  $stK = $koneksi->prepare("SELECT 1 FROM setting_clinic WHERE id_customer = ? LIMIT 1");
  $stK->bind_param('i', $idcDash);
  $stK->execute();
  $klinikAda = (bool) $stK->get_result()->fetch_row();
  $stK->close();
}
$hariId = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$blnId = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggalHariIni = $hariId[(int) date('w')] . ', ' . date('d') . ' ' . $blnId[(int) date('n')] . ' ' . date('Y');
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
          if (!$klinikAda) { ?>
            <div class="alert alert-danger" role="alert">
              Klinik Anda Belum Dibuat , maka silahkan lakukan pengaturan terlebih dahulu di menu <a href="module/admin/setting">
                <strong>Pengaturan Klinik</strong>
              </a>
            </div>
          <?php   }
          ?>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>👋 Selamat Datang, <?= htmlspecialchars((string) ($_SESSION['fullname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5>
            <span><?= $tanggalHariIni ?></span>
          </div>
          <!--  Row 1 -->
          <?php
          if ($role == 'admin') {
            require '../dashboard/dashboard-admin.php';
            // require '../dashboard/welcome-page.php';
          } else if ($role == 'receptionis') {
            require '../dashboard/dashboard-admisi.php';
          } else if ($role == 'dokter') {
            require '../dashboard/dashboard-dokter.php';
          } else if ($role == 'apoteker') {
            require '../dashboard/dashboard-farmasi.php';
          } else if ($role == 'kasir') {
            require '../dashboard/dashboard-kasir.php';
          } else if ($role == 'perawat') {
            require '../dashboard/dashboard-perawat.php';
          } else if ($role == 'superadmin') {
            require '../dashboard/dashboard-administrator.php';
          } else if ($role == 'analislab') {
            require '../dashboard/dashboard-analislab.php';
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