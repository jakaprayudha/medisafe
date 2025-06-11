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
                              <label for="product_code" class="col-sm-2 col-form-label">Kode Inventory</label>
                              <div class="col-sm-10">
                                <input type="text" name="product_code" class="form-control" id="product_code">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_barcode" class="col-sm-2 col-form-label">Barcode</label>
                              <div class="col-sm-10">
                                <input type="text" name="product_barcode" class="form-control" id="product_barcode">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_name" class="col-sm-2 col-form-label">Nama Barang</label>
                              <div class="col-sm-10">
                                <input type="text" name="product_name" class="form-control" id="product_name">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="id_merk" class="col-sm-2 col-form-label">Merk</label>
                              <div class="col-sm-10">
                                <select name="id_merk" id="id_merk" class="form-select">
                                  <option value="">PILIH</option>
                                  <?php
                                  $getMerk = mysqli_query($koneksi, "SELECT * FROM ms_merk ORDER BY merk_name ASC");
                                  while ($merk = mysqli_fetch_array($getMerk)) { ?>
                                    <option value="<?= $merk['id_merk'] ?>"><?= $merk['merk_name'] ?></option>
                                  <?php } ?>
                                </select>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="id_category" class="col-sm-2 col-form-label">Kategori</label>
                              <div class="col-sm-10">
                                <select name="id_category" id="id_category" class="form-select">
                                  <option value="">PILIH</option>
                                  <?php
                                  $getCategory = mysqli_query($koneksi, "SELECT * FROM ms_product_category ORDER BY category_name ASC");
                                  while ($category = mysqli_fetch_array($getCategory)) { ?>
                                    <option value="<?= $category['id_category'] ?>"><?= $category['category_name'] ?></option>
                                  <?php } ?>
                                </select>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_description" class="col-sm-2 col-form-label">Deskripsi</label>
                              <div class="col-sm-10">
                                <textarea name="product_description" class="form-control" rows="5" id="product_description"></textarea>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_color" class="col-sm-2 col-form-label">Spesifikasi</label>
                              <div class="col-sm-3">
                                <input type="text" name="product_color" placeholder="Warna" class="form-control" id="product_color">
                              </div>
                              <div class="col-sm-3">
                                <input type="text" name="product_size" placeholder="Ukuran" class="form-control" id="product_size">
                              </div>
                              <div class="col-sm-4">
                                <input type="text" name="product_any" placeholder="Lainnya" class="form-control" id="product_any">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_sn" class="col-sm-2 col-form-label">Serial Number</label>
                              <div class="col-sm-10">
                                <input type="text" name="product_sn" class="form-control" id="product_sn">
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="product_base" class="col-sm-2 col-form-label">Harga Modal</label>
                              <div class="col-sm-10">
                                <input type="number" name="product_base" class="form-control" id="product_base">
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
      fetch("controller/product/productUpdate", {
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