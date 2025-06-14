<?php
$title = 'User';
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
                    <h5 class="card-title fw-semibold">Data User</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama</th>
                          <th class="text-dark fw-normal">Username</th>
                          <th class="text-dark fw-normal">Roles</th>
                          <th>Create</th>
                          <th>Update</th>
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
            <label for="roles" class="form-label">Roles <span class="text-danger">*</span> </label>
            <select name="roles" required id="roles" class="form-select">
              <option value="">PILIH</option>
              <option value="admin">Admin</option>
              <option value="dokter">Dokter</option>
              <option value="perawat">Perawat</option>
              <option value="kasir">Kasir</option>
              <option value="apoteker">Apoteker</option>
              <option value="receptionis">Receptionis</option>
            </select>
          </div>
          <div class="mb-3" id="namaInputGroup">
            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span> </label>
            <input type="text" name="nama" id="nama" class="form-control">
          </div>
          <div class="mb-3 d-none" id="namaDokterGroup">
            <label for="nama_dokter" class="form-label">Pilih Dokter <span class="text-danger">*</span> </label>
            <select name="nama" id="nama_dokter" class="form-select">
              <option value="">PILIH DOKTER</option>
              <?php
              require '../../database/connect.php';
              $dokter = mysqli_query($koneksi, "SELECT nama_dokter FROM ms_dokter ORDER BY nama_dokter ASC");
              while ($d = mysqli_fetch_assoc($dokter)) {
                echo '<option value="' . htmlspecialchars($d['nama_dokter']) . '">' . htmlspecialchars($d['nama_dokter']) . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username <span class="text-danger">*</span> </label>
            <input type="text" name="username" id="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span> </label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="confirmpassword" class="form-label">Confirm Password <span class="text-danger">*</span> </label>
            <input type="password" name="confirmpassword" id="confirmpassword" class="form-control" required>
          </div>
          <div id="passwordError" class="alert alert-danger d-none" role="alert">
            Password dan Konfirmasi Password tidak cocok!
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

<div class="modal fade" id="edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Perubahan Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="editnama" class="form-label">Nama Lengkap <span class="text-danger">*</span> </label>
            <input type="text" name="editnama" id="editnama" class="form-control">
          </div>
          <div class="mb-3">
            <label for="editusername" class="form-label">Username <span class="text-danger">*</span> </label>
            <input type="text" name="editusername" id="editusername" class="form-control" required>
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
  document.getElementById('roles').addEventListener('change', function() {
    const selectedRole = this.value;
    const namaInput = document.getElementById('namaInputGroup');
    const namaDokter = document.getElementById('namaDokterGroup');

    if (selectedRole === 'dokter') {
      namaInput.classList.add('d-none');
      namaDokter.classList.remove('d-none');
      document.getElementById('nama').removeAttribute('required');
      document.getElementById('nama_dokter').setAttribute('required', true);
    } else {
      namaInput.classList.remove('d-none');
      namaDokter.classList.add('d-none');
      document.getElementById('nama').setAttribute('required', true);
      document.getElementById('nama_dokter').removeAttribute('required');
    }
  });
</script>

<script>
  // Mengambil nilai API_URL dari PHP
  $(document).ready(function() {
    $("#confirmpassword").on("keyup", function() {
      var password = $("#password").val();
      var confirmPassword = $(this).val();

      if (confirmPassword.length > 0) { // Hanya cek jika confirmPassword sudah diisi
        if (password !== confirmPassword) {
          $("#passwordError").removeClass("d-none"); // Tampilkan alert
          $(".btn-primary").prop("disabled", true); // Nonaktifkan tombol submit
        } else {
          $("#passwordError").addClass("d-none"); // Sembunyikan alert
          $(".btn-primary").prop("disabled", false); // Aktifkan tombol submit
        }
      } else {
        $("#passwordError").addClass("d-none"); // Sembunyikan alert jika kosong
      }
    });

    // Validasi sebelum submit form
    $("#addForm").on("submit", function(e) {
      var password = $("#password").val();
      var confirmPassword = $("#confirmpassword").val();

      if (password !== confirmPassword) {
        e.preventDefault(); // Stop submit
        $("#passwordError").removeClass("d-none"); // Tampilkan error
        $(".btn-primary").prop("disabled", true);
      }
    });
  });

  const apiUrl = '<?php echo $apiUrl . 'user/' . 'userController' ?>';
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            return {
              "actions": `
                  <div class="text-center">
                      <button class="btn btn-warning edit-btn" data-id="${row.id}">Ubah</button>
                      <button class="btn btn-danger delete-btn" data-id="${row.id}">Hapus</button>
                  </div>
              `,
              "fullname": row.fullname,
              "username": row.username,
              "roles": row.roles,
              "create_at": row.created_at,
              "update_at": row.udpated_at,
              "status_user": '<span class="badge ' + (row.status_user == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.status_user == 1 ? 'Active' : 'Inactive') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "fullname"
        },
        {
          "data": "username"
        },
        {
          "data": "roles"
        },
        {
          "data": "create_at"
        },
        {
          "data": "update_at"
        },
        {
          "data": "status_user"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      let nama = '';
      const roles = document.getElementById("roles").value;
      if (roles === 'dokter') {
        nama = document.getElementById("nama_dokter").value;
      } else {
        nama = document.getElementById("nama").value;
      }
      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'nama': nama,
            'username': username,
            'password': password,
            'roles': roles
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

    // Handle click on edit button
    $(document).on('click', '.edit-btn', function() {
      var userId = $(this).data('id'); // Ambil iduser dari data-id

      // Fetch data user by iduser and populate the form
      fetch(apiUrl + `?id=${userId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            const user = data.user;
            // Populate the modal form with user data
            $('#editnama').val(user.fullname);
            $('#editusername').val(user.username);
            // Show the modal
            $('#edit').modal('show'); // Show the modal after populating the form
            // Store the user id in the form for later use
            $('#editForm').data('id', user.id);
          } else {
            Swal.fire('Gagal!', 'Data user tidak ditemukan.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Terjadi Kesalahan!', 'Gagal memuat data. Coba lagi nanti.', 'error');
        });
    });

    // Handle form submit for updating user
    $('#editForm').on('submit', function(e) {
      e.preventDefault(); // Prevent default form submission

      var userId = $(this).data('id'); // Get user id from form data
      var nama = $('#editnama').val();
      var username = $('#editusername').val();

      // Create the data to send with the PUT request
      var data = {
        iduser: userId,
        nama: nama,
        username: username
      };

      // Send PUT request to update user
      // Kirim request ke server menggunakan fetch
      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(data).toString(),
        })
        .then(response => response.json())
        .then(data => {
          // console.log('Response from server:', data); 
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success').then(() => {
              $('#edit').modal('hide'); // Hide the modal after successful update
              table.ajax.reload(null, false); // Reload the table data (if using DataTables)
            });
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat memperbarui data.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error); // Log error
          Swal.fire('Terjadi Kesalahan!', 'Gagal memperbarui data. Coba lagi nanti.', 'error');
        });
    });
  });
</script>

</html>