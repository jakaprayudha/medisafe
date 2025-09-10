<?php
$title = 'Doktor';
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
          <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
              <a href="module/admin/pemeriksaan_a?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                <button class="nav-link ">Pemeriksaan Medis</button>
              </a>
              <a href="module/admin/permintaan_farmasi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                <button class="nav-link active">Permintaan Farmasi</button>
              </a>
              <a href="module/admin/vaksin?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                <button class="nav-link">Vaksin</button>
              </a>
              <a href="module/admin/tindakan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                <button class="nav-link">Tindakan</button>
              </a>
              <a href="module/admin/riwayat?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                <button class="nav-link">Riwayat Pengobatan</button>
              </a>
            </div>
          </nav>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Item Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Item</th>
                          <th scope="col" class="text-dark fw-normal">Qty</th>
                          <th scope="col" class="text-dark fw-normal">Signa</th>
                          <th scope="col" class="text-dark fw-normal">Harga</th>
                          <th scope="col" class="text-dark fw-normal">Catatan</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
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
        <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Nama Item <span class="text-danger">*</span> </label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select js-example-basic-item" required>
                <option value="">Select Option</option>
                <?php
                $getbarang = tampildata("SELECT * FROM ms_pharmacy WHERE pharmacy_status='1'");
                ?>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>" data-harga="<?= $barang['pharmacy_sale']; ?>"><?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Harga Dasar</label>
              <input type="number" id="harga" name="harga" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Qty</label>
              <input type="number" id="qty" name="qty" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Signa</label>
              <input type="text" id="signa" name="signa" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="5"></textarea>
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
    $('#id_pharmacy').select2({
      dropdownParent: $('#programModal'),
      width: '100%'
    });

    $('#id_pharmacy').on('change', function() {
      let harga = $(this).find(':selected').data('harga') || '';
      $('#harga').val(harga);
    });
  });
</script>
<script>
  const apiUrl = 'controller/visit/permintaanFarmasi?no=<?= $_GET['no'] ?>';

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
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_permintaan_farmasi}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_permintaan_farmasi}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "nama": row.pharmacy_name_generic + '/' + row.pharmacy_name_trade ?? "-",
              "qty": row.qty ?? "-",
              "signa": row.signa ?? "-",
              "harga": row.harga ?? "-",
              "catatan": row.catatan_permintaan ?? "-",
              "status": row.status_permintaan === '1' ?
                '<span class="badge bg-success text-center d-block">Selesai</span>' : '<span class="badge bg-danger text-center d-block">Belum proses</span>'
            };
          });
        }
      },
      columns: [{
          data: "nama"
        },
        {
          data: "qty"
        },
        {
          data: "signa"
        },
        {
          data: "harga"
        },
        {
          data: "catatan"
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
      $('#id_permintaan_farmasi').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_permintaan_farmasi').val();

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
      fetch(apiUrl + `&id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis field biasa
            for (let key in d) {
              if (key !== "id_pharmacy" && key !== "harga") { // skip select & harga
                $(`[name="${key}"]`).val(d[key]);
              }
            }

            // isi dropdown select2
            $('#id_pharmacy').val(d.id_pharmacy).trigger("change");

            // isi harga langsung dari response DB
            $('#harga').val(d.harga);

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