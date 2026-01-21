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
                    <h5 class="card-title fw-semibold">Pemeriksaan Pasien Poliklinik</h5>
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
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">TTL</th>
                          <th class="text-dark fw-normal">Dokter</th>
                          <th>Jenis Bayar</th>
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
<?php
$setting = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT rme_type FROM setting_clinic LIMIT 1"));
$rme_type = $setting ? $setting['rme_type'] : 1; // default 1
?>
<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = 'controller/doctor/registrasiController';
  var today = new Date().toISOString().split("T")[0];
  const doctorName = <?= json_encode($_SESSION['fullname'] ?? '') ?>;
  $("#fromDate").val(today);
  $("#toDate").val(today);
  const rmeType = '<?php echo $rme_type ?>'; // ambil dari PHP
  $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        data: function(d) {
          // kirim tanggal filter ke backend
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctorName = doctorName;
        },
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            // pilih file tujuan sesuai rme_type
            let pemeriksaanFile = (rmeType == 1) ? 'pemeriksaan_a' : 'pemeriksaan_b';
             // ✅ Kondisi tampil tombol panggil
            let callButton = '';  
            if (row.source_hub === 'Poliklinik') {
            callButton = `
              <button class="btn btn-sm btn-warning"
                data-bs-toggle="tooltip"
                title="Panggil Pasien"
                onclick="callPatient(
                  '${row.visit_antrian}',
                  '${row.patient_name}',
                  '${row.poli_name}'
                )">
                <i class="ti ti-volume"></i>
              </button>
            `;
          }
            return {
              "actions": `
                  <div class="text-center">
                  <!-- Pemeriksaan -->
                  <a href="module/admin/${pemeriksaanFile}?no=${row.visit_ID}&rm=${row.nomor_rm}"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Pemeriksaan">
                    <i class="ti ti-stethoscope"></i>
                  </a>

                  ${callButton}
                  </div>
              `,
              "tanggal": row.visit_date + ' ' + row.visit_time,
              "antrian": row.visit_antrian,
              "source_hub": row.source_hub,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.patient_name,
              "gender": row.patient_gender,
              "ttl": row.patient_datebirth + '/' + row.patient_place,
              "dokter": row.doctor_name,
              "jenis_bayar": row.provider_name,
              "layanan": row.poli_name,
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
        }, {
          "data": "tanggal"
        },
        {
          "data": "antrian"
        },
        {
          "data": "source_hub"
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
          "data": "jenis_bayar"
        },
        {
          "data": "layanan"
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
      $('#toDate').val(today);
      table.ajax.reload();
    });



  });

  function callPatient(noAntrian, namaPasien, poli) {
   if (!('speechSynthesis' in window)) {
      alert('Browser tidak mendukung suara');
      return;
   }

   // Hentikan suara sebelumnya
   speechSynthesis.cancel();

   const text = `Nomor antrean ${noAntrian}, atas nama ${namaPasien}, silakan menuju poli ${poli}`;
   const utterance = new SpeechSynthesisUtterance(text);

   utterance.lang = 'id-ID';
   utterance.rate = 0.9;
   utterance.pitch = 1;
   utterance.volume = 1;

   // pilih voice Indonesia jika ada
   const voices = speechSynthesis.getVoices();
   const indoVoice = voices.find(v => v.lang === 'id-ID');
   if (indoVoice) utterance.voice = indoVoice;

   speechSynthesis.speak(utterance);
}
</script>

</html>