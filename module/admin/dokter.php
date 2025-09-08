<?php
$title = 'Dokter';
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
                    <h5 class="card-title fw-semibold">Data Dokter</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#doctorModal"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Dokter</th>
                          <th class="text-dark fw-normal">Kategori</th>
                          <th class="text-dark fw-normal">Spesialis</th>
                          <th class="text-dark fw-normal">No.Handphone</th>
                          <th class="text-dark fw-normal">Email</th>
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
<div class="modal fade" id="doctorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="doctorForm">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah Dokter</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_doctor" id="id_doctor">
          <div class="mb-3">
            <label for="doctor_name" class="form-label required">Nama Dokter</label>
            <input type="text" name="doctor_name" id="doctor_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="doctor_category" class="form-label required">Kategori</label>
            <select name="doctor_category" id="doctor_category" required class="form-select">
              <option value="Umum">Dokter Umum</option>
              <option value="Spesialis">Dokter Spesialis</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="doctor_spesialis" class="form-label required">Spesialis (Poli)</label>
            <select name="doctor_spesialis" id="doctor_spesialis" required class="form-select">
              <option value="">PILIH</option>
              <?php
              $getpoli = tampildata("SELECT * FROM ms_poli ORDER BY poliklinik ASC");
              ?>
              <?php foreach ($getpoli as $poli): ?>
                <option value="<?= $poli['id_poli'] ?>"><?= $poli['poliklinik'] ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="doctor_phone" class="form-label required">Telepon</label>
            <input type="tel" name="doctor_phone" required id="doctor_phone" class="form-control">
          </div>
          <div class="mb-3">
            <label for="doctor_mail" class="form-label">Email</label>
            <input type="email" name="doctor_mail" id="doctor_mail" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light  " data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = 'controller/master/dokterController.php';
  $(document).ready(function() {
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "scrollX": true,
      "ajax": {
        "url": apiUrl,
        "type": "GET",
        "data": function(d) {
          // Kirim parameter 'draw' agar bisa dikembalikan nanti
          d.draw = d.draw || 1;
        },
        "dataSrc": function(json) {
          // Ubah struktur JSON agar sesuai format DataTables
          return json.data.map(function(row) {
            return {
              "nama": row.doctor_name || "-",
              "kategori_dokter": row.doctor_category || "-",
              "spesialis": row.poliklinik || "-",
              "phone": row.doctor_phone || "-",
              "email": row.doctor_mail || "-",
              "actions": `
            <div class="text-center">
             <button class="btn btn-info details-btn" data-id="${row.id_doctor}"><i class="fas fa-info-circle"></i> Detail</button>
              <button class="btn btn-warning edit-btn" data-id="${row.id_doctor}"><i class="fas fa-edit"></i> Ubah</button>
              <button class="btn btn-danger delete-btn" data-id="${row.id_doctor}"><i class="fas fa-trash"></i> Hapus</button>
            </div>
          `
            };
          });
        }
      },
      "columns": [{
          "data": "nama"
        },
        {
          "data": "kategori_dokter"
        },
        {
          "data": "spesialis"
        },
        {
          "data": "phone"
        },
        {
          "data": "email"
        },
        {
          "data": "actions"
        }
      ]
    });
    // Tambah data → buka modal kosong
    function addDokter() {
      document.getElementById("doctorForm").reset();
      document.getElementById("id_doctor").value = "";
      document.getElementById("modalTitle").innerText = "Tambah Dokter";
      $("#doctorModal").modal("show");
    }

    // Edit data → ambil by ID
    function editDokter(id) {
      fetch(apiUrl + "?id=" + id)
        .then(res => res.json())
        .then(res => {
          if (res.status === "success") {
            Object.keys(res.data).forEach(key => {
              if (document.getElementById(key)) {
                document.getElementById(key).value = res.data[key];
              }
            });
            document.getElementById("modalTitle").innerText = "Ubah Dokter";
            $("#doctorModal").modal("show");
          }
        });
    }

    // Submit form (Tambah / Update)
    document.getElementById("doctorForm").addEventListener("submit", function(e) {
      e.preventDefault();
      fetch(apiUrl, {
          method: "POST",
          body: new FormData(this)
        })
        .then(res => res.json())
        .then(res => {
          Swal.fire(res.status, res.message, res.status);
          $("#doctorModal").modal("hide");
          $("#zero_config").DataTable().ajax.reload(null, false);
        });
    });

    // Handle edit action
    $(document).on('click', '.edit-btn', function() {
      var id = $(this).data('id');
      editDokter(id);
    });

    // Handle delete action
    $(document).on('click', '.delete-btn', function() {
      var id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success').then(() => {
                  table.ajax.reload(null, false);
                });
              } else {
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire('Terjadi Kesalahan!', 'Gagal menghapus data. Coba lagi nanti.', 'error');
            });
        }
      });
    });

    // Handle detail action (POST ke session)
    $(document).on('click', '.details-btn', function() {
      var id = $(this).data('id');

      fetch('controller/master/setSessionDoctor.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id_doctor=' + encodeURIComponent(id)
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            // Redirect tanpa id di URL
            window.location.href = 'module/admin/dokterDetail';
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error!', 'Tidak bisa menyimpan session.', 'error');
        });
    });

  });
</script>

</html>