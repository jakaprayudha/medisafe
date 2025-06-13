<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM ms_pasien INNER JOIN pasien_visit ON pasien_visit.nomor_rm = ms_pasien.nomor_rm WHERE pasien_visit.nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
if ($data) {
  $tanggal_lahir = new DateTime($data['tanggal_lahir']);
  $tanggal_visit = new DateTime($data['tanggal']);

  $usia = $tanggal_lahir->diff($tanggal_visit);
}

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
            <nav>
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a href="module/admin/pemeriksaan_details?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link ">Pemeriksaan Medis</button>
                </a>
                <a href="module/admin/permintaan_farmasi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link active">Permintaan Farmasi</button>
                </a>
                <a href="module/admin/vaksin?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link">Vaksin</button>
                </a>
                <a href="module/admin/riwayat?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link">Riwayat Pengobatan</button>
                </a>
              </div>
            </nav>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Permintaan Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Item</th>
                          <th class="text-dark fw-normal">Satuan</th>
                          <th class="text-dark fw-normal">Signa</th>
                          <th class="text-dark fw-normal">QTY</th>
                          <th class="text-dark fw-normal">Harga</th>
                          <th class="text-dark fw-normal">Total</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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
</body>



</html>