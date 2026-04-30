<?php
$title = 'Poliklinik';
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
                    <h5 class="card-title fw-semibold">Data Poliklinik</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nama Faskes</th>
                          <th class="text-dark fw-normal">Orgnization ID</th>
                          <th class="text-dark fw-normal">Client ID</th>
                          <th class="text-dark fw-normal">Client Secret</th>
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
        <input type="hidden" hidden name="id_clinic" id="id_clinic">
        <div class="row">
          <div class="col-12>
            <div class=" mb-3">
            <label class="form-label required" id="comp_code">Orgnization ID</label>
            <input type="text" id="organization_id" name="organization_id" class="form-control" required>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Client ID</label>
              <input type="text" id="client_id" name="client_id" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Client Secret</label>
              <input type="text" id="client_secret" name="client_secret" class="form-control" required>
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
  const apiUrl = 'controller/master/satuSehatController';

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
                      <div class="text-end">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id}">
											<i class="fas fa-edit"></i>
									</a>
								</div>
							</div>
                    `,
              "clinic_name": row.clinic_name,
              "organization_id": row.organization_id,
              "client_id": row.client_id,
              "client_secret": row.client_secret
            };
          });
        }
      },
      columns: [{
          data: "clinic_name"
        },
        {
          data: "organization_id"
        },
        {
          data: "client_id"
        },
        {
          data: "client_secret"
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
      $('#id_clinic').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });
    $('#programForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new URLSearchParams(new FormData(this));
      formData.append('action', 'update');

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Data berhasil disimpan', 'success');
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
            $('#id_clinic').val(d.id); // 🔥 WAJIB

            $('#organization_id').val(d.organization_id);
            $('#client_id').val(d.client_id);
            $('#client_secret').val(d.client_secret);

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });
  });
</script>

</html>