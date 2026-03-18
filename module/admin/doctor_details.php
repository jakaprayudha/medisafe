<?php
$title = 'Dokter Details';
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
                        aria-selected="false">Profil</button>
                      <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                        aria-selected="false">Dokumen</button>
                      <button class="nav-link" id="nav-jadwal-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-jadwal" type="button" role="tab" aria-controls="nav-jadwal"
                        aria-selected="false">Jadwal Praktik</button>
                    </div>
                  </nav>

                  <div class="tab-content mt-3" id="nav-tabContent">
                    <!-- Data Umum -->
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                      aria-labelledby="nav-home-tab" tabindex="0">
                      <form id="formIdentitas">
                        <input type="hidden" hidden name="doctor_number" value="<?= $no ?>">
                        <div class="row">
                          <div class="col-2">
                            <div class="mb-3">
                              <label class="form-label">Gelar Depan</label>
                              <input type="text" class="form-control" name="doctor_title_front" id="doctor_title_front" required>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="mb-3">
                              <label class="form-label">Nama Dokter</label>
                              <input type="text" class="form-control" name="doctor_name" required>
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Gelar Belakang</label>
                              <input type="text" class="form-control" name="doctor_title_back" id="doctor_title_back">
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Kode Antrian</label>
                              <input type="text" class="form-control" name="doctor_antrean" id="doctor_antrean" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kategori Dokter</label>
                              <select class="form-select" name="doctor_category">
                                <option value="Umum">Umum</option>
                                <option value="Spesialis">Spesialis</option>
                                <option value="Sub Spesialis">Sub Spesialis</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Poli</label>
                              <select name="id_poli" id="id_poli" class="form-select" required>
                                <option value="">PILIH</option>
                                <?php
                                $getpoli = tampildata("SELECT * FROM ms_poli WHERE poli_status='1'");
                                ?>
                                <?php foreach ($getpoli as $poli) : ?>
                                  <option value="<?= $poli['id_poli'] ?>"><?= $poli['poli_name'] ?></option>
                                <?php endforeach ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Estimasi Waktu Pelayanan (Menit)</label>
                              <input type="text" class="form-control" name="estimated_services" id="estimated_services" required>
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
                      <form id="formProfile">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">No. HP</label>
                              <input type="text" class="form-control" name="doctor_phone">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Email</label>
                              <input type="email" class="form-control" name="doctor_mail">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Tanggal Lahir</label>
                              <input type="date" class="form-control" name="doctor_birthdate">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Jenis Kelamin</label>
                              <select class="form-select" name="doctor_gender">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Nomor KTP</label>
                              <input type="text" class="form-control" name="doctor_nik" required id="doctor_nik">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Satu Sehat ID</label>
                              <input type="text" class="form-control" name="doctor_his" required id="doctor_his">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Lembaga Registrasi (STR)</label>
                              <select name="str_lembaga" class="form-select" id="str_lembaga">
                                <option value="Konsil Kedokteran Indonesia">Konsil Kedokteran Indonesia</option>
                                <option value="Majelis Tenaga Kesehatan Provinsi">Majelis Tenaga Kesehatan Provinsi</option>
                                <option value="Majelis Tenaga Kesehatan Indonesia">Majelis Tenaga Kesehatan Indonesia</option>
                                <option value="Himpunan Psikologis Indonesia">Himpunan Psikologis Indonesia</option>
                                <option value="Ikatan Fisioterapi Indonesia">Ikatan Fisioterapi Indonesia</option>
                                <option value="Perhimpunan Dokter Hewan Indonesia">Perhimpunan Dokter Hewan Indonesia</option>
                                <option value="Ikatan Okupasi Terapi Indonesia">Ikatan Okupasi Terapi Indonesia</option>
                                <option value="Ikatan Psikologis Klinis Indonesia">Ikatan Psikologis Klinis Indonesia</option>
                                <option value="Ikatan Sinse Indonesia">Ikatan Sinse Indonesia</option>
                                <option value="Komisi Farmasi Nasional">Komisi Farmasi Nasional</option>
                                <option value="Dinas Kesehatan Provinsi">Dinas Kesehatan Provinsi</option>
                                <option value="PTGMI">PTGMI</option>
                                <option value="Konsil Tenaga Kesehatan Gigi Indonesia">Konsil Tenaga Kesehatan Gigi Indonesia</option>
                                <option value="Konsil Kesehatan Indonesia">Konsil Kesehatan Indonesia</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Nomor Registrasi (STR)</label>
                              <input type="text" class="form-control" name="str_number" required id="str_number">
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Masa Berlaku STR</label>
                              <input type="date" class="form-control" name="str_date_expaired" required id="str_date_expaired">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Lembaga Registrasi (SIP)</label>
                              <select name="sip_lembaga" class="form-select" id="sip_lembaga">
                                <option value="Ikatan Dokter Indonesia">Ikatan Dokter Indonesia (IDI)</option>
                                <option value="Persatuan Dokter Gigi Indonesia">Persatuan Dokter Gigi Indonesia (PDGI)</option>
                                <option value="Dinas Kesehatan Daerah">Dinas Kesehatan Daerah</option>
                                <option value="Dinas Kesehatan Kabupaten">Dinas Kesehatan Kabupaten</option>
                                <option value="Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu Kabupaten">Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu Kabupaten</option>
                                <option value="DPC PTGMI Daerah">DPC PTGMI Daerah</option>
                                <option value="Ikatan Bidan Indonesia">Ikatan Bidan Indonesia (IBI)</option>
                                <option value="Persatuan Perawat Nasional Indonesia">Persatuan Perawat Nasional Indonesia (PPNI)</option>
                                <option value="Persatuan Ahli Teknologi Laboratorium">Persatuan Ahli Teknologi Laboratorium (PATELKI)</option>
                                <option value="Ikatan Apoteker Indonesia">Ikatan Apoteker Indonesia (IAI)</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Nomor Registrasi (SIP)</label>
                              <input type="text" class="form-control" name="sip_number" required id="sip_number">
                            </div>
                          </div>
                          <div class="col-3">
                            <div class="mb-3">
                              <label class="form-label">Masa Berlaku SIP</label>
                              <input type="date" class="form-control" name="sip_date_expaired" required id="sip_date_expaired">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Alamat</label>
                              <textarea class="form-control" name="doctor_address"></textarea>
                            </div>
                          </div>
                        </div>



                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>
                    <!-- Dokumen -->
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel"
                      aria-labelledby="nav-contact-tab" tabindex="0">
                      <div class="row">
                        <div class="col-lg-12 d-flex align-items-stretch">
                          <div class="card w-100">
                            <div class="card-body p-4">
                              <div class="d-flex justify-content-between align-items-center mb-4">
                                <!-- Grup tombol di sisi kanan -->
                                <div class="d-flex ms-auto">
                                  <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                                </div>
                              </div>
                              <ol class="list-group list-group-numbered">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                  <div class="ms-2 me-auto">
                                    <div class="fw-bold">Kategori File</div>
                                    Catatan File :
                                  </div>
                                  <span class="badge text-bg-primary rounded-pill">Lihat File</span>
                                </li>
                              </ol>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Jadwal Prakikt -->
                    <div class="tab-pane fade" id="nav-jadwal" role="tabpanel" aria-labelledby="nav-jadwal-tab" tabindex="0">
                      <form id="formJadwal">
                        <div class="row">
                          <div class="col-4">
                            <div class="mb-3">
                              <label class="form-label">Hari</label>
                              <select class="form-select" name="day_of_week" required>
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
                          </div>
                          <div class="col-4">
                            <div class="mb-3">
                              <label class="form-label">Jam Mulai</label>
                              <input type="time" class="form-control" name="start_time" required>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="mb-3">
                              <label class="form-label">Jam Selesai</label>
                              <input type="time" class="form-control" name="end_time" required>
                            </div>
                          </div>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>

                      <hr>
                      <h6>Jadwal Dokter</h6>
                      <table class="table table-bordered" id="jadwalTable">
                        <thead>
                          <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th class="col-1">Aksi</th>
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
      fetch("controller/master/dokterDetailsController.php?no=" + doctorNo)
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
      formData.append("doctor_number", doctorNo);
      formData.append("_method", "PUT");

      fetch("controller/master/dokterDetailsController.php", {
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
      formData.append("doctor_number", doctorNo);
      formData.append("_method", "PUT");

      fetch("controller/master/dokterDetailsController.php", {
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
    const doctorNo = urlParams.get("no");

    // Load Jadwal
    function loadJadwal() {
      fetch("controller/master/dokterJadwalController.php?no=" + doctorNo)
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
      formData.append("doctor_number", doctorNo);
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

    if (doctorNo) loadJadwal();
  });
</script>

</html>