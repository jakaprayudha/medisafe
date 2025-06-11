<?php
$title = 'Dashboard';
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
          $check = mysqli_query($koneksi, "SELECT * FROM setting_bisnis LIMIT 1");
          $data = mysqli_fetch_array($check);
          if ($data == NULL) { ?>
            <div class="alert alert-danger" role="alert">
              Bisnis Anda Belum Dibuat , maka silahkan lakukan pengaturan terlebih dahulu di menu <a href="module/admin/setting">
                <strong>Pengaturan Bisnis</strong>
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
          <div class="row">
            <div class="col-lg-6 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h5 class="card-title fw-semibold mb-4">Revenue by Tenant</h5>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal ps-0">Tenant
                          </th>
                          <th scope="col" class="text-dark fw-normal">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($gettenant as $tenant): ?>
                          <tr>
                            <td class="ps-0">
                              <div class="d-flex align-items-center gap-6">
                                <img src="assets/images/products/dash-prd-3.jpg" alt="prd1" width="48"
                                  class="rounded" />
                                <div>
                                  <h6 class="mb-0"><?= $tenant['tenant_name'] ?></h6>
                                  <span><?= $tenant['owner'] ?></span>
                                </div>
                              </div>
                            </td>
                            <td>
                              <?php
                              $totalamountbytenant = mysqli_query($koneksi, "SELECT SUM(price*qty) as total FROM transaction_order INNER JOIN product ON transaction_order.id_product=product.id INNER JOIN tenant ON product.id_tenant=tenant.id WHERE transaction_order.status_item!=0 AND tenant.id='" . $tenant['id'] . "'");
                              $dataamounttenant = mysqli_fetch_array($totalamountbytenant);
                              $amounttenant = $dataamounttenant['total'];
                              ?>
                              <?= number_format($amounttenant) ?>
                            </td>
                          </tr>
                        <?php endforeach ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h5 class="card-title fw-semibold mb-4">Revenue by Product</h5>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal ps-0">Product
                          </th>
                          <th scope="col" class="text-dark fw-normal">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($getproduct as $product): ?>
                          <tr>
                            <td class="ps-0">
                              <div class="d-flex align-items-center gap-6">
                                <img src="assets/images/products/<?= $product['image_product'] ?>" alt="prd1" width="48"
                                  class="rounded" />
                                <div>
                                  <h6 class="mb-0"><?= $product['product_name'] ?></h6>
                                  <span><?= $product['description'] ?></span>
                                </div>
                              </div>
                            </td>
                            <td>
                              <?php
                              $totalamountbyproduct = mysqli_query($koneksi, "SELECT SUM(price*qty) as total FROM transaction_order INNER JOIN product ON transaction_order.id_product=product.id INNER JOIN tenant ON product.id_tenant=tenant.id WHERE transaction_order.status_item!=0 AND product.id='" . $product['id'] . "'");
                              $dataamountproduct = mysqli_fetch_array($totalamountbyproduct);
                              $amountproduct = $dataamountproduct['total'];
                              ?>
                              <?= number_format($amountproduct) ?>
                            </td>
                          </tr>
                          <tr>
                          <?php endforeach ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- <div class="row">
            <div class="col-lg-4">
              <div class="card overflow-hidden hover-img">
                <div class="position-relative">
                  <a href="javascript:void(0)">
                    <img src="assets/images/blog/blog-img1.jpg" class="card-img-top" alt="matdash-img">
                  </a>
                  <span
                    class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2
                    min Read</span>
                  <img src="assets/images/profile/user-3.jpg" alt="matdash-img"
                    class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40"
                    height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                </div>
                <div class="card-body p-4">
                  <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Social</span>
                  <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">As yen tumbles, gadget-loving
                    Japan goes
                    for secondhand iPhones</a>
                  <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-eye text-dark fs-5"></i>9,125
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-message-2 text-dark fs-5"></i>3
                    </div>
                    <div class="d-flex align-items-center fs-2 ms-auto">
                      <i class="ti ti-point text-dark"></i>Mon, Dec 19
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card overflow-hidden hover-img">
                <div class="position-relative">
                  <a href="javascript:void(0)">
                    <img src="assets/images/blog/blog-img2.jpg" class="card-img-top" alt="matdash-img">
                  </a>
                  <span
                    class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2
                    min Read</span>
                  <img src="assets/images/profile/user-2.jpg" alt="matdash-img"
                    class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40"
                    height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                </div>
                <div class="card-body p-4">
                  <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Gadget</span>
                  <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">Intel loses bid to revive
                    antitrust case
                    against patent foe Fortress</a>
                  <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-eye text-dark fs-5"></i>4,150
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-message-2 text-dark fs-5"></i>38
                    </div>
                    <div class="d-flex align-items-center fs-2 ms-auto">
                      <i class="ti ti-point text-dark"></i>Sun, Dec 18
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card overflow-hidden hover-img">
                <div class="position-relative">
                  <a href="javascript:void(0)">
                    <img src="assets/images/blog/blog-img3.jpg" class="card-img-top" alt="matdash-img">
                  </a>
                  <span
                    class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2
                    min Read</span>
                  <img src="assets/images/profile/user-3.jpg" alt="matdash-img"
                    class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40"
                    height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                </div>
                <div class="card-body p-4">
                  <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Health</span>
                  <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">COVID outbreak deepens as more
                    lockdowns
                    loom in China</a>
                  <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-eye text-dark fs-5"></i>9,480
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-message-2 text-dark fs-5"></i>12
                    </div>
                    <div class="d-flex align-items-center fs-2 ms-auto">
                      <i class="ti ti-point text-dark"></i>Sat, Dec 17
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> -->
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