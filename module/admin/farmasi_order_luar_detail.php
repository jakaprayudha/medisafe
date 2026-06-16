<?php
$title = 'Farmasi Order';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
$no = $_GET['no'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient  WHERE pasien_visit.visit_ID='$no'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
if ($data) {
  $patient_datebirth = new DateTime($data['patient_datebirth']);
  $tanggal_visit = new DateTime($data['visit_date']);

  $usia = $patient_datebirth->diff($tanggal_visit);
}

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
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><?= $data['patient_name'] ?> <span class="badge bg-warning">RM : <?= $data['nomor_rm'] ?></span> </h5>
                  <p class="card-text">Usia : <?php echo $usia->y . " Tahun " . $usia->m . " Bulan " . $usia->d . " Hari"; ?> <br> <?= $data['patient_gender'] ?></p>
                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Resep Luar</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/print/struk_obat_luar?no=<?= $no ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                        <button class="btn btn-info"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Prescriptio</th>
                          <th scope="col" class="text-dark fw-normal">Signatura</th>
                          <th scope="col" class="text-dark fw-normal">Subscriptio</th>
                          <th>Pro</th>
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

</html>

<script>
  const apiUrl = 'controller/visit/resepLuar?no=<?= $_GET['no'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "prescriptio": row.prescriptio ?? "-",
              "signatura": row.signatura ?? "-",
              "subscriptio": row.subscriptio ?? "-",
              "pro": row.pro ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "prescriptio",
          className: "text-wrap"
        },
        {
          data: "signatura",
          className: "text-wrap"
        },
        {
          data: "subscriptio",
          className: "text-wrap"
        },
        {
          data: "pro",
          className: "text-wrap"
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

  });
</script>