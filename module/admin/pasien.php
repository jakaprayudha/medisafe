<?php
$title = 'Pasien';
require '../../controller/view.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
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
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th class="text-dark fw-normal">Nama Pasien</th>
                          <th class="text-dark fw-normal">TTL</th>
                          <th class="text-dark fw-normal">Agama</th>
                          <th class="text-dark fw-normal">P/L</th>
                          <th class="text-dark fw-normal">Alamat</th>
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
<div class="modal fade" id="add" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="nama_pasien" class="form-label">Nama Pasien <span class="text-danger">*</span> </label>
            <input type="text" name="nama_pasien" id="nama_pasien" class="form-control" required>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir </label>
                <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir </label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="gender" class="form-label">Jenis Kelamin </label>
                <select name="gender" id="gender" class="form-select">
                  <option value="Laki Laki">Laki Laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="agama" class="form-label">Agama </label>
                <select name="agama" id="agama" class="form-select">
                  <option value="Islam">Islam</option>
                  <option value="Kristen">Kristen</option>
                  <option value="Katolik">Katolik</option>
                  <option value="Hindu">Hindu</option>
                  <option value="Buddha">Buddha</option>
                  <option value="Konghucu">Konghucu</option>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="telepon" class="form-label">No.Telepon </label>
                <input type="text" name="telepon" id="telepon" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat </label>
            <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="catatan" class="form-label">Catatan Pasien </label>
            <textarea name="catatan" id="catatan" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const apiUrl = '<?php echo $apiUrl . 'master/' . 'pasienController' ?>';
  let editMode = false; // flag untuk tahu tambah / edit
  let editId = null; // simpan id yang sedang diubah

  $(document).ready(function() {
    // Init DataTable
    var table = $('#zero_config').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: json => json.data
      },
      columns: [{
          data: "nomor_rm"
        },
        {
          data: "nama_pasien"
        },
        {
          data: "tanggal_lahir"
        },
        {
          data: "agama"
        },
        {
          data: "gender"
        },
        {
          data: "alamat"
        },
        {
          data: "status_pasien",
          render: d => `<span class="badge ${d == 1 ? 'bg-success':'bg-danger'}">${d == 1 ? 'Active':'Inactive'}</span>`
        },
        {
          data: null,
          render: row => `
            <div class="text-center">
              <button class="btn btn-warning btn-edit" data-id="${row.id}">Ubah</button>
              <button class="btn btn-danger btn-delete" data-id="${row.id}">Hapus</button>
            </div>
          `
        }
      ]
    });

    // Reset form saat modal dibuka
    $('#add').on('hidden.bs.modal', function() {
      editMode = false;
      editId = null;
      document.getElementById("addForm").reset();
      document.querySelector("#add .modal-title").innerText = "Tambah Data";
    });

    // Tambah / Edit submit
    document.getElementById("addForm").addEventListener("submit", function(e) {
      e.preventDefault();

      // ambil semua field sesuai name
      const formData = new FormData(this);
      let method = editMode ? "PUT" : "POST";
      if (editMode) formData.append("id", editId);

      fetch(apiUrl, {
          method: method,
          body: editMode ? new URLSearchParams(formData) : formData
        })
        .then(r => r.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire("Berhasil!", data.message, "success").then(() => {
              $('#add').modal('hide');
              table.ajax.reload(null, false);
            });
          } else {
            Swal.fire("Gagal!", data.message, "error");
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire("Error!", "Terjadi kesalahan.", "error");
        });
    });

    // Klik tombol Edit
    $(document).on("click", ".btn-edit", function() {
      let id = $(this).data("id");
      fetch(apiUrl + "?id=" + id)
        .then(r => r.json())
        .then(res => {
          if (res.status === "success") {
            let user = res.user;
            // isi form sesuai name
            for (const key in user) {
              if (document.querySelector(`[name=${key}]`)) {
                document.querySelector(`[name=${key}]`).value = user[key];
              }
            }
            editMode = true;
            editId = id;
            document.querySelector("#add .modal-title").innerText = "Ubah Data";
            $('#add').modal('show');
          }
        })
    });

    // Hapus
    $(document).on("click", ".btn-delete", function() {
      let id = $(this).data("id");
      Swal.fire({
        title: "Yakin hapus?",
        text: "Data tidak bisa dikembalikan",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus"
      }).then(res => {
        if (res.isConfirmed) {
          fetch(apiUrl + "?id=" + id, {
              method: "DELETE"
            })
            .then(r => r.json())
            .then(d => {
              if (d.status === "success") {
                Swal.fire("Terhapus!", d.message, "success");
                table.ajax.reload(null, false);
              } else {
                Swal.fire("Gagal!", d.message, "error");
              }
            })
        }
      })
    });
  });
</script>

</html>