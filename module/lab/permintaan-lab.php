<?php
$title = 'Lab Order';
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
          <ul class="nav nav-tabs" id="tabStatus">

            <li class="nav-item">
              <a class="nav-link active"
                data-status="permintaan"
                href="javascript:;">

                Permintaan

              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link"
                data-status="selesai"
                href="javascript:;">

                Selesai

              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link"
                data-status="pulang"
                href="javascript:;">

                Pasien Pulang

              </a>
            </li>

          </ul>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Laboratorium Order</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <form id="filterForm" class="row g-2 align-items-end">
                        <div class="col-auto">
                          <label for="fromDate" class="form-label mb-0">Dari</label>
                          <input type="date" id="fromDate" name="fromDate" max="" class="form-control">
                        </div>
                        <div class="col-auto">
                          <label for="toDate" class="form-label mb-0">Sampai</label>
                          <input type="date" id="toDate" name="toDate" class="form-control">
                        </div>
                        <div class="col-auto">
                          <button type="button" id="btnFilter" class="btn btn-dark">
                            <i class="fas fa-filter"></i> Filter
                          </button>
                        </div>
                        <div class="col-auto">
                          <button type="button" id="btnReset" class="btn btn-light">
                            <i class="fas fa-undo"></i> Reset
                          </button>
                        </div>
                      </form>

                      <!-- Tombol kembali -->
                      <div class="d-flex ms-auto gap-2">

                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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
  let currentFilter = 'permintaan';
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'lab/' . 'pemeriksaanOrder' ?>';
  $(document).ready(function() {


    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": false,
      scrollX: true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        dataSrc: function(json) {

          let fromDate = $('#fromDate').val();
          let toDate = $('#toDate').val();

          let filtered = json.data.filter(function(row) {

            let status = parseInt(row.status_permintaan);

            // 🔥 FILTER STATUS
            let statusMatch = false;

            // ===============================
            // TAB SELESAI
            // ===============================
            if (currentFilter === 'selesai') {

              statusMatch = status === 3;

            }

            // ===============================
            // TAB PASIEN PULANG
            // ===============================
            else if (currentFilter === 'pulang') {

              statusMatch =
                row.visit_status == 'pulang' ||
                row.visit_status == 'selesai' ||
                row.visit_status == 'checkout';

            }

            // ===============================
            // TAB PERMINTAAN
            // ===============================
            else {

              statusMatch =
                status !== 3 &&
                row.visit_status != 'pulang' &&
                row.visit_status != 'selesai' &&
                row.visit_status != 'checkout';

            }

            // 🔥 FILTER TANGGAL
            let rowDate = row.created_at ? row.created_at.substring(0, 10) : null;

            let dateMatch = true;

            if (fromDate && rowDate < fromDate) {
              dateMatch = false;
            }

            if (toDate && rowDate > toDate) {
              dateMatch = false;
            }

            return statusMatch && dateMatch;
          });

          return filtered.map(function(row, index) {
            return {
              "actions": `
        <div class="text-center">
          <a href="module/lab/permintaan-lab-detail?no=${row.visit_ID}&rm=${row.nomor_rm}&id=${row.id_inspection}">
            <button class="btn btn-sm btn-primary">Lihat Pemeriksaan</button>
          </a>
        </div>
      `,
              "tanggal": row.created_at,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.patient_name_pcare,
              "gender": row.patient_gender,
              "dokter": row.id_doctor,
              "layanan": row.id_poli,
              "status_visit": (function() {

                let status = row.status_permintaan;
                let visitStatus = row.visit_status;

                let badgeClass = '';
                let label = '';

                // ===============================
                // PASIEN PULANG
                // ===============================
                if (
                  visitStatus == 'pulang' ||
                  visitStatus == 'selesai' ||
                  visitStatus == 'checkout'
                ) {

                  badgeClass = 'bg-dark';
                  label = 'Pasien Pulang';

                }

                // ===============================
                // BELUM
                // ===============================
                else if (status == 1) {

                  badgeClass = 'bg-danger';
                  label = 'Belum';

                }

                // ===============================
                // PERSIAPAN
                // ===============================
                else if (status == 2) {

                  badgeClass = 'bg-primary';
                  label = 'Persiapan';

                }

                // ===============================
                // SELESAI
                // ===============================
                else if (status == 3) {

                  badgeClass = 'bg-success';
                  label = 'Selesai';

                }

                // ===============================
                // DEFAULT
                // ===============================
                else {

                  badgeClass = 'bg-secondary';
                  label = 'Belum Pemeriksaan';

                }

                return `
      <span class="badge ${badgeClass} d-block text-center">
         ${label}
      </span>
   `;

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


    var today = new Date().toLocaleDateString("sv-SE", {
      timeZone: "Asia/Jakarta"
    });

    // default
    $('#fromDate').val(today);
    $('#toDate').val('');

    // limit
    $('#fromDate').attr('max', today);
    $('#toDate').attr('max', today);

    $('#tabStatus .nav-link').on('click', function(e) {
      e.preventDefault();

      $('#tabStatus .nav-link').removeClass('active');
      $(this).addClass('active');

      currentFilter = $(this).data('status');

      table.ajax.reload(); // 🔥 reload table
    });

    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val('');
      table.ajax.reload();
    });
  });
</script>

</html>