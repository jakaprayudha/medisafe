<?php
$title = 'Permintan Farmasi';
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
                          <th class="text-dark fw-normal">ID Order</th>
                          <th class="text-dark fw-normal">Tanggal</th>
                          <th class="text-dark fw-normal">Tipe Obat</th>
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
            <div class="alert alert-primary" role="alert">
              Buat Tiket Order Permintaan Farmasi Sebelum Membuat Isi Rincian Obat yang akan dibuat
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Tipe Obat</label>
              <select class='form-select' required id="tipe_obat" name="tipe_obat">
                <option value="">PILIH</option>
                <option value="Racikan">Racikan</option>
                <option value="Non Racikan">Non Racikan</option>
              </select>
            </div>
          </div>
          <div class="col-6 racikan-field">
            <div class="mb-3">
              <label class="form-label">Jumlah </label>
              <input type="number" name="rck_jumlah" id="rck_jumlah" class='form-control'>
            </div>
          </div>

          <div class="col-6 racikan-field">
            <div class="mb-3">
              <label class="form-label">Satuan </label>
              <input type="text" id='rck_satuan' name='rck_satuan' class='form-control'>
            </div>
          </div>

          <div class="col-12 racikan-field">
            <div class="mb-3">
              <label class="form-label">Signa </label>
              <input type="text" name="rck_signa" id='rck_signa' class='form-control'>
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
      let harga = $(this).find(':selected').data('harga') || 0;
      $('#harga').val(harga);
    });
  });
</script>
<script>
  const apiUrl = 'controller/visit/permintaanFarmasi?no=<?= $_GET['no'] ?>';
  const urlParams = new URLSearchParams(window.location.search);
  const rmeParam = urlParams.get('rme') || 'c'; // default kalau kosong
  const nomorm = urlParams.get('rm') || 'c'; // default kalau kosong

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
								<a class="btn btn-info" 
                  href="module/admin/permintaan_farmasi_details?no=${urlParams.get('no')}&rm=${nomorm}&rme=${rmeParam}&id=${row.id_permintaan_farmasi}">
                  <i class="fas fa-pencil"></i>
                </a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_permintaan_farmasi}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "idorder": row.permintaan_number ?? "-",
              "timestamp": row.created_at ?? "-",
              "tipe_obat": row.tipe_obat ?? "-",
              "catatan": row.catatan_permintaan ?? "-",
              "status_permintaan": (function() {
                let status = row.status_permintaan;

                let badgeClass = '';
                let label = '';

                if (status == 1) {
                  badgeClass = 'bg-danger';
                  label = 'Belum';
                } else if (status == 2) {
                  badgeClass = 'bg-primary';
                  label = 'Persiapan';
                } else if (status == 3) {
                  badgeClass = 'bg-success';
                  label = 'Selesai';
                } else {
                  badgeClass = 'bg-dark';
                  label = 'Belum Dikirim';
                }

                return `<span class="badge ${badgeClass} d-block text-center">${label}</span>`;
              })()
            };
          });
        }
      },
      columns: [{
          data: "idorder"
        },
        {
          data: "timestamp"
        },
        {
          data: "tipe_obat"
        },
        {
          data: "catatan"
        },
        {
          data: "status_permintaan"
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
<script>
  $(document).ready(function() {

    function toggleRacikan() {
      let tipe = $('#tipe_obat').val();

      if (tipe === 'Racikan') {
        $('.racikan-field').show();
      } else {
        $('.racikan-field').hide();
        $('.racikan-field input').val(''); // reset value
      }
    }

    // event change
    $(document).on('change', '#tipe_obat', function() {
      toggleRacikan();
    });

    // initial load
    toggleRacikan();

  });
</script>

</html>