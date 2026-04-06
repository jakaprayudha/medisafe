<?php
$title = 'Setting Bisnis';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .logo-wrapper {
      background: #f8f9fa;
      border: 2px dashed #ddd;
      padding: 20px;
      border-radius: 12px;
    }

    .logo-wrapper img {
      max-height: 120px;
      object-fit: contain;
    }

    .card {
      border-radius: 16px;
    }

    .form-control {
      border-radius: 10px;
    }

    .btn {
      border-radius: 10px;
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
          <div class="row g-4">

            <!-- 🔹 FORM -->
            <div class="col-lg-8">
              <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                  <h5 class="fw-bold mb-4">🏥 Informasi Klinik</h5>

                  <form id="updateForm">

                    <!-- BASIC -->
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label>Nama Klinik</label>
                        <input type="text" class="form-control bg-light" readonly name="clinic_name">
                      </div>

                      <div class="col-md-6 mb-3">
                        <label>Nama PIC</label>
                        <input type="text" class="form-control" name="pic_name">
                      </div>
                    </div>

                    <!-- CONTACT -->
                    <h6 class="mt-3 fw-semibold text-primary">Kontak</h6>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <input type="text" class="form-control" name="pic_phone" placeholder="No HP">
                      </div>

                      <div class="col-md-6 mb-3">
                        <input type="email" class="form-control" name="pic_email" placeholder="Email">
                      </div>
                    </div>

                    <!-- ADDRESS -->
                    <h6 class="mt-3 fw-semibold text-primary">Alamat</h6>
                    <div class="mb-3">
                      <textarea class="form-control" name="faskes_address" rows="3"></textarea>
                    </div>

                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="faskes_prov" placeholder="Provinsi">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="faskes_city" placeholder="Kota">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="faskes_district" placeholder="Kecamatan">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="faskes_village" placeholder="Kelurahan">
                      </div>
                    </div>

                    <!-- CONTRACT -->
                    <h6 class="mt-3 fw-semibold text-primary">Kontrak</h6>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <input type="date" class="form-control" name="contract_start">
                      </div>
                      <div class="col-md-6 mb-3">
                        <input type="date" class="form-control" name="contract_end">
                      </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-3">💾 Simpan Perubahan</button>

                  </form>
                </div>
              </div>
            </div>

            <!-- 🔹 LOGO PANEL -->
            <div class="col-lg-4">
              <div class="card shadow-sm border-0 text-center">
                <div class="card-body p-4">

                  <h5 class="fw-bold mb-3">Logo Klinik</h5>

                  <div class="logo-wrapper mb-3">
                    <img id="previewLogo" src="<?= $logoPath ?>" class="img-fluid rounded shadow-sm">
                  </div>

                  <form id="uploadForm" enctype="multipart/form-data">
                    <input type="file" name="logo" id="logo" hidden>

                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="$('#logo').click()">
                      📁 Pilih Logo
                    </button>

                    <button class="btn btn-primary w-100">⬆ Upload</button>
                  </form>

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
  $('#logo').on('change', function(e) {
    let reader = new FileReader();
    reader.onload = function(e) {
      $('#previewLogo').attr('src', e.target.result);
    }
    reader.readAsDataURL(this.files[0]);
  });
  $(document).ready(function() {
    const apiUrl = 'controller/master/faskesProfileController';

    // Fungsi untuk mengambil data bisnis (tanpa parameter ID)
    function getSurveyData() {
      fetch(apiUrl)
        .then(res => res.json())
        .then(res => {

          if (res.status === 'success' && res.data) {

            let d = res.data;

            // 🔥 AUTO BIND (BEST PRACTICE)
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

          }

        })
        .catch(err => console.error(err));
    }

    // Fungsi untuk memperbarui data bisnis
    $('#updateForm').on('submit', function(e) {
      e.preventDefault(); // Hindari reload halaman

      let formData = new FormData(this);

      fetch(apiUrl, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Data bisnis berhasil diperbarui.', 'success')
              .then(() => location.reload());
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data.', 'error');
        });
    });

    // Panggil fungsi untuk mengambil data tanpa parameter
    getSurveyData();
  });

  $(document).ready(function() {
    $("#uploadForm").on("submit", function(e) {
      e.preventDefault(); // Mencegah submit normal

      let formData = new FormData(this);

      $.ajax({
        url: "controller/master/uploadLogoController", // Endpoint PHP untuk menangani upload
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(response) {
          if (response.status === "success") {
            $("#previewLogo").attr("src", "uploads/" + response.file); // Update tampilan gambar
            alert("Upload berhasil!");
          } else {
            alert("Error: " + response.message);
          }
        },
        error: function() {
          alert("Terjadi kesalahan saat mengunggah.");
        }
      });
    });
  });
</script>

</html>