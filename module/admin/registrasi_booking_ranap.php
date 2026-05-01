<?php
$title = 'Permintaan Pasien Rawat Inap';
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
                  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <!-- Judul -->
                    <h5 class="card-title fw-semibold mb-0">Data Pasien Permintaan Rawat Inap</h5>
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <!-- Tombol kembali -->

                      <button type="button" class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Kembali
                      </button>
                      <button class="btn btn-success" id="btnLihatTempatTidur"><i class="fas fa-bed"></i> Lihat Tempat Tidur</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">DPJP</th>
                          <th scope="col" class="text-dark fw-normal">Diagnosa Awal</th>

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



  <?php
  require 'library.php';
  ?>
</body>


<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_visit" id="id_visit">
        <input type="hidden" name="id_patient" id="id_patient"> <!-- 🔹 dari klik add -->
        <input type="hidden" name="user" value="<?= $_SESSION['fullname'] ?>" id="user">
        <input type="hidden" name="id_ranap" id="id_ranap">
        <div class="row">
          <div class="col-12">
            <!-- Data Registrasi -->
            <div class="mb-3">
              <label class="form-label required">Kelas</label>
              <select name="service_class" id="service_class" class="form-select" required>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label required">Nama Kamar</label>
              <select name="room_name" id="room_name" class="form-select" required>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label required">No.Tempat Tidur</label>
              <select name="bed_name" id="bed_name" class="form-select" required>
              </select>
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

<div class="modal fade" id="bedModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">🛏️ Data Tempat Tidur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Kelas</th>
                <th>Nama Kamar</th>
                <th>Nama Bed</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Pengaturan</th> <!-- 🔥 kolom baru -->
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody id="bedTableBody">
              <tr>
                <td colspan="5" class="text-center text-muted">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // 🔹 Load data kelas saat modal dibuka
    $('#programModal').on('show.bs.modal', function() {
      $('#service_class').html('<option value="">Loading...</option>');
      $('#room_name').empty();
      $('#bed_name').empty();

      fetch('controller/visit/getRoomRanap.php?type=service_class')
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let opt = '<option value="">-- Pilih Kelas --</option>';
            resp.data.forEach(v => opt += `<option value="${v}">${v}</option>`);
            $('#service_class').html(opt);
          }
        });
    });

    // 🔹 Saat pilih kelas → load daftar kamar
    $('#service_class').on('change', function() {
      let kelas = $(this).val();
      $('#room_name').html('<option value="">Loading...</option>');
      $('#bed_name').html('');

      if (kelas) {
        fetch(`controller/visit/getRoomRanap.php?type=room_name&value=${kelas}`)
          .then(res => res.json())
          .then(resp => {
            if (resp.status === 'success') {
              let opt = '<option value="">-- Pilih Kamar --</option>';
              resp.data.forEach(r => opt += `<option value="${r.id_room}">${r.room_name}</option>`);
              $('#room_name').html(opt);
            } else {
              $('#room_name').html('<option value="">Tidak ada data</option>');
            }
          });
      } else {
        $('#room_name').html('');
        $('#bed_name').html('');
      }
    });

    // 🔹 Saat pilih kamar → load daftar bed
    $('#room_name').on('change', function() {
      let id_room = $(this).val();
      $('#bed_name').html('<option value="">Loading...</option>');

      if (id_room) {
        fetch(`controller/visit/getRoomRanap.php?type=bed_name&value=${id_room}`)
          .then(res => res.json())
          .then(resp => {
            if (resp.status === 'success') {
              let opt = '<option value="">-- Pilih Tempat Tidur --</option>';
              resp.data.forEach(b => {
                let isUsed = b.is_used == 1;
                opt += `
                <option value="${b.id_bed}" ${isUsed ? 'disabled' : ''}>
                  ${b.bed_name}-${b.bed_gender} 
                  ${isUsed ? '(Terpakai)' : ''}
                </option>
              `;
              });
              $('#bed_name').html(opt);
            } else {
              $('#bed_name').html('<option value="">Tidak ada data</option>');
            }
          });
      } else {
        $('#bed_name').html('');
      }
    });
  });
</script>

<script>
  const apiUrl = 'controller/visit/addListBookingRanap';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                <div class="text-center d-flex justify-content-center gap-1">
                  
                  <button class="btn btn-sm btn-primary approve-btn" 
                          data-id="${row.id_ranap}">
                    <i class="fas fa-check-circle"></i> Approve
                  </button>

                  <button class="btn btn-sm btn-danger cancel-btn" 
                          data-id="${row.id_ranap}">
                    <i class="fas fa-times-circle"></i> Cancel
                  </button>

                </div>
              `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "gender": row.patient_gender ?? "-",
              "dpjp": row.id_doctor ?? "-",
              "diagnosa": row.diagnosa_awal ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        }, {
          data: "rm"
        },
        {
          data: "name"
        },
        {
          data: "gender"
        },
        {
          data: "dpjp"
        },
        {
          data: "diagnosa"
        },

      ]
    });

    // 🔹 Search
    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Approve -> buka modal
    $(document).on('click', '.approve-btn', function() {
      let id = $(this).data('id');
      $('#id_ranap').val(id); // ✅ simpan ke input hidden

      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }
            $('#programModal .modal-title').text('Approve Rawat Inap');
            $('#programModal').modal('show');
          } else {
            Swal.fire('Gagal', resp.message || 'Data tidak ditemukan.', 'error');
          }
        });
    });

    // 🔹 Submit Approve
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('controller/visit/approveRawatInap.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            Swal.fire('Berhasil!', resp.message, 'success');
            $('#programModal').modal('hide');
            $('#periodeTable').DataTable().ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', resp.message, 'error');
          }
        });
    });

    // 🔹 Batal -> panggil cancelRawatInap
    $(document).on('click', '.cancel-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Data permintaan rawat inap ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "controller/visit/cancelRawatInap",
            type: "POST",
            dataType: "json",
            data: {
              id_ranap: id
            },
            success: function(response) {
              if (response.status === "success") {
                Swal.fire({
                  icon: 'success',
                  title: 'Dibatalkan!',
                  text: response.message,
                  timer: 2000,
                  showConfirmButton: false
                });
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() {
              Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
            }
          });
        }
      });
    });


  });
</script>


<script>
  $('#btnLihatTempatTidur').on('click', function() {

    $('#bedModal').modal('show');

    fetch('controller/master/bedController')
      .then(res => res.json())
      .then(res => {

        let html = '';

        if (res.data.length > 0) {

          res.data.forEach((bed, i) => {

            let statusBadge = bed.bed_status == 1 ?
              '<span class="badge bg-warning-subtle text-warning d-block text-center">Digunakan</span>' :
              '<span class="badge bg-success-subtle text-success d-block text-center">Kosong</span>';

            let statusSwitch = `
                <label class="switch">
                  <input type="checkbox" 
                    class="toggle-status-bed"
                    data-id="${bed.id_bed}"
                    ${bed.bed_status == 1 ? 'checked' : ''}>
                  <span class="slider"></span>
                </label>
              `;

            html += `
            <tr>
              <td>${i + 1}</td>
              <td>${bed.service_class}</td>
              <td>${bed.room_name}</td>
              <td>${bed.bed_name}</td>
              <td>${bed.bed_gender || '-'}</td>
              <td>${statusBadge}</td>
               <td>${statusSwitch}</td>
              <td>${bed.bed_notes || '-'}</td>
            </tr>
          `;
          });

        } else {
          html = `<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>`;
        }

        $('#bedTableBody').html(html);

      });

    $(document).on('change', '.toggle-status-bed', function() {

      let el = $(this);
      let id = el.data('id');
      let status = el.is(':checked') ? 1 : 0;

      fetch('controller/master/bedController?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_bed=${id}&bed_status=${status}`
        })
        .then(res => res.json())
        .then(res => {

          if (res.status === 'success') {

            // 🔥 update badge tanpa reload
            let badge = status == 1 ?
              '<span class="badge bg-success">Terisi</span>' :
              '<span class="badge bg-secondary">Kosong</span>';

            el.closest('tr').find('td:eq(3)').html(badge);

          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }

        });

    });

  });
</script>

</html>