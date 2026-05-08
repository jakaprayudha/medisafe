<?php
$title = 'Data Lab';
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
                    <h5 class="card-title fw-semibold">Data Master Laboratorium</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <select id="filterStatus" class="form-select form-select-sm" style="width:150px;">
                        <option value="">Semua</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                      </select>

                      <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus"></i> Tambah
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="col-1">Kode</th>
                          <th class="text-dark fw-normal">Nama Pemeriksaan</th>
                          <th style="display:none;">StatusVal</th>
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
        <input type="hidden" name="id_lab" id="id_lab">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Pemeriksaan</label>
              <input type="text" id="assemen" name="assemen" class="form-control" required>
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
  const apiUrl = 'controller/master/labController';
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
                	<a class="btn btn-primary" href="module/administrator/master-lab-detail?kode=${row.kode}" data-id="${row.id_lab}">
											<i class="fas fa-folder"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_lab}">
											<i class="fas fa-pencil"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_lab}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "kode": row.kode,
              "assemen": row.assemen,
              "status_val": row.status, // 🔥 ini kunci filter
              "status": `
                <label class="switch">
                  <input type="checkbox" class="toggle-status" 
                    data-id="${row.id_lab}" 
                    ${row.status == '1' ? 'checked' : ''}>
                  <span class="slider round"></span>
                </label>
                `
            };
          });
        }
      },
      columns: [{
          data: "kode"
        },
        {
          data: "assemen"
        },
        // 🔥 hidden column (logic only)
        {
          data: "status_val",
          visible: false
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
      footerCallback: function(row, data) {
        let totalAktif = data.reduce((a, b) => {
          return a + (b.status_val == 1 ? 1 : 0);
        }, 0);

      }
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      let filter = $('#filterStatus').val();

      if (!filter) return true;

      let rowData = table.row(dataIndex).data();

      return rowData.status_val == filter;
    });

    $('#filterStatus').on('change', function() {
      table.draw();
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
      let id = $('#id_lab').val();
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

            // 🔥 WAJIB
            $('#id_lab').val(d.id_lab);

            // isi field lain
            $('#assemen').val(d.assemen);

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
          body: `id_lab=${id}&status=${status}`
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