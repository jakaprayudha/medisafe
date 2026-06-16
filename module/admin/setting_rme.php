<?php
$title = 'Setting RME';
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
            <div class="col-lg-6 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Pengaturan RME </h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <form id="updateForm" method="POST">
                      <div class="mb-3">
                        <label for="rme_type" class="form-label">Dokumen RME <span class="text-danger">*</span> </label>
                        <select name="rme_type" class="form-select" required id="rme_type">
                          <option value="">PILIH</option>
                          <option value="1">RME Assesment Manual</option>
                          <option value="2">RME Generate Otomatis</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="billing_tarif" class="form-label">Entry Tindakan (Billing) <span class="text-danger">*</span> </label>
                        <select name="billing_tarif" class="form-select" required id="billing_tarif">
                          <option value="">PILIH</option>
                          <option value="1">Manual</option>
                          <option value="2">Otomatis Dengan Master Data</option>
                        </select>
                      </div>
                      <button type="submit" class="btn btn-primary">Simpan</button>
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
  <?php
  require 'library.php';
  ?>
</body>
<script>
  $(document).ready(function() {
    const apiUrl = 'controller/master/rmeController';

    // Fungsi untuk mengambil data bisnis (tanpa parameter ID)
    function getSurveyData() {
      fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            let survey = data.user;
            $('#rme_type').val(survey.rme_type);
            $('#billing_tarif').val(survey.billing_tarif);
          } else {
            Swal.fire('Data tidak ditemukan', 'Pastikan setting bisnis sudah ada.', 'warning');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Error', 'Terjadi kesalahan saat mengambil data.', 'error');
        });
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
</script>

</html>