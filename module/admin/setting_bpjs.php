<?php
session_start();
$title = 'Setting Bridging BPJS';
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
                    <h5 class="card-title fw-semibold">Bridging BPJS Kesehatan</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Login Akun P-Care</button>
                      <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Antrean Online</button>
                      <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">I Care</button>
                    </div>
                  </nav>
                  <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                      <div class="alert mt-4 alert-warning" role="alert">
                        Untuk usernam dan password pcare apabila anda telah melakukan perubahan di aplikasi pcare, silakan update juga di halaman ini agar aplikasi dapat terhubung dengan pcare dengan baik karena apabila tidak diperbarui maka aplikasi tidak dapat terhubung dengan pcare dan fitur yang terhubung dengan pcare tidak dapat digunakan dengan baik. Terima kasih.
                      </div>
                      <div class="row mt-4">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="username_pcare" class="form-label">Username Pcare</label>
                            <input type="text" class="form-control" id="username_pcare" name="username_pcare" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="password_pcare" class="form-label">Password Pcare</label>
                            <input type="text" class="form-control" id="password_pcare" name="password_pcare" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="consumer_id" class="form-label">Kode PPK</label>
                            <input type="text" class="form-control bg-light" id="kodePPK" name="kodePPK" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="consumer_id" class="form-label">Consumer ID</label>
                            <input type="text" class="form-control bg-light" id="consumer_id" name="consumer_id" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="secret_key" class="form-label">Secret Key</label>
                            <input type="text" class="form-control bg-light" id="secret_key" name="secret_key" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="apps_code" class="form-label">Kode Aplikasi (Services)</label>
                            <input type="text" class="form-control bg-light" id="apps_code" name="apps_code" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" class="form-control bg-light" id="user_key" name="user_key" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="kode_provider" class="form-label">Kode Provider</label>
                            <input type="text" class="form-control bg-light" id="kode_provider" name="kode_provider" readonly>
                          </div>
                        </div>
                      </div>
                      <button class="btn btn-primary col-12">Simpan</button>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                      <div class="alert alert-danger" role="alert">
                        Proses Integrasi (Bridging) Antrean Online sedang dalam tahap koordinasi menunggu jadwal UAT, untuk informasi lebih lanjut silakan hubungi tim IT kami. Terima kasih atas pengertiannya.
                      </div>
                      <!-- <div class="row mt-4">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="username_antrol" class="form-label">Username Antrean Online</label>
                            <input type="text" class="form-control" id="username_antrol" name="username_antrol" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="password_antrol" class="form-label">Password Antrean Online</label>
                            <input type="text" class="form-control" id="password_antrol" name="password_antrol" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="consumer_id" class="form-label">Consumer ID</label>
                            <input type="text" class="form-control" id="consumer_id" name="consumer_id" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="secret_key" class="form-label">Secret Key</label>
                            <input type="text" class="form-control" id="secret_key" name="secret_key" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" class="form-control" id="user_key" name="user_key" required>
                          </div>
                        </div>
                      </div>
                      <button class="btn btn-primary col-12">Simpan</button> -->
                    </div>
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                      <div class="alert alert-danger" role="alert">
                        Proses Integrasi (Bridging) I Care sedang dalam tahap koordinasi menunggu jadwal UAT, untuk informasi lebih lanjut silakan hubungi tim IT kami. Terima kasih atas pengertiannya.
                      </div>
                      <!-- <div class="row mt-4">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="username_antrol" class="form-label">Username I Care</label>
                            <input type="text" class="form-control" id="username_antrol" name="username_antrol" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="password_antrol" class="form-label">Password I Care</label>
                            <input type="text" class="form-control" id="password_antrol" name="password_antrol" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="consumer_id" class="form-label">Consumer ID</label>
                            <input type="text" class="form-control" id="consumer_id" name="consumer_id" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="secret_key" class="form-label">Secret Key</label>
                            <input type="text" class="form-control" id="secret_key" name="secret_key" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" class="form-control" id="user_key" name="user_key" required>
                          </div>
                        </div>
                      </div> -->
                      <!-- <button class="btn btn-primary col-12">Simpan</button> -->
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
<script>
  document.addEventListener("DOMContentLoaded", function() {
    fetch('controller/master/pcareSetting.php')
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          const data = res.data;

          document.getElementById('username_pcare').value = data.username ?? '';
          document.getElementById('password_pcare').value = data.password ?? '';
          document.getElementById('consumer_id').value = data.KodePPK ?? '';
          document.getElementById('kodePPK').value = data.KodePPK ?? '';
          document.getElementById('secret_key').value = data.secret_key ?? '';
          document.getElementById('user_key').value = data.user_key ?? '';
          document.getElementById('kode_provider').value = data.KodePPK ?? '';

          // tambahan kalau mau
          document.getElementById('apps_code').value = data.service_name ?? '';
        }
      })
      .catch(err => console.error(err));
  });
</script>

<script>
  document.querySelector('.btn-primary').addEventListener('click', function(e) {
    e.preventDefault();

    const username = document.getElementById('username_pcare').value;
    const password = document.getElementById('password_pcare').value;

    fetch('controller/master/pcareUpdate.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
      })
      .then(res => res.json())
      .then(res => {
        alert(res.message);
      })
      .catch(err => console.error(err));
  });
</script>

</html>