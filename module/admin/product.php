<?php
error_reporting(1);
$title = 'Produk';
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
                    <h5 class="card-title fw-semibold">Data Produk</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/product_merk">
                        <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> Merk</button>
                      </a>
                      <a href="module/admin/product_satuan">
                        <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> Satuan</button>
                      </a>
                      <a href="module/admin/product_category">
                        <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> Kategori</button>
                      </a>
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Kode</th>
                          <th scope="col" class="text-dark fw-normal">Nama Produk</th>
                          <th class="text-dark fw-normal">Kategori</th>
                          <th class="text-dark fw-normal">Satuan</th>
                          <th class="text-dark fw-normal">Harga Beli</th>
                          <th class="text-dark fw-normal">Harga Jual</th>
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
                <label for="kode" class="form-label">Kode Produk </label>
                <input type="text" class="form-control" id="kode" name="kode">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="produk" class="form-label">Nama Produk <span class="text-danger">*</span> </label>
                <input type="text" name="produk" id="produk" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span> </label>
                <select name="kategori" required id="kategori" class="form-select">
                  <option value="">PILIH</option>
                  <?php
                  $getCategory = tampildata("SELECT * FROM ms_product_category WHERE status_category=1");
                  ?>
                  <?php foreach ($getCategory as $category): ?>
                    <option value="<?= $category['id_category'] ?>"><?= $category['category_name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="harga_beli" class="form-label">Harga Beli <span class="text-danger">*</span> </label>
                <input type="number" name="harga_beli" id="harga_beli" value="0" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="harga_jual" class="form-label">Harga Jual (Pcs) <span class="text-danger">*</span> </label>
                <input type="number" name="harga_jual" id="harga_jual" value="0" class="form-control" required>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span> </label>
                <input type="text" name="satuan" id="satuan" value="Pcs" class="form-control bg-light" readonly>
              </div>
            </div>
          </div>
          <div class="alert alert-warning" role="alert">
            Apabila ada harga satuan lainnya, maka bisa gunakan fitur detail products untuk menambahkan harga lainnya.
          </div>
          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi </label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"></textarea>
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
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="editkode" class="form-label">Kode Produk </label>
                <input type="text" class="form-control" id="editkode" name="editkode">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="editproduk" class="form-label">Nama Produk <span class="text-danger">*</span> </label>
                <input type="text" name="editproduk" id="editproduk" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="editkategori" class="form-label">Kategori <span class="text-danger">*</span> </label>
                <select name="editkategori" required id="editkategori" class="form-select">
                  <option value="">PILIH</option>
                  <?php
                  $getCategory = tampildata("SELECT * FROM ms_product_category WHERE status_category=1");
                  ?>
                  <?php foreach ($getCategory as $category): ?>
                    <option value="<?= $category['id_category'] ?>"><?= $category['category_name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="editsatuan" class="form-label">Satuan <span class="text-danger">*</span> </label>
                <select name="editsatuan" required id="editsatuan" class="form-select">
                  <option value="">PILIH</option>
                  <?php
                  $getUnit = tampildata("SELECT * FROM ms_product_unit WHERE status_unit=1");
                  ?>
                  <?php foreach ($getUnit as $unit): ?>
                    <option value="<?= $unit['id_unit'] ?>"><?= $unit['unit_name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="editharga_beli" class="form-label">Harga Beli <span class="text-danger">*</span> </label>
                <input type="number" name="editharga_beli" id="editharga_beli" value="0" class="form-control" required>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="editharga_jual" class="form-label">Harga Jual <span class="text-danger">*</span> </label>
                <input type="number" name="editharga_jual" id="editharga_jual" value="0" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="editdeskripsi" class="form-label">Deskripsi </label>
            <textarea name="editdeskripsi" id="editdeskripsi" class="form-control" rows="10"></textarea>
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
  const apiUrl = '<?php echo $apiUrl . 'product/' . 'productController' ?>';
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "scrollX": true, // Aktifkan horizontal scroll
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
                    <form action="module/admin/product_details" method="POST" style="display:inline;">
                      <input type="hidden" name="id_product" value="${row.id_product}">
                      <button type="submit" class="btn btn-info">Details</button>
                    </form>
                      <button class="btn btn-warning edit-btn" data-id="${row.id_product}">Ubah</button>
                      <button class="btn btn-danger delete-btn" data-id="${row.id_product}">Hapus</button>
                  </div>
              `,
              "product_name": row.product_name,
              "category_name": row.category_name,
              "unit_name": row.unit_name,
              "product_base": row.product_base ? parseFloat(row.product_base).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }) : '-', // Format angka ke Rupiah
              "product_price": row.product_price ? parseFloat(row.product_price).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }) : '-', // Format angka ke Rupiah
              "product_status": '<span class="badge ' + (row.product_status == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.product_status == 1 ? 'Active' : 'Inactive') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "product_code"
        }, {
          "data": "product_name"
        },
        {
          "data": "category_name"
        },
        {
          "data": "unit_name"
        },
        {
          "data": "product_base"
        },
        {
          "data": "product_price"
        },
        {
          "data": "product_status"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const kode = document.getElementById("kode").value;
      const produk = document.getElementById("produk").value;
      const kategori = document.getElementById("kategori").value;
      const satuan = document.getElementById("satuan").value;
      const harga_beli = document.getElementById("harga_beli").value;
      const harga_jual = document.getElementById("harga_jual").value;
      const description = document.getElementById("deskripsi").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'kode': kode,
            'produk': produk,
            'kategori': kategori,
            'satuan': satuan,
            'harga_beli': harga_beli,
            'harga_jual': harga_jual,
            'description': description
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
            $('#editkode').val(user.product_code);
            $('#editproduk').val(user.product_name);
            $('#editkategori').val(user.id_category);
            $('#editsatuan').val(user.id_unit);
            $('#editharga_beli').val(user.product_base);
            $('#editharga_jual').val(user.product_price);
            $('#editdeskripsi').val(user.product_description);
            // Show the modal
            $('#edit').modal('show'); // Show the modal after populating the form
            // Store the user id in the form for later use
            $('#editForm').data('id_product', user.id_product);
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

      var userId = $(this).data('id_product'); // Get user id from form data
      var kode = $('#editkode').val();
      var produk = $('#editproduk').val();
      var kategori = $('#editkategori').val();
      var satuan = $('#editsatuan').val();
      var harga_beli = $('#editharga_beli').val();
      var harga_jual = $('#editharga_jual').val();
      var deskripsi = $('#editdeskripsi').val();

      // Create the data to send with the PUT request
      var data = {
        iduser: userId,
        kode: kode,
        produk: produk,
        kategori: kategori,
        satuan: satuan,
        harga_beli: harga_beli,
        harga_jual: harga_jual,
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