<?php
$title = 'Ruangan Rawat Inap';
require '../../database/connect.php';
require '../../controller/view.php';
$idroom = $_SESSION['selected_room_id'];
$check = mysqli_query($koneksi, "SELECT * FROM ms_room WHERE id_room = '$idroom'");
$dataroom = mysqli_fetch_array($check);
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
            <div class="col-12">
              <div class="card shadow-sm border-0">
                <div class="card-body">

                  <div class="row g-3">

                    <div class="col-md-4">
                      <small class="text-muted">Kelas</small>
                      <div class="fw-semibold"><?php echo $dataroom['service_class']; ?></div>
                    </div>

                    <div class="col-md-4">
                      <small class="text-muted">Nama Ruangan</small>
                      <div class="fw-semibold"><?php echo $dataroom['room_name']; ?></div>
                    </div>

                    <div class="col-md-4">
                      <small class="text-muted">Kapasitas</small>
                      <div class="fw-semibold"><?php echo $dataroom['room_capacity']; ?> Orang</div>
                    </div>

                    <div class="col-md-4">
                      <small class="text-muted">Status</small>
                      <div>
                        <?php
                        if ($dataroom['room_status'] == 0) {
                          echo '<span class="badge bg-danger-subtle text-danger">Tidak Aktif</span>';
                        } else {
                          echo '<span class="badge bg-success-subtle text-success">Aktif</span>';
                        }
                        ?>
                      </div>
                    </div>

                    <div class="col-md-8">
                      <small class="text-muted">Deskripsi</small>
                      <div class="fw-semibold">
                        <?php echo $dataroom['room_description']; ?>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Tempat Tidur</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-light" onclick="history.back()"><i class="fas fa-arrow-left"></i> Kembali</button>
                      <button class="btn btn-danger" id="btnResetBed">
                        <i class="fas fa-sync"></i> Reset Bed
                      </button>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal col-2">Nama/Nomor Tempat Tidur</th>
                          <th class="text-dark fw-normal">Kebutuhan (P/L)</th>
                          <th scope="col" class="text-dark fw-normal">Catatan</th>
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
        <input type="hidden" name="id_bed" id="id_bed">
        <input type="hidden" name="id_room" id="id_room" value="<?= $idroom ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required" id="bed_gender">Digunakan Untuk (P/L) </label>
              <select name="bed_gender" id="bed_gender" class="form-select" required>
                <option value="">PILIH</option>
                <option value="Pria">Pria</option>
                <option value="Wanita">Wanita</option>
                <option value="Pria-Wanita">Pria-Wanita</option>
              </select>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama/Nomor Tempat Tidur</label>
              <input type="text" id="bed_name" name="bed_name" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Catatan</label>
              <textarea name="bed_notes" rows="5" id="bed_notes" class="form-control"></textarea>
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
  const apiUrl = 'controller/master/roomDetailsController?no=' + '<?php echo $idroom ?>';
  console.log(apiUrl);

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
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_bed}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_bed}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "bed_name": row.bed_name ?? "-",
              "bed_gender": row.bed_gender ?? "-",
              "bed_notes": row.bed_notes ?? "-",
              "status": row.bed_status == '0' ?
                '<span class="badge bg-warning-subtle text-warning d-block text-center">Digunakan</span>' :
                row.bed_status == '1' ?
                '<span class="badge bg-success-subtle text-success d-block text-center">Kosong</span>' :
                '<span class="badge bg-secondary-subtle text-secondary d-block text-center">Tidak Dipakai</span>'
            };
          });
        }
      },
      columns: [{
          data: "bed_name"
        },
        {
          data: "bed_gender"
        },
        {
          data: "bed_notes"
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
      $('#id_bed').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_bed').val();

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
            id_bed: id
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


  });
</script>

<script>
  $('#btnResetBed').on('click', function() {
    Swal.fire({
      title: 'Reset Semua Bed?',
      text: 'Semua tempat tidur akan dikosongkan!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Reset!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {

        fetch('controller/master/resetBedController', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              id_room: '<?php echo $idroom ?>'
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire('Berhasil!', data.message, 'success');
              $('#periodeTable').DataTable().ajax.reload(null, false);
            } else {
              Swal.fire('Gagal!', data.message, 'error');
            }
          });

      }
    });
  });
</script>

</html>