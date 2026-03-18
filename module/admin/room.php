<?php
$title = 'Ruangan Rawat Inap';
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
                    <h5 class="card-title fw-semibold">Data Ruangan Rawat Inap</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Kelas</th>
                          <th class="text-dark fw-normal">Nama Ruangan</th>
                          <th scope="col" class="text-dark fw-normal">Kapasitas</th>
                          <th class="text-dark fw-normal">Deskripsi</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Status</th>
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
        <input type="hidden" name="id_room" id="id_room">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required" id="service_class">Kelas Layanan</label>
              <select name="service_class" id="service_class" class="form-select" required>
                <option value="">PILIH</option>
                <option value="Kelas I">Kelas I</option>
                <option value="Kelas II">Kelas II</option>
                <option value="Kelas III">Kelas III</option>
                <option value="Kelas VIP">Kelas VIP</option>
                <option value="Kelas VVIP">Kelas VVIP</option>
              </select>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Ruangan</label>
              <input type="text" id="room_name" name="room_name" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Kapasitas Tempat Tidur</label>
              <input type="number" id="room_capacity" name="room_capacity" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Deskripsi</label>
              <textarea name="room_description" rows="5" id="room_description" class="form-control"></textarea>
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
  const apiUrl = 'controller/master/roomController';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
                	<a class="btn btn-info details-btn" href="javascript:;" data-id="${row.id_room}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_room}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_room}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "service_class": row.service_class,
              "name": row.room_name,
              "kapasitas": row.room_capacity,
              "notes": row.room_description,
              "status": `
                <label class="switch">
                  <input type="checkbox" class="toggle-status-room" 
                    data-id="${row.id_room}" 
                    ${row.room_status == '1' ? 'checked' : ''}>
                  <span class="slider"></span>
                </label>
                `
            };
          });
        }
      },
      columns: [{
          data: "service_class"
        },
        {
          data: "name"
        },
        {
          data: "kapasitas"
        },
        {
          data: "notes"
        },
        {
          data: "status"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_room').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_room').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });
    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });

    // 🔹 Details (via session biar aman)
    $(document).on('click', '.details-btn', function() {
      let id = $(this).data('id');

      // Kirim ke server untuk disimpan di session
      fetch('controller/session/set_room_session', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            id_room: id
          })
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            // Redirect setelah session diset
            window.location.href = 'module/admin/room_details';
          } else {
            Swal.fire('Gagal!', resp.message || 'Tidak dapat membuka detail.', 'error');
          }
        });
    });

    $(document).on('change', '.toggle-status-room', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_room=${id}&room_status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message, 'error');
          }
        });
    });


  });
</script>

</html>