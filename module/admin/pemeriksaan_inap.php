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
  <style>
    table.dataTable td {
      overflow: visible !important;
    }

    .dropdown-menu {
      z-index: 9999 !important;
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
                          <input type="date" id="fromDate" name="fromDate" class="form-control">
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
                        <a href="module/admin/registrasi_ranap">
                          <button class="btn btn-primary"><i class="fas fa-list"></i> Booking</button>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th>Kamar</th>
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">TTL</th>
                          <th class="text-dark fw-normal">Dokter</th>
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
  require 'library.php';
  ?>
</body>
<?php
$setting = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT rme_type FROM setting_clinic LIMIT 1"));
$rme_type = $setting ? $setting['rme_type'] : 1; // default 1
?>
<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = 'controller/visit/registrasiRanapController';
  var today = new Date().toISOString().split("T")[0];
  $("#fromDate").val(today);
  $("#toDate").val(today);
  const rmeType = '<?php echo $rme_type ?>'; // ambil dari PHP

  $(document).ready(function() {

    // 🔹 Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": false,
      "responsive": true,
      "autoWidth": false,
      "drawCallback": function() {
        // Re-init dropdown supaya event Bootstrap aktif setelah redraw
        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdowns.forEach(el => new bootstrap.Dropdown(el));
      },
      "ajax": {
        "url": apiUrl,
        "type": "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
        },
        "dataSrc": function(json) {
          return json.data.map(function(row, index) {
            return {
              "actions": `
                <div class="dropdown text-center position-relative">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="z-index:1055; min-width: 180px;">
                    <li>
                      <a class="dropdown-item" target="_blank" href="module/admin/print/bundle_klaim?no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c">
                        <i class="bi bi-clipboard2-pulse me-2"></i>Preview RME Klaim
                      </a>
                    </li>
                       <li>
                        <a class="dropdown-item" href="module/admin/form_sep?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>SEP (Surat Eligibilitas Peserta) 
                        </a>
                      </li>
                    <li>
                      <a class="dropdown-item" href="module/admin/rme_inap?no=${row.visit_ID}&rm=${row.nomor_rm}&rme=c">
                        <i class="bi bi-clipboard2-pulse me-2"></i>Pemeriksaan
                      </a>
                    </li>
                      <li>
                        <a class="dropdown-item" href="module/admin/form_pernyataan?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Formulir Pernyataan Peserta
                        </a>
                      </li>
                       <li>
                        <a class="dropdown-item" href="module/admin/form_capture_patient?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Foto Pasien
                        </a>
                      </li>
                         <li>
                        <a class="dropdown-item" href="module/admin/cppt?no=${row.visit_ID}&rm=${row.nomor_rm}" >
                          <i class="bi bi-file-earmark-text me-2"></i>CPPT
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="module/admin/form_persalinan?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Rekapitulasi Pelayanan Persalinan
                        </a>
                      </li>
                       <li>
                        <a class="dropdown-item" href="module/admin/form_upload_buku_kia?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Upload Buku Kesehatan Ibu dan Anak
                        </a>
                      </li>
                       <li>
                        <a class="dropdown-item" href="module/admin/form_kb?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Kartu Status Peserta KB
                        </a>
                      </li>
                        <li>
                        <a class="dropdown-item" href="module/admin/form_ranap?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="bi bi-file-earmark-text me-2"></i>Keterangan Rawat Inap
                        </a>
                      </li>
                        <li>
                        <a class="dropdown-item" href="module/admin/print/formulir_surat_persetujuan?no=${row.visit_ID}&rm=${row.nomor_rm}" target="_blank">
                          <i class="bi bi-file-earmark-text me-2"></i>Surat Persetujuan Tindakan Medis
                        </a>
                      </li>
                        <li>
                        <a class="dropdown-item" href="module/admin/print/formulir_inout_ranap?no=${row.visit_ID}&rm=${row.nomor_rm}" target="_blank">
                          <i class="bi bi-file-earmark-text me-2"></i>Lembar Masuk dan Keluar Rawat Inap
                        </a>
                      </li>
                        <li>
                        <a class="dropdown-item" href="module/admin/print/formulir_instruksi?no=${row.visit_ID}&rm=${row.nomor_rm}" target="_blank">
                          <i class="bi bi-file-earmark-text me-2"></i>Perkembangan Pasien & Instruksi Dokter
                        </a>
                      </li>
                    
                        <li>
                        <a class="dropdown-item" href="module/admin/print/formulir_resume?no=${row.visit_ID}&rm=${row.nomor_rm}" target="_blank">
                          <i class="bi bi-file-earmark-text me-2"></i>Resume Medis
                        </a>
                      </li>
                       <li>
                        <a class="dropdown-item" href="module/admin/print/formulir_lbp?no=${row.visit_ID}&rm=${row.nomor_rm}" target="_blank">
                          <i class="bi bi-file-earmark-text me-2"></i>Formulir Lembar Bukti Pelayanan (LBP)
                        </a>
                      </li>
                  </ul>
                </div>
              `,
              "tanggal": row.visit_date + ' ' + row.visit_time,
              "kamar": row.room_name + ' - ' + row.bed_name,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.patient_name,
              "gender": row.patient_gender,
              "ttl": row.patient_datebirth + '/' + row.patient_place,
              "dokter": row.doctor_name,
              "status_visit": `
                <span class="badge ${row.status_dilayani == 1 ? 'bg-success' : 'bg-danger'} d-block text-center">
                  ${row.status_dilayani == 1 ? 'Sudah Dilayani' : 'Belum Dilayani'}
                </span>
              `
            };
          });
        }
      },
      "columns": [{
          "data": "actions"
        },
        {
          "data": "tanggal"
        },
        {
          "data": "kamar"
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
          "data": "ttl"
        },
        {
          "data": "dokter"
        },
        {
          "data": "status_visit"
        }
      ]
    });

    // 🔹 Tambahkan perbaikan dropdown agar muncul di atas tabel scroll
    $(document).on('show.bs.dropdown', '.table-responsive', function(e) {
      $(e.relatedTarget).next('.dropdown-menu').appendTo('body');
    });
    $(document).on('hide.bs.dropdown', '.table-responsive', function(e) {
      $('.dropdown-menu').appendTo(e.currentTarget);
    });

    // 🔹 Filter manual
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    // 🔹 Reset filter ke today
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      table.ajax.reload();
    });

  });
</script>

</html>