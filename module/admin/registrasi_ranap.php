<?php
$title = 'Registrasi Rawat Inap';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style id="fixcss">
    /* =========================
   🎯 DROPDOWN (PRIORITAS)
========================= */
    .dropdown-menu {
      z-index: 999999 !important;
      min-width: 200px;
      max-width: 250px;
      width: auto;
      white-space: normal;
    }

    /* =========================
   🎯 DATATABLE FIX (INTI MASALAH)
========================= */

    /* wrapper utama */
    .dataTables_wrapper {
      overflow: visible !important;
    }

    /* scroll container */
    .dataTables_scroll {
      overflow: visible !important;
    }

    /* body scroll (INI YANG PALING PENTING) */
    .dataTables_scrollBody {
      overflow: visible !important;
    }

    /* responsive table */
    .table-responsive {
      overflow: visible !important;
    }

    /* card container */
    .card {
      overflow: visible !important;
    }

    /* =========================
   🎯 DROPUP POSITION FIX
========================= */
    .dropup .dropdown-menu {
      top: auto !important;
      bottom: 100% !important;
      margin-bottom: 5px;
      transform: none !important;
    }

    /* =========================
   🎯 CAMERA MODAL (JANGAN DIUBAH)
========================= */
    #cameraModal .modal-body {
      position: relative;
    }

    #cameraModal video {
      width: 100%;
    }

    #cameraModal canvas {
      position: absolute;
      top: 0;
      left: 0;
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
                    <h5 class="card-title fw-semibold">Data Registrasi Rawat Inap</h5>
                    <!-- Grup tombol di sisi kanan -->

                    <!-- 🔽 Filter + Tombol Kembali -->
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <div class="col-auto">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal" class="btn btn-dark">
                          <i class="fas fa-filter"></i> Filter
                        </button>
                      </div>
                      <div class="col-auto">
                        <button type="button" id="btnReset" class="btn btn-light">
                          <i class="fas fa-undo"></i> Reset
                        </button>
                      </div>
                      <!-- Tombol kembali -->
                      <div class="d-flex ms-auto gap-2">
                        <!-- <div class="dropdown">
                          <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus"></i> Tambah
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                              <a class="dropdown-item" href="module/admin/patient_new">
                                <i class="fas fa-user-plus me-2 text-primary"></i> Pasien Baru
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item poli-btn" href="javascript:;">
                                <i class="fas fa-procedures me-2 text-success"></i> Pasien Rawat Inap
                              </a>
                            </li>
                          </ul>
                        </div> -->
                        <a href="module/admin/registrasi_booking_ranap">
                          <button class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Permintaan Rawat Inap
                          </button>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal">Tanggal</th>
                          <th class="text-dark fw-normal">No.BPJS</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Jenis Bayar</th>

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



<div class="modal fade" id="detailModal">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow">

      <!-- HEADER -->
      <div class="modal-header  text-white">
        <h5 class="modal-title">
          🩺 Detail Pemeriksaan
        </h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- 👤 INFO PASIEN -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3">👤 Informasi Pasien</h6>

            <div class="row g-3">
              <div class="col-md-6">
                <small class="text-muted">Nama Pasien</small>
                <div class="fw-semibold" id="d_patient_name">-</div>
              </div>

              <div class="col-md-6">
                <small class="text-muted">Dokter</small>
                <div id="d_doctor_name">-</div>
              </div>

              <div class="col-md-6">
                <small class="text-muted">Poliklinik</small>
                <div id="d_poli_name">-</div>
              </div>

              <div class="col-md-6">
                <small class="text-muted">Tanggal</small>
                <div id="d_visit_date">-</div>
              </div>

              <div class="col-md-6">
                <small class="text-muted">No. SEP</small>
                <div id="d_no_sep">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ❤️ VITAL SIGN -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3">❤️ Vital Sign</h6>

            <div class="d-flex flex-wrap gap-2">

              <span class="badge bg-light text-dark">
                🩸 TD: <span id="d_tekanan_darah">-</span>
              </span>

              <span class="badge bg-light text-dark">
                🌡️ Suhu: <span id="d_suhu">-</span>
              </span>

              <span class="badge bg-light text-dark">
                ❤️ Nadi: <span id="d_nadi">-</span>
              </span>

              <span class="badge bg-light text-dark">
                🫁 RR: <span id="d_respirasi">-</span>
              </span>

              <span class="badge bg-light text-dark">
                📏 TB: <span id="d_tinggi">-</span>
              </span>

              <span class="badge bg-light text-dark">
                ⚖️ BB: <span id="d_berat">-</span>
              </span>

            </div>
          </div>
        </div>

        <!-- 🧠 ANAMNESA -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-2">🧠 Keluhan / Anamnesa</h6>
            <div id="d_anamnesa" class="text-muted">-</div>
          </div>
        </div>

        <!-- 🔬 DIAGNOSA -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-2">🔬 Diagnosa</h6>
            <div id="d_diagnosa" class="text-muted">-</div>
          </div>
        </div>

        <!-- 💊 TINDAKAN -->
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-2">💊 Tindakan</h6>
            <div id="d_tindakan" class="text-muted">-</div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="cameraModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">📸 Capture Foto</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">

        <video id="video" autoplay class="w-100 rounded mb-3"></video>

        <canvas id="canvas" class="d-none"></canvas>

        <img id="preview" class="img-fluid rounded d-none" />

        <div class="mt-3">
          <button class="btn btn-primary" id="btnCapture">📸 Ambil Foto</button>
          <button class="btn btn-success d-none" id="btnSave">💾 Simpan</button>
        </div>

      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="filterModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Data</h5>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 mb-3">
            <label for="fromDate" class="form-label mb-0">Dari</label>
            <input type="date" id="fromDate" name="fromDate" class="form-control">
          </div>
          <div class="col-6 mb-3">
            <label for="toDate" class="form-label mb-0">Sampai</label>
            <input type="date" id="toDate" name="toDate" class="form-control">
          </div>
          <div class="col-12 mb-3">
            <label for="doctorSelect" class="form-label mb-0">Dokter</label>
            <select name="doctorSelect" class="form-select" id="doctorSelect">
              <option value="">Semua Dokter</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label for="providerSelect" class="form-label mb-0">Provider</label>
            <select name="providerSelect" class="form-select" id="providerSelect">
              <option value="">Semua Metode Pembayaran</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button class="btn btn-primary" id="btnApplyFilter">Terapkan Filter</button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="screeningModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header text-dark">
        <h5 class="modal-title">📝 Screening Pasien</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="screening_id_visit">

        <div class="mb-3">
          <label class="form-label">Keluhan</label>
          <textarea id="sc_keluhan" class="form-control"></textarea>
        </div>

        <h5>Pemeriksaan Vital Sign (Perawat)</h5>
        <div class="row g-2">
          <div class="col-md-4">
            <label for="kondisi_masuk" class="form-label">Kondisi Masuk <span class="text-danger">*</span></label>
            <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
              <option value="Baik">Baik</option>
              <option value="Lemah">Lemah</option>
              <option value="Sedang">Sedang</option>
              <option value="Buruk">Buruk</option>
              <option value="Gawat Darurat">Gawat Darurat</option>
              <option value="Tidak Sadar">Tidak Sadar</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="tekanan_darah" class="form-label">Tekanan Darah (mmHg) <span class="text-danger">*</span></label>
            <input
              type="text"
              id="tekanan_darah"
              name="tekanan_darah"
              class="form-control"
              maxlength="7"
              required>
          </div>
          <script>
            document.getElementById("tekanan_darah").addEventListener("input", function() {
              let value = this.value.replace(/[^\d]/g, ''); // hanya angka
              if (value.length > 3) {
                value = value.slice(0, 3) + '/' + value.slice(3, 6); // sisipkan '/'
              }
              this.value = value;
            });
          </script>
          <div class="col-md-4">
            <label for="suhu" class="form-label">Suhu (°C) <span class="text-danger">*</span></label>
            <input type="number" step="0.1" id="suhu" required name="suhu" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="nadi" class="form-label">Nadi (x/menit) <span class="text-danger">*</span></label>
            <input type="number" id="nadi" name="nadi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="respirasi" class="form-label">Respirasi (x/menit) <span class="text-danger">*</span></label>
            <input type="number" id="respirasi" name="respirasi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="tinggi" class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
            <input type="number" id="tinggi" name="tinggi" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="berat" class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
            <input type="number" id="berat" name="berat" required class="form-control">
          </div>
          <div class="col-md-4 mt-2">
            <label for="bmi" class="form-label">BMI <span class="text-danger">*</span></label>
            <input type="number" readonly id="bmi" name="bmi" required class="form-control bg-light">
          </div>
          <div class="col-md-4 mt-2">
            <label class="form-label">Keterangan</label>
            <input type="text" id="bmi_ket" name="bmi_ket" readonly class="form-control bg-light">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Catatan Screening</label>
          <textarea id="sc_catatan" class="form-control"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnSaveScreening">💾 Simpan</button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="poliModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Registrasi Rawat Inap</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="formPoli">
          <!-- Pasien -->
          <div class="mb-3">
            <label class="form-label">Nama Pasien</label>
            <select name="id_patient_select" id="id_patient_select"
              class="form-select js-example-basic-item" required>
            </select>
          </div>
          <div class="row">
            <div class="col">
              <!-- Tanggal -->
              <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" id="poli_date" class="form-control">
              </div>
            </div>
            <div class="col">
              <!-- Jam -->
              <div class="mb-3">
                <label class="form-label">Jam Kunjungan</label>
                <input type="time" id="poli_time" class="form-control">
              </div>
            </div>
          </div>
          <!-- Dokter -->
          <div class="mb-3">
            <label class="form-label">Dokter</label>
            <select id="poli_doctor" class="form-select"></select>
          </div>

          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <select id="service_class" class="form-select"></select>
          </div>

          <div class="mb-3">
            <label class="form-label">Nama Kamar</label>
            <select id="room_name" class="form-select"></select>
          </div>

          <div class="mb-3">
            <label class="form-label">No. Tempat Tidur</label>
            <select id="bed_name" class="form-select"></select>
          </div>

          <!-- Provider -->
          <div class="mb-3">
            <label class="form-label">Provider</label>
            <select id="poli_provider" class="form-select"></select>
          </div>

        </form>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" id="btnSavePoli">
          <i class="fas fa-save me-2"></i>Simpan
        </button>
      </div>

    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#filterModal').on('show.bs.modal', function() {
      loadDoctors();
      loadProviders();
    });

    function loadDoctors() {
      $.ajax({
        url: 'controller/visit/getdoctor',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          let html = '<option value="">Semua Dokter</option>';

          res.forEach(d => {
            html += `<option value="${d.id_doctor}">${d.doctor_name}</option>`;
          });

          $('#doctorSelect').html(html);
        }
      });
    }

    function loadProviders() {
      $.ajax({
        url: 'controller/visit/getprovider',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          let html = '<option value="">Semua Metode Pembayaran</option>';

          res.forEach(p => {
            html += `<option value="${p.id_provider}">${p.provider_name}</option>`;
          });

          $('#providerSelect').html(html);
        }
      });
    }


    $('#btnApplyFilter').on('click', function() {
      table.ajax.reload();
      $('#filterModal').modal('hide');
    });
    var today = new Date().toISOString().split("T")[0];
    $("#fromDate").val(today);
    $("#toDate").val(today);
    const tipePasien = "Rawat Inap";
    const apiUrl = 'controller/visit/registrasiRanapController';
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      scrollX: true, // ✅ ini wajib
      scrollCollapse: true,

      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctor = $('#doctorSelect').val();
          d.provider = $('#providerSelect').val();
          d.tipe_pasien = tipePasien;
        },
        dataSrc: function(json) {
          return json.data.map(function(row) {

            function hitungUmur(tglLahir, tglKunjungan) {
              if (!tglLahir || !tglKunjungan) return "-";

              const birth = new Date(tglLahir);
              const visit = new Date(tglKunjungan);

              let tahun = visit.getFullYear() - birth.getFullYear();
              let bulan = visit.getMonth() - birth.getMonth();
              let hari = visit.getDate() - birth.getDate();

              if (hari < 0) {
                bulan--;
                const lastMonth = new Date(visit.getFullYear(), visit.getMonth(), 0);
                hari += lastMonth.getDate();
              }

              if (bulan < 0) {
                tahun--;
                bulan += 12;
              }

              return `${tahun} th ${bulan} bln ${hari} hr`;
            }
            return {
              "actions": `
                <div class="text-center">
                  <div class="dropdown">

                     <button class="btn btn-sm btn-primary dropdown-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-boundary="window">
                        ⚙️ Aksi
                      </button>

                      <ul class="dropdown-menu dropdown-menu-end shadow">

                      <li>
                        <a class="dropdown-item detail-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-eye me-2 text-info"></i> Lihat Hasil Pemeriksaan
                        </a>
                      </li>

                       <li>
                        <a class="dropdown-item" href="module/admin/form_sep?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="fas fa-upload me-2 text-info"></i> Upload SEP
                        </a>
                      </li>

                         <li>
                        <a class="dropdown-item" href="module/admin/form_fkpp?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="fas fa-upload me-2 text-info"></i> Upload FKPP
                        </a>
                      </li>
                        
                       <li>
                        <a class="dropdown-item" href="module/admin/form_pernyataan?no=${row.visit_ID}&rm=${row.nomor_rm}">
                          <i class="fas fa-file me-2 text-info"></i>Surat Pernyataan Pasien
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item camera-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-camera me-2 text-success"></i> Ambil Foto
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item edit-btn" href="javascript:;" 
                          data-id="${row.id_visit}" 
                          data-patient="${row.id_patient}" 
                          data-doctor="${row.id_doctor}" 
                          data-poli="${row.id_poli}" 
                          data-source="${row.source_hub}" 
                          data-notes="${row.visit_notes}">
                          <i class="fas fa-edit me-2 text-warning"></i> Edit Data
                        </a>
                      </li>

                      <li><hr class="dropdown-divider"></li>

                      <li>
                        <a class="dropdown-item delete-btn text-danger" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-trash me-2"></i> Hapus
                        </a>
                      </li>

                    </ul>

                  </div>
                </div>
              `,
              "registrasi": row.visit_date + ' ' + row.visit_time ?? "-",
              "nomor_bpjs": row.patient_bpjs ?? "-",
              "nama": `
                ${row.patient_name ?? "-"}
              `,
              "gender": row.patient_gender ?? "-",
              "dokter": row.id_doctor ?? "-",
              "provider": row.provider_name ?? "-",
              "status": row.status_perawatan_inap === 0 ? '<span class="badge bg-primary text-center d-block">Perawatan</span>' : row.status_perawatan_inap === 1 ? '<span class="badge bg-success text-center d-block">Pulang</span>' : '<span class="badge bg-dark text-center d-block">Unknown</span>'
            };
          });
        }
      },
      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        }, {
          data: "status"
        }, {
          data: "registrasi"
        },
        {
          data: "nomor_bpjs"
        },
        {
          data: "nama"
        },
        {
          data: "gender"
        },
        {
          data: "dokter"
        },
        {
          data: "provider"
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

    $('#periodeTable').on('draw.dt', function() {
      document.querySelectorAll('.dropdown-toggle').forEach(function(el) {
        new bootstrap.Dropdown(el);
      });
    });

    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });

    // filter manual
    $('#btnFilter').on('click', function() {
      table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      $('#doctorSelect').val('');
      $('#providerSelect').val('');
      table.ajax.reload();
    });

    $(document).on('click', '.detail-btn', function() {
      let id = $(this).data('id');

      $('#detailModal').modal('show');

      fetch(`controller/visit/getDetailPemeriksaan?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            fillDetail(resp.data);
          } else {
            alert('Gagal load data');
          }
        });
    });

    function fillDetail(d) {
      $('#d_patient_name').text(d.patient_name ?? '-');
      $('#d_doctor_name').text(d.doctor_name ?? '-');
      $('#d_poli_name').text(d.poli_name ?? '-');
      $('#d_visit_date').text(d.visit_date ?? '-');
      $('#d_no_sep').text(d.no_sep ?? '-');

      $('#d_tekanan_darah').text(d.tekanan_darah ?? '-');
      $('#d_suhu').text((d.suhu ?? '-') + ' °C');
      $('#d_nadi').text((d.nadi ?? '-') + ' bpm');
      $('#d_respirasi').text((d.respirasi ?? '-') + ' /menit');
      $('#d_tinggi').text((d.tinggi ?? '-') + ' cm');
      $('#d_berat').text((d.berat ?? '-') + ' kg');

      $('#d_anamnesa').text(d.anamnesa ?? '-');
      $('#d_diagnosa').text(d.diagnosa ?? '-');
      $('#d_tindakan').text(d.tindakan ?? '-');

      if (d.suhu > 37.5) {
        $('#d_suhu').addClass('text-danger fw-bold');
      }
    }
  });
</script>
<script>
  let stream;
  let currentVisitId = null;

  $(document).on('click', '.camera-btn', function() {
    currentVisitId = $(this).data('id');

    $('#cameraModal').modal('show');

    navigator.mediaDevices.getUserMedia({
        video: true
      })
      .then(s => {
        stream = s;
        document.getElementById('video').srcObject = stream;
      });
  });

  $('#btnCapture').click(function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    const img = canvas.toDataURL('image/png');

    $('#preview').attr('src', img).removeClass('d-none');
    $('#btnSave').removeClass('d-none');
  });
  $('#btnSave').click(function() {
    const img = document.getElementById('canvas').toDataURL('image/png');

    fetch('controller/visit/uploadCapture', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          image: img,
          id_visit: currentVisitId
        })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          alert('Foto berhasil disimpan');
          $('#cameraModal').modal('hide');
        }
      });

    $('#cameraModal').on('hidden.bs.modal', function() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
      }
    });
  });
</script>
<script>
  $(document).on('click', '.screening-btn', function() {
    let id = $(this).data('id');

    $('#screening_id_visit').val(id);
    $('#sc_keluhan').val('');
    $('#sc_catatan').val('');

    $('#screeningModal').modal('show');
  });

  $('#btnSaveScreening').on('click', function() {
    const data = {
      id_visit: $('#screening_id_visit').val(),
      keluhan: $('#sc_keluhan').val(),
      catatan: $('#sc_catatan').val()
    };

    fetch('controller/visit/saveScreening.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          alert('Screening berhasil disimpan');
          $('#screeningModal').modal('hide');
        }
      });
  });
</script>
<script>
  $(document).on('click', '.poli-btn', function() {
    $('#poliModal').modal('show');

    const now = new Date();
    $('#poli_date').val(now.toISOString().split('T')[0]);
    $('#poli_time').val(now.toTimeString().slice(0, 5));

    loadDoctors();
    loadKelas();
    loadProvider();
  });


  function loadDoctors() {
    fetch('controller/visit/getdoctor')
      .then(res => res.json())
      .then(res => {
        let html = '<option value="">Pilih Dokter</option>';
        res.forEach(d => {
          html += `<option value="${d.id_doctor}">${d.doctor_name}</option>`;
        });
        $('#poli_doctor').html(html);
      });
  }

  function loadProvider() {
    fetch('controller/visit/getprovider')
      .then(res => res.json())
      .then(res => {
        let html = '<option value="">Pilih Provider</option>';
        res.forEach(p => {
          html += `<option value="${p.id_provider}">${p.provider_name}</option>`;
        });
        $('#poli_provider').html(html);
      });
  }

  function loadKelas() {
    $('#service_class').html('<option value="">Loading...</option>');
    $('#room_name').empty();
    $('#bed_name').empty();

    fetch('controller/visit/getRoomRanap.php?type=service_class')
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          let opt = '<option value="">-- Pilih Kelas --</option>';
          resp.data.forEach(v => opt += `<option value="${v}">${v}</option>`);
          $('#service_class').html(opt);
        }
      });
  }

  $('#service_class').on('change', function() {
    let kelas = $(this).val();
    $('#room_name').html('<option value="">Loading...</option>');
    $('#bed_name').html('');

    if (kelas) {
      fetch(`controller/visit/getRoomRanap.php?type=room_name&value=${kelas}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let opt = '<option value="">-- Pilih Kamar --</option>';
            resp.data.forEach(r => opt += `<option value="${r.id_room}">${r.room_name}</option>`);
            $('#room_name').html(opt);
          } else {
            $('#room_name').html('<option value="">Tidak ada data</option>');
          }
        });
    } else {
      $('#room_name').html('');
      $('#bed_name').html('');
    }
  });

  $('#room_name').on('change', function() {
    let id_room = $(this).val();
    $('#bed_name').html('<option value="">Loading...</option>');

    if (id_room) {
      fetch(`controller/visit/getRoomRanap.php?type=bed_name&value=${id_room}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let opt = '<option value="">-- Pilih Tempat Tidur --</option>';
            resp.data.forEach(b => opt += `<option value="${b.id_bed}">${b.bed_name}-${b.bed_gender}</option>`);
            $('#bed_name').html(opt);
          } else {
            $('#bed_name').html('<option value="">Tidak ada data</option>');
          }
        });
    } else {
      $('#bed_name').html('');
    }
  });

  $('#btnSavePoli').on('click', function() {
    const data = {
      id_patient: $('#id_patient_select').val(),
      id_doctor: $('#poli_doctor').val(),
      room_name: $('#room_name').val(),
      bed_name: $('#bed_name').val(),
      id_provider: $('#poli_provider').val(),
      visit_date: $('#poli_date').val(),
      visit_time: $('#poli_time').val(),
      source_hub: 'Rawat Inap'
    };

    if (!data.id_patient || !data.id_doctor || !data.visit_date || !data.visit_time || !data.room_name || !data.bed_name) {
      alert('Data wajib belum lengkap');
      return;
    }

    const formData = new URLSearchParams();
    for (let key in data) {
      formData.append(key, data[key] ?? '');
    }

    fetch('controller/visit/autoApproveRanap', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          Swal.fire('Registrasi Rawat Inap berhasil', '', 'success');
          $('#poliModal').modal('hide');
          $('#periodeTable').DataTable().ajax.reload(null, false);
        } else {
          Swal.fire('Gagal', resp.message || 'Terjadi kesalahan', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Terjadi error');
      });
  });

  $('#poliModal').on('shown.bs.modal', function() {
    const $select = $('#id_patient_select');

    if ($select.hasClass('select2-hidden-accessible')) {
      $select.select2('destroy');
    }

    $select.select2({
      dropdownParent: $('#poliModal'),
      width: '100%',
      placeholder: 'Cari pasien... ',
      minimumInputLength: 2,
      ajax: {
        url: 'controller/admisi/patientSearchController',
        type: 'GET',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            search: params.term
          };
        },
        processResults: function(data) {
          let items = data.data ? data.data : data;

          return {
            results: items.map(item => ({
              id: item.id_patient,
              text: `${item.patient_name} (${item.nomor_rm})`
            }))
          };
        },
        cache: true
      }
    });
  });
</script>

</html>