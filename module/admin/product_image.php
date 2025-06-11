<?php
session_start();
$title = 'Sales';
require '../../database/connect.php';
require '../../controller/view.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
if (isset($_POST['id_product'])) {
  $_SESSION['id_product'] = $_POST['id_product'];
}
$checkproduct = mysqli_query($koneksi, "SELECT * FROM ms_product WHERE id_product = '" . $_SESSION['id_product'] . "'");
$data = mysqli_fetch_array($checkproduct);
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .main-content.fade-out {
      opacity: 0;
      transition: opacity 0.5s ease-out;
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
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h5 class="card-title mb-0"><?= $data['product_name'] ?></h5>
                  <small>
                    <?php
                    if ($data['product_status'] == 1) { ?>
                      <span class="badge bg-success mt-1">Active</span>
                    <?php  } else { ?>
                      <span class="badge bg-danger mt-1">Non Active</span>
                    <?php    }
                    ?>

                  </small>
                </div>
                <span class="badge bg-info">Harga Beli: <?= number_format($data['product_base']) ?></span>
              </div>
              <p class="card-text mt-2">Kode: <?= $data['product_code'] ?> | Description: <?= $data['product_description'] ?></p>
            </div>
          </div>
          <div class="main-content">
            <?php
            require 'menu-product.php';
            ?>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="office-tab-pane" role="tabpanel" aria-labelledby="office-tab" tabindex="0">
                <div class="row mt-2">
                  <div class="row">
                    <div class="col-6">
                      <div class="card">
                        <div class="card-header">Form Upload Gambar</div>
                        <div class="card-body">
                          <form id="uploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?= $_SESSION['id_product'] ?>">
                            <div class="mb-3">
                              <label for="gambar" class="form-label">File </label>
                              <input type="file" class="form-control" name="gambar" id="gambar" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                          </form>
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="card">
                        <img id="previewImage" src="uploads/default.png" class="card-img-top" alt="Preview Gambar">
                      </div>
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const productId = <?= json_encode($_SESSION['id_product']) ?>;

    // Fetch image saat halaman dimuat
    fetch(`controller/product/productImageGet?id=${productId}`)
      .then(response => response.json())
      .then(data => {
        console.log('Load image response:', data);
        if (data.status === 'success') {
          document.getElementById('previewImage').src = data.url || 'uploads/default.png';
        }
      })
      .catch(error => console.error('Gagal memuat gambar:', error));

    // Upload image baru
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const url = 'controller/product/productImage.php';

      console.log('Sending to:', url);

      fetch(url, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          console.log('Upload response:', data);

          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Gambar berhasil diupload.', 'success');
            document.getElementById('previewImage').src = data.url;
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          Swal.fire('Terjadi kesalahan!', 'Gagal upload gambar.', 'error');
        });
    });
  });
</script>

</html>