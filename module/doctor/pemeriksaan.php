<?php
$title = 'Pemeriksaan';
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
<style>
  @keyframes spin {
    from {
      transform: rotate(0deg);
    }

    to {
      transform: rotate(360deg);
    }
  }

  .icon-spin {
    display: inline-block;
    animation: spin 1s linear infinite;
  }
</style>

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
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Belum Dilayani</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Sudah Dilayani</button>
            </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0"></div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0"></div>
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Pemeriksaan Pasien Poliklinik</h5>
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
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th>Antrian</th>
                          <th>Layanan</th>
                          <th scope="col" class="text-dark fw-normal">No.BPJS</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th>Jenis Bayar</th>
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
  <script src="controller/socket/socket.js"></script>
</body>
<?php
$setting = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT rme_type FROM setting_clinic LIMIT 1"));
$rme_type = $setting ? $setting['rme_type'] : 1; // default 1
?>
<script>
  let activeTab = 'belum'; // default tab

  const apiUrl = 'controller/doctor/registrasiController';

  const doctorName = <?= json_encode($_SESSION['fullname'] ?? '') ?>;
  const rmeType = '<?php echo $rme_type ?>';
  var today = new Date().toLocaleDateString("sv-SE", {
    timeZone: "Asia/Jakarta"
  });

  $('#fromDate').val(today);
  $('#fromDate').attr('max', today);

  $('#toDate').val(today);
  $('#toDate').attr('max', today);
  $(document).ready(function() {

    var table = $('#zero_config').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      order: [
        [1, 'asc']
      ], // 🔥 URUTKAN KOLOM TANGGAL TERBARU
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctorName = doctorName;
          d.tab = activeTab; // 🔥 kirim tab (opsional backend)
        },

        dataSrc: function(json) {

          return json.data

            // 🔥 FILTER TAB DISINI
            .filter(function(row) {
              if (activeTab === 'belum') {
                return row.visit_status == 0 || row.visit_status == 1;
              } else if (activeTab === 'sudah') {
                return row.visit_status == 4;
              }
              return true;
            })

            .map(function(row, index) {

              let statusClass = '';
              let statusText = '';

              if (row.visit_status == 0) {
                statusClass = 'bg-danger';
                statusText = 'Belum Dilayani';
              } else if (row.visit_status == 1) {
                statusClass = 'bg-warning text-dark';
                statusText = 'Sedang Diperiksa';
              } else if (row.visit_status == 4) {
                statusClass = 'bg-success';
                statusText = 'Selesai Dilayani';
              } else {
                statusClass = 'bg-secondary';
                statusText = 'Unknown';
              }

              let pemeriksaanFile = (rmeType == 1) ? 'kunjungan' : 'pemeriksaan_b';

              let callButton = '';
              // 🔥 hanya tampil di tab BELUM
              if (row.source_hub === 'Poliklinik' && activeTab === 'belum') {
                callButton = `
                    <button class="btn btn-sm btn-warning btn-call"
                      data-antrian="${row.visit_antrian}"
                      data-nama="${row.patient_name}"
                      data-poli="${row.poli_name}"
                      data-visit="${row.visit_ID}"
                      data-dokter="${row.id_doctor}"
                      title="Panggil Pasien">
                      <i class="ti ti-volume"></i>
                    </button>
                `;
              }
              return {
                actions: `
                  <div class="text-center">
                    <a href="module/admin/${pemeriksaanFile}?no=${row.visit_ID}&rm=${row.nomor_rm}"
                      class="btn btn-sm btn-primary"
                      title="Pemeriksaan">
                      <i class="ti ti-stethoscope"></i>
                    </a>
                    ${callButton}
                  </div>
                `,
                tanggal: `
                  <span style="display:none">
                    ${row.visit_date} ${row.visit_time}
                  </span>
                  ${row.visit_date} ${row.visit_time}
                `,
                antrian: row.visit_antrian,
                source_hub: row.source_hub,
                bpjs: row.patient_bpjs,
                nama_pasien: row.patient_name,
                gender: row.patient_gender,
                jenis_bayar: row.provider_name,
                status_visit: `
                  <span class="badge ${statusClass} d-block text-center">
                    ${statusText}
                  </span>
                `
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
          data: "antrian"
        },
        {
          data: "source_hub"
        },
        {
          data: "bpjs"
        },
        {
          data: "nama_pasien"
        },
        {
          data: "gender"
        },
        {
          data: "jenis_bayar"
        },
        {
          data: "status_visit"
        }
      ]
    });

    // 🔥 TAB EVENT
    $('#home-tab').on('click', function() {
      activeTab = 'belum';
      table.ajax.reload();
    });

    $('#profile-tab').on('click', function() {
      activeTab = 'sudah';
      table.ajax.reload();
    });

    // 🔍 FILTER
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    // 🔄 RESET
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val('');
      table.ajax.reload();
    });

  });
</script>

<script>
  $(document).on('click', '.btn-call', function() {
    const noAntrian = $(this).data('antrian');
    const namaPasien = $(this).data('nama');
    const poli = $(this).data('poli');
    const visit = $(this).data('visit');
    const id_doctor = $(this).data('dokter');
    // console.log('DOKTER:', dokter);
    // callPatient(noAntrian, nama, poli, visit, dokter);
    let dokterRaw = (id_doctor || '').trim();
    dokterRaw = dokterRaw.replace(/^dr\.?/i, 'dr. ');
    dokterRaw = dokterRaw.replace(/\s+/g, ' ').trim();
    let isPrefixDr = /^dr\./i.test(dokterRaw);
    let isSuffixDr = /,\s*dr\.?$/i.test(dokterRaw);
    let text;
    if (isPrefixDr) {
      text = `Pasien atas nama ${namaPasien}, dipersilakan masuk ke ruangan ${dokterRaw}`;
    } else if (isSuffixDr) {
      let cleanName = dokterRaw.replace(/,\s*dr\.?$/i, '').trim();
      text = `Pasien atas nama ${namaPasien}, dipersilakan masuk ke ruangan dokter ${cleanName}`;
    } else {
      text = `Pasien atas nama ${namaPasien}, dipersilakan masuk ke ruangan dokter ${dokterRaw}`;
    }
    console.log(text);
    $.ajax({
      url: 'controller/admisi/sound.php',
      type: 'POST',
      data: {
        text: text
      },
      dataType: 'json',
      success: function(response) {
        console.log(response);
      },
      beforeSend: function() {
        $('.btn-call')
          .prop('disabled', true)
          .find('i')
          .attr('class', 'ti ti-loader-2 icon-spin');
      },

      complete: function() {
        setTimeout(() => {
          $('.btn-call').prop('disabled', false).find('i').attr('class', 'ti ti-volume');
        }, 10000);
      }
    })
  });
</script>

<script>

</script>

</html>