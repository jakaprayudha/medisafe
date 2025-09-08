<?php
$title = 'Dokter';
require '../../controller/view.php';
if (!isset($_SESSION['id_doctor'])) {
  header("Location: module/admin/dokterDetail"); // kembali kalau session kosong
  exit;
}
$id_doctor = $_SESSION['id_doctor'];
$checkdokter = mysqli_query($koneksi, "SELECT * FROM ms_doctor d LEFT JOIN ms_poli p ON d.doctor_spesialis = p.id_poli WHERE d.id_doctor = '$id_doctor'");
$datadokter = mysqli_fetch_array($checkdokter);
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
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Identitas</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Jadwal</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Upload File</button>
            </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
              <div class="row">
                <div class="col-lg-12 d-flex align-items-stretch">
                  <div class="card w-100">
                    <div class="card-body p-4">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-semibold">Identitas Dokter</h5>
                        <div class="d-flex ms-auto gap-2">
                          <button class="btn btn-light" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Kembali
                          </button>
                        </div>
                      </div>

                      <form id="doctorForm">
                        <input type="hidden" name="id_doctor" id="id_doctor">

                        <div class="row">
                          <!-- NIK -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_nik" class="form-label required">NIK</label>
                            <input type="text" name="doctor_nik" id="doctor_nik" class="form-control" required>
                          </div>

                          <!-- STR -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_str" class="form-label">No. STR</label>
                            <input type="text" name="doctor_str" id="doctor_str" class="form-control">
                          </div>

                          <!-- Nama Dokter -->
                          <div class="col-md-6 mb-3">
                            <label for="doctor_name" class="form-label required">Nama Dokter</label>
                            <input type="text" name="doctor_name" id="doctor_name" class="form-control" required>
                          </div>

                          <!-- Spesialis -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_spesialis" class="form-label required">Spesialis (Poli)</label>
                            <select name="doctor_spesialis" id="doctor_spesialis" required class="form-select">
                              <option value="">PILIH</option>
                              <?php
                              $getpoli = tampildata("SELECT * FROM ms_poli ORDER BY poliklinik ASC");
                              foreach ($getpoli as $poli): ?>
                                <option value="<?= $poli['id_poli'] ?>"><?= $poli['poliklinik'] ?></option>
                              <?php endforeach ?>
                            </select>
                          </div>

                          <!-- Subspesialis -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_subspesialis" class="form-label">Sub Spesialis</label>
                            <input type="text" name="doctor_subspesialis" id="doctor_subspesialis" class="form-control">
                          </div>

                          <!-- Kategori -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_category" class="form-label required">Kategori</label>
                            <select name="doctor_category" id="doctor_category" required class="form-select">
                              <option value="Umum">Dokter Umum</option>
                              <option value="Spesialis">Dokter Spesialis</option>
                            </select>
                          </div>

                          <!-- Status -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_status" class="form-label required">Status</label>
                            <select name="doctor_status" id="doctor_status" class="form-select" required>
                              <option value="1">Aktif</option>
                              <option value="0">Non Aktif</option>
                            </select>
                          </div>


                          <!-- Telepon -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_phone" class="form-label required">Telepon</label>
                            <input type="tel" name="doctor_phone" id="doctor_phone" class="form-control" required>
                          </div>

                          <!-- Email -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_mail" class="form-label">Email</label>
                            <input type="email" name="doctor_mail" id="doctor_mail" class="form-control">
                          </div>


                          <!-- Gender -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_gender" class="form-label required">Jenis Kelamin</label>
                            <select name="doctor_gender" id="doctor_gender" class="form-select" required>
                              <option value="">Pilih</option>
                              <option value="Laki-laki">Laki-laki</option>
                              <option value="Perempuan">Perempuan</option>
                            </select>
                          </div>

                          <!-- Wilayah -->
                          <div class="col-md-3 mb-3">
                            <label for="doctor_region" class="form-label">Agama</label>
                            <select name="doctor_region" id="doctor_region" class="form-select">
                              <option value="">Pilih</option>
                              <option value="Islam">Islam</option>
                              <option value="Kristen">Kristen</option>
                              <option value="Katholik">Katholik</option>
                              <option value="Hindu">Hindu</option>
                              <option value="Budha">Budha</option>
                              <option value="Konghucu">Konghucu</option>
                            </select>
                          </div>

                          <!-- Alamat -->
                          <div class="col-md-12 mb-3">
                            <label for="doctor_address" class="form-label">Alamat</label>
                            <textarea name="doctor_address" id="doctor_address" class="form-control"></textarea>
                          </div>
                        </div>

                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                          </button>
                        </div>
                      </form>

                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
              <div class="row">
                <div class="col-lg-12 d-flex align-items-stretch">
                  <div class="card w-100">
                    <div class="card-body p-4">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-semibold">Data Dokter</h5>
                        <!-- Grup tombol di sisi kanan -->
                        <div class="d-flex ms-auto gap-2">
                          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#doctorModal"><i class="fas fa-plus"></i> Tambah</button>
                        </div>
                      </div>
                      <div class="table-responsive" data-simplebar>
                        <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                          <thead>
                            <tr>
                              <th scope="col" class="text-dark fw-normal">Nama Dokter</th>
                              <th class="text-dark fw-normal">Kategori</th>
                              <th class="text-dark fw-normal">Spesialis</th>
                              <th class="text-dark fw-normal">No.Handphone</th>
                              <th class="text-dark fw-normal">Email</th>
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
            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">...</div>
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