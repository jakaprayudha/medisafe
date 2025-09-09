<?php
$title = 'Pasien Details';
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
                    <h5 class="card-title fw-semibold">Formulir Data Dokter</h5>
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
                        aria-selected="false">Kontak & Alamat</button>
                      <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                        aria-selected="false">Emergency Kontak</button>
                      <button class="nav-link" id="nav-dokumen-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-dokumen" type="button" role="tab" aria-controls="nav-dokumen"
                        aria-selected="false">Dokumen</button>
                    </div>
                  </nav>

                  <div class="tab-content mt-3" id="nav-tabContent">
                    <!-- Data Umum -->
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                      aria-labelledby="nav-home-tab" tabindex="0">
                      <form id="formIdentitas">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Nomor Rekam Medis</label>
                              <input type="text" class="form-control" name="nomor_rm" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Nomor Induk Kependudukan</label>
                              <input type="text" class="form-control" name="patient_nik">
                            </div>
                          </div>
                          <div class="col">
                            <div class="mb-3">
                              <label class="form-label required">Nama Pasien</label>
                              <input type="text" class="form-control" name="patient_name" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Tempat Lahir</label>
                              <input type="text" class="form-control" name="patient_place" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Tanggal Lahir</label>
                              <input type="date" class="form-control" name="patient_datebirth" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Jenis Kelamin</label>
                              <select name="patient_gender" class="form-select" id="patient_gender" required>
                                <option value="">PILIH</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Agama</label>
                              <select name="patient_religion" class="form-select" id="patient_religion" required>
                                <option value="">PILIH</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Budha">Budha</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Golongan Darah</label>
                              <select name="patient_blood" class="form-select" id="patient_blood">
                                <option value="">PILIH</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="O">O</option>
                                <option value="AB">AB</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Status Perkawinan</label>
                              <select name="patient_marital_status" class="form-select" id="patient_marital_status">
                                <option value="">PILIH</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Belum Kawin">Belum Kawin</option>
                                <option value="Janda/Duda">Janda/Duda</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kewarganegaraan</label>
                              <input type="text" value="Indonesia" class="form-control" name="patient_nationality">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Pendidikan Terakhir</label>
                              <select name="patient_education" class="form-select" id="">
                                <option value="">PILIH</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                                <option value="D3">D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Pekerjaan</label>
                              <input type="text" class="form-control" name="patient_occupation">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Disabilitas</label>
                              <input type="text" class="form-control" name="patient_disability">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Alergi</label>
                              <input type="text" class="form-control" name="patient_allergy">
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="mb-3">
                              <label class="form-label">Catatan Pasien</label>
                              <textarea name="patient_notes" class="form-control" id="" rows="5"></textarea>
                            </div>
                          </div>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>
                    <!-- Profil -->
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                      aria-labelledby="nav-profile-tab" tabindex="0">
                      <form id="formKontakAlamat">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">No. HP</label>
                              <input type="text" class="form-control" name="patient_phone">
                            </div>
                          </div>
                          <div class="col">
                            <div class="mb-3">
                              <label class="form-label">Email</label>
                              <input type="email" class="form-control" name="patient_mail">
                            </div>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Alamat</label>
                          <textarea class="form-control" name="patient_address"></textarea>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>

                    <!-- Kepegawaian -->
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel"
                      aria-labelledby="nav-contact-tab" tabindex="0">
                      <form id="formEmergency">
                        <div class="mb-3">
                          <label class="form-label required">Nama Kontak Darurat</label>
                          <input type="text" class="form-control" name="patient_emergency_contact_name" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label required">Nomor Handpone Emergency</label>
                          <input type="text" class="form-control" name="patient_emergency_contact_phone" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label required">Hubungan</label>
                          <input type="text" class="form-control" name="patient_emergency_contact_relation" required>
                        </div>

                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>
                    <!-- Jadwal Prakikt -->
                    <div class="tab-pane fade" id="nav-dokumen" role="tabpanel" aria-labelledby="nav-dokumen-tab" tabindex="0">
                      <form id="formDokumen" enctype="multipart/form-data">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload KTP</label>
                              <input type="file" class="form-control" name="ktp" accept="image/*,application/pdf">
                              <p class="mt-1 small" id="statusKtp"></p>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload Kartu Keluarga (KK)</label>
                              <input type="file" class="form-control" name="kk" accept="image/*,application/pdf">
                              <p class="mt-1 small" id="statusKk"></p>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload Kartu BPJS</label>
                              <input type="file" class="form-control" name="bpjs" accept="image/*,application/pdf">
                              <p class="mt-1 small" id="statusBpjs"></p>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload Foto Diri</label>
                              <input type="file" class="form-control" name="foto" accept="image/*">
                              <p class="mt-1 small" id="statusFoto"></p>
                            </div>
                          </div>
                        </div>

                    </div>
                    <div class="text-end">
                      <button type="submit" class="btn btn-primary">Upload Dokumen</button>
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
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNumber = urlParams.get("no"); // ambil param no dari URL

    if (patientNumber) {
      $.ajax({
        url: "controller/master/getPatientDocs.php",
        type: "GET",
        data: {
          patient_number: patientNumber
        },
        success: function(res) {
          let data = JSON.parse(res);
          if (data.status === "success") {
            updateStatus("statusKtp", data.files.patient_ktp, "KTP");
            updateStatus("statusKk", data.files.patient_kk, "KK");
            updateStatus("statusBpjs", data.files.patient_bpjs, "BPJS");
            updateStatus("statusFoto", data.files.patient_foto, "Foto");
          }
        }
      });
    }

    function updateStatus(elementId, fileName, label) {
      const baseUrl = "uploads/patient/";
      if (fileName) {
        $("#" + elementId).html(
          `<a href="${baseUrl + fileName}" target="_blank" class="text-success">
             <i class="fas fa-check-circle"></i> ${label} sudah diupload (klik untuk lihat)
           </a>`
        );
      } else {
        $("#" + elementId).html(
          `<span class="text-danger"><i class="fas fa-times-circle"></i> Belum upload ${label}</span>`
        );
      }
    }
  });
</script>
<script>
  $(document).ready(function() {
    // Ambil nomor pasien dari URL (?patient_number=XXXXXX)
    const urlParams = new URLSearchParams(window.location.search);
    const patient_number = urlParams.get("no");

    $("#formDokumen").on("submit", function(e) {
      e.preventDefault();

      let formData = new FormData(this);
      formData.append("patient_number", patient_number); // tambahkan manual

      $.ajax({
        url: "controller/master/uploadPatient.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
          let data = JSON.parse(res);
          if (data.status === "success") {
            alert("Upload berhasil!");
          } else {
            alert("Error: " + data.message);
          }
        },
        error: function() {
          alert("Terjadi kesalahan saat mengirim data.");
        }
      });
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNo = urlParams.get("no");

    // Auto fill form
    if (patientNo) {
      fetch("controller/master/patientDetailsController.php?no=" + patientNo)
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

      if (!patientNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formIdentitas);
      formData.append("patient_number", patientNo);
      formData.append("_method", "PUT");

      fetch("controller/master/patientDetailsController.php", {
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
    const formKontakAlamat = document.getElementById("formKontakAlamat");
    formKontakAlamat.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!patientNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formKontakAlamat);
      formData.append("patient_number", patientNo);
      formData.append("_method", "PUT");

      fetch("controller/master/patientDetailsController.php", {
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
    const formEmergency = document.getElementById("formEmergency");
    formEmergency.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!patientNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formEmergency);
      formData.append("patient_number", patientNo);
      formData.append("_method", "PUT");

      fetch("controller/master/patientDetailsController.php", {
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
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNo = urlParams.get("no");

    // Load Jadwal
    function loadJadwal() {
      fetch("controller/master/dokterJadwalController.php?no=" + patientNo)
        .then(res => res.json())
        .then(data => {
          const tbody = document.querySelector("#jadwalTable tbody");
          tbody.innerHTML = "";
          if (data.success) {
            data.data.forEach(j => {
              const tr = document.createElement("tr");
              tr.innerHTML = `
              <td>${j.day_of_week}</td>
              <td>${j.start_time} - ${j.end_time}</td>
              <td>
                <button class="btn btn-danger btn-sm" onclick="deleteJadwal(${j.id_schedule})">Hapus</button>
              </td>
            `;
              tbody.appendChild(tr);
            });
          }
        })
        .catch(err => console.error("Error:", err));
    }

    // Submit Form Jadwal
    const formJadwal = document.getElementById("formJadwal");
    formJadwal.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(formJadwal);
      formData.append("patient_number", patientNo);
      formData.append("_method", "POST");

      fetch("controller/master/dokterJadwalController.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire("Berhasil", "Jadwal ditambahkan!", "success");
            formJadwal.reset();
            loadJadwal();
          } else {
            Swal.fire("Gagal", data.message, "error");
          }
        });
    });

    // Delete Jadwal
    window.deleteJadwal = function(id) {
      const formData = new FormData();
      formData.append("id_schedule", id);
      formData.append("_method", "DELETE");

      fetch("controller/master/dokterJadwalController.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire("Berhasil", "Jadwal dihapus!", "success");
            loadJadwal();
          } else {
            Swal.fire("Gagal", data.message, "error");
          }
        });
    }

    if (patientNo) loadJadwal();
  });
</script>

</html>