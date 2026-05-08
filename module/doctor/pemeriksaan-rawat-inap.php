<?php
$title = 'Pemeriksaan Rawat Inap';
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
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Belum Dilayani</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Sudah Dilayani</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="pulang-tab"
                data-bs-toggle="tab"
                data-bs-target="#pulang-tab-pane"
                type="button"
                role="tab"
                aria-controls="pulang-tab-pane"
                aria-selected="false">
                Selesai
              </button>
            </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0"></div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0"></div>
            <div class="tab-pane fade"
              id="pulang-tab-pane"
              role="tabpanel"
              aria-labelledby="pulang-tab"
              tabindex="0">
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Pemeriksaan Pasien Rawat Inap</h5>
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
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th class="text-dark fw-normal">Dokter</th>
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
</body>
<?php
$setting = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT rme_type FROM setting_clinic LIMIT 1"));
$rme_type = $setting ? $setting['rme_type'] : 1; // default 1
?>
<script>
  let currentTab = 'belum';

  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {

    const target = $(e.target).attr("id");

    if (target === 'home-tab') {
      currentTab = 'belum';

    } else if (target === 'profile-tab') {
      currentTab = 'selesai';

    } else if (target === 'pulang-tab') {
      currentTab = 'pulang';
    }

    console.log("TAB AKTIF:", currentTab);

    $('#zero_config').DataTable().ajax.reload(null, false);
  });
</script>
<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = 'controller/doctor/registrasiInpatientController';
  let now = new Date();

  // 🔥 awal bulan
  let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);

  // 🔥 hari ini (atau bisa end of month kalau mau full 1 bulan)
  let today = now.toISOString().split("T")[0];

  let firstDayStr = firstDay.toISOString().split("T")[0];

  $('#fromDate').val(today);
  $('#fromDate').attr('max', today);
  $('#toDate').val();
  const rmeType = '<?php echo $rme_type ?>'; // ambil dari PHP
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": false,
      scrollX: true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        data: function(d) {
          // kirim tanggal filter ke backend
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.tab = currentTab;
        },
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            // pilih file tujuan sesuai rme_type
            let pemeriksaanFile = (rmeType == 1) ? 'kunjungan' : 'pemeriksaan_b';
            return {
              "actions": `
                <div class="dropdown text-center position-relative">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="z-index:1055; min-width: 180px;">
                    <li>
                     <a class="dropdown-item" target="_blank"
                        href="module/admin/print/bundle_klaim?id_patient=${row.id_patient}&no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c">
                        <i class="bi bi-clipboard2-pulse me-2"></i>Preview RME Klaim
                      </a>
                    </li>
                        <li>
                        <a class="dropdown-item" href="module/admin/rme_inap?no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c">
                          <i class="bi bi-file-earmark-text me-2"></i>Pemeriksaan Rawat Inap
                        </a>
                      </li>
                       </li>
                        ${row.patient_gender === 'Perempuan' ? `
                        <li>
                          <a class="dropdown-item" href="module/admin/rme_persalinan?no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c">
                            <i class="bi bi-file-earmark-text me-2"></i>Persalinan
                          </a>
                        </li>
                        ` : ''}

                        <!-- 🔥 Divider -->
                        <li><hr class="dropdown-divider"></li>

                        <!-- 🔥 DOWNLOAD -->
                        <li>
                         <a class="dropdown-item text-success"
                            href="module/admin/print/bundle_klaim?id_patient=${row.id_patient}&no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c&download=1">
                            <i class="bi bi-download me-2"></i>Download PDF Klaim
                          </a>
                        </li>
                  </ul>
                </div>
              `,
              "tanggal": row.visit_date + ' ' + row.visit_time,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.patient_name,
              "gender": row.patient_gender,
              "dokter": row.id_doctor,
              "jenis_bayar": row.provider_name,
              "status_visit": `
                  <span class="badge ${row.status_cppt == 1 ? 'bg-success' : 'bg-danger'} d-block text-center">
                    ${row.status_cppt == 1 ? 'Sudah Dilayani' : 'Belum Dilayani'}
                  </span>
                `
            };
          });
        }
      },
      "columns": [{
          "data": "actions"
        }, {
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
          "data": "jenis_bayar"
        },
        {
          "data": "status_visit"
        }

      ]
    });

    // filter manual
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    // reset filter ke today
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val();
      table.ajax.reload();
    });



  });
</script>

<script>
  function callPatient(noAntrian, namaPasien, poli, visitID) {

    /* =========================
       1. SUARA (LANGSUNG - USER GESTURE)
    ========================= */
    if ('speechSynthesis' in window) {

      speechSynthesis.cancel();

      const text = `Nomor antrean ${noAntrian}, atas nama ${namaPasien}, silakan menuju poli ${poli}`;
      const utterance = new SpeechSynthesisUtterance(text);

      utterance.lang = 'id-ID';
      utterance.rate = 0.9;
      utterance.pitch = 1;
      utterance.volume = 1;

      const voices = speechSynthesis.getVoices();
      const indo = voices.find(v => v.lang === 'id-ID');
      if (indo) utterance.voice = indo;

      speechSynthesis.speak(utterance);
    }

    /* =========================
       2. UPDATE DISPLAY (ASYNC)
    ========================= */
    fetch('controller/queue/poliCall.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          visit_ID: visitID
        })
      })
      .then(res => res.json())
      .then(res => {
        if (res.status !== 'success') {
          console.warn('Update display gagal');
        }
      });
  }
</script>

<script>
  $(document).on('shown.bs.dropdown', '.dropdown', function() {
    const $menu = $(this).find('.dropdown-menu');
    const $btn = $(this).find('[data-bs-toggle="dropdown"]');

    const offset = $btn.offset();

    $('body').append($menu);

    $menu.css({
      position: 'absolute',
      top: offset.top + $btn.outerHeight(),
      left: offset.left,
      display: 'block',
      zIndex: 999999
    });
  });

  $(document).on('hide.bs.dropdown', '.dropdown', function() {
    const $menu = $('body > .dropdown-menu');
    $(this).append($menu);
    $menu.removeAttr('style');
  });
</script>

</html>