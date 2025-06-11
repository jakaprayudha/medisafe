<?php
session_start();
$title = 'Sales';
require '../../database/connect.php';
require '../../controller/view.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
if (isset($_POST['id_product'])) {
  $_SESSION['id_product'] = $_POST['id_product'];
}
$checkproduct = mysqli_query($koneksi, "SELECT * FROM ms_product WHERE id_product = '" . $_SESSION['id_product'] . "'");
$data = mysqli_fetch_array($checkproduct);
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
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h5 class="card-title mb-0"><?= $data['product_name'] ?></h5>
                  <small>
                    <?php
                    if ($data['product_status'] == 1) { ?>
                      <span class="badge bg-success mt-1">Active</span>
                    <?php  } else { ?>
                      <span class="badge bg-danger mt-1">Non Active</span>
                    <?php    }
                    ?>

                  </small>
                </div>
                <span class="badge bg-info">Harga Beli: <?= number_format($data['product_base']) ?></span>
              </div>
              <p class="card-text mt-2">Kode: <?= $data['product_code'] ?> | Description: <?= $data['product_description'] ?></p>
            </div>
          </div>
          <div class="main-content">
            <?php
            require 'menu-product.php';
            ?>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="office-tab-pane" role="tabpanel" aria-labelledby="office-tab" tabindex="0">
                <div class="row mt-2">
                  <div class="col-lg-12 d-flex align-items-stretch">
                    <div class="card w-100">
                      <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="card-title fw-semibold">Harga Jual </h5>
                          <!-- Grup tombol di sisi kanan -->
                          <div class="d-flex ms-auto gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                          </div>
                        </div>
                        <div class="table-responsive" data-simplebar>
                          <div class="alert alert-success" role="alert">
                            Harga Jual Per Pcs : <strong><?= number_format($data['product_price']) ?></strong> Update terakhir : <?= $data['update_at'] ?>
                          </div>
                          <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                            <thead>
                              <tr>
                                <th class="text-dark fw-normal">Satuan</th>
                                <th class="text-dark fw-normal">Harga Jual</th>
                                <th class="text-dark fw-normal">Catatan</th>
                                <th scope="col" class="text-dark fw-normal text-center">Status</th>
                                <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                              </tr>
                            </thead>
                            <tbody></tbody>
                          </table>
                        </div>
                        <div id="responseMessage"></div>
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

  <div class="modal fade" id="add" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Data</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addForm">
          <input type="hidden" name="product_id" id="product_id" value="<?= $_SESSION['id_product'] ?>" hidden">
          <div class="modal-body">
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span> </label>
                  <select name="satuan" required id="satuan" class="form-select">
                    <option value="">PILIH</option>
                    <?php
                    $getSatuan = tampildata("SELECT * FROM ms_product_unit WHERE status_unit=1");
                    ?>
                    <?php foreach ($getSatuan as $satuan): ?>
                      <option value="<?= $satuan['id_unit'] ?>"><?= $satuan['unit_name'] ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
              </div>
              <div class="col">
                <div class="mb-3">
                  <label for="harga_jual" class="form-label">Harga Jual <span class="text-danger">*</span> </label>
                  <input type="number" name="harga_jual" id="harga_jual" class="form-control" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="catatan" class="form-label">Catatan </label>
              <textarea name="catatan" id="catatan" class="form-control" rows="10"></textarea>
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
          <input type="hidden" name="product_id" id="product_id" value="<?= $_SESSION['id_product'] ?>" hidden">
          <div class="modal-body">
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label for="editsatuan" class="form-label">Satuan <span class="text-danger">*</span> </label>
                  <select name="editsatuan" required id="editsatuan" class="form-select">
                    <option value="">PILIH</option>
                    <?php
                    $getSatuan = tampildata("SELECT * FROM ms_product_unit WHERE status_unit=1");
                    ?>
                    <?php foreach ($getSatuan as $satuan): ?>
                      <option value="<?= $satuan['id_unit'] ?>"><?= $satuan['unit_name'] ?></option>
                    <?php endforeach ?>
                  </select>
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
              <label for="editcatatan" class="form-label">Catatan </label>
              <textarea name="editcatatan" id="editcatatan" class="form-control" rows="10"></textarea>
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
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'product/' . 'priceController' ?>';
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
              "unit_name": row.unit_name,
              "actions": `
                  <div class="text-center">
                      <button class="btn btn-warning edit-btn" data-id="${row.id_price}">Ubah</button>
                      <button class="btn btn-danger delete-btn" data-id="${row.id_price}">Hapus</button>
                  </div>
              `,
              "price": row.price ? parseFloat(row.price).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }) : '-', // Format angka ke Rupiah
              "price_notes": row.price_notes,
              "status_price": '<span class="badge ' + (row.status_price == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.status_price == 1 ? 'Active' : 'Inactive') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "unit_name"
        }, {
          "data": "price"
        },
        {
          "data": "price_notes"
        },
        {
          "data": "status_price"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const satuan = document.getElementById("satuan").value;
      const harga_jual = document.getElementById("harga_jual").value;
      const catatan = document.getElementById("catatan").value;
      const product_id = document.getElementById("product_id").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'satuan': satuan,
            'harga_jual': harga_jual,
            'catatan': catatan,
            'product_id': product_id
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
            $('#editsatuan').val(user.id_unit);
            $('#editharga_jual').val(user.price);
            $('#editcatatan').val(user.price_notes);
            // Show the modal
            $('#edit').modal('show'); // Show the modal after populating the form
            // Store the user id in the form for later use
            $('#editForm').data('id', user.id_price);
          } else {
            Swal.fire('Gagal!', 'Data user tidak ditemukan.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Terjadi Kesalahan!', 'Gagal memuat data. Coba lagi nanti.', 'error');
        });
    });

    $('#editForm').on('submit', function(e) {
      e.preventDefault();

      var userId = $(this).data('id'); // Pastikan atributnya data-id_price
      var satuan = $('#editsatuan').val();
      var harga_jual = $('#editharga_jual').val();
      var catatan = $('#editcatatan').val();

      var data = {
        id_price: userId,
        satuan: satuan,
        harga_jual: harga_jual,
        catatan: catatan
      };
      console.log(data);

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(data).toString(),
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success').then(() => {
              $('#edit').modal('hide');
              table.ajax.reload(null, false);
            });
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat memperbarui data.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Terjadi Kesalahan!', 'Gagal memperbarui data. Coba lagi nanti.', 'error');
        });
    });
  });
</script>

</html>