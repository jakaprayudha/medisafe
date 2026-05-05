<?php
$title = 'Data Lab';
require '../../controller/view.php';
$kode = $_GET['kode'];
$checklab = mysqli_query($koneksi, "SELECT * FROM  laboratorium_detail WHERE kode = '$kode'");
$datalab = mysqli_fetch_array($checklab);
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
          <div class="card mb-3 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">

              <!-- LEFT: NAMA PEMERIKSAAN -->
              <div>
                <h5 class="fw-bold mb-1">
                  <i class="fas fa-vial me-2 text-primary"></i>
                  <?= $datalab['assemen'] ?>
                </h5>
                <div class="text-muted small">
                  Detail Parameter Pemeriksaan
                </div>
              </div>

              <!-- RIGHT: STATUS -->
              <?php
              $status = $datalab['status'];
              if ($status == 1) { ?>
                <span class="badge bg-success px-3 py-2">
                  AKTIF
                </span>
              <?php  } else { ?>
                <span class="badge bg-danger px-3 py-2">
                  TIDAK AKTIF
                </span>
              <?php   }
              ?>


            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Master Parameter Lab</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-light" id="" onclick="window.history.back()">
                        <i class=" fas fa-arrow-left"></i> Kembali
                      </button>
                      <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus"></i> Tambah
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Urutan</th>
                          <th class="text-dark fw-normal">Parameter</th>
                          <th class="text-dark fw-normal">Satuan</th>
                          <th class="text-dark fw-normal">Minumum</th>
                          <th class="text-dark fw-normal">Maksimum</th>
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
        <h5 class="modal-title">Tambah Parameter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- 🔥 ID & RELASI -->
        <input type="hidden" name="id_item" id="id_item">
        <input type="hidden" name="kode_lab" id="kode_lab" value="<?= $_GET['kode'] ?>">

        <div class="row">

          <!-- URUTAN -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Urutan</label>
              <input type="number" name="urutan" id="urutan" class="form-control" required>
            </div>
          </div>

          <!-- SATUAN -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Satuan</label>
              <input type="text" name="satuan" id="satuan" class="form-control">
            </div>
          </div>

          <!-- NAMA PARAMETER -->
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Parameter</label>
              <input type="text" name="nama" id="nama" class="form-control" required>
            </div>
          </div>

          <!-- MIN -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Minimum</label>
              <input type="text" name="minimum" id="minimum" class="form-control">
            </div>
          </div>

          <!-- MAX -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Maksimum</label>
              <input type="text" name="maksimum" id="maksimum" class="form-control">
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
  const urlParams = new URLSearchParams(window.location.search);
  const kode = urlParams.get('kode');
  const apiUrl = 'controller/master/labDetailController';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      scrollX: true,
      ajax: {
        url: apiUrl + '?kode=' + kode,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-end">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id}">
											<i class="fas fa-pencil"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "urutan": row.urutan,
              "assemen": row.assemen,
              "satuan": row.satuan,
              "maksimum": row.maksimum,
              "minimum": row.minimum,
            };
          });
        }
      },
      columns: [{
          data: "urutan"
        },
        {
          data: "assemen"
        },
        {
          data: "satuan"
        },
        {
          data: "minimum"
        },
        {
          data: "maksimum"
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
      let id = $('#id_item').val();
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

      fetch(apiUrl + `&id_item=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // 🔥 WAJIB
            $('#id').val(d.id);

            // isi field lain
            $('#id_item').val(d.id);
            $('#urutan').val(d.urutan);
            $('#nama').val(d.assemen);
            $('#satuan').val(d.satuan);
            $('#minimum').val(d.minimum);
            $('#maksimum').val(d.maksimum);

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
          fetch(apiUrl + `&id_item=${id}`, {
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



  });
</script>

</html>