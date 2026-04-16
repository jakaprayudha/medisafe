<?php
$title = 'Pasien Details';
require '../../controller/view.php';
$pt = $_GET['pt'];
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <base href="<?= 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/' ?>">
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
                    <h5 class="card-title fw-semibold">Formulir Data Pasien</h5>
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
                      <!-- <button class="nav-link" id="nav-ttd-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-ttd" type="button" role="tab" aria-controls="nav-ttd"
                        aria-selected="false">Tanda Tangan</button> -->
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
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Nomor Kartu Keluarga</label>
                              <input type="text" class="form-control" name="patient_kk">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Nomor BPJS</label>
                              <input type="text" class="form-control" name="patient_bpjs">
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
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Provinsi</label>
                              <select id="provinsi" class="form-select">
                                <option value="">PILIH</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kabupaten</label>
                              <select id="kabupaten" class="form-select">
                                <option value="">PILIH</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kecamatan</label>
                              <select id="kecamatan" class="form-select">
                                <option value="">PILIH</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kelurahan</label>
                              <select id="kelurahan" class="form-select">
                                <option value="">PILIH</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <!-- hidden untuk simpan text -->
                        <input type="hidden" name="patient_provinsi" id="provinsi_text">
                        <input type="hidden" name="patient_kabupaten" id="kabupaten_text">
                        <input type="hidden" name="patient_kecamatan" id="kecamatan_text">
                        <input type="hidden" name="patient_kelurahan" id="kelurahan_text">
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
                      <div class="alert alert-info d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                          <strong>Informasi Upload Dokumen:</strong>
                          <ul class="mb-0 small">
                            <li>Format file: JPG, JPEG, PNG</li>
                            <li>Ukuran maksimal: 2 MB</li>
                            <li>
                              Gambar wajib posisi <b>landscape</b>
                              (<a href="javascript:void(0)" onclick="showSample()">lihat contoh</a>)
                            </li>
                            <li>Pastikan dokumen terlihat jelas dan tidak blur</li>
                          </ul>
                        </div>
                      </div>
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
                          <!-- <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload Foto Diri</label>
                              <input type="file" class="form-control" name="foto" accept="image/*">
                              <p class="mt-1 small" id="statusFoto"></p>
                            </div>
                          </div> -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-ttd" role="tabpanel" aria-labelledby="nav-ttd-tab" tabindex="0">
                      <canvas id="signature-pad" style="border: 1px dashed #ccc; width: 100%; height: 200px; border-radius: 8px;"></canvas>
                      <hr>
                      <div id="ttd-preview-container" class="mt-3 text-center">
                        <label class="fw-semibold">Preview Tanda Tangan:</label><br>

                        <img id="ttd-preview"
                          src=""
                          style="max-width:300px; display:none; border:1px solid #ddd; border-radius:8px; padding:5px;">
                        <div id="ttd-empty" class="text-muted mt-2">
                          Belum ada tanda tangan
                        </div>
                      </div>
                      <div class="mt-3 text-end">
                        <button type="button" class="btn btn-light" id="clear-signature">Hapus</button>
                        <button type="button" class="btn btn-primary" id="save-signature">Simpan Tanda Tangan</button>
                      </div>
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

  <div class="modal fade" id="modalSample" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Contoh Dokumen Landscape</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">
          <img src="assets/images/sample-landscape.jpg"
            class="img-fluid rounded border"
            alt="Contoh Landscape">

          <p class="mt-2 text-muted small">
            Contoh dokumen dengan posisi landscape (lebar lebih besar dari tinggi)
          </p>
        </div>

      </div>
    </div>
  </div>

  <!-- 🔥 Modal Preview File -->
  <div class="modal fade" id="filePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Preview Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">
          <!-- image -->
          <img id="previewImage" src="" class="img-fluid d-none" />

          <!-- pdf -->
          <iframe id="previewPdf"
            style="width:100%; height:500px;"
            class="d-none"></iframe>
        </div>

      </div>
    </div>
  </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  const nomor_visit = urlParams.get("no");
  const id_patient = urlParams.get("pt");
  document.addEventListener("DOMContentLoaded", function() {

    const canvas = document.getElementById("signature-pad");
    const clearBtn = document.getElementById("clear-signature");
    const saveBtn = document.getElementById("save-signature");

    const signaturePad = new SignaturePad(canvas, {
      backgroundColor: "rgba(255,255,255,0)",
      penColor: "black"
    });

    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);
      signaturePad.clear();
    }

    // 🔥 INIT AWAL (kalau langsung buka tab)
    resizeCanvas();

    // 🔥 KEY FIX → saat tab TTD dibuka
    document.querySelector('#nav-ttd-tab')
      .addEventListener('shown.bs.tab', function() {
        resizeCanvas();
        loadTTD(); // 🔥 ini tambahan
      });

    window.addEventListener("resize", resizeCanvas);

    clearBtn.addEventListener("click", function() {
      signaturePad.clear();
    });

    saveBtn.addEventListener("click", function() {

      if (signaturePad.isEmpty()) {
        alert("Tanda tangan kosong!");
        return;
      }

      const dataUrl = signaturePad.toDataURL();

      const urlParams = new URLSearchParams(window.location.search);
      const nomor_visit = urlParams.get("no");

      fetch("controller/visit/saveSignature.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            nomor_visit: nomor_visit,
            nomor_rm: nomor_visit, // sementara pakai ini dulu
            id_patient: id_patient, // kalau ada isi dari API nanti
            ttd: dataUrl
          })
        })
        .then(res => res.json())
        .then(res => {
          console.log(res);

          if (res.status === "success") {

            const baseUrl = window.location.origin + '/medisafe/'; // sesuaikan
            const imageUrl = baseUrl + 'uploads/ttd/' + res.file;

            const preview = document.getElementById("ttd-preview");
            const empty = document.getElementById("ttd-empty");

            preview.src = imageUrl;
            preview.style.display = "block";
            empty.style.display = "none";

            alert("✅ Tanda tangan berhasil disimpan");
          } else {
            alert("❌ Gagal: " + res.message);
          }
        })
        .catch(err => {
          console.error(err);
          alert("❌ Error server");
        });

    });

    function loadTTD() {
      const urlParams = new URLSearchParams(window.location.search);
      const nomor = urlParams.get("pt");

      fetch("controller/visit/getSignature.php?pt=" + nomor)
        .then(res => res.json())
        .then(res => {
          if (res.status === "success") {

            const baseUrl = window.location.origin + '/medisafe/';
            const imageUrl = baseUrl + 'uploads/ttd/' + res.file;

            document.getElementById("ttd-preview").src = imageUrl;
            document.getElementById("ttd-preview").style.display = "block";
            document.getElementById("ttd-empty").style.display = "none";
          }
        });
    }

  });
</script>
<script>
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNumber = urlParams.get("pt");

    if (patientNumber) {
      $.ajax({
        url: "controller/master/getPatientDocs.php",
        type: "GET",
        dataType: "json", // 🔥 penting
        data: {
          id_patient: patientNumber
        },
        success: function(data) {

          console.log("DATA DOC:", data); // 🔥 debug

          if (data.status === "success") {
            updateStatus("statusKtp", data.files.patient_ktp_file, "KTP");
            updateStatus("statusKk", data.files.patient_kk_file, "KK");
            updateStatus("statusBpjs", data.files.patient_bpjs_file, "BPJS");
            updateStatus("statusFoto", data.files.patient_foto, "Foto");
          }

        },
        error: function(xhr) {
          console.log("ERROR:", xhr.responseText);
        }
      });
    }


    function updateStatus(elementId, fileName, label) {
      const baseUrl = document.querySelector("base").href + "uploads/patient/";

      if (fileName && fileName !== "null" && fileName !== null) {

        $("#" + elementId).html(`
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success">${label} sudah upload</span>

     <button type="button" class="btn btn-sm btn-primary"
  onclick="previewFile('${baseUrl + fileName}')">
  Lihat
</button>
      </div>
    `);

      } else {

        $("#" + elementId).html(`
      <span class="text-danger">
        <i class="fas fa-times-circle"></i> Belum upload ${label}
      </span>
    `);

      }
    }


  });
</script>


<script>
  document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNo = urlParams.get("pt");

    // Auto fill form
    if (patientNo) {
      fetch("controller/master/patientDetailsController.php?pt=" + patientNo)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const doctor = data.data;
            // 🔥 load provinsi awal
            loadProvinsi(doctor.patient_provinsi, function(provId) {

              loadKabupatenByName(doctor.patient_kabupaten, provId, function(kabId) {

                loadKecamatanByName(doctor.patient_kecamatan, kabId, function(kecId) {

                  loadKelurahanByName(doctor.patient_kelurahan, kecId);

                });

              });

            });
            // 🔥 isi text wilayah lama
            $('#provinsi_text').val(doctor.patient_provinsi);
            $('#kabupaten_text').val(doctor.patient_kabupaten);
            $('#kecamatan_text').val(doctor.patient_kecamatan);
            $('#kelurahan_text').val(doctor.patient_kelurahan);
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
        Swal.fire("Oops!", "Parameter ?pt= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formIdentitas);
      formData.append("id_patient", patientNo);
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
      formData.append("id_patient", patientNo);
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
      formData.append("id_patient", patientNo);
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
  const apiWilayah = "controller/master/wilayah.php";

  // LOAD PROVINSI
  function loadProvinsi(selectedText = null, callback = null) {
    fetch(`${apiWilayah}?type=provinsi`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        let selectedId = null;

        data.forEach(d => {
          if (d.nama.toUpperCase() === selectedText?.toUpperCase()) {
            selectedId = d.id;
          }
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#provinsi').html(html);

        if (selectedId) {
          $('#provinsi').val(selectedId);
          $('#provinsi_text').val(selectedText);

          if (callback) callback(selectedId); // 🔥 lanjut ke kabupaten
        }
      });
  }

  function loadKabupatenByName(nama, provId, callback = null) {
    fetch(`${apiWilayah}?type=kabupaten&id=${provId}`)
      .then(res => res.json())
      .then(data => {
        let selectedId = null;
        let html = '<option value="">PILIH</option>';

        data.forEach(d => {
          if (d.nama.toUpperCase() === nama?.toUpperCase()) {
            selectedId = d.id;
          }
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kabupaten').html(html);

        if (selectedId) {
          $('#kabupaten').val(selectedId);
          $('#kabupaten_text').val(nama);

          if (callback) callback(selectedId); // 🔥 lanjut kecamatan
        }
      });
  }

  function loadKecamatanByName(nama, kabId, callback = null) {
    fetch(`${apiWilayah}?type=kecamatan&id=${kabId}`)
      .then(res => res.json())
      .then(data => {
        let selectedId = null;
        let html = '<option value="">PILIH</option>';

        data.forEach(d => {
          if (d.nama.toUpperCase() === nama?.toUpperCase()) {
            selectedId = d.id;
          }
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kecamatan').html(html);

        if (selectedId) {
          $('#kecamatan').val(selectedId);
          $('#kecamatan_text').val(nama);

          if (callback) callback(selectedId); // 🔥 lanjut kelurahan
        }
      });
  }

  function loadKelurahanByName(nama, kecId) {
    fetch(`${apiWilayah}?type=kelurahan&id=${kecId}`)
      .then(res => res.json())
      .then(data => {
        let selectedId = null;
        let html = '<option value="">PILIH</option>';

        data.forEach(d => {
          if (d.nama.toUpperCase() === nama?.toUpperCase()) {
            selectedId = d.id;
          }
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kelurahan').html(html);

        if (selectedId) {
          $('#kelurahan').val(selectedId);
          $('#kelurahan_text').val(nama);
        }
      });
  }

  $('#kabupaten').on('change', function() {
    let id = $(this).val();

    fetch(`${apiWilayah}?type=kecamatan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kecamatan').html(html);
      });

    $('#kabupaten_text').val($(this).find('option:selected').text());

    // reset bawahnya
    $('#kelurahan').html('<option value="">PILIH</option>');
  });

  $('#kecamatan').on('change', function() {
    let id = $(this).val();

    fetch(`${apiWilayah}?type=kelurahan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kelurahan').html(html);
      });

    $('#kecamatan_text').val($(this).find('option:selected').text());
  });

  $('#kelurahan').on('change', function() {
    $('#kelurahan_text').val($(this).find('option:selected').text());
  });

  $('#provinsi').on('change', function() {
    let id = $(this).val();

    // reset semua dulu
    $('#kabupaten').html('<option value="">PILIH</option>');
    $('#kecamatan').html('<option value="">PILIH</option>');
    $('#kelurahan').html('<option value="">PILIH</option>');

    if (!id) return;

    // 🔥 LOAD KABUPATEN (INI YANG KURANG)
    fetch(`${apiWilayah}?type=kabupaten&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });

        $('#kabupaten').html(html);
      });

    $('#provinsi_text').val($(this).find('option:selected').text());
  });
</script>

<script>
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id_patient = urlParams.get("pt");

    // 🔥 semua input file
    $("input[type='file']").on("change", function() {
      let input = this;
      let fieldName = $(this).attr("name"); // ktp / kk / bpjs / foto

      if (!input.files.length) return;

      let formData = new FormData();
      formData.append("id_patient", id_patient);
      formData.append(fieldName, input.files[0]);

      showStatus(fieldName, "Uploading...", true);
      $(input).prop("disabled", true);

      $.ajax({
        url: "controller/master/uploadPatient.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json", // 🔥 WAJIB TAMBAH INI

        success: function(data) {

          if (data.status === "success") {

            let fileName = data.files[fieldName];

            showStatus(fieldName, fileName, true);

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Upload berhasil',
              showConfirmButton: false,
              timer: 2000
            });

          } else {

            showStatus(fieldName, data.message, false);

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: data.message,
              showConfirmButton: false,
              timer: 2500
            });

          }

          $(input).prop("disabled", false);
        },

        error: function(xhr) {
          console.log(xhr.responseText); // 🔥 debug penting

          showStatus(fieldName, "Upload gagal", false);
          $(input).prop("disabled", false);
        }
      });
    });

    // 🔥 FUNCTION STATUS FINAL
    function showStatus(field, fileName, success) {
      let el = "";

      if (field === "ktp") el = "#statusKtp";
      if (field === "kk") el = "#statusKk";
      if (field === "bpjs") el = "#statusBpjs";
      if (field === "foto") el = "#statusFoto";

      const baseUrl = document.querySelector("base").href + "uploads/patient/";

      // 🔥 kondisi loading
      if (fileName === "Uploading...") {
        $(el).html(`
        <span class="text-warning">
          <i class="fas fa-spinner fa-spin"></i> Uploading...
        </span>
      `);
        return;
      }

      // 🔥 kalau success dan file ada
      if (success && fileName && fileName !== "null") {
        $(el).html(`
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success">Sudah upload</span>

            <button type="button" class="btn btn-sm btn-primary"
              onclick="previewFile('${baseUrl + fileName}')">
              Lihat
            </button>
          </div>
        `);
      } else {
        $(el).html(`
        <span class="text-danger">
          <i class="fas fa-times-circle"></i> ${fileName}
        </span>
      `);
      }
    }

  });

  function previewFile(url) {
    const ext = url.split('.').pop().toLowerCase();

    const img = document.getElementById("previewImage");
    const pdf = document.getElementById("previewPdf");

    // reset dulu
    img.classList.add("d-none");
    pdf.classList.add("d-none");

    if (["jpg", "jpeg", "png"].includes(ext)) {
      img.src = url;
      img.classList.remove("d-none");
    } else if (ext === "pdf") {
      pdf.src = url;
      pdf.classList.remove("d-none");
    }

    // buka modal
    const modal = new bootstrap.Modal(document.getElementById("filePreviewModal"));
    modal.show();
  }

  function showSample() {
    const modal = new bootstrap.Modal(document.getElementById("modalSample"));
    modal.show();
  }
</script>

</html>