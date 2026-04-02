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
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">TTL</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">DPJP</th>
                          <th scope="col" class="text-dark fw-normal">Diagnosa Awal</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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
              resp.data.forEach(b => opt += `<option value="${b.id_bed}">${b.bed_name}-${b.bed_gender}</option>`);
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
              <div class="dropdown text-center">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Aksi
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item approve-btn" href="javascript:;" data-id="${row.id_ranap}">
                      <i class="fas fa-check-circle text-success me-2"></i> Approve
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item cancel-btn text-danger" href="javascript:;" data-id="${row.id_ranap}">
                      <i class="fas fa-times-circle me-2"></i> Batalkan
                    </a>
                  </li>
                </ul>
              </div>
            `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "ttl": (row.patient_place ?? '-') + ' / ' + (row.patient_datebirth ?? '-'),
              "gender": row.patient_gender ?? "-",
              "dpjp": row.doctor_name ?? "-",
              "diagnosa": row.diagnosa_awal ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "rm"
        },
        {
          data: "name"
        },
        {
          data: "ttl"
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
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
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

</html>