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
                  <div class="col-lg-12 d-flex align-items-stretch">
                    <div class="card w-100">
                      <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="card-title fw-semibold">Data Barang</h5>
                          <!-- Grup tombol di sisi kanan -->
                          <div class="d-flex ms-auto gap-2">
                            <button class="btn btn-s">Data Update Terakhir : <?= $data['update_at'] ?></button>
                          </div>
                        </div>
                        <form id="inventoryForm">
                          <input type="hidden" name="id_product" id="id_product" value="<?= $_SESSION['id_product'] ?>">
                          <div class="row">
                            <div class="mb-3 row">
                              <label for="product_min" class="col-sm-2 col-form-label">Stock Minumum</label>
                              <div class="col-sm-10">
                                <input type="number" name="product_min" class="form-control" id="product_min">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_max" class="col-sm-2 col-form-label">Stock Maksimum</label>
                              <div class="col-sm-10">
                                <input type="number" name="product_max" class="form-control" id="product_max">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_alert" class="col-sm-2 col-form-label">Stock Alert</label>
                              <div class="col-sm-10">
                                <input type="number" name="product_alert" class="form-control" id="product_alert">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_weight" class="col-sm-2 col-form-label">Produk Weight</label>
                              <div class="col-sm-10">
                                <input type="text" name="product_weight" class="form-control" id="product_weight">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="tax_id" class="col-sm-2 col-form-label">Tax ID</label>
                              <div class="col-sm-10">
                                <input type="text" name="tax_id" class="form-control" id="tax_id">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="id_account" class="col-sm-2 col-form-label">Kode Perkiraan Akun</label>
                              <div class="col-sm-10">
                                <select name="id_account" id="  " class="form-select">
                                  <option value="">PILIH</option>
                                  <?php
                                  $getAccount = mysqli_query($koneksi, "SELECT * FROM ms_account ORDER BY account_number ASC");
                                  while ($account = mysqli_fetch_array($getAccount)) { ?>
                                    <option value="<?= $account['id_account'] ?>"><?= $account['account_number'] ?> - <?= $account['account_name'] ?></option>
                                  <?php } ?>
                                </select>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_status" class="col-sm-2 col-form-label">Status</label>
                              <div class="col-sm-10 d-flex align-items-center">
                                <div class="form-check form-switch">
                                  <!-- Hidden fallback jika tidak dicentang -->
                                  <input type="hidden" name="product_status" value="0">
                                  <input class="form-check-input" type="checkbox" name="product_status" id="product_status" value="1">
                                  <label class="form-check-label" for="product_status">
                                    <span id="statusLabel">Tidak Aktif</span>
                                  </label>
                                </div>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="" class="col-sm-2"></label>
                              <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                              </div>
                            </div>
                          </div>
                        </form>
                        <div id="responseMessage"></div>
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
  document.addEventListener("DOMContentLoaded", function() {
    const kodeInput = document.getElementById("id_product");
    // Load data jika dalam mode edit
    if (kodeInput && kodeInput.value.trim() !== "") {
      const id_product = kodeInput.value.trim();
      const fetchUrl = `controller/product/productDetailsController?id_product=${id_product}`;
      console.log("Fetch URL:", fetchUrl);

      fetch(fetchUrl)
        .then(response => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then(result => {
          console.log("Fetch GET result:", result);
          if (result.success && result.data) {
            const data = result.data;
            for (const key in data) {
              const input = document.getElementById(key);
              if (input) {
                input.value = data[key];
              }
            }
          } else {
            Swal.fire({
              icon: "warning",
              title: "Data tidak ditemukan",
              text: result.message || "Data produk tidak tersedia.",
            });
          }
        })
        .catch(err => {
          console.error("Error loading data:", err);
          Swal.fire({
            icon: "error",
            title: "Gagal Memuat Data",
            text: "Terjadi kesalahan saat memuat data produk.",
          });
        });
    }

    // Submit form simpan
    document.getElementById("inventoryForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch("controller/product/productUpdateAdvanced", {
          method: "POST",
          body: formData,
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            Swal.fire({
              icon: "success",
              title: "Sukses",
              text: result.message || "Data berhasil disimpan!",
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: result.message || "Gagal menyimpan data.",
            });
          }
        })
        .catch(error => {
          console.error("Fetch error:", error);
          Swal.fire({
            icon: "error",
            title: "Kesalahan",
            text: "Terjadi kesalahan saat menyimpan data.",
          });
        });
    });
  });
</script>

</html>