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
                          <h5 class="card-title fw-semibold">Data Pesanan</h5>
                          <!-- Grup tombol di sisi kanan -->
                          <div class="d-flex ms-auto gap-2">
                          </div>
                        </div>
                        <div class="table-responsive" data-simplebar>
                          <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                            <thead>
                              <tr>
                                <th scope="col" class="text-dark fw-normal">Tanggal</th>
                                <th class="text-dark fw-normal">No.Faktur</th>
                                <th class="text-dark fw-normal">Pelanggan</th>
                                <th class="text-dark fw-normal">Marketing</th>
                                <th scope="col" class="text-dark fw-normal text-center">Total Item</th>
                                <th scope="col" class="text-dark fw-normal text-center">Total Transaksi</th>
                                <th scope="col" class="text-dark fw-normal text-center">Total Diskon</th>
                                <th scope="col" class="text-dark fw-normal text-center">Total Bayar</th>
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
<script>
  $(document).ready(function() {
    let currentPath = window.location.pathname;
    let currentPage = currentPath.split('/').pop();

    $(".nav-tabs a").each(function() {
      let tabUrl = $(this).attr("href");
      let tabPage = tabUrl.split('/').pop();
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

    // Format number to IDR
    function formatIDR(number) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(number);
    }

    // URL API
    const apiUrl = '<?php echo $apiUrl . 'sales/' . 'salesQuatation' ?>';

    // Inisialisasi DataTable
    var table = $('#zero_config').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      scrollY: "500px",
      scrollCollapse: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              actions: `
                <div class="text-center">
                  <a href="module/admin/sales_order_item?no=${row.id_quotation}">
                    <button class="btn btn-primary">Tambah Item</button>
                  </a>  
                  <a href="module/admin/sales_payment?no=${row.id_quotation}">
                    <button class="btn btn-success">Bayar</button>
                  </a>  
                </div>
              `,
              tanggal: row.tanggal,
              no_faktur: row.no_faktur,
              customer_name: row.customer_name,
              employee_name: row.employee_name,
              total_item: row.total_item,
              total_bayar: formatIDR(row.total_bayar),
              total_diskon: formatIDR(row.total_diskon),
              total_bayar_akhir: formatIDR(row.total_bayar_akhir)
            };
          });
        }
      },
      columns: [{
          data: "tanggal"
        },
        {
          data: "no_faktur"
        },
        {
          data: "customer_name"
        },
        {
          data: "employee_name"
        },
        {
          data: "total_item"
        },
        {
          data: "total_bayar"
        },
        {
          data: "total_diskon"
        },
        {
          data: "total_bayar_akhir"
        },
        {
          data: "actions"
        }
      ]
    });

    // Form Tambah
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
              document.getElementById("addForm").reset();
              $('#add').modal('hide');
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

    // Hapus Data
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
  });
</script>

</html>