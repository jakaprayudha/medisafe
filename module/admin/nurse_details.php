<?php
$title = 'Tenaga Kesehatan Details';
require '../../controller/view.php';
$no = $_GET['no'];
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
                    <h5 class="card-title fw-semibold">Formulir Data Tenaga Kesehatan</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Kembali
                      </button>
                    </div>
                  </div>
                  <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home"
                        aria-selected="true">Data Umum</button>
                      <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile"
                        aria-selected="false">Profil</button>
                      <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                        aria-selected="false">Kepegawaian</button>
                    </div>
                  </nav>

                  <div class="tab-content mt-3" id="nav-tabContent">
                    <!-- Data Umum -->
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                      aria-labelledby="nav-home-tab" tabindex="0">
                      <div class="mb-3">
                        <label class="form-label">Nomor Tenaga Kesehatan</label>
                        <input type="text" class="form-control bg-light" value="<?= $no ?>" name="nurse_number" readonly>
                      </div>
                      <form id="formIdentitas">
                        <div class="mb-3">
                          <label class="form-label">Nama Tenaga Kesehatan</label>
                          <input type="text" class="form-control" name="nurse_name" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Profesi</label>
                          <select name="nurse_profesi" id="nurse_profesi" class="form-select" required>
                            <option value="Perawat">Perawat</option>
                            <option value="Farmasi">Farmasi</option>
                            <option value="Apoteker">Apoteker</option>
                            <option value="Psikolog">Psikolog</option>
                            <option value="Bidan">Bidan</option>
                            <option value="Entomolog">Entomolog</option>
                            <option value="Ahli Gizi">Ahli Gizi</option>
                            <option value="Fisioterapis">Fisioterapis</option>
                            <option value="Konselor">Konselor</option>
                            <option value="Lainnya">Lainnya</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="nurse_status">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                          </select>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>


                    <!-- Profil -->
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                      aria-labelledby="nav-profile-tab" tabindex="0">
                      <form id="formProfile">
                        <div class="mb-3">
                          <label class="form-label">No. HP</label>
                          <input type="text" class="form-control" name="nurse_phone">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" class="form-control" name="nurse_mail">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Tanggal Lahir</label>
                          <input type="date" class="form-control" name="nurse_birthdate">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Jenis Kelamin</label>
                          <select class="form-select" name="nurse_gender">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Alamat</label>
                          <textarea class="form-control" name="nurse_address"></textarea>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>

                    <!-- Kepegawaian -->
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel"
                      aria-labelledby="nav-contact-tab" tabindex="0">
                      <form id="formKepegawaian">
                        <div class="mb-3">
                          <label class="form-label required">No. STR</label>
                          <input type="text" class="form-control" name="nurse_str" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label required">No. SIP</label>
                          <input type="text" class="form-control" name="nurse_sip" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label required">Masa Berlaku Izin</label>
                          <input type="date" class="form-control" name="nurse_expaired" required>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
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
    const urlParams = new URLSearchParams(window.location.search);
    const doctorNo = urlParams.get("no");

    // Auto fill form
    if (doctorNo) {
      fetch("controller/master/nurseDetailsController.php?no=" + doctorNo)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const doctor = data.data;
            for (const key in doctor) {
              const input = document.querySelector("[name='" + key + "']");
              if (input) input.value = doctor[key] ?? "";
              const el = document.getElementById(key);
              if (el) el.value = doctor[key] ?? "";
            }
          } else {
            Swal.fire("Oops!", data.message, "warning");
          }
        })
        .catch(err => console.error("Error:", err));
    }

    // Handle Identitas submit
    const formIdentitas = document.getElementById("formIdentitas");
    formIdentitas.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!doctorNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formIdentitas);
      formData.append("nurse_number", doctorNo);
      formData.append("_method", "PUT");

      fetch("controller/master/nurseDetailsController.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil",
              text: "Data berhasil diperbarui!"
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: data.message
            });
          }
        })
        .catch(err => {
          console.error("Error:", err);
          Swal.fire("Error!", "Terjadi kesalahan sistem.", "error");
        });
    });

    // Handle Profile submit
    const formProfile = document.getElementById("formProfile");
    formProfile.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!doctorNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formProfile);
      formData.append("nurse_number", doctorNo);
      formData.append("_method", "PUT");

      fetch("controller/master/nurseDetailsController.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil",
              text: "Data berhasil diperbarui!"
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: data.message
            });
          }
        })
        .catch(err => {
          console.error("Error:", err);
          Swal.fire("Error!", "Terjadi kesalahan sistem.", "error");
        });
    });

    // Handle Kepegawaian submit
    const formKepegawaian = document.getElementById("formKepegawaian");
    formKepegawaian.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!doctorNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formKepegawaian);
      formData.append("nurse_number", doctorNo);
      formData.append("_method", "PUT");

      fetch("controller/master/nurseDetailsController.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil",
              text: "Data berhasil diperbarui!"
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: data.message
            });
          }
        })
        .catch(err => {
          console.error("Error:", err);
          Swal.fire("Error!", "Terjadi kesalahan sistem.", "error");
        });
    });
  });
</script>


</html>