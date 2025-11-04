<?php
$title = 'Penunjang';
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
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Penunjang</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nama Pemeriksaan</th>
                          <th scope="col" class="text-dark fw-normal">Tanggal</th>
                          <th scope="col" class="text-dark fw-normal">File</th>
                          <th scope="col" class="text-dark fw-normal">Sumber Data</th>
                          <th>Keterangan</th>
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
        <input type="hidden" name="id_inspection" id="id_inspection">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="mb-3">
            <label for="inspection_name" class="form-label required">Nama Pemeriksaan</label>
            <input type="text" class="form-control" id="inspection_name" name="inspection_name" placeholder="Contoh: Darah Rutin, Thorax" required>
          </div>
          <div class="mb-3">
            <label for="inspection_date" class="form-label required">Tanggal Pemeriksaan</label>
            <input type="date" value="<?= date('Y-m-d') ?>" class="form-control" id="inspection_date" name="inspection_date" required>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="inspection_results" class="form-label ">File</label>
                <input type="file" class="form-control" id="inspection_results" name="inspection_results">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="inspection_source" class="form-label required ">Sumber Data</label>
                <input type="text" class="form-control" id="inspection_source" name="inspection_source" required>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="inspection_summary" class="form-label">Kesimpulan</label>
            <textarea class="form-control" id="inspection_summary" name="inspection_summary" rows="5"></textarea>
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
  const apiUrl = 'controller/visit/penunjang?no=<?= $_GET['no'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
            <div class="text-center">
              <div class="btn-group btn-group-sm" role="group">
                <a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_inspection}">
                  <i class="fas fa-edit"></i>
                </a>
                <a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_inspection}">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </div>
          `,
              "name": row.inspection_name ?? "-",
              "tanggal": row.inspection_date ?? "-",
              "file": row.inspection_results ?
                `<a href="${row.inspection_results}" target="_blank">Lihat File</a>` : "-",
              "sumber": row.inspection_source ?? "-",
              "kesimpulan": row.inspection_summary ?? "-"
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
          data: "file",
          className: "text-wrap"
        },
        {
          data: "sumber",
          className: "text-wrap"
        },
        {
          data: "kesimpulan",
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
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_inspection').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });


    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this); // ✅ ambil langsung FormData
      let id = $('#id_inspection').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'POST' : 'POST', // biar gampang semua via POST
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

            // isi otomatis berdasarkan name field (kecuali file)
            for (let key in d) {
              if (key !== "inspection_results") {
                $(`[name="${key}"]`).val(d[key]);
              }
            }

            // kalau ada file lama → kasih link preview
            if (d.inspection_results) {
              $("#inspection_results").after(`
            <div id="oldFile" class="mt-2">
              <a href="${d.inspection_results}" target="_blank">Lihat File Lama</a>
            </div>
          `);
            } else {
              $("#oldFile").remove();
            }

            $('#id_inspection').val(d.id_inspection);
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