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
                    <h5 class="card-title fw-semibold">Formulir Data Farmasi</h5>
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
                        aria-selected="false">Data Persediaan & Harga</button>
                      <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                        data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                        aria-selected="false">Supplier</button>
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
                              <label class="form-label" id="pharmacy_name_trade">ID Pharmacy</label>
                              <input type="text" id="pharmacy_number" name="pharmacy_number" readonly class="form-control bg-light">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label" id="pharmacy_name_trade">Kode Barang</label>
                              <input type="text" id="pharmacy_code" name="pharmacy_code" class="form-control">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required" id="pharmacy_name_generic">Nama Generic</label>
                              <input type="text" id="pharmacy_name_generic" name="pharmacy_name_generic" class="form-control" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required" id="pharmacy_name_trade">Nama Pabrikan/Dagang</label>
                              <input type="text" id="pharmacy_name_trade" name="pharmacy_name_trade" class="form-control" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Kategori</label>
                              <select name="pharmacy_category" class="form-select" id="pharmacy_category" required>
                                <option value="">PILIH</option>
                                <option value="Obat">Obat</option>
                                <option value="BMHP">BMHP</option>
                                <option value="Alkes">Alkes</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Sub Kategori</label>
                              <input type="text" id="pharmacy_sub_category" name="pharmacy_sub_category" class="form-control">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Golongan</label>
                              <select name="pharmcy_golongan" class="form-select" id="pharmcy_golongan" required>
                                <option value="">PILIH</option>
                                <option value="Bebas">Bebas</option>
                                <option value="Bebas Terbatas">Bebas Terbatas</option>
                                <option value="Keras">Keras</option>
                                <option value="Psikotropika">Psikotropika</option>
                                <option value="Narkotika">Narkotika</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Jenis</label>
                              <select name="pharmcy_jenis_drugs" class="form-select" id="pharmcy_jenis_drugs" required>
                                <option value="">PILIH</option>
                                <option value="Generic">Generic</option>
                                <option value="Paten">Paten</option>
                                <option value="Non-Generic">Non-Generic</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label" id="pharmacy_bentuk_sediaan">Bentuk Sediaan</label>
                              <input type="text" id="pharmacy_bentuk_sediaan" name="pharmacy_bentuk_sediaan" class="form-control">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label" id="pharmacy_dosis">Dosis Penggunaan</label>
                              <input type="text" id="pharmacy_dosis" name="pharmacy_dosis" class="form-control">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label" id="pharmacy_unit">Unit Terkecil</label>
                              <input type="text" id="pharmacy_unit" name="pharmacy_unit" class="form-control">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label" id="pharmacy_kemasan">Kemasan</label>
                              <input type="text" id="pharmacy_kemasan" name="pharmacy_kemasan" class="form-control">
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
                      <form id="formPersediaan">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Harga Beli Terakhir</label>
                              <input type="number" id="pharmacy_buy" class="form-control" name="pharmacy_buy">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Harga JUal</label>
                              <input type="number" class="form-control" name="pharmacy_sale" id="pharmacy_sale">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Stock Minumum</label>
                              <input type="number" class="form-control" name="stok_min" id="stok_min" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label required">Stock Maksimum</label>
                              <input type="number" class="form-control" name="stok_max" id="stok_max" required>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Kode E-Katalog</label>
                              <input type="text" class="form-control" name="pharmacy_code_catalog" id="pharmacy_code_catalog">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Fornas</label>
                              <input type="text" class="form-control" name="fornas" id="fornas">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Formularium RS</label>
                              <input type="text" class="form-control" name="formularium_rs" id="formularium_rs">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Status</label>
                              <select name="pharmacy_status" class="form-select" id="pharmacy_status">
                                <option value="">PILIH</option>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Deskripsi</label>
                          <textarea class="form-control" name="pharmacy_description" id="pharmacy_description"></textarea>
                        </div>
                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>

                    <!-- Kepegawaian -->
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel"
                      aria-labelledby="nav-contact-tab" tabindex="0">
                      <form id="formSupplier">
                        <div class="row">
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Supplier</label>
                              <input type="text" class="form-control" name="pharmacy_supplier" id="pharmacy_supplier">
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Pabrik</label>
                              <input type="text" class="form-control" name="pharmacy_factory" id="pharmacy_factory">
                            </div>
                          </div>
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
                              <label class="form-label">Upload Image</label>
                              <input type="file" class="form-control" name="gambar" accept="image/*,application/pdf">
                              <p class="mt-1 small" id="statusImage"></p>
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="mb-3">
                              <label class="form-label">Upload Dokumen Barang</label>
                              <input type="file" class="form-control" name="dokumen" accept="image/*,application/pdf">
                              <p class="mt-1 small" id="statusDocs"></p>
                            </div>
                          </div>
                        </div>
                        <div class="text-end">
                          <button type="submit" class="btn btn-primary">Upload Dokumen</button>
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
</body>
<script>
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNumber = urlParams.get("no"); // ambil param no dari URL

    if (patientNumber) {
      $.ajax({
        url: "controller/master/getPharmacyDocs.php",
        type: "GET",
        data: {
          pharmacy_number: patientNumber
        },
        success: function(res) {
          let data = JSON.parse(res);
          if (data.status === "success") {
            updateStatus("statusImage", data.files.pharmacy_image, "Image");
            updateStatus("statusDocs", data.files.patient_kk, "KK");
          }
        }
      });
    }

    function updateStatus(elementId, fileName, label) {
      const baseUrl = "uploads/pharmacy/";
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
    const urlParams = new URLSearchParams(window.location.search);
    const pharmacy_number = urlParams.get("no");

    $("#formDokumen").on("submit", function(e) {
      e.preventDefault();

      let formData = new FormData(this);
      formData.append("pharmacy_number", pharmacy_number); // tambahkan manual

      $.ajax({
        url: "controller/master/uploadPharmacy.php",
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
    const pharmacyNo = urlParams.get("no");
    console.log(pharmacyNo);

    // Auto fill form
    if (pharmacyNo) {
      fetch("controller/master/pharmacyDetailsController.php?no=" + pharmacyNo)
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

      if (!pharmacyNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formIdentitas);
      formData.append("pharmacy_number", pharmacyNo);
      formData.append("_method", "PUT");

      fetch("controller/master/pharmacyDetailsController.php", {
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
    const formPersediaan = document.getElementById("formPersediaan");
    formPersediaan.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!pharmacyNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formPersediaan);
      formData.append("pharmacy_number", pharmacyNo);
      formData.append("_method", "PUT");

      fetch("controller/master/pharmacyDetailsController.php", {
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
    const formSupplier = document.getElementById("formSupplier");
    formSupplier.addEventListener("submit", function(e) {
      e.preventDefault();

      if (!pharmacyNo) {
        Swal.fire("Oops!", "Parameter ?no= tidak ditemukan!", "error");
        return;
      }

      const formData = new FormData(formSupplier);
      formData.append("pharmacy_number", pharmacyNo);
      formData.append("_method", "PUT");

      fetch("controller/master/pharmacyDetailsController.php", {
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