<?php
$title = 'Data Faskes';
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
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar-master.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Master Faskes</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th class="text-dark fw-normal col-1">Nomor PKS</th>
                          <th class="text-dark fw-normal col-1">Kode Faskes</th>
                          <th scope="col" class="text-dark fw-normal">Nama Faskes (Klinik)</th>
                          <th class="text-dark fw-normal">Admin</th>
                          <th class="text-dark fw-normal">Mulai</th>
                          <th class="text-dark fw-normal">Berakhir</th>
                          <th class="text-dark fw-normal">Biaya</th>
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
  require '../admin/library.php';
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
        <input type="hidden" name="id" id="id">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Klinik (Faskes)</label>
              <input type="text" id="clinic_name" name="clinic_name" class="form-control" required>
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

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="userForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Admin Klinik</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id_customer_user" name="id_customer">

        <div class="mb-3">
          <label>Nama</label>
          <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  const apiUrl = 'controller/master/faskesController';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-end">
								<div class="btn-group btn-group-sm" role="group">
                	<a class="btn btn-info" href="module/administrator/master-faskes-detail?no=${row.id}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-primary user-btn" href="javascript:;" data-id="${row.id_customer}">
											<i class="fas fa-user"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "id": row.id_customer,
              "pks": row.contract_number,
              "code": row.faskes_code,
              "name": row.clinic_name,
              "pic": row.fullname ? `${row.fullname}` : '-',
              "start": row.contract_start,
              "end": row.contract_end,
              "amount": row.contract_amount,
              "status": `
                <label class="switch">
                  <input type="checkbox" class="toggle-status" 
                    data-id="${row.id}" 
                    ${row.status == '1' ? 'checked' : ''}>
                  <span class="slider round"></span>
                </label>
                `
            };
          });
        }
      },
      columns: [{
          data: "id"
        },
        {
          data: "pks"
        },
        {
          data: "code"
        },
        {
          data: "name"
        },
        {
          data: "pic"
        },
        {
          data: "start"
        },
        {
          data: "end"
        },
        {
          data: "amount",
          render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
        }, {
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
      $('#id').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id').val();

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

    $(document).on('change', '.toggle-status', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}&faskes_status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message, 'error');
          }
        });
    });

    $(document).on('click', '.user-btn', function() {

      let id = $(this).data('id');

      $('#id_customer_user').val(id);
      $('#userForm')[0].reset();

      // 🔥 ambil user existing
      fetch(`controller/master/getUserByCustomer.php?id_customer=${id}`)
        .then(res => res.json())
        .then(res => {

          if (res.status === 'success') {

            let u = res.data;

            // isi form
            $('[name="nama"]').val(u.fullname);
            $('[name="username"]').val(u.username);

            // password kosongkan (security)
            $('[name="password"]').val('');

            $('#userModal .modal-title').text('Edit Admin Klinik');

          } else {
            $('#userModal .modal-title').text('Tambah Admin Klinik');
          }

          $('#userModal').modal('show');
        });

    });

    $('#userForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new URLSearchParams(new FormData(this));

      fetch('controller/master/userAdminController.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire('Berhasil!', res.message, 'success');
            $('#userModal').modal('hide');
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        });
    });
  });
</script>

</html>