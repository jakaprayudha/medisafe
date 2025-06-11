<?php
$title = 'Karyawan';
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
  <style>
    .main-content.fade-out {
      opacity: 0;
      transition: opacity 0.5s ease-out;
    }
  </style>
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
          <div class="main-content">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a href="module/admin/employee">
                  <button class="nav-link" id="office-tab" type="button" role="tab" aria-controls="office-tab-pane" aria-selected="true">Karyawan Kantor</button>
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a href="module/admin/employee_sales">
                  <button class="nav-link active" id="sales-tab" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="false">Karyawan Sales</button>
                </a>
              </li>
            </ul>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="sales-tab-pane" role="tabpanel" aria-labelledby="sales-tab" tabindex="0">
                <div class="row mt-2">
                  <div class="col-lg-12 d-flex align-items-stretch">
                    <div class="card w-100">
                      <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="card-title fw-semibold">Data Karyawan</h5>
                          <!-- Grup tombol di sisi kanan -->
                          <div class="d-flex ms-auto gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                          </div>
                        </div>
                        <div class="table-responsive" data-simplebar>
                          <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                            <thead>
                              <tr>
                                <th scope="col" class="text-dark fw-normal">Nama Karyawan</th>
                                <th class="text-dark fw-normal">Telepon</th>
                                <th class="text-dark fw-normal">Email</th>
                                <th class="text-dark fw-normal">Alamat</th>
                                <th class="text-dark fw-normal">Description</th>
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
            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span> </label>
            <input type="text" name="nama" id="nama" class="form-control" required>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="telepon" class="form-label">No.Telepon <span class="text-danger">*</span> </label>
                <input type="tel" required name="telepon" id="telepon" class="form-control">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="email" class="form-label">E-Mail </label>
                <input type="email" name="email" id="email" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
            <textarea name="alamat" required id="alamat" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi </label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
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
            <input type="text" name="editnama" id="editnama" class="form-control" required>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="edittelepon" class="form-label">No.Telepon <span class="text-danger">*</span> </label>
                <input type="tel" required name="edittelepon" id="edittelepon" class="form-control">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="editemail" class="form-label">E-Mail </label>
                <input type="email" name="editemail" id="editemail" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="editalamat" class="form-label">Alamat <span class="text-danger">*</span></label>
            <textarea name="alamat" required id="editalamat" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="editdeskripsi" class="form-label">Deskripsi </label>
            <textarea name="deskripsi" id="editdeskripsi" class="form-control" rows="3"></textarea>
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
  $(document).ready(function() {
    $(".nav-tabs a").click(function(event) {
      event.preventDefault(); // Mencegah pindah langsung

      var targetUrl = $(this).attr("href"); // Ambil URL tujuan

      $(".main-content").addClass("fade-out"); // Tambahkan efek fade-out ke konten saja

      setTimeout(function() {
        window.location.href = targetUrl; // Arahkan ke halaman setelah animasi selesai
      }, 300); // Tunggu 0.5 detik
    });
  });
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'employee/' . 'employeeSalesController' ?>';
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
                      <button class="btn btn-warning edit-btn" data-id="${row.id_employee}">Ubah</button>
                      <button class="btn btn-danger delete-btn" data-id="${row.id_employee}">Hapus</button>
                  </div>
              `,
              "employee_name": row.employee_name,
              "employee_phone": row.employee_phone,
              "employee_email": row.employee_email,
              "employee_address": row.employee_address,
              "employee_description": row.employee_description,
              "employee_status": '<span class="badge ' + (row.employee_status == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.employee_status == 1 ? 'Active' : 'Inactive') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "employee_name"
        },
        {
          "data": "employee_phone"
        },
        {
          "data": "employee_email"
        },
        {
          "data": "employee_address"
        },
        {
          "data": "employee_description"
        },
        {
          "data": "employee_status"
        },
        {
          "data": "actions"
        }
      ]
    });
    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const nama = document.getElementById("nama").value;
      const telepon = document.getElementById("telepon").value;
      const email = document.getElementById("email").value;
      const alamat = document.getElementById("alamat").value;
      const deskripsi = document.getElementById("deskripsi").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'nama': nama,
            'telepon': telepon,
            'email': email,
            'alamat': alamat,
            'deskripsi': deskripsi
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
            $('#editnama').val(user.employee_name);
            $('#edittelepon').val(user.employee_phone);
            $('#editemail').val(user.employee_email);
            $('#editalamat').val(user.employee_address);
            $('#editdeskripsi').val(user.employee_description);
            // Show the modal
            $('#edit').modal('show'); // Show the modal after populating the form
            // Store the user id in the form for later use
            $('#editForm').data('id_employee', user.id_employee);
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

      var userId = $(this).data('id_employee'); // Get user id from form data
      var nama = $('#editnama').val();
      var telepon = $('#edittelepon').val();
      var email = $('#editemail').val();
      var alamat = $('#editalamat').val();
      var deskripsi = $('#editdeskripsi').val();

      // Create the data to send with the PUT request
      var data = {
        iduser: userId,
        nama: nama,
        telepon: telepon,
        email: email,
        alamat: alamat,
        deskripsi: deskripsi
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