<?php
$title = 'Dokumen Pasien';
require '../../database/connect.php';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$id_customer = $_SESSION['id_customer'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit WHERE visit_ID='$no' AND id_customer='$id_customer'");
$data = mysqli_fetch_array($check);
$id_patient = $data['id_patient'];
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
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Dokumen Pasien</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/formulir_dokumen?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-outline-primary"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                    </div>

                  </div>
                  <div class="row">

                    <div class="col-md-4">
                      <div class="border rounded p-3 text-center">
                        <h6>KTP</h6>
                        <div id="docKtp"></div>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="border rounded p-3 text-center">
                        <h6>Kartu Keluarga</h6>
                        <div id="docKk"></div>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="border rounded p-3 text-center">
                        <h6>BPJS</h6>
                        <div id="docBpjs"></div>
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

  <div class="modal fade" id="modalView" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Preview Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <img id="previewImg" src="" class="img-fluid d-none" />
          <iframe id="previewPdf" src="" width="100%" height="500px" class="d-none"></iframe>
        </div>
      </div>
    </div>
  </div>
</body>


</html>

<script>
  $(document).ready(function() {

    const urlParams = new URLSearchParams(window.location.search);
    const id_patient = <?= json_encode($id_patient) ?>;
    console.log(id_patient);

    loadDokumen();

    function loadDokumen() {
      $.ajax({
        url: "controller/master/getPatientDocs.php",
        type: "GET",
        dataType: "json",
        data: {
          id_patient: id_patient
        },
        success: function(res) {

          if (res.status === "success") {
            renderDoc("docKtp", res.files.patient_ktp_file, "KTP");
            renderDoc("docKk", res.files.patient_kk_file, "KK");
            renderDoc("docBpjs", res.files.patient_bpjs_file, "BPJS");
          }

        }
      });
    }

    function renderDoc(el, fileName, label) {

      const baseUrl = window.location.origin + "/uploads/patient/";

      if (fileName && fileName !== "null") {

        $("#" + el).html(`
        <div class="d-flex flex-column gap-2">
          <span class="badge bg-success">Sudah upload</span>

          <button class="btn btn-sm btn-primary"
            onclick="viewFile('${baseUrl + fileName}')">
            Lihat
          </button>
        </div>
      `);

      } else {

        $("#" + el).html(`
        <span class="text-danger">
          <i class="fas fa-times-circle"></i> Belum upload
        </span>
      `);

      }
    }

  });

  // 🔥 VIEW FILE MODAL
  function viewFile(url) {

    let ext = url.split('.').pop().toLowerCase();

    $('#previewImg').addClass('d-none');
    $('#previewPdf').addClass('d-none');

    if (ext === 'pdf') {
      $('#previewPdf').attr('src', url).removeClass('d-none');
    } else {
      $('#previewImg').attr('src', url).removeClass('d-none');
    }

    $('#modalView').modal('show');
  }
</script>