<?php
$title = 'Farmasi Order';
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
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Farmasi Order</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">

                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <!-- <th class="text-dark fw-normal">ID</th> -->
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th class="text-dark fw-normal">Dokter</th>
                          <th class="text-dark fw-normal">Poliklinik</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
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
  require '../admin/library.php';
  ?>
</body>

<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'farmasi/' . 'farmasiOrder' ?>';
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": false,
      scrollX: true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            return {
              "actions": `
                  <div class="text-center">
                    <a href="module/admin/farmasi_order_detail?no=${row.visit_ID}&rm=${row.nomor_rm}&id=${row.id_permintaan_farmasi}">
                      <button class="btn btn-primary">Lihat Resep</button>
                    </a>
                  </div>
              `,
              // "permintaan_number": row.permintaan_number,
              "tanggal": row.created_at,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.patient_name_pcare,
              "gender": row.patient_gender,
              "dokter": row.id_doctor,
              "layanan": row.id_poli,
              "status_visit": (function() {
                let status = row.status_permintaan;

                let badgeClass = '';
                let label = '';

                if (status == 1) {
                  badgeClass = 'bg-danger';
                  label = 'Belum';
                } else if (status == 2) {
                  badgeClass = 'bg-primary';
                  label = 'Persiapan';
                } else if (status == 3) {
                  badgeClass = 'bg-success';
                  label = 'Selesai';
                } else {
                  badgeClass = 'bg-secondary';
                  label = 'Unknown';
                }

                return `<span class="badge ${badgeClass} d-block text-center">${label}</span>`;
              })()
            };
          });
        }
      },
      "columns": [{
          "data": "actions"
        },
        // {
        //   "data": "permintaan_number"
        // },
        {
          "data": "tanggal"
        },
        {
          "data": "nomor_rm"
        },
        {
          "data": "nama_pasien"
        },
        {
          "data": "gender"
        },
        {
          "data": "dokter"
        },
        {
          "data": "layanan"
        },
        {
          "data": "status_visit"
        }

      ]
    });


  });
</script>

</html>