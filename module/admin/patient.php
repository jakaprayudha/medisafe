<?php
$title = 'Pasien';
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
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Pasien</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <!-- Tombol -->
                      <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalNomorRM">
                        <i class="fas fa-file"></i> Setting Nomor RM
                      </button>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">NIK</th>
                          <th scope="col" class="text-dark fw-normal">NO.BPJS</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Agama</th>
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

<!-- Modal -->
<div class="modal fade" id="modalNomorRM" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Setting Nomor RM Tertinggi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formNomorRM">
          <div class="alert alert-warning" role="alert">
            Nomor RM tertinggi ini akan digunakan sebagai referensi untuk nomor rekam medis otomatis lanjutan + 1
          </div>
          <div class="mb-3">
            <label class="form-label">Nomor RM Tertinggi</label>
            <input type="number" class="form-control" name="nomor_rm" id="nomor_rm" required>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-primary">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_patient" id="id_patient">
        <div class="row">
          <div class="col-12">
            <div class="alert alert-warning" role="alert">
              Nomor rekam medis di generate otomatis, untuk melakukan perubahan silahkan klik tombol ubah pada data pasien
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required" id="patient_name">Nama Pasien</label>
              <input type="text" id="patient_name" name="patient_name" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label " id="patient_name">NIK</label>
              <input type="text" id="patient_nik" name="patient_nik" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label " id="patient_name">No.BPJS</label>
              <input type="text" id="patient_bpjs" name="patient_bpjs" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Jenis Kelamin</label>
              <select name="patient_gender" class="form-select" id="patient_gender" required>
                <option value="">PILIH</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Agama</label>
              <select name="patient_religion" class="form-select" id="patient_religion" required>
                <option value="">PILIH</option>
                <option value="Islam">Islam</option>
                <option value="Kristen">Kristen</option>
                <option value="Katolik">Katolik</option>
                <option value="Hindu">Hindu</option>
                <option value="Budha">Budha</option>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Tempat Lahir</label>
              <input type="text" id="patient_place" name="patient_place" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Tanggal Lahir</label>
              <input type="date" id="patient_datebirth" name="patient_datebirth" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label ">No.Handphone</label>
              <input type="text" id="patient_phone" name="patient_phone" class="form-control">
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="patient_address" id="patient_address" class="form-control" rows="5"></textarea>
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
  const apiRM = "controller/master/setRM";

  // Load data ketika modal dibuka
  const modalNomorRM = document.getElementById('modalNomorRM');
  modalNomorRM.addEventListener('show.bs.modal', async function() {
    try {
      let res = await fetch(apiRM);
      let json = await res.json();

      if (json.status === 200 && json.data) {
        document.getElementById("nomor_rm").value = json.data.nomor_rm_end;
      } else {
        document.getElementById("nomor_rm").value = "";
      }
    } catch (err) {
      console.error("Gagal load data:", err);
    }
  });

  // Simpan data
  document.getElementById("formNomorRM").addEventListener("submit", async function(e) {
    e.preventDefault();
    let nomor_rm = document.getElementById("nomor_rm").value;

    try {
      let res = await fetch(apiRM, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          nomor_rm: nomor_rm
        })
      });

      let json = await res.json();
      if (json.status === 200) {
        alert("✅ " + json.message);
        bootstrap.Modal.getInstance(modalNomorRM).hide();
      } else {
        alert("❌ " + json.message);
      }
    } catch (err) {
      console.error("Error:", err);
      alert("Terjadi kesalahan saat simpan data");
    }
  });
</script>
<script>
  const apiUrl = 'controller/master/patientContrroller';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: true, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
                	<a class="btn btn-info" href="module/admin/patient_details?no=${row.patient_number}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_patient}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_patient}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "nik": row.patient_nik ?? "-",
              "bpjs": row.patient_bpjs ?? "-",
              "gender": row.patient_gender ?? "-",
              "agama": row.patient_religion ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "rm"
        }, {
          data: "name"
        },
        {
          data: "nik"
        },
        {
          data: "bpjs"
        },
        {
          data: "gender"
        },
        {
          data: "agama"
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
      $('#id_patient').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_patient').val();

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

              // ✅ SUCCESS
              if (data.status === 'success') {
                Swal.fire('Berhasil!', data.message || 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
              // ❌ ERROR (INI YANG KAMU BELUM ADA)
              else {
                Swal.fire('Gagal!', data.message || 'Tidak bisa menghapus data.', 'error');
              }

            })
            .catch(err => {
              // ❌ NETWORK / SERVER ERROR
              Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
              console.error(err);
            });

        }
      });
    });
  });
</script>

</html>