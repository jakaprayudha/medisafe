<?php
$title = 'Sales';
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
            <?php
            require 'menu-sales.php';
            ?>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="office-tab-pane" role="tabpanel" aria-labelledby="office-tab" tabindex="0">
                <div class="row mt-2">
                  <div class="col-lg-12 d-flex align-items-stretch">
                    <div class="card w-100">
                      <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="card-title fw-semibold">Data Penawaran</h5>
                          <!-- Grup tombol di sisi kanan -->
                          <div class="d-flex ms-auto gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                          </div>
                        </div>
                        <div class="table-responsive" data-simplebar>
                          <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                            <thead>
                              <tr>
                                <th scope="col" class="text-dark fw-normal">Tanggal</th>
                                <th class="text-dark fw-normal">No.Faktur</th>
                                <th class="text-dark fw-normal">Pelanggan</th>
                                <th class="text-dark fw-normal">Sales</th>
                                <th>Catatan</th>
                                <th scope="col" class="text-dark fw-normal text-center">Status</th>
                                <th scopemei1="col" class="text-dark fw-normal text-center">Actions</th>
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
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span> </label>
                <input type="date" value="<?= date('Y-m-d') ?>" name="tanggal" id="tanggal" class="form-control" required>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="pelanggan" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                <select name="pelanggan" id="pelanggan" class="form-select select2" required>
                  <option value="">PILIH</option>
                  <?php
                  $getpelanggan = tampildata("SELECT * FROM ms_customer WHERE customer_status='1'");
                  foreach ($getpelanggan as $rows) {
                    echo '<option value="' . $rows['id_customer'] . '">' . $rows['customer_name'] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="marketing" class="form-label">Sales <span class="text-danger">*</span></label>
            <select name="marketing" id="marketing" class="form-select select2" required>
              <option value="">PILIH</option>
              <?php
              $getmarketing = tampildata("SELECT * FROM ms_employee WHERE employee_status='1'");
              foreach ($getmarketing as $row) {
                echo '<option value="' . $row['id_employee'] . '">' . $row['employee_name'] . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="catatan" class="form-label">Catatan </label>
            <textarea name="catatan" id="catatan" class="form-control" rows="5"></textarea>
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
  $('#add').on('shown.bs.modal', function() {
    $('#pelanggan').select2({
      dropdownParent: $('#add'), // penting agar dropdown tidak tertutup modal
      placeholder: 'Pilih pelanggan',
      allowClear: true
    });
    $('#marketing').select2({
      dropdownParent: $('#add'), // penting agar dropdown tidak tertutup modal
      placeholder: 'Pilih Sales',
      allowClear: true
    });
  });
</script>



<script>
  $(document).ready(function() {
    let currentPath = window.location.pathname; // contoh: /admin/sales_order
    let currentPage = currentPath.split('/').pop(); // hasil: sales_order

    $(".nav-tabs a").each(function() {
      let tabUrl = $(this).attr("href"); // contoh: admin/sales_order
      let tabPage = tabUrl.split('/').pop(); // hasil: sales_order

      if (currentPage === tabPage) {
        $(this).find("button").addClass("active");
      } else {
        $(this).find("button").removeClass("active");
      }
    });

    $(".nav-tabs a").click(function(event) {
      event.preventDefault();
      var targetUrl = $(this).attr("href");

      $(".main-content").addClass("fade-out");

      setTimeout(function() {
        window.location.href = targetUrl;
      }, 300);
    });
  });
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'sales/' . 'salesQuatation' ?>';
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
                      <button class="btn btn-danger delete-btn" data-id="${row.id_quotation}">Hapus</button>
                  </div>
              `,
              "tanggal": row.tanggal,
              "no_faktur": row.no_faktur,
              "employee_name": row.employee_name,
              "customer_name": row.customer_name,
              "catatan": row.catatan,
              "sales_status": '<span class="badge ' + (row.sales_status == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.sales_status == 1 ? 'Active' : 'Belum Selesai') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "tanggal"
        },
        {
          "data": "no_faktur"
        },
        {
          "data": "customer_name"
        },
        {
          "data": "employee_name"
        },
        {
          "data": "catatan"
        },
        {
          "data": "sales_status"
        },
        {
          "data": "actions"
        }
      ]
    });
    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const tanggal = document.getElementById("tanggal").value;
      const catatan = document.getElementById("catatan").value;
      const pelanggan = document.getElementById("pelanggan").value;
      const marketing = document.getElementById("marketing").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'tanggal': tanggal,
            'catatan': catatan,
            'pelanggan': pelanggan,
            'marketing': marketing
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