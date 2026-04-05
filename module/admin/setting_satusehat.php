<?php
session_start();
$title = 'Setting Satu Sehat';
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
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Bridging Satu Sehat</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Kredensial Satu Sehat</button>
                      <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Tenaga Medis KTP</button>
                    </div>
                  </nav>
                  <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                      <div class="row mt-4">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="client_id" class="form-label">Client ID</label>
                            <input type="text" class="form-control" id="client_id" name="client_id" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="client_secret" class="form-label">Client Secret</label>
                            <input type="text" class="form-control" id="client_secret" name="client_secret" required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="organization_id" class="form-label">Organization ID</label>
                            <input type="text" class="form-control" id="organization_id" name="organization_id" required>
                          </div>
                        </div>
                      </div>
                      <button class="btn btn-primary col-12">Simpan</button>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                      <div class="table-responsive" data-simplebar>
                        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                          <thead>
                            <tr>
                              <th class="text-dark fw-normal">Nama Dokter</th>
                              <th scope="col" class="text-dark fw-normal">Poliklinik</th>
                              <th scope="col" class="text-dark fw-normal">No.Handphone</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">NIK</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
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

  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_doctor" id="id_doctor">
          <div class="row">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label required" id="comp_code">Nama Dokter</label>
                <input type="text" id="doctor_name" name="doctor_name" class="form-control" readonly>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label required">Nomor KTP (NIK)</label>
                <input type="text" id="doctor_nik" name="doctor_nik" class="form-control" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</body>
<script>
  const apiSatusehat = 'controller/master/settingSatusehatController.php';

  // 🔹 Load data saat halaman dibuka
  $(document).ready(function() {
    fetch(apiSatusehat)
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success' && res.data) {
          $('#client_id').val(res.data.client_id);
          $('#client_secret').val(res.data.client_secret);
          $('#organization_id').val(res.data.organization_id);
        }
      });
  });


  // 🔹 Submit
  $('button.btn-primary').on('click', function() {

    let formData = new URLSearchParams({
      client_id: $('#client_id').val(),
      client_secret: $('#client_secret').val(),
      organization_id: $('#organization_id').val()
    });

    fetch(apiSatusehat, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          Swal.fire('Berhasil', res.message, 'success');
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }
      });
  });
</script>

</html>