<?php
$title = 'Dkter';
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
                    <h5 class="card-title fw-semibold">Data Dokter</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/spesialis">
                        <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> Spesialis</button>
                      </a>
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Dokter</th>
                          <th class="text-dark fw-normal">Kategori</th>
                          <th class="text-dark fw-normal">Spesialis</th>
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
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="str" class="form-label">STR </label>
                <input type="text" class="form-control" id="str" name="str">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="nik" class="form-label">NIK </label>
                <input type="text" name="nik" id="nik" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Dokter <span class="text-danger">*</span> </label>
            <input type="text" name="nama" id="nama" class="form-control" required>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span> </label>
                <select name="kategori" required id="kategori" class="form-select">
                  <option value="Dokter Umum">Dokter Umum</option>
                  <option value="Dokter Spesialis">Dokter Spesialis</option>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="spesialis" class="form-label">Spesialis <span class="text-danger">*</span> </label>
                <select name="spesialis" required id="spesialis" class="form-select">
                  <option value="">PILIH</option>
                  <?php
                  $getCategory = tampildata("SELECT * FROM ms_spesialis WHERE status=1");
                  ?>
                  <?php foreach ($getCategory as $category): ?>
                    <option value="<?= $category['spesialis'] ?>"><?= $category['spesialis'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="telepon" class="form-label">No.Telepon </label>
                <input type="tel" name="telepon" id="telepon" class="form-control">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="email" class="form-label">Email </label>
                <input type="email" name="email" id="email" class="form-control">
              </div>
            </div>
          </div>
          <div class="alert alert-warning" role="alert">
            Apabila data medis ingin dikirim ke platform satu sehat kemenkes maka silahkan isi nomor NIK dokter dengan benar karena ini jadi kunci untuk mengirim data medis ke platform satu sehat
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
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'master/' . 'dokterController' ?>';
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
              "nama_dokter": row.nama_dokter || "-",
              "kategori_dokter": row.kategori_dokter || "-",
              "spesialis": row.spesialis || "-",
              "status_dokter": `<span class="badge ${row.status_dokter == 1 ? 'bg-success' : 'bg-danger'} d-block text-center">
                              ${row.status_dokter == 1 ? 'Active' : 'Inactive'}
                            </span>`,
              "actions": `
            <div class="text-center">
              <button class="btn btn-warning edit-btn" data-id="${row.id}">Ubah</button>
              <button class="btn btn-danger delete-btn" data-id="${row.id}">Hapus</button>
            </div>
          `
            };
          });
        }
      },
      "columns": [{
          "data": "nama_dokter"
        },
        {
          "data": "kategori_dokter"
        },
        {
          "data": "spesialis"
        },
        {
          "data": "status_dokter"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const str = document.getElementById("str").value;
      const nik = document.getElementById("nik").value;
      const nama = document.getElementById("nama").value;
      const kategori = document.getElementById("kategori").value;
      const spesialis = document.getElementById("spesialis").value;
      const telepon = document.getElementById("telepon").value;
      const email = document.getElementById("email").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'str': str,
            'nik': nik,
            'nama': nama,
            'kategori': kategori,
            'spesialis': spesialis,
            'telepon': telepon,
            'email': email
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({
              title: 'Berhasil!',
              text: data.message,
              icon: 'success',
              confirmButtonText: 'OK'
            }).then(() => {
              // RESET FORM INPUT
              document.getElementById("addForm").reset();

              // TUTUP MODAL
              $('#add').modal('hide');

              // REFRESH DATA TABLE
              table.ajax.reload(null, false);
            });
          } else {
            Swal.fire({
              title: 'Gagal!',
              text: data.message,
              icon: 'error',
              confirmButtonText: 'Coba Lagi'
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            title: 'Terjadi Kesalahan!',
            text: 'Gagal mengirim data. Coba lagi nanti.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        });
    });
    // Handle delete action
    $(document).on('click', '.delete-btn', function() {
      var id = $(this).data('id'); // Ambil iduser dari data-id
      Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Perform the deletion action using GET method
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE', // Gunakan GET, bukan DELETE
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success').then(() => {
                  table.ajax.reload(null, false); // Reload table without changing page
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

  });
</script>

</html>