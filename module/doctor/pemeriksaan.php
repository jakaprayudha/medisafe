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
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <form id="filterForm" class="row g-2 align-items-end">
                        <div class="col-auto">
                          <label for="provider" class="form-label mb-0">Provider</label>
                          <select id="provider" name="provider" class="form-select">
                            <option value="">Semua Provider</option>
                          </select>
                        </div>
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
                          <th scope="col" class="text-dark fw-normal">No.RM</th>
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
  let activeTab = 'belum';

  const apiUrl = 'controller/doctor/registrasiController';

  const doctorName = <?= json_encode($_SESSION['fullname'] ?? '') ?>;
  const kodeDokter = <?= json_encode($_SESSION['kode_dokter'] ?? '') ?>;
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
      order: [],
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctorName = doctorName;
          d.tab = activeTab;
          d.kdDokter = kodeDokter;
          d.provider = $('#provider').val();
        },
        dataSrc: function(json) {
          return json.data
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
              } else if (row.visit_status == 10) {
                // PERBAIKAN 3: Tangani status 10 (Sesuaikan teksnya dengan sistem Anda)
                statusClass = 'bg-info';
                statusText = 'Menunggu';
              } else {
                statusClass = 'bg-secondary';
                statusText = 'Unknown';
              }

              let pemeriksaanFile = (rmeType == 1) ? 'kunjungan' : 'pemeriksaan_b';

              let callButton = '';
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
              let actionBtn = '';
              if (row.id_provider == 1 && (row.status_panggil == 0 || row.status_pcare == 0)) {
                actionBtn = `
                  <button type="button"
                    class="btn btn-sm btn-secondary btn-hadir"
                    data-visit="${row.visit_ID}" 
                    data-jnsbyr="${row.provider_name}"
                    data-status="1"
                    title="Hadir">
                    <i class="ti ti-check"></i>
                  </button>

                  <button type="button"
                    class="btn btn-sm btn-danger btn-hadir"
                    data-visit="${row.visit_ID}" 
                    data-jnsbyr="${row.provider_name}"
                    data-status="2"
                    title="Tidak hadir">
                    <i class="ti ti-x"></i>
                  </button> 
              `;
              } else {
                actionBtn = `
                 <button
                    type="button"
                    class="btn btn-sm btn-primary btn-pemeriksaan"
                    title="Pemeriksaan"
                    data-url="module/admin/${pemeriksaanFile}"
                    data-no="${row.visit_ID}"
                    data-visitidpasien="${row.id_visit}"
                    data-provider="${row.id_provider}"
                    data-rm="${row.nomor_rm}">
                    <i class="ti ti-stethoscope"></i>
                </button>
                `;
              }

              // PERBAIKAN 2: Tangani visit_time null agar tidak muncul kata "null"
              let waktuVisit = row.visit_time ? row.visit_time : '';

              return {
                actions: `
                  <div class="text-center">
                    ${actionBtn}
                    ${callButton}
                  </div>
                `,
                tanggal: row.visit_date + ' ' + waktuVisit,
                antrian: row.visit_antrian,
                source_hub: row.source_hub,
                nomor_rm: row.nomor_rm,
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
          data: "nomor_rm"
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

    $(document).on('click', '.btn-hadir', function() {
      let visitID = $(this).data('visit');
      let bayar = $(this).data('jnsbyr');
      let status = $(this).data('status');
      let konfirmasi = status == '1' ?
        "Konfirmasi pasien hadir?" :
        "Konfirmasi pasien tidak hadir?";
      Swal.fire({
        title: konfirmasi,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'controller/wsbpjs/setHadir.php',
            type: 'POST',
            dataType: 'json',
            data: {
              visit_id: visitID,
              type: bayar,
              statushadir: status
            },
            beforeSend: function() {
              Swal.fire({
                title: 'Sedang diproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                  Swal.showLoading();
                }
              });
            },
            success: function(res) {
              if (status == '2') {
                Swal.fire({
                  icon: 'success',
                  title: 'Berhasil',
                  text: 'Berhasil Batal Pasien'
                });
                table.ajax.reload(null, false);
              } else {
                if (res.success) {
                  $.ajax({
                    url: 'controller/admisi/services/chackinv2.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                      visit: visitID
                    },
                    success: function(pcare) {
                      if (pcare.success) {
                        Swal.fire({
                          icon: 'success',
                          title: 'Berhasil',
                          text: pcare.message
                        });
                        table.ajax.reload(null, false);
                      } else {
                        Swal.fire({
                          icon: 'warning',
                          title: 'Berhasil Set Hadir',
                          text: 'Set hadir berhasil, namun pendaftaran PCare gagal : ' + pcare.message
                        });
                      }
                      table.ajax.reload(null, false);
                    },
                    error: function() {
                      Swal.fire({
                        icon: 'warning',
                        title: 'Berhasil Set Hadir',
                        text: 'Set hadir berhasil, namun server PCare tidak merespon'
                      });
                      table.ajax.reload(null, false);
                    }
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message
                  });
                  table.ajax.reload(null, false);
                }
              }
            },
            error: function() {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Server tidak merespon'
              });
            }
          });
        }
      });
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
    const visit_ID = $(this).data('visit');
    // console.log('DOKTER:', dokter);
    // callPatient(noAntrian, nama, poli, visit, dokter);
    let dokterRaw = (id_doctor || '').trim();
    dokterRaw = dokterRaw.replace(/^dr\.?/i, 'dr. ');
    dokterRaw = dokterRaw.replace(/\s+/g, ' ').trim();
    dokterRaw = dokterRaw.replace(/^dr\.\s+g\.\s+/i, 'dr. ');
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
    // console.log(text);
    const requestId = crypto.randomUUID();
    sessionStorage.setItem('requestId', requestId);
    $.ajax({
      url: 'controller/admisi/sound.php',
      type: 'POST',
      data: {
        text: text,
        requestId: requestId,
        nama: namaPasien,
        nomor_visit: visit_ID
      },
      dataType: 'json',
      success: function(response) {
        console.log("SUCCESS", response);
      },
      error: function(xhr) {
        console.log("ERROR", xhr.responseText);
        $('.btn-call').prop('disabled', false).find('i').attr('class', 'ti ti-volume');
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
        }, 5000);
      }
    })
  });
</script>
<script>
  $(document).ready(function() {
    $.ajax({
      url: 'controller/admisi/services/get_provider.php?type=BPJS',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        let options = '<option value="">Semua Provider</option>';
        $.each(response, function(index, item) {
          options += `<option value="${item.id}">${item.text}</option>`;
        });
        $('#provider').html(options);
      },
      error: function(xhr, status, error) {
        console.error('Gagal mengambil data provider:', error);
        $('#provider').html('<option value="">Gagal memuat data</option>');
      }
    });

    $(document).on('click', '.btn-pemeriksaan', function() {
      const url = $(this).data('url');
      const no = $(this).data('no');
      const rm = $(this).data('rm');
      const id_visit = $(this).data('visitidpasien');
      const provider = $(this).data('provider');
      if (provider != 1) {
        window.location.href = `${url}?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm)}`;
      } else {
        $.ajax({
          url: 'controller/admisi/services/validateicare',
          type: 'POST',
          dataType: 'json',
          data: {
            id: id_visit
          },
          beforeSend: function() {
            Swal.fire({
              title: 'Memeriksa...',
              text: 'Mohon tunggu',
              allowOutsideClick: false,
              showConfirmButton: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
          },
          success: function(response) {
            if (response.success) {
              const urlIcare = response.message.data.url;
              window.open(urlIcare, '_blank');
              window.location.href = `${url}?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm)}`;
            } else {
              Swal.fire({
                icon: 'warning',
                title: 'ICare tidak dapat diakses',
                text: response.message?.message || 'Coba beberapa saat lagi.',
                showCancelButton: true,
                confirmButtonText: 'Buka Tanpa ICare',
                cancelButtonText: 'Kembali',
                reverseButtons: true
              }).then((result) => {
                if (result.isConfirmed) {
                  window.location.href =
                    `${url}?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm)}`;
                }
              });
            }
          },
          error: function() {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Gagal melakukan validasi.'
            });
          }
        });
      }
    });
  });
</script>

</html>