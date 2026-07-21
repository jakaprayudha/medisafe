<?php
session_start();
$id_customer = $_SESSION['id_customer'];
$title = 'Setting Bridging BPJS';
require '../../controller/view.php';

$antrol = mysqli_fetch_assoc(mysqli_query(
  $koneksi,
  "SELECT * FROM setting_antrol WHERE id_customer = '" . mysqli_real_escape_string($koneksi, $id_customer) . "' LIMIT 1"
));
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
                      <button class="nav-link" id="nav-dokter-tab" data-bs-toggle="tab" data-bs-target="#nav-dokter" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Dokter BPJS</button>
                      <button class="nav-link" id="nav-dokterinternal-tab" data-bs-toggle="tab" data-bs-target="#nav-dokterinternal" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Dokter Internal</button>
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
                      <?php if ($antrol) { ?>
                        <div class="alert alert-success d-flex align-items-center mt-4" role="alert">
                          <i class="fas fa-check-circle me-2"></i>
                          <div>
                            Klinik Anda <strong>telah terhubung</strong> dengan layanan Antrean Online BPJS. Konfigurasi di bawah dikelola oleh tim IT dan bersifat hanya-baca.
                          </div>
                        </div>
                        <div class="row mt-4">
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_username" class="form-label">Username Antrean Online</label>
                              <input type="text" class="form-control bg-light" id="antrol_username" value="<?= htmlspecialchars($antrol['username'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_base_url" class="form-label">Base URL</label>
                              <input type="text" class="form-control bg-light" id="antrol_base_url" value="<?= htmlspecialchars($antrol['base_url'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_service" class="form-label">Service</label>
                              <input type="text" class="form-control bg-light" id="antrol_service" value="<?= htmlspecialchars($antrol['service'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_kodePPK" class="form-label">Kode PPK</label>
                              <input type="text" class="form-control bg-light" id="antrol_kodePPK" value="<?= htmlspecialchars($antrol['kodePPK'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_constid" class="form-label">Consumer ID</label>
                              <input type="text" class="form-control bg-light" id="antrol_constid" value="<?= htmlspecialchars($antrol['constid'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_secretkey" class="form-label">Secret Key</label>
                              <input type="text" class="form-control bg-light" id="antrol_secretkey" value="<?= htmlspecialchars($antrol['secretkey'] ?? '') ?>" readonly>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label for="antrol_userkey" class="form-label">User Key</label>
                              <input type="text" class="form-control bg-light" id="antrol_userkey" value="<?= htmlspecialchars($antrol['userkey'] ?? '') ?>" readonly>
                            </div>
                          </div>
                        </div>
                      <?php } else { ?>
                        <div class="alert alert-danger" role="alert">
                          Proses Integrasi (Bridging) Antrean Online sedang dalam tahap koordinasi menunggu jadwal UAT, untuk informasi lebih lanjut silakan hubungi tim IT kami. Terima kasih atas pengertiannya.
                        </div>
                      <?php } ?>
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
                    <div class="tab-pane fade" id="nav-dokter" role="tabpanel" aria-labelledby="nav-dokter-tab" tabindex="0">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold">
                          <i class="fas fa-user-md me-2 text-primary"></i>Data Dokter
                        </h6>
                        <button class="btn btn-primary btn-sm" id="btnTambah">
                          <i class="fas fa-sync-alt me-1"></i>
                          Sinkron Dokter
                        </button>
                      </div>
                      <div class="table-responsive" data-simplebar>
                        <table class="table table-hover align-middle table-bordered mb-0" id="dokterTable">
                          <thead class="table-light">
                            <tr>
                              <th>Nama Dokter</th>
                              <th class="text-center" width="120">Kode</th>
                              <th class="text-center" width="120">Status</th>
                              <th class="text-center" width="120">Aksi</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $query = mysqli_query($koneksi, "SELECT id, kdDokter, nmDokter, status FROM master_doctor_bpjs WHERE id_customer = '$id_customer' ORDER BY nmDokter ASC");
                            if (mysqli_num_rows($query) > 0) {
                              while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                                <tr>
                                  <td><?= htmlspecialchars($row['nmDokter']) ?></td>
                                  <td class="text-center">
                                    <?= htmlspecialchars($row['kdDokter']) ?>
                                  </td>
                                  <td class="text-center">
                                    <?php if ($row['status'] == 1) { ?>
                                      <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Aktif
                                      </span>
                                    <?php } else { ?>
                                      <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Nonaktif
                                      </span>
                                    <?php } ?>
                                  </td>
                                  <td class="text-center">
                                    <button
                                      class="btn btn-info btn-sm btn-jadwal"
                                      data-kode="<?= $row['kdDokter'] ?>"
                                      data-nama="<?= htmlspecialchars($row['nmDokter']) ?>"
                                      title="Lihat Jadwal">

                                      <i class="fas fa-calendar-alt"></i>
                                    </button>
                                  </td>
                                </tr>
                              <?php
                              }
                            } else {
                              ?>
                              <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                  <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                  Belum ada data dokter.
                                </td>
                              </tr>
                            <?php } ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="nav-dokterinternal" role="tabpanel" aria-labelledby="nav-dokterinternal-tab" tabindex="0">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold">
                          <i class="fas fa-user-md me-2 text-primary"></i>
                          Data Dokter Internal
                        </h6>
                        <button class="btn btn-primary btn-sm" id="btnTambahDokterInternal">
                          <i class="fas fa-plus me-1"></i>
                          Tambah Dokter
                        </button>
                      </div>
                      <div class="table-responsive" data-simplebar>
                        <table class="table table-hover align-middle table-bordered mb-0" id="dokterInternalTable">
                          <thead class="table-light">
                            <tr>
                              <th>Nama Dokter</th>
                              <th class="text-center" width="300">Dokter BPJS</th>
                              <th class="text-center" width="200">Poliklinik</th>
                              <th class="text-center" width="120">Action</th>
                            </tr>
                          </thead>
                          <tbody id="listDokterInternal">
                            <tr>
                              <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                                Memuat data...
                              </td>
                            </tr>
                          </tbody>
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
    </div>
    <?php
    require 'library.php';
    ?>

    <div class="modal fade" id="modalDokterInternal" tabindex="-1">
      <div class="modal-dialog">
        <form id="formDokterInternal" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Tambah Dokter Internal
            </h5>
            <button type="button"
              class="btn-close"
              data-bs-dismiss="modal">
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="id_doctor_internal" name="id_doctor_internal">
            <div class="mb-3">
              <label class="form-label required">
                Nama Dokter
              </label>
              <input type="text"
                class="form-control"
                id="doctor_name_internal"
                name="doctor_name"
                required>
            </div>
            <div class="mb-3">
              <label class="form-label required">
                Dokter BPJS
              </label>
              <select
                class="form-select"
                id="doctor_bpjs_internal"
                name="doctor_bpjs"
                required>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Tutup
            </button>
            <button type="button"
              class="btn btn-primary"
              id="btnSimpanDokterInternal">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Modal Jadwal Dokter -->
    <div class="modal fade" id="modalJadwalDokter" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title mb-1">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                Jadwal Dokter
              </h5>
              <small class="text-muted">
                <strong id="namaDokterJadwal">-</strong>
              </small>
            </div>
            <button type="button"
              class="btn-close"
              data-bs-dismiss="modal">
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="doctorCodeJadwal">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <button
                class="btn btn-primary btn-sm"
                id="btnTambahJadwal">
                <i class="fas fa-plus me-1"></i>
                Tambah Jadwal
              </button>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th width="90">Hari</th>
                    <th>Poliklinik</th>
                    <th width="110" class="text-center">
                      Jam Mulai
                    </th>
                    <th width="110" class="text-center">
                      Jam Selesai
                    </th>
                    <th width="90" class="text-center">
                      Kuota
                    </th>
                    <th width="90" class="text-center">
                      Status
                    </th>
                    <th width="70" class="text-center">
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody id="listJadwalDokter">
                  <tr>
                    <td colspan="7"
                      class="text-center py-5 text-muted">
                      <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block"></i>
                      Memuat jadwal...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Jadwal -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
      <div class="modal-dialog">
        <form id="formJadwal" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Tambah Jadwal
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal">
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden"
              id="schedule_id">
            <input type="hidden"
              id="doctor_code_schedule">
            <div class="mb-3">
              <label class="form-label required">
                Hari
              </label>
              <select
                class="form-select"
                id="day_of_week"
                required>
                <option value="">Pilih Hari</option>
                <option value="Senin">Senin</option>
                <option value="Selasa">Selasa</option>
                <option value="Rabu">Rabu</option>
                <option value="Kamis">Kamis</option>
                <option value="Jumat">Jumat</option>
                <option value="Sabtu">Sabtu</option>
                <option value="Minggu">Minggu</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label required">
                Poliklinik
              </label>
              <select
                class="form-select"
                id="id_poli"
                required>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label required">
                    Jam Mulai
                  </label>
                  <input
                    type="text"
                    class="form-control timepicker"
                    id="start_time"
                    required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label required">
                    Jam Selesai
                  </label>
                  <input
                    type="text"
                    class="form-control timepicker"
                    id="end_time"
                    required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">
                Kuota JKN
              </label>
              <input
                type="number"
                class="form-control"
                id="kuota"
                min="1"
                value="30">
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Batal
            </button>
            <button
              type="button"
              class="btn btn-primary"
              id="btnSimpanJadwal">
              <i class="fas fa-save me-1"></i>
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
    <script>
      flatpickr(".timepicker", {
        enableTime: true,
        noCalendar: true,
        time_24hr: true,
        dateFormat: "H:i",
        minuteIncrement: 1,
        allowInput: true,
        onClose: function(selectedDates, dateStr, instance) {
          if (dateStr && !/^([01]\d|2[0-3]):([0-5]\d)$/.test(dateStr)) {
            alert("Format jam harus HH:mm");
            instance.clear();
          }
        }
      });
    </script>
</body>
<script src="controller/admisi/helper.js"></script>
<script>
  $('#btnTambah').on('click', async function() {

    let btn = $(this);
    let text = btn.html();

    btn.prop('disabled', true);
    btn.html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Sinkron Data...
    `);
    try {
      let response = await $.ajax({
        url: 'controller/admisi/services/getApi.php',
        type: 'POST',
        data: {
          url: 'dokter/0/100'
        },
        dataType: 'json'
      });
      if (!response.list || response.list.length == 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Data Kosong',
          text: 'Data dokter tidak ditemukan.'
        });
        return;
      }
      let simpan = await $.ajax({

        url: 'module/admin/sinkron_dokter.php',
        type: 'POST',
        dataType: 'json',

        data: {
          dokter: JSON.stringify(response.list)
        }

      });


      if (simpan.success) {

        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: simpan.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });


      } else {

        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: simpan.message
        });

      }


    } catch (error) {

      console.error(error);

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Gagal mengambil data dokter.'
      });


    } finally {

      btn.prop('disabled', false);
      btn.html(text);

    }

  });
  $("#btnTambahDokterInternal").on("click", function() {
    $("#formDokterInternal")[0].reset();
    $("#modalDokterInternal").modal("show");
    APP.loadDokterBPJSInternal();
  });
</script>

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

<script>
  APP.loadDokterBPJSInternal = function() {
    $("#doctor_bpjs_internal")
      .html('<option>Loading...</option>');
    $.ajax({
      url: "module/admin/get_dokter_bpjs.php",
      type: "POST",
      dataType: "json",
      success: function(res) {
        $("#doctor_bpjs_internal").empty();
        $("#doctor_bpjs_internal")
          .append('<option value="">- Pilih Dokter BPJS -</option>');
        if (res.success) {
          $.each(res.data, function(i, item) {
            $("#doctor_bpjs_internal")
              .append(`
                        <option value="${item.kdDokter}">
                            ${item.nmDokter}
                        </option>
                    `);
          });
        }
      },
      error: function() {
        $("#doctor_bpjs_internal")
          .html('<option>Gagal mengambil data</option>');

      }
    });
  };

  $("#btnSimpanDokterInternal").on("click", function() {
    let btn = $(this);
    let text = btn.html();
    let doctor_name = $("#doctor_name_internal").val();
    let doctor_bpjs = $("#doctor_bpjs_internal").val();
    let id_doctor = $('#id_doctor_internal').val();
    if (doctor_name == "") {
      Swal.fire(
        "Perhatian",
        "Nama dokter harus diisi.",
        "warning"
      );
      return;
    }
    if (doctor_bpjs == "") {
      Swal.fire(
        "Perhatian",
        "Dokter BPJS harus dipilih.",
        "warning"
      );
      return;
    }
    btn.prop("disabled", true);
    btn.html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Menyimpan...
    `);
    $.ajax({
      url: "module/admin/simpan_dokter_internal.php",
      type: "POST",
      dataType: "json",
      data: {
        doctor_name: doctor_name,
        doctor_code: doctor_bpjs,
        id_doctor_internal: id_doctor
      },
      success: function(res) {
        if (res.success) {
          $("#modalDokterInternal").modal("hide");
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            APP.loadDokterInternal();
          });
        } else {
          Swal.fire(
            "Gagal",
            res.message,
            "error"
          );
        }
      },
      error: function() {
        Swal.fire(
          "Error",
          "Terjadi kesalahan server.",
          "error"
        );
      },
      complete: function() {
        btn.prop("disabled", false);
        btn.html(text);
      }
    });
  });

  $('button[data-bs-target="#nav-dokterinternal"]').on('click', function() {
    APP.loadDokterInternal();
  });

  APP.loadDokterInternal = function() {
    $("#listDokterInternal").html(`
        <tr>
            <td colspan="5" class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                Memuat data...
            </td>
        </tr>
    `);
    $.ajax({
      url: "module/admin/get_dokter_internal.php",
      type: "POST",
      dataType: "json",
      success: function(res) {
        let html = "";
        if (res.success && res.data.length > 0) {
          $.each(res.data, function(i, row) {
            html += `
                    <tr>
                        <td>
                            ${row.doctor_name}
                        </td>
                        <td class="text-center">
                            ${row.nmDokter ?? '-'}
                        </td>
                        <td class="text-center">
                            ${row.nmPoli ?? '-'}
                        </td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm edit-dokter-internal"
                                data-id="${row.id_doctor}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm hapus-dokter-internal"
                                data-id="${row.id_doctor}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;
          });
        } else {
          html = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        Data dokter internal belum tersedia.
                    </td>
                </tr>
                `;
        }
        $("#listDokterInternal").html(html);
      },
      error: function() {
        $("#listDokterInternal").html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Gagal mengambil data.
                    </td>
                </tr>
            `);
      }
    });
  };

  $(document).on("click", ".edit-dokter-internal", function() {
    let id = $(this).data("id");
    Swal.fire({
      title: "Memuat...",
      text: "Mohon tunggu",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    $.ajax({
      url: "module/admin/getDokterInternal.php",
      type: "POST",
      dataType: "json",
      data: {
        id: id
      },
      success: function(res) {
        Swal.close();
        if (res.success) {
          $("#formDokterInternal")[0].reset();
          $("#modalDokterInternal .modal-title").text("Edit Dokter Internal");
          $("#id_doctor_internal").val(res.data.id_doctor);
          $("#doctor_name_internal").val(res.data.doctor_name);
          APP.loadDokterBPJS("#doctor_bpjs_internal", res.data.doctor_bpjs);
          $("#modalDokterInternal").modal("show");
        } else {
          Swal.fire(
            "Gagal",
            res.message,
            "error"
          );
        }
      },
      error: function() {
        Swal.fire(
          "Error",
          "Terjadi kesalahan server.",
          "error"
        );
      }
    });
  });

  APP.loadDokterBPJS = function(target, selected = "") {
    $.ajax({
      url: "module/admin/get_dokter_bpjs.php",
      type: "POST",
      dataType: "json",
      success: function(res) {
        let html = '<option value="">- Pilih Dokter -</option>';
        $.each(res.data, function(i, row) {
          html += `<option value="${row.kdDokter}">${row.nmDokter}</option>`;
        });
        $(target).html(html);
        if (selected !== "") {
          $(target).val(selected).trigger("change");
        }
      },
      error: function() {
        $(target).html('<option value="">Gagal memuat data</option>');
      }
    });
  };

  $("#btnTambahDokterInternal").click(function() {
    $("#formDokterInternal")[0].reset();
    $("#id_doctor_internal").val("");
    $("#modalDokterInternal .modal-title").text("Tambah Dokter Internal");
    APP.loadDokterBPJS("#doctor_bpjs_internal");
    $("#modalDokterInternal").modal("show");
  });

  $(document).on("click", ".hapus-dokter-internal", function() {
    let id = $(this).data("id");
    Swal.fire({
      title: "Hapus Dokter?",
      text: "Data dokter internal beserta jadwalnya akan dihapus.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal"
    }).then((result) => {
      if (!result.isConfirmed) return;
      Swal.fire({
        title: "Menghapus...",
        text: "Mohon tunggu",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      $.ajax({
        url: "module/admin/hapus_dokter_internal.php",
        type: "POST",
        dataType: "json",
        data: {
          id_doctor: id
        },
        success: function(res) {
          if (res.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil",
              text: res.message,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              APP.loadDokterInternal();
            });
          } else {
            Swal.fire(
              "Gagal",
              res.message,
              "error"
            );
          }
        },
        error: function() {
          Swal.fire(
            "Error",
            "Terjadi kesalahan server.",
            "error"
          );
        }
      });
    });
  });
</script>

<script>
  $(document).on("click", ".btn-jadwal", function() {
    let kode = $(this).data("kode");
    let nama = $(this).data("nama");
    $("#namaDokterJadwal").text(nama);
    $("#btnSinkronJadwal").data("kode", kode);
    $("#modalJadwalDokter").modal("show");
    $("#doctorCodeJadwal").val(kode);
    APP.loadJadwalDokter(kode);
  });

  APP.loadJadwalDokter = function(kodeDokter) {
    $("#listJadwalDokter").html(`
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                Memuat jadwal...
            </td>
        </tr>
    `);
    $.ajax({
      url: "module/admin/get_jadwal_dokter.php",
      type: "POST",
      dataType: "json",
      data: {
        doctor_code: kodeDokter
      },
      success: function(res) {
        let html = "";
        $("#totalJadwal").text(res.data ? res.data.length : 0);
        if (res.success && res.data.length > 0) {
          $.each(res.data, function(i, row) {
            html += `
                    <tr>
                        <td>
                            ${row.nmHari}
                        </td>
                        <td>
                            ${row.nmPoli}
                        </td>
                        <td class="text-center">
                            ${row.jamMulai}
                        </td>
                        <td class="text-center">
                            ${row.jamSelesai}
                        </td>
                        <td class="text-center">
                            <input 
                                type="number"
                                class="form-control form-control-sm kuota-input text-center"
                                data-id="${row.id}"
                                data-old="${row.kuota}"
                                value="${row.kuota}"
                                min="0"
                                style="width:80px;margin:auto">
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input 
                                    class="form-check-input"
                                    type="checkbox"
                                    ${row.status == 1 ? "checked" : ""}
                                    onchange="toggleStatus(${row.id},this)">
                            </div>
                        </td>
                        <td class="text-center">
                            <button 
                                class="btn btn-danger btn-sm hapus-jadwal"
                                data-id="${row.id}"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;
          });
        } else {
          html = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                        <strong>Tidak ada jadwal dokter.</strong>
                        <br>
                        <small>
                            Silakan klik tombol 
                            <b>Tambah Jadwal</b>
                            untuk membuat jadwal.
                        </small>
                    </td>
                </tr>
                `;
        }
        $("#listJadwalDokter").html(html);
      },
      error: function() {
        $("#listJadwalDokter").html(`
                <tr>
                    <td colspan="7" class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        Gagal mengambil data jadwal.
                    </td>
                </tr>
            `);
      }
    });
  }

  $(document).on("click", "#btnTambahJadwal", function() {
    $("#formJadwal")[0].reset();
    $("#schedule_id").val("");
    $("#doctor_code_schedule").val($("#doctorCodeJadwal").val());
    $("#kuota").val(30);
    APP.loadMasterPoli("#id_poli");
    $("#modalTambahJadwal").modal("show");
  });
  APP.loadMasterPoli = function(target) {
    $(target).html('<option>Memuat...</option>');
    $.ajax({
      url: "module/admin/get_master_poli.php",
      type: "POST",
      dataType: "json",
      success: function(res) {
        let html = '<option value="">- Pilih Poliklinik -</option>';
        if (res.success) {
          $.each(res.data, function(i, row) {
            if (row.poliSakit == '1'){
              html += `<option value="${row.kdPoli}">${row.nmPoli}</option>`;
            }
          });
        }
        $(target).html(html);
      },
      error: function() {
        $(target).html('<option value="">Gagal memuat data</option>');
      }
    });
  }

  $(document).on("click", "#btnSimpanJadwal", function() {
    let btn = $(this);
    let data = {
      schedule_id: $("#schedule_id").val(),
      doctor_code: $("#doctor_code_schedule").val(),
      day_of_week: $("#day_of_week").val(),
      id_poli: $("#id_poli").val(),
      start_time: $("#start_time").val(),
      end_time: $("#end_time").val(),
      kuota: $("#kuota").val()
    };
    if (data.day_of_week == "") {
      Swal.fire("Perhatian", "Pilih hari.", "warning");
      return;
    }
    if (data.id_poli == "") {
      Swal.fire("Perhatian", "Pilih poliklinik.", "warning");
      return;
    }
    if (data.start_time == "") {
      Swal.fire("Perhatian", "Jam mulai belum diisi.", "warning");
      return;
    }
    if (data.end_time == "") {
      Swal.fire("Perhatian", "Jam selesai belum diisi.", "warning");
      return;
    }
    btn.prop("disabled", true);
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
    $.ajax({
      url: "module/admin/simpan_jadwal.php",
      type: "POST",
      data: data,
      dataType: "json",
      success: function(res) {
        if (res.success) {
          $("#modalTambahJadwal").modal("hide");
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          });
          APP.loadJadwalDokter(data.doctor_code);
        } else {
          Swal.fire("Gagal", res.message, "error");
        }
      },
      error: function() {
        Swal.fire("Error", "Terjadi kesalahan server.", "error");
      },
      complete: function() {
        btn.prop("disabled", false);
        btn.html('<i class="fas fa-save me-1"></i>Simpan');
      }
    });
  });

  $(document).on("blur", ".kuota-input", function() {
    let input = $(this);
    let id = input.data("id");
    let kuota = input.val();
    let old = input.data("old");
    if (kuota == "" || kuota < 0) {
      input.val(old);
      Swal.fire("Perhatian", "Kuota tidak valid", "warning");
      return;
    }
    if (kuota == old) {
      return;
    }
    input.prop("disabled", true);
    input.css("background", "#fff3cd");
    $.ajax({
      url: "module/admin/updateKuotaJKN.php",
      type: "POST",
      dataType: "json",
      data: {
        id: id,
        kuota: kuota
      },
      success: function(res) {
        if (res.success) {
          input.data("old", kuota);
          input.css("background", "#d4edda");
        } else {
          input.val(old);
          Swal.fire("Gagal", res.message, "error");
        }
      },
      error: function() {
        input.val(old);
        Swal.fire("Error", "Gagal mengupdate kuota", "error");
      },
      complete: function() {
        input.prop("disabled", false);
        setTimeout(function() {
          input.css("background", "");
        }, 800);
      }
    });
  });

  function toggleStatus(id, el) {
    let status = el.checked ? 1 : 0;
    let text = status == 1 ? "mengaktifkan" : "menonaktifkan";
    let oldStatus = !el.checked;

    Swal.fire({
      title: "Konfirmasi",
      text: "Yakin ingin " + text + " jadwal ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya",
      cancelButtonText: "Batal"
    }).then((result) => {
      if (result.isConfirmed) {
        el.disabled = true;
        $.ajax({
          url: "module/admin/updateStatusJadwal.php",
          type: "POST",
          dataType: "json",
          data: {
            id: id,
            status: status
          },
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: res.message,
                timer: 1200,
                showConfirmButton: false
              });
            } else {
              el.checked = oldStatus;
              Swal.fire("Gagal", res.message, "error");
            }
          },
          error: function() {
            el.checked = oldStatus;
            Swal.fire("Error", "Terjadi kesalahan server", "error");
          },
          complete: function() {
            el.disabled = false;
          }
        });
      } else {
        el.checked = oldStatus;
      }
    });
  }

  $(document).on("click", ".hapus-jadwal", function() {
    let id = $(this).data("id");
    Swal.fire({
      title: "Hapus Jadwal?",
      text: "Data jadwal yang dihapus tidak dapat dikembalikan.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Menghapus...",
          text: "Mohon tunggu",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url: "module/admin/hapusJadwal.php",
          type: "POST",
          dataType: "json",
          data: {
            id: id
          },
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: res.message,
                timer: 1500,
                showConfirmButton: false
              });
              APP.loadJadwalDokter($("#doctorCodeJadwal").val());
            } else {
              Swal.fire(
                "Gagal",
                res.message,
                "error"
              );
            }
          },
          error: function() {
            Swal.fire(
              "Error",
              "Terjadi kesalahan pada server.",
              "error"
            );
          }
        });
      }
    });
  });
</script>

</html>