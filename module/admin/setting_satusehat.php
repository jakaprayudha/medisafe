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
                            <input type="text" class="form-control bg-light" id="client_id" name="client_id" required readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="client_secret" class="form-label">Client Secret</label>
                            <input type="text" class="form-control bg-light" id="client_secret" name="client_secret" required readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="organization_id" class="form-label">Organization ID</label>
                            <input type="text" class="form-control bg-light" id="organization_id" name="organization_id" required readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control bg-light" id="latitude" name="latitude" readonly>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control bg-light" id="longitude" readonly name="longitude">
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control bg-light" id="address" readonly name="address" rows="3"></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                      <div class="table-responsive" data-simplebar>
                        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                          <thead>
                            <tr>
                              <th class="text-dark fw-normal">Nama Dokter</th>
                              <th>ID Satu Sehat</th>
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
  <div class="modal fade" id="modalNik" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formNik">

          <div class="modal-header">
            <h5 class="modal-title">Edit NIK Dokter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <input type="hidden" id="id_doctor">

            <div class="mb-3">
              <label class="form-label">Nama Dokter</label>
              <input type="text" id="doctor_name" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">NIK</label>
              <input type="text" id="doctor_nik" class="form-control" maxlength="16">
              <small class="text-danger d-none" id="nikWarning">
                NIK harus 16 digit
              </small>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSaveNik">Simpan</button>
          </div>

        </form>
      </div>
    </div>
  </div>

</body>
<script>
  const apiSatusehat = 'controller/master/settingSatusehatController.php';

  let tableDokter;

  // ===============================
  // READY
  // ===============================
  $(document).ready(function() {

    // 🔹 load satusehat
    fetch(apiSatusehat)
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success' && res.data) {
          $('#client_id').val(res.data.client_id);
          $('#client_secret').val(res.data.client_secret);
          $('#organization_id').val(res.data.organization_id);
          $('#latitude').val(res.data.latitude);
          $('#longitude').val(res.data.longitude);
          $('#address').val(res.data.address);

          ['#client_id', '#client_secret', '#organization_id', '#latitude', '#longitude', '#address']
          .forEach(id => $(id).prop('readonly', !!$(id).val()));
        }
      });

    // ===============================
    // TAB DOKTER LOAD
    // ===============================
    $('button[data-bs-target="#nav-profile"]').on('shown.bs.tab', function() {

      if ($.fn.DataTable.isDataTable('#periodeTable')) {
        tableDokter.ajax.reload(null, false);
        return;
      }

      tableDokter = $('#periodeTable').DataTable({
        processing: true,
        ajax: {
          url: 'controller/master/dokterController.php',
          dataSrc: function(res) {
            if (res.status !== 'success') return [];

            return res.data.map(row => ({
              nama: row.doctor_name || '-',
              idsh: row.idsh || '-',
              hp: row.doctor_phone || '-',
              nik: row.doctor_nik || '-',
              actions: `
              <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id_doctor}">
                <i class="fas fa-pencil"></i>
              </button>
            `
            }));
          }
        },
        columns: [{
            data: 'nama'
          },
          {
            data: 'idsh'
          },
          {
            data: 'hp'
          },
          {
            data: 'nik'
          },
          {
            data: 'actions',
            orderable: false
          }
        ]
      });

    });

    // ===============================
    // CLICK EDIT
    // ===============================
    $(document).on('click', '.edit-btn', function() {

      if (!tableDokter) return; // 🔥 guard

      let id = $(this).data('id');
      let data = tableDokter.row($(this).closest('tr')).data();

      if (!data) return;

      $('#id_doctor').val(id);
      $('#doctor_name').val(data.nama);
      $('#doctor_nik').val(data.nik === '-' ? '' : data.nik);

      let modal = new bootstrap.Modal(document.getElementById('modalNik'));
      modal.show();
    });

    // ===============================
    // VALIDASI NIK
    // ===============================
    $('#doctor_nik').on('input', function() {
      let val = this.value;

      if (val.length !== 16) {
        $('#nikWarning').removeClass('d-none');
        $('#btnSaveNik').prop('disabled', true);
      } else {
        $('#nikWarning').addClass('d-none');
        $('#btnSaveNik').prop('disabled', false);
      }
    });

    // ===============================
    // SUBMIT
    // ===============================
    $('#formNik').on('submit', function(e) {
      e.preventDefault();

      let id = $('#id_doctor').val();
      let nik = $('#doctor_nik').val();

      $.ajax({
        url: 'controller/master/dokterUpdateController.php',
        type: 'POST',
        data: {
          id_doctor: id,
          doctor_nik: nik
        },
        success: function(res) {


          if (res.status === 'success') {

            let modalEl = document.getElementById('modalNik');
            let modal = bootstrap.Modal.getInstance(modalEl);

            if (modal) modal.hide();

            // 🔥 setelah modal close
            modalEl.addEventListener('hidden.bs.modal', function() {

              tableDokter.ajax.reload(null, false);

              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'NIK berhasil disimpan'
              });

            }, {
              once: true
            });

          } else {
            Swal.fire('Gagal', r.message, 'error');
          }

        }
      });

    });

  });
</script>

</html>