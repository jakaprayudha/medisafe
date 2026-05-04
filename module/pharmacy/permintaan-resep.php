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
          <ul class="nav nav-tabs" id="tabStatus">
            <li class="nav-item">
              <a class="nav-link active" data-status="permintaan" href="javascript:void(0)">Permintaan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-status="selesai" href="javascript:void(0)">Selesai</a>
            </li>
          </ul>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Farmasi Order</h5>
                    <!-- 🔽 Filter + Tombol Kembali -->
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
                          <!-- <th class="text-dark fw-normal">ID</th> -->
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th class="text-dark fw-normal">Dokter</th>
                          <th class="text-dark fw-normal">Poliklinik</th>
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
  $(document).ready(function() {

    let today = new Date().toISOString().split('T')[0];

    $('#fromDate').attr('max', today);
    $('#toDate').val(today);

  });
  let table;
  let currentFilter = 'permintaan';
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'farmasi/' . 'farmasiOrder' ?>';
  $(document).ready(function() {

    let today = new Date().toISOString().split('T')[0];

    $('#fromDate').val(today);
    $('#toDate').val(today);

    table = $('#zero_config').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
        },
        dataSrc: function(json) {

          let filtered = json.data.filter(function(row) {
            let status = parseInt(row.status_permintaan);
            return currentFilter === 'selesai' ?
              status === 3 :
              status !== 3;
          });

          return filtered.map(function(row) {
            return {
              actions: `
              <div class="text-center">
                <a href="module/admin/farmasi_order_detail?no=${row.visit_ID}&rm=${row.nomor_rm}&id=${row.id_permintaan_farmasi}">
                  <button class="btn btn-sm btn-primary">
                    <i class="fas fa-file"></i> Lihat Resep
                  </button>
                </a>
              </div>
            `,
              tanggal: row.created_at,
              nomor_rm: row.nomor_rm,
              nama_pasien: row.patient_name_pcare,
              gender: row.patient_gender,
              dokter: row.id_doctor,
              layanan: row.id_poli
            };
          });
        }
      },
      columns: [{
          data: "actions"
        },
        {
          data: "tanggal"
        },
        {
          data: "nomor_rm"
        },
        {
          data: "nama_pasien"
        },
        {
          data: "gender"
        },
        {
          data: "dokter"
        },
        {
          data: "layanan"
        }
      ]
    });

  });
  $('#btnFilter').on('click', function() {
    table.ajax.reload();
  });
  $('#btnReset').on('click', function() {

    let today = new Date().toISOString().split('T')[0];

    $('#fromDate').val(today);
    $('#toDate').val(today);

    table.ajax.reload();
  });
  $(document).ready(function() {

    $('#tabStatus .nav-link').on('click', function(e) {
      e.preventDefault();

      $('#tabStatus .nav-link').removeClass('active');
      $(this).addClass('active');

      currentFilter = $(this).data('status');

      console.log("TAB:", currentFilter);

      table.ajax.reload();
    });

  });
</script>

</html>