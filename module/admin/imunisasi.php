<?php
$title = 'Imunisasi';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
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
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Imunisasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Jenis Imunisasi</th>
                          <th scope="col" class="text-dark fw-normal">Tanggal</th>
                          <th scope="col" class="text-dark fw-normal">Dosis</th>
                          <th scope="col" class="text-dark fw-normal">No. Batch</th>
                          <th>Catatan</th>
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
        <input type="hidden" name="id_imunisasi" id="id_imunisasi">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="mb-3">
            <label for="imunisasi_name" class="form-label required">Jenis Imunisasi</label>
            <select class="form-select" id="imunisasi_name" name="imunisasi_name" required>
              <option value="">-- Pilih Jenis Imunisasi --</option>
              <option value="BCG">BCG</option>
              <option value="Hepatitis B">Hepatitis B</option>
              <option value="Polio (OPV/IPV)">Polio (OPV/IPV)</option>
              <option value="DPT">DPT</option>
              <option value="Hib">Hib</option>
              <option value="PCV">PCV (Pneumokokus)</option>
              <option value="Rotavirus">Rotavirus</option>
              <option value="Campak/MR">Campak / MR</option>
              <option value="MMR">MMR</option>
              <option value="Td">Td</option>
              <option value="HPV">HPV</option>
              <option value="Influenza">Influenza</option>
              <option value="COVID-19">COVID-19</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="imunisasi_date" class="form-label required">Tanggal Imunisasi</label>
            <input type="date" value="<?= date('Y-m-d') ?>" class="form-control" id="imunisasi_date" name="imunisasi_date" required>
          </div>

          <div class="mb-3">
            <label for="imunisasi_dosis" class="form-label required">Dosis ke-</label>
            <select class="form-select" id="imunisasi_dosis" name="imunisasi_dosis" required>
              <option value="">-- Pilih Dosis --</option>
              <option value="1">Dosis 1</option>
              <option value="2">Dosis 2</option>
              <option value="3">Dosis 3</option>
              <option value="4">Dosis 4</option>
              <option value="Booster">Booster</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="imunisasi_batch" class="form-label">Nomor Batch Vaksin</label>
            <input type="text" class="form-control" id="imunisasi_batch" name="imunisasi_batch" placeholder="Nomor batch/lot vaksin">
          </div>

          <div class="mb-3">
            <label for="imunisasi_notes" class="form-label">Catatan Tambahan</label>
            <textarea class="form-control" id="imunisasi_notes" name="imunisasi_notes" rows="5"></textarea>
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
  const apiUrl = 'controller/visit/imunisasiController?no=<?= $_GET['no'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return (json.data || []).map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_imunisasi}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_imunisasi}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "name": row.imunisasi_name ?? "-",
              "tanggal": row.imunisasi_date ?? "-",
              "dosis": row.imunisasi_dosis ?? "-",
              "batch": row.imunisasi_batch ?? "-",
              "notes": row.imunisasi_notes ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "name",
          className: "text-wrap"
        },
        {
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "dosis",
          className: "text-wrap"
        },
        {
          data: "batch",
          className: "text-wrap"
        },
        {
          data: "notes",
          className: "text-wrap"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
      ]
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset();
      $('#id_imunisasi').val('');
      $('#imunisasi_date').val('<?= date('Y-m-d') ?>');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_imunisasi').val();

      fetch(apiUrl + (id ? `&id=${id}` : ''), {
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
      fetch(apiUrl + `&id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

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
          fetch(apiUrl + `&id=${id}`, {
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
