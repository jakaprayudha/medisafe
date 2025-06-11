<?php
$title = 'Setting Bisnis';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
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
                    <h5 class="card-title fw-semibold">Pengaturan Bisnis </h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <form id="updateForm" method="POST">
                      <div class="mb-3">
                        <label for="nama_bisnis" class="form-label">Nama Bisnis (Toko) <span class="text-danger">*</span> </label>
                        <input type="text" class="form-control" id="nama_bisnis" name="nama_bisnis" required>
                      </div>
                      <div class="mb-3">
                        <label for="telepon" class="form-label">No.Telepon <span class="text-danger">*</span> </label>
                        <input type="tel" class="form-control" id="telepon" name="telepon" required>
                      </div>
                      <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span> </label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="10"></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <?php
            require '../../database/connect.php'; // Koneksi ke database
            // Ambil logo dari database
            $query = "SELECT image_logo FROM setting_bisnis LIMIT 1";
            $result = $koneksi->query($query);
            $data = $result->fetch_assoc();
            $logoPath = isset($data['image_logo']) ? "uploads/" . $data['image_logo'] : "uploads/default.png";
            ?>

            <div class="col-lg-6">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Upload Logo</h5>
                    <div class="d-flex ms-auto gap-2"></div>
                  </div>
                  <div class="text-center mb-3">
                    <img id="previewLogo" src="<?= $logoPath ?>" alt="Logo Bisnis" class="img-fluid rounded" style="max-height: 150px;">
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <form id="uploadForm" enctype="multipart/form-data">
                      <div class="mb-3">
                        <label for="logo" class="form-label">File Logo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="logo" name="logo" required>
                      </div>
                      <button type="submit" class="btn btn-primary">Upload Proses</button>
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
    const apiUrl = '<?php echo $apiUrl . "user/settingController"; ?>';

    // Fungsi untuk mengambil data bisnis (tanpa parameter ID)
    function getSurveyData() {
      fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            let survey = data.user;
            $('#nama_bisnis').val(survey.business_name);
            $('#telepon').val(survey.phone_number);
            $('#alamat').val(survey.address);
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

  $(document).ready(function() {
    $("#uploadForm").on("submit", function(e) {
      e.preventDefault(); // Mencegah submit normal

      let formData = new FormData(this);

      $.ajax({
        url: "controller/user/upload_logo.php", // Endpoint PHP untuk menangani upload
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