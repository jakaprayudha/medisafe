<?php
$title = 'Form SEP (Surat Eligibilitas Peserta)';
$no = $_GET['no'];
$rm = $_GET['rm'];
require '../../controller/view.php';
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
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h4 class="mb-3">Form SEP (Surat Eligibilitas Peserta)</h4>
                  <!-- Data Pasien -->
                  <?php require 'card-pasien.php'; ?>
                  <div class="mb-3">
                    <div class="alert alert-info border-2 shadow-sm"
                      role="alert"
                      style="background-color:#e8f4ff; border-color:#0d6efd;">
                      <div class="d-flex align-items-start">

                        <div class="me-3">
                          <iconify-icon icon="mdi:information-outline"
                            style="font-size: 2rem; color:#0d6efd;"></iconify-icon>
                        </div>

                        <div>
                          <h6 class="fw-bold text-primary mb-2">PENJELASAN SEP & INTEGRASI BPJS</h6>

                          <p class="mb-0" style="font-size: 0.9rem; color:#003366; line-height:1.55;">
                            <strong>Surat Eligibilitas Peserta (SEP)</strong> adalah dokumen resmi yang
                            digunakan untuk memvalidasi kepesertaan BPJS Kesehatan dalam setiap layanan medis.
                            SEP memastikan bahwa peserta memiliki hak layanan sesuai ketentuan yang berlaku.
                            <br><br>
                            Saat ini, proses unggah dokumen SEP digunakan untuk kebutuhan klaim sebelum adanya proses adanya <strong>Integrasi dengan BPJS Kesehatan</strong>. Apabila nantinya Faskes telah terintegrasi dengan BPJS Kesehatan, maka proses pembuatan SEP akan dilakukan secara otomatis melalui sistem kami, sehingga menghilangkan kebutuhan untuk mengunggah dokumen SEP secara manual.
                          </p>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="text-end mt-2">
                    <div id="sep_preview_box" class="mt-2"></div>
                    <button class="btn btn-light" onclick="window.history.back()">
                      <iconify-icon icon="mdi:arrow-left"></iconify-icon>
                      Kembail
                    </button>
                    <a href="module/admin/print/formulir_sep.php?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                      <button class="btn btn-outline-primary">
                        <iconify-icon icon="mdi:printer-outline"></iconify-icon>
                        Cetak
                      </button>
                    </a>
                    <button class="btn btn-primary" id="openModal">
                      <iconify-icon icon="mdi:upload-outline"></iconify-icon>
                      Upload File SEP
                    </button>
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
  document.addEventListener("DOMContentLoaded", () => {

    const no = "<?= $_GET['no'] ?>";
    const rm = "<?= $_GET['rm'] ?>";

    // ==========================================
    //  👉 FUNGSI UTAMA: LOAD SEP TANPA REFRESH
    // ==========================================
    function loadSEP() {

      fetch(`controller/sep/getSEP.php?no=${no}`)
        .then(r => r.json())
        .then(res => {

          if (res.status !== "success") return;

          const sep = res.sep;
          const box = document.getElementById("sep_preview_box");

          if (sep && sep.sep_file) {

            let fileURL = `uploads/sep/${sep.sep_file}`;

            box.innerHTML = `
          <div class="alert alert-success d-flex justify-content-between align-items-center">
            <div>
              <strong>File SEP sudah ada:</strong><br>
              <small>${sep.sep_file}</small>
            </div>

            <div>
              <a href="${fileURL}" target="_blank" class="btn btn-sm btn-info">
                <iconify-icon icon="mdi:eye-outline"></iconify-icon>
                Lihat
              </a>
            </div>
          </div>
        `;

          } else {
            box.innerHTML = `
          <div class="alert alert-warning">
            <strong>Belum ada file SEP.</strong>
          </div>
        `;
          }
        });
    }

    // MUAT DATA PERTAMA KALI
    loadSEP();


    // ==========================================
    //  👉 MODAL UPLOAD SEP (LIVE REFRESH)
    // ==========================================
    document.getElementById("openModal").addEventListener("click", () => {
      Swal.fire({
        title: "Upload File SEP",
        html: `<input type="file" id="sep_input" class="form-control mb-3">`,
        showCancelButton: true,
        confirmButtonText: "Upload",
        preConfirm: () => {
          let file = document.getElementById('sep_input').files[0];
          if (!file) {
            Swal.showValidationMessage("Silakan pilih file!");
            return false;
          }
          return file;
        }
      }).then(result => {

        if (!result.isConfirmed) return;
        let file = result.value;

        let form = new FormData();
        form.append("sep_file", file);
        form.append("id_patient", rm);
        form.append("no_visit", no);

        fetch("controller/sep/uploadSEP.php", {
            method: "POST",
            body: form
          })
          .then(r => r.json())
          .then(res => {

            Swal.fire(res.status, res.message, res.status);

            if (res.status === "success") {
              loadSEP(); // ← UPDATE PREVIEW TANPA REFRESH
            }
          });
      });
    });

  });
</script>

</html>