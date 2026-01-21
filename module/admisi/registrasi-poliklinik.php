<?php
$title = 'Registrasi Polilklinik';
require '../../controller/view.php';
$source_hub = 'Poliklinik';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Registrasi</h5>
                    <!-- Grup tombol di sisi kanan -->

                    <!-- 🔽 Filter + Tombol Kembali -->
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <form id="filterForm" class="row g-2 align-items-end">
                        <div class="col-auto">
                          <label for="fromDate" class="form-label mb-0">Dari</label>
                          <input type="date" id="fromDate" name="fromDate" class="form-control">
                        </div>
                        <div class="col-auto">
                          <label for="toDate" class="form-label mb-0">Sampai</label>
                          <input type="date" id="toDate" name="toDate" class="form-control">
                        </div>
                        <div class="col-auto">
                          <button type="button" id="btnFilter" class="btn btn-dark">
                            <i class="fas fa-filter"></i> Filter
                          </button>
                        </div>
                        <div class="col-auto">
                          <button type="button" id="btnReset" class="btn btn-light">
                            <i class="fas fa-undo"></i> Reset
                          </button>
                        </div>
                      </form>

                      <!-- Tombol kembali -->
                      <div class="d-flex ms-auto gap-2">
                        <a href="module/admisi/patient-list?type=Poliklinik">
                          <button class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Registrasi</th>
                          <th>Antrian</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Layanan</th>
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
        <input type="hidden" name="id_visit" id="id_visit">
        <input type="hidden" name="id_patient" id="id_patient"> <!-- 🔹 dari klik add -->
        <input type="hidden" name="user" value="<?= $_SESSION['fullname'] ?>" id="user">
        <div class="mb-3">
          <label class="form-label required">Layanan (Poli)</label>
          <select name="id_poli" id="id_poli" class="form-select" required>
            <option value="">PILIH</option>
            <?php
            $getpoli = tampildata("SELECT * FROM ms_poli WHERE poli_status='1'");
            foreach ($getpoli as $poli) :
            ?>
              <option value="<?= $poli['id_poli'] ?>"><?= $poli['poli_name'] ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label required">Dokter</label>
          <select name="id_doctor" id="id_doctor" class="form-select" required>
            <option value="">PILIH</option>
            <?php
            $getdoc = tampildata("SELECT * FROM ms_doctor WHERE doctor_status='1'");
            foreach ($getdoc as $doc) :
            ?>
              <option value="<?= $doc['id_doctor'] ?>"><?= $doc['doctor_name'] ?></option>
            <?php endforeach ?>
          </select>
        </div>


        <div class="mb-3">
          <label class="form-label required">Layanan</label>
          <select name="source_hub" id="source_hub" class="form-select" required>
            <option value="Poliklinik">Poliklinik</option>
            <option value="UGD">UGD</option>
            <option value="Rawat Inap">Rawat Inap</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Catatan</label>
          <textarea name="visit_notes" id="visit_notes" class="form-control" rows="5"></textarea>
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
    var today = new Date().toISOString().split("T")[0];
    var source_hub = '<?= $source_hub ?>';
    $("#fromDate").val(today);
    $("#toDate").val(today);
    const apiUrl = 'controller/admisi/registrasiController';
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          // kirim tanggal filter ke backend
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.source_hub = source_hub;
        },
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-warning edit-btn" href="javascript:;" 
                    data-id="${row.id_visit}" 
                  data-patient="${row.id_patient}" 
                  data-doctor="${row.id_doctor}" 
                  data-poli="${row.id_poli}" 
                  data-source="${row.source_hub}" 
                  data-notes="${row.visit_notes}">
                  
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_visit}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "registrasi": row.visit_ID + '<br>' + row.visit_date + ' ' + row.visit_time ?? "-",
              "antrian": row.visit_antrian ?? "-",
              "nomor_rm": row.nomor_rm ?? "-",
              "nama": row.patient_name ?? "-",
              "gender": row.patient_gender ?? "-",
              "dokter": row.doctor_name ?? "-",
              "layanan": row.poli_name ?? "-",
              "status": row.visit_status === '1' ?
                '<span class="badge bg-success text-center d-block">Aktif</span>' : '<span class="badge bg-danger text-center d-block">Belum Di Layani</span>'
            };
          });
        }
      },
      columns: [{
          data: "registrasi"
        },
        {
          data: "antrian"
        },
        {
          data: "nomor_rm"
        },
        {
          data: "nama"
        },
        {
          data: "gender"
        },
        {
          data: "dokter"
        },
        {
          data: "layanan"
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
      $('#id_visit').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_visit').val();

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

    // filter manual
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    // reset filter ke today
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      table.ajax.reload();
    });
  });
</script>

</html>