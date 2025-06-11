<?php
$title = 'Supplier';
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
                    <h5 class="card-title fw-semibold">Data Supplier</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/supplier_category">
                        <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> Kategori</button>
                      </a>
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Supplier</th>
                          <th class="text-dark fw-normal">Kategori</th>
                          <th class="text-dark fw-normal">Telepon</th>
                          <th class="text-dark fw-normal">Fax</th>
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
            <label for="supplier" class="form-label">Nama Supplier <span class="text-danger">*</span> </label>
            <input type="text" name="supplier" id="supplier" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span> </label>
            <select name="kategori" required id="kategori" class="form-select">
              <option value="">PILIH</option>
              <?php
              $getCategory = tampildata("SELECT * FROM ms_supplier_category WHERE status_category=1");
              ?>
              <?php foreach ($getCategory as $category): ?>
                <option value="<?= $category['id_category'] ?>"><?= $category['category_name'] ?></option>
              <?php endforeach ?>
            </select>
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
                <label for="fax" class="form-label">No.Fax </label>
                <input type="tel" name="fax" id="fax" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">E-Mail </label>
            <input type="email" name="email" id="email" class="form-control">
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat </label>
            <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
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
            <label for="editsupplier" class="form-label">Nama Supplier <span class="text-danger">*</span> </label>
            <input type="text" name="editsupplier" id="editsupplier" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="editkategori" class="form-label">Kategori <span class="text-danger">*</span> </label>
            <select name="editkategori" required id="editkategori" class="form-select">
              <option value="">PILIH</option>
              <?php
              $getCategory = tampildata("SELECT * FROM ms_supplier_category WHERE status_category=1");
              ?>
              <?php foreach ($getCategory as $category): ?>
                <option value="<?= $category['id_category'] ?>"><?= $category['category_name'] ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="edittelepon" class="form-label">No.Telepon </label>
                <input type="tel" name="edittelepon" id="edittelepon" class="form-control">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="editfax" class="form-label">No.Fax </label>
                <input type="tel" name="editfax" id="editfax" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="editemail" class="form-label">E-Mail </label>
            <input type="editemail" name="editemail" id="editemail" class="form-control">
          </div>
          <div class="mb-3">
            <label for="editalamat" class="form-label">Alamat </label>
            <textarea name="editalamat" id="editalamat" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="editdeskripsi" class="form-label">Deskripsi </label>
            <textarea name="editdeskripsi" id="editdeskripsi" class="form-control" rows="3"></textarea>
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
  const apiUrl = '<?php echo $apiUrl . 'supplier/' . 'supplierController' ?>';
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
              "product_code": row.product_code,
              "actions": `
                  <div class="text-center">
                      <button class="btn btn-warning edit-btn" data-id="${row.id_supplier}">Ubah</button>
                      <button class="btn btn-danger delete-btn" data-id="${row.id_supplier}">Hapus</button>
                  </div>
              `,
              "supplier_name": row.supplier_name,
              "category_name": row.category_name,
              "supplier_phone": row.supplier_phone,
              "supplier_fax": row.supplier_fax,
              "supplier_email": row.supplier_email,
              "supplier_address": row.supplier_address,
              "supplier_description": row.supplier_description,
              "supplier_status": '<span class="badge ' + (row.supplier_status == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.supplier_status == 1 ? 'Active' : 'Inactive') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "supplier_name"
        },
        {
          "data": "category_name"
        },
        {
          "data": "supplier_phone"
        },
        {
          "data": "supplier_fax"
        },
        {
          "data": "supplier_email"
        },
        {
          "data": "supplier_address"
        },
        {
          "data": "supplier_description"
        },
        {
          "data": "supplier_status"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const supplier = document.getElementById("supplier").value;
      const kategori = document.getElementById("kategori").value;
      const telepon = document.getElementById("telepon").value;
      const fax = document.getElementById("fax").value;
      const email = document.getElementById("email").value;
      const alamat = document.getElementById("alamat").value;
      const deskripsi = document.getElementById("deskripsi").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'supplier': supplier,
            'kategori': kategori,
            'telepon': telepon,
            'fax': fax,
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
            $('#editsupplier').val(user.supplier_name);
            $('#editkategori').val(user.id_category);
            $('#edittelepon').val(user.supplier_phone);
            $('#editfax').val(user.supplier_fax);
            $('#editemail').val(user.supplier_email);
            $('#editalamat').val(user.supplier_address);
            $('#editdeskripsi').val(user.supplier_description);
            // Show the modal
            $('#edit').modal('show'); // Show the modal after populating the form
            // Store the user id in the form for later use
            $('#editForm').data('id_supplier', user.id_supplier);
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

      var userId = $(this).data('id_supplier'); // Get user id from form data
      var supplier = $('#editsupplier').val();
      var kategori = $('#editkategori').val();
      var telepon = $('#edittelepon').val();
      var fax = $('#editfax').val();
      var email = $('#editemail').val();
      var alamat = $('#editalamat').val();
      var deskripsi = $('#editdeskripsi').val();

      // Create the data to send with the PUT request
      var data = {
        iduser: userId,
        supplier: supplier,
        kategori: kategori,
        telepon: telepon,
        fax: fax,
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