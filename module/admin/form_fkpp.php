<?php
$title = 'Form FKPP (Formulir Klaim Pelayanan Primer)';
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
                  <h4 class="mb-3">Form FKPP (Formulir Klaim Pelayanan Primer)</h4>
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
                          <h6 class="fw-bold text-primary mb-2">PENJELASAN FKPP</h6>

                          <p class="mb-0" style="font-size: 0.9rem; color:#003366; line-height:1.55;">
                            <strong>Formulir Klaim Pelayanan Primer (FKPP)</strong> adalah dokumen yang digunakan oleh fasilitas kesehatan tingkat pertama (FKTP)
                            untuk mengajukan klaim pelayanan kepada BPJS Kesehatan. FKPP berisi informasi pelayanan medis yang telah diberikan kepada pasien
                            sebagai dasar proses verifikasi dan pembayaran klaim.
                            <br><br>
                            Saat ini, proses unggah dokumen FKPP masih dilakukan secara manual sebagai bagian dari mekanisme klaim sebelum adanya
                            <strong>integrasi sistem dengan BPJS Kesehatan</strong>. Apabila fasilitas kesehatan telah terintegrasi, maka proses klaim akan
                            dilakukan secara otomatis melalui sistem, sehingga tidak lagi memerlukan pengunggahan dokumen FKPP secara manual.
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
                    <a href="module/admin/print/formulir_fkpp.php?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                      <button class="btn btn-outline-primary">
                        <iconify-icon icon="mdi:printer-outline"></iconify-icon>
                        Cetak
                      </button>
                    </a>
                    <button class="btn btn-primary" id="openModal">
                      <iconify-icon icon="mdi:upload-outline"></iconify-icon>
                      Upload File FKPP
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

      fetch(`controller/sep/getFKPP.php?no=${no}`)
        .then(r => r.json())
        .then(res => {

          const box = document.getElementById("sep_preview_box");

          if (res.status !== "success") {
            box.innerHTML = `
          <div class="alert alert-danger">
            Gagal mengambil data FKPP
          </div>
        `;
            return;
          }

          // 🔥 FIX DISINI
          const sep = res.fkpp || {};

          if (sep.fkpp_file && sep.fkpp_file !== "") {

            let fileURL = `uploads/fkpp/${sep.fkpp_file}`;

            box.innerHTML = `
          <div class="alert alert-success d-flex justify-content-between align-items-center">
            <div>
              <strong>File FKPP sudah ada:</strong><br>
              <small>${sep.fkpp_file}</small>
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
            <strong>Belum ada file FKPP.</strong>
          </div>
        `;
          }

        })
        .catch(err => {
          console.error("FKPP ERROR:", err);

          document.getElementById("sep_preview_box").innerHTML = `
        <div class="alert alert-danger">
          Error load FKPP
        </div>
      `;
        });
    }
    // MUAT DATA PERTAMA KALI
    loadSEP();


    // ==========================================
    //  👉 MODAL UPLOAD SEP (LIVE REFRESH)
    // ==========================================
    document.getElementById("openModal").addEventListener("click", () => {
      Swal.fire({
        title: "Upload File FKPP",
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
        form.append("fkpp_file", file);
        form.append("id_patient", rm);
        form.append("no_visit", no);

        fetch("controller/fkpp/uploadFKPP.php", {
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