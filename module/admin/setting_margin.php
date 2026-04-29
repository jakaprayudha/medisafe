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
          <div class="row g-2">
            <!-- 🔹 FORM -->
            <div class="col-lg-12">
              <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                  <h5 class="fw-bold mb-4">🗂️ Margin Farmasi</h5>
                  <form id="updateForm">
                    <!-- BASIC -->
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label>Margin Obat</label>
                        <div class="input-group mb-3">
                          <input type="text" class="form-control" name="margin_obat" aria-describedby="basic-addon2">
                          <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label>Margin BMHP</label>
                        <div class="input-group mb-3">
                          <input type="text" class="form-control" name="margin_bmhp" aria-describedby="basic-addon2">
                          <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                      </div>
                    </div>
                    <button class="btn btn-primary">💾 Simpan</button>
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
    const apiUrl = 'controller/master/marginFarmasiController';

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
            Swal.fire('Berhasil!', 'Data Margin berhasil diperbarui.', 'success')
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
</script>

</html>