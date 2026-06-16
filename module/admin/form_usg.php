<?php
$title = 'Foto USG';
require '../../database/connect.php';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$patient = mysqli_query($koneksi, "SELECT nomor_rm, id_patient FROM ms_patient WHERE nomor_rm='$rm'");
$datapatient = mysqli_fetch_array($patient);
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
                    <h5 class="card-title fw-semibold">Foto Ultrasonografi (USG)</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/formulir_usg?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-outline-primary"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Tanggal Pemeriksaan</th>
                          <th>Usia Kandungan</th>
                          <th>USG 1</th>
                          <th>USG 2</th>
                          <th>USG 3</th>
                          <th>Interpretasi</th>
                          <th>Dokter</th>
                          <th class="text-center">Actions</th>
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
    <form id="programForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_usg" id="id_usg">
        <input type="hidden" name="nomor_rm" value="<?= $_GET['rm'] ?>">
        <input type="hidden" name="visit_ID" value="<?= $_GET['no'] ?>">

        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="tanggal_pemeriksaan" class="form-label">
                Tanggal Pemeriksaan <span class="text-danger">*</span>
              </label>
              <input type="date" value="<?= date('Y-m-d') ?>" name="tanggal_pemeriksaan" class="form-control" id="tanggal_pemeriksaan" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label for="pilih_usg" class="form-label">
                Pilih USG <span class="text-danger">*</span>
              </label>
              <select name="pilih_usg" id="pilih_usg" class="form-select" required>
                <option value="1">USG 1</option>
                <option value="2">USG 2</option>
                <option value="3">USG 3</option>
              </select>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label for="usia_kandungan" class="form-label">
                Usia Kandungan <span class="text-danger">*</span>
              </label>
              <input type="text" name="usia_kandungan" class="form-control" id="usia_kandungan" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label for="dokter" class="form-label">
                Dokter <span class="text-danger">*</span>
              </label>
              <input type="text" name="dokter" class="form-control" id="dokter" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label for="foto_path" class="form-label">
                File Dokumen <span class="text-danger">*</span>
              </label>
              <input type="file" name="foto_path" class="form-control" id="foto_path" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label for="interpretasi" class="form-label">
                Interpretasi <span class="text-danger">*</span>
              </label>
              <textarea name="interpretasi" id="interpretasi" class="form-control" rows="5"></textarea>
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
<?php
$id_patient = $datapatient['id_patient'];
?>
<script>
  const apiUrl = 'controller/ranap/usgController?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {

          if (!json || json.status !== "success") {
            console.warn("API Error:", json);
            return []; // return kosong agar tidak loading
          }

          if (!Array.isArray(json.data)) {
            return [];
          }

          return json.data.map(row => ({
            actions: `
                <div class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_usg}">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
              `,
            tanggal: row.tanggal_pemeriksaan ?? "-",
            usia_kandungan: row.usia_kandungan ?? "-",
            usg1: row.usg1 ?
              `<img src="${row.usg1}" style="max-width:80px">` : "-",
            usg2: row.usg2 ?
              `<img src="${row.usg2}" style="max-width:80px">` : "-",
            usg3: row.usg3 ?
              `<img src="${row.usg2}" style="max-width:80px">` : "-",
            interpretasi: row.interpretasi ?? "-",
            dokter: row.dokter ?? "-",
          }));
        }
      },
      columns: [{
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "usia_kandungan",
          className: "text-wrap"
        },
        {
          data: "usg1",
          className: "text-wrap"
        },
        {
          data: "usg2",
          className: "text-wrap"
        },
        {
          data: "usg3",
          className: "text-wrap"
        },
        {
          data: "interpretasi",
          className: "text-wrap"
        },
        {
          data: "dokter",
          className: "text-wrap"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
      ],

    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_usg').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this); // WAJIB FORM DATA
      let id = $('#id_usg').val();

      fetch(apiUrl + (id ? `&id=${id}` : ''), {
          method: id ? 'POST' : 'POST', // pakai POST untuk upload
          body: formData, // JANGAN pakai header Content-Type
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
        })
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