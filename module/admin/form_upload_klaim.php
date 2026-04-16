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
                    <div class="alert alert-info d-flex align-items-start gap-2">
                      <i class="fas fa-info-circle mt-1"></i>
                      <div>
                        <strong>Informasi Upload Dokumen:</strong>
                        <ul class="mb-0 small">
                          <li>Format file: PDF</li>
                          <li>Ukuran maksimal: 2 MB</li>
                          <li>Pastikan dokumen jelas dan tidak blur</li>
                        </ul>
                      </div>
                    </div>
                    <form id="formDokumen" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-6">
                          <div class="mb-3">
                            <label class="form-label">Upload SPP (SEP)</label>
                            <input type="file" class="form-control" name="sep" accept="application/pdf">
                            <p class="mt-1 small" id="statusSep"></p>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="mb-3">
                            <label class="form-label">Upload FKPP</label>
                            <input type="file" class="form-control" name="fkpp" accept="application/pdf">
                            <p class="mt-1 small" id="statusFkpp"></p>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="mb-3">
                            <label class="form-label">Upload Informed Consent</label>
                            <input type="file" class="form-control" name="informed" accept="application/pdf">
                            <p class="mt-1 small" id="statusInformed"></p>
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
    </div>
  </div>
  </div>

  <?php
  require 'library.php';
  ?>

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

<script>
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const patientNumber = urlParams.get("no");

    if (patientNumber) {
      $.ajax({
        url: "controller/master/getPatientDocsVisit.php",
        type: "GET",
        dataType: "json", // 🔥 penting
        data: {
          no: patientNumber
        },
        success: function(data) {

          console.log("DATA DOC:", data); // 🔥 debug

          if (data.status === "success") {
            updateStatus("statusSep", data.files.file_spp, "SPP");
            updateStatus("statusFkpp", data.files.file_fkpp, "FKPP");
            updateStatus("statusInformed", data.files.file_informed, "INFORMED");
          }

        },
        error: function(xhr) {
          console.log("ERROR:", xhr.responseText);
        }
      });
    }


    function updateStatus(elementId, fileName, label) {
      const baseUrl = document.querySelector("base").href + "uploads/dokumen/";

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
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const no = urlParams.get("no");

    // 🔥 semua input file
    $("input[type='file']").on("change", function() {
      let input = this;
      let fieldName = $(this).attr("name"); // ktp / kk / bpjs / foto

      if (!input.files.length) return;

      let formData = new FormData();
      formData.append("no", no);
      formData.append(fieldName, input.files[0]);

      if (input.files[0].type !== "application/pdf") {
        Swal.fire({
          icon: 'error',
          title: 'Format salah',
          text: 'Hanya file PDF yang diperbolehkan'
        });
        return;
      }

      showStatus(fieldName, "Uploading...", true);
      $(input).prop("disabled", true);

      $.ajax({
        url: "controller/master/uploadPatientVisit.php",
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
</script>

</html>