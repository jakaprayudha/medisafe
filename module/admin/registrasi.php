<?php
$title = 'Registrasi Poliklinik';
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
  <style>
    .dropdown-menu {
      z-index: 99999 !important;
    }
  </style>
  <style id="fixcss">
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
                    <h5 class="card-title fw-semibold">Data Registrasi Poliklinik</h5>
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
                      <div class="d-flex ms-auto">
                        <div class="dropdown">
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
                                <i class="fas fa-stethoscope me-2 text-success"></i> Pasien Poliklinik
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal">Registrasi</th>
                          <th>Antrian</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Poli</th>
                          <th scope="col" class="text-dark fw-normal">Jenis Bayar</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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


<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_visit" id="id_visit">
        <input type="hidden" name="id_patient" id="id_patient"> <!-- 🔹 dari klik add -->
        <input type="hidden" name="user" value="<?= $_SESSION['fullname'] ?>" id="user">
        <div class="mb-3">
          <label class="form-label required">Layanan (Poli)</label>
          <select name="id_poli" id="id_poli" class="form-select" required>
            <option value="">PILIH</option>
            <?php
            $getpoli = tampildata("SELECT * FROM ms_poli WHERE poli_status='1'");
            foreach ($getpoli as $poli) :
            ?>
              <option value="<?= $poli['id_poli'] ?>"><?= $poli['poli_name'] ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label required">Dokter</label>
          <select name="id_doctor" id="id_doctor" class="form-select" required>
            <option value="">PILIH</option>
            <?php
            $getdoc = tampildata("SELECT * FROM ms_doctor WHERE doctor_status='1'");
            foreach ($getdoc as $doc) :
            ?>
              <option value="<?= $doc['id_doctor'] ?>"><?= $doc['doctor_name'] ?></option>
            <?php endforeach ?>
          </select>
        </div>


        <div class="mb-3">
          <label class="form-label required">Layanan</label>
          <select name="source_hub" id="source_hub" class="form-select" required>
            <option value="Poliklinik">Poliklinik</option>
            <option value="UGD">UGD</option>
            <option value="Rawat Inap">Rawat Inap</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Catatan</label>
          <textarea name="visit_notes" id="visit_notes" class="form-control" rows="5"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
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
                <span>
                  <div id="d_visit_date">-</div>
                </span>

              </div>

              <div class="col-md-6">
                <small class="text-muted">No. SEP</small>
                <div id="d_no_sep">-</div>
              </div>
              <div class="col-md-6">
                <small class="text-muted">Kondisi Masuk</small>
                <div id="d_kondisi_masuk">-</div>
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

              <span class="badge bg-light text-dark">
                🏋 BMI: <span id="d_bmi">-</span> Keterangan : <span id="d_bmi_keterangan"></span>
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

        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-2">📝 Catatan Screening</h6>
            <div id="d_catatan_screening" class="text-muted">-</div>
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

      <div class="modal-header">
        <h5 class="modal-title">📸 Pengenalan Wajah</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">

        <video id="video" autoplay class="w-100 rounded mb-3"></video>

        <canvas id="canvas" class="d-none"></canvas>

        <img id="preview" class="img-fluid rounded d-none" />

        <div class="mt-3">
          <button class="btn btn-outline-primary" id="btnCapture">📸 Ambil Foto</button>
          <!-- 🔥 TAMBAHAN -->
          <button class="btn btn-success d-none" id="btnVerify">
            ✅ Verifikasi Pasien
          </button>
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
          <div class="col-12 mb-3">
            <label for="poliSelect" class="form-label mb-0">Poliklinik</label>
            <select name="poliSelect" class="form-select" id="poliSelect">
              <option value="">Semua Poliklinik</option>
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
            <label class="form-label">Tekanan Darah (mmHg)</label>

            <div class="d-flex gap-2">
              <input type="number" id="sistolik" class="form-control" placeholder="Sistolik" required>
              <span class="align-self-center">/</span>
              <input type="number" id="diastolik" class="form-control" placeholder="Diastolik" required>
            </div>

            <!-- hidden untuk backend -->
            <input type="hidden" id="tekanan_darah" name="tekanan_darah">
          </div>

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
        <h5 class="modal-title">🩺 Registrasi Poliklinik</h5>
        <button class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="formPoli">
          <!-- Pasien -->
          <div class="mb-3">
            <label class="form-label">👤 Nama Pasien</label>
            <select name="id_patient_select" id="id_patient_select"
              class="form-select js-example-basic-item" required>
            </select>
          </div>
          <div class="row">
            <div class="col">
              <!-- Tanggal -->
              <div class="mb-3">
                <label class="form-label">📅 Tanggal</label>
                <input type="date" id="poli_date" class="form-control">
              </div>
            </div>
            <div class="col">
              <!-- Jam -->
              <div class="mb-3">
                <label class="form-label">⏰ Jam Kunjungan</label>
                <input type="time" id="poli_time" class="form-control">
              </div>
            </div>
          </div>
          <!-- Dokter -->
          <div class="mb-3">
            <label class="form-label">👨‍⚕️ Dokter</label>
            <select id="poli_doctor" class="form-select"></select>
          </div>

          <!-- Poli -->
          <div class="mb-3">
            <label class="form-label">🏥 Poliklinik</label>
            <select id="poli_poli" class="form-select"></select>
          </div>

          <!-- Provider -->
          <div class="mb-3">
            <label class="form-label">💳 Provider</label>
            <select id="poli_provider" class="form-select"></select>
          </div>



        </form>

      </div>

      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" id="btnSavePoli">💾 Simpan</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="ttdModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">✍️ Tanda Tangan Pasien</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">

        <input type="hidden" id="ttd_id_visit">

        <canvas id="signaturePad"
          style="border:1px solid #ccc; width:100%; height:200px;">
        </canvas>

        <div class="mt-3 d-flex justify-content-between">
          <button class="btn btn-warning" id="clearSignature">🧹 Clear</button>
          <button class="btn btn-primary" id="saveSignature">💾 Simpan</button>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  function hitungBMI() {
    const tinggi = parseFloat(document.getElementById('tinggi').value);
    const berat = parseFloat(document.getElementById('berat').value);

    if (!tinggi || !berat) return;

    const tinggiMeter = tinggi / 100;
    const bmi = berat / (tinggiMeter * tinggiMeter);

    // set nilai BMI (2 decimal)
    document.getElementById('bmi').value = bmi.toFixed(2);

    // kategori BMI
    let ket = '';

    if (bmi < 18.5) {
      ket = 'Kurus';
    } else if (bmi < 25) {
      ket = 'Normal';
    } else if (bmi < 30) {
      ket = 'Gemuk';
    } else {
      ket = 'Obesitas';
    }

    document.getElementById('bmi_ket').value = ket;
  }

  // trigger saat input berubah
  document.getElementById('tinggi').addEventListener('input', hitungBMI);
  document.getElementById('berat').addEventListener('input', hitungBMI);
</script>
<script>
  $(document).ready(function() {
    $('#filterModal').on('show.bs.modal', function() {
      loadDoctors();
      loadProviders();
      loadPoli();
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

    function loadPoli() {
      $.ajax({
        url: 'controller/visit/getpoli',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          let html = '<option value="">Semua Poliklinik</option>';

          res.forEach(p => {
            html += `<option value="${p.id_poli}">${p.poli_name}</option>`;
          });

          $('#poliSelect').html(html);
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
    const tipePasien = "Poliklinik";
    const apiUrl = 'controller/visit/registrasiController';
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
          d.poli = $('#poliSelect').val();
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
                  <div class="dropup">

                    <button class="btn btn-sm btn-primary dropdown-toggle" 
                      type="button" 
                      data-bs-toggle="dropdown"
                      data-bs-display="static"
                      data-bs-boundary="viewport">
                      ⚙️ Aksi
                    </button>

                      <ul class="dropdown-menu dropdown-menu-end shadow">

                      <li>
                        <a class="dropdown-item detail-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-eye me-2 text-info"></i> Lihat Hasil Pemeriksaan
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item screening-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-pencil me-2 text-primary"></i> Screening
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item camera-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-camera me-2 text-success"></i> Ambil Foto
                        </a>
                      </li>
                        <li>
                        <a class="dropdown-item ttd-btn" href="javascript:;" data-id="${row.id_visit}">
                          <i class="fas fa-signature me-2 text-dark"></i> Tanda Tangan
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
              "registrasi": row.visit_ID + '<br>' + row.visit_date + ' ' + row.visit_time ?? "-",
              "antrian": row.visit_antrian ?? "-",
              "nomor_rm": row.nomor_rm ?? "-",
              "nama": `
                ${row.patient_name ?? "-"}<br>
                <small class="text-muted">
                  ${hitungUmur(row.patient_datebirth, row.visit_date)}
                </small>
              `,
              "gender": row.patient_gender ?? "-",
              "dokter": row.doctor_name ?? "-",
              "layanan": row.poli_name ?? "-",
              "provider": row.provider_name ?? "-",
              "status": row.visit_status === 99 ? '<span class="badge bg-danger text-center d-block">Batal</span>' : row.visit_status === 1 ? '<span class="badge bg-warning text-center d-block">Menunggu</span>' : row.visit_status === 2 ? '<span class="badge bg-secondary text-center d-block">Dipanggil</span>' : row.visit_status === 3 ? '<span class="badge bg-primary text-center d-block">Dilayani</span>' : row.visit_status === 4 ? '<span class="badge bg-success text-center d-block">Selesai</span>' : '<span class="badge bg-dark text-center d-block">Unknown</span>'
            };
          });
        }
      },
      columns: [{
          data: "status"
        }, {
          data: "registrasi"
        },
        {
          data: "antrian"
        },
        {
          data: "nomor_rm"
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
          data: "layanan"
        },
        {
          data: "provider"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
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

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_visit').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_visit').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
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
      $('#poliSelect').val('');
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
      $('#d_visit_date').text(d.visit_date + ' ' + d.visit_time ?? '-');
      $('#d_no_sep').text(d.no_sep ?? '-');
      $('#d_kondisi_masuk').text(d.kondisi_masuk ?? '-');

      $('#d_tekanan_darah').text(d.tekanan_darah ?? '-');
      $('#d_suhu').text((d.suhu ?? '-') + ' °C');
      $('#d_nadi').text((d.nadi ?? '-') + ' bpm');
      $('#d_respirasi').text((d.respirasi ?? '-') + ' /menit');
      $('#d_tinggi').text((d.tinggi_badan ?? '-') + ' cm');
      $('#d_berat').text((d.berat_badan ?? '-') + ' kg');
      $('#d_bmi').text(d.bmi ?? '-');
      $('#d_bmi_keterangan').text(d.bmi_keterangan ?? '-');

      $('#d_anamnesa').text(d.anamnesa ?? '-');
      $('#d_catatan_screening').text(d.catatan_screening ?? '-');
      $('#d_diagnosa').text(d.diagnosa ?? '-');
      $('#d_tindakan').text(d.tindakan ?? '-');

      if (d.suhu > 37.5) {
        $('#d_suhu').addClass('text-danger fw-bold');
      }
    }
  });
</script>
<!-- <script>
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
</script> -->
<script>
  $(document).on('shown.bs.dropdown', '.dropdown, .dropup', function() {
    const $menu = $(this).find('.dropdown-menu');
    const $button = $(this).find('[data-bs-toggle="dropdown"]');

    const offset = $button.offset();
    const height = $menu.outerHeight();

    // pindahkan ke body
    $('body').append($menu);

    let top;

    if ($(this).hasClass('dropup')) {
      // 🔼 dropup → ke atas
      top = offset.top - height;
    } else {
      // 🔽 dropdown → ke bawah (normal)
      top = offset.top + $button.outerHeight();
    }

    const left = offset.left;

    $menu.css({
      position: 'absolute',
      top: top + 'px',
      left: left + 'px',
      display: 'block',
      zIndex: 99999
    });
  });
  $(document).on('hide.bs.dropdown', '.dropdown, .dropup', function() {
    const $menu = $('body > .dropdown-menu');

    $(this).append($menu);
    $menu.removeAttr('style');
  });

  $(document).on('click', '.screening-btn', function() {
    let id = $(this).data('id');

    $('#screening_id_visit').val(id);
    $('#sc_keluhan').val('');
    $('#sc_catatan').val('');
    $('#kondisi_masuk').val('');
    $('#tekanan_darah').val('');
    $('#suhu').val('');
    $('#nadi').val('');
    $('#respirasi').val('');
    $('#tinggi').val('');
    $('#berat').val('');
    $('#bmi').val('');
    $('#bmi_ket').val('');
    $('#screeningModal').modal('show');
  });

  $('#btnSaveScreening').on('click', function() {
    const sistolik = $('#sistolik').val();
    const diastolik = $('#diastolik').val();

    if (!sistolik || !diastolik) {
      alert("Tekanan darah harus diisi!");
      return;
    }

    const tekananDarah = `${sistolik}/${diastolik}`;
    const data = {
      id_visit: $('#screening_id_visit').val(),
      keluhan: $('#sc_keluhan').val(),
      catatan: $('#sc_catatan').val(),
      kondisi_masuk: $('#kondisi_masuk').val(),
      tekanan_darah: tekananDarah,
      suhu: $('#suhu').val(),
      nadi: $('#nadi').val(),
      respirasi: $('#respirasi').val(),
      tinggi: $('#tinggi').val(),
      berat: $('#berat').val(),
      bmi: $('#bmi').val(),
      bmi_ket: $('#bmi_ket').val()
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
    loadPoli();
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

  function loadPoli() {
    fetch('controller/visit/getpoli')
      .then(res => res.json())
      .then(res => {
        let html = '<option value="">Pilih Poli</option>';
        res.forEach(p => {
          html += `<option value="${p.id_poli}">${p.poli_name}</option>`;
        });
        $('#poli_poli').html(html);
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
  $('#btnSavePoli').on('click', function() {

    const data = {
      id_patient: $('#id_patient_select').val(), // 🔥 FIX select2
      id_doctor: $('#poli_doctor').val(),
      id_poli: $('#poli_poli').val(),
      id_provider: $('#poli_provider').val(),
      visit_date: $('#poli_date').val(),
      visit_time: $('#poli_time').val()
    };

    // 🔥 VALIDASI (optional tapi bagus)
    if (!data.id_patient || !data.id_doctor || !data.visit_date || !data.visit_time) {
      alert('Data wajib belum lengkap');
      return;
    }

    // 🔥 CONVERT KE FORM-ENCODED
    const formData = new URLSearchParams();
    for (let key in data) {
      formData.append(key, data[key] ?? '');
    }

    fetch('controller/visit/visitController', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
      .then(res => res.json())
      .then(resp => {

        if (resp.status === 'success') {

          // 🔥 ALERT LEBIH INFORMATIVE
          alert(`✅ Registrasi berhasil\nNo Antrian: ${resp.data.antrian}`);

          $('#poliModal').modal('hide');

          // reload table tanpa reset paging
          $('#periodeTable').DataTable().ajax.reload(null, false);

        } else {
          alert('❌ ' + resp.message);
        }

      })
      .catch(err => {
        console.error(err);
        alert('Terjadi error');
      });

  });

  $('#poliModal').on('shown.bs.modal', function() {

    const $select = $('#id_patient_select');

    // destroy kalau sudah ada
    if ($select.hasClass("select2-hidden-accessible")) {
      $select.select2('destroy');
    }
    console.log("INIT SELECT2");
    $select.select2({
      dropdownParent: $('#poliModal'),
      width: '100%',
      placeholder: 'Cari pasien...',
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
          console.log("RESPONSE:", data);

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

<script>
  let canvas = document.getElementById('signaturePad');
  let ctx = canvas.getContext('2d');

  let drawing = false;

  // resize canvas biar presisi
  function resizeCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = 200;
  }
  resizeCanvas();

  // start drawing
  canvas.addEventListener('mousedown', () => drawing = true);
  canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('mousemove', draw);

  // support touch (HP)
  canvas.addEventListener('touchstart', (e) => {
    drawing = true;
  });
  canvas.addEventListener('touchend', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('touchmove', drawTouch);

  function draw(e) {
    if (!drawing) return;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
  }

  function drawTouch(e) {
    e.preventDefault();
    if (!drawing) return;

    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];

    const x = touch.clientX - rect.left;
    const y = touch.clientY - rect.top;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
  }

  // clear
  document.getElementById('clearSignature').onclick = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  };

  // open modal
  $(document).on('click', '.ttd-btn', function() {
    let id = $(this).data('id');

    $('#ttd_id_visit').val(id);
    $('#ttdModal').modal('show');

    setTimeout(resizeCanvas, 200);
  });

  // save
  document.getElementById('saveSignature').onclick = function() {
    const image = canvas.toDataURL('image/png');

    fetch('controller/admisi/saveSignature.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_visit: $('#ttd_id_visit').val(),
          image: image
        })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          alert('Tanda tangan berhasil disimpan');
          $('#ttdModal').modal('hide');
        }
      });
  };
</script>

<script src="assets/js/face-api.min.js"></script>

<script>
  let currentVisitId = null;
  let verifiedUser = null;
  window.addEventListener("load", function() {

    console.log("faceapi:", typeof faceapi);

    /* =========================
       GLOBAL STATE
    ========================= */
    let stream = null;
    let intervalId = null;
    let modelsLoaded = false;

    /* =========================
       LOAD MODELS
    ========================= */
    async function loadModels() {
      if (modelsLoaded) return;

      if (typeof faceapi === "undefined") {
        alert("❌ face-api.js belum ter-load");
        return;
      }

      console.log("⏳ Loading models...");

      await faceapi.nets.tinyFaceDetector.loadFromUri('models');
      await faceapi.nets.faceRecognitionNet.loadFromUri('models');
      await faceapi.nets.faceLandmark68Net.loadFromUri('models');

      modelsLoaded = true;
      console.log("✅ Models loaded");
      console.log("MODEL URL:", 'models/tiny_face_detector_model-weights_manifest.json');
    }

    /* =========================
       CLICK CAMERA
    ========================= */
    $(document).on("click", ".camera-btn", async function() {

      currentVisitId = $(this).data("id"); // 🔥 ambil id_visit
      console.log(currentVisitId);

      try {
        await loadModels();

        const modalEl = document.getElementById("cameraModal");
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener("shown.bs.modal", async function handler() {
          modalEl.removeEventListener("shown.bs.modal", handler);

          stream = await navigator.mediaDevices.getUserMedia({
            video: true
          });

          const video = document.getElementById("video");
          video.srcObject = stream;

          video.onloadedmetadata = () => {
            video.play();
            startRecognition(video);
          };
        });

      } catch (err) {
        console.error("❌ Camera error:", err);
      }

    });

    /* =========================
       FACE RECOGNITION
    ========================= */
    async function startRecognition(video) {

      try {
        const labeledDescriptors = await getLabeledFaceDescriptions();

        if (!labeledDescriptors.length) {
          console.warn("⚠️ Tidak ada data wajah di database");
          return;
        }

        const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

        const container = document.querySelector("#cameraModal .modal-body");

        const oldCanvas = container.querySelector("canvas.overlay");
        if (oldCanvas) oldCanvas.remove();

        const canvas = faceapi.createCanvasFromMedia(video);
        canvas.classList.add("overlay");
        container.append(canvas);

        const displaySize = {
          width: video.videoWidth,
          height: video.videoHeight
        };

        faceapi.matchDimensions(canvas, displaySize);

        if (intervalId) clearInterval(intervalId);

        intervalId = setInterval(async () => {

          const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptors();

          const resized = faceapi.resizeResults(detections, displaySize);

          const ctx = canvas.getContext("2d");
          ctx.clearRect(0, 0, canvas.width, canvas.height);

          resized.forEach((d) => {
            const match = faceMatcher.findBestMatch(d.descriptor);
            const label = match.toString();

            if (label !== "unknown") {
              console.log("✅ MATCH:", label);

              verifiedUser = label; // simpan hasil match
              enableVerifyButton(label);

            } else {
              verifiedUser = null;
              disableVerifyButton();
            }

            const drawBox = new faceapi.draw.DrawBox(d.detection.box, {
              label: match.toString()
            });

            drawBox.draw(canvas);
          });

        }, 150);

      } catch (err) {
        console.error("❌ Recognition error:", err);
      }
    }

    /* =========================
       LOAD FACE DATA
    ========================= */
    async function getLabeledFaceDescriptions() {

      try {
        console.log(currentVisitId);
        const res = await fetch(`controller/visit/getFaces.php?id_visit=${currentVisitId}`);
        const data = await res.json();

        return Promise.all(
          data.map(async (user) => {

            try {
              if (!user.image) return null;

              const img = new Image();
              img.crossOrigin = "anonymous";
              img.src = user.image;

              await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = () => {
                  console.warn("❌ Gagal load image:", user.image);
                  reject();
                };
              });

              const detection = await faceapi
                .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

              if (!detection) return null;

              return new faceapi.LabeledFaceDescriptors(
                user.name,
                [detection.descriptor]
              );

            } catch (err) {
              console.warn("❌ Error processing image:", user.image);
              return null;
            }

          })
        ).then(results => results.filter(r => r !== null));

      } catch (err) {
        console.error("❌ Error ambil data wajah:", err);
        return [];
      }
    }

    function enableVerifyButton(name) {
      $("#btnVerify")
        .removeClass("d-none")
        .text(`✅ Verifikasi (${name})`);
    }

    function disableVerifyButton() {
      $("#btnVerify")
        .addClass("d-none")
        .text("Verifikasi");
    }

    /* =========================
       CLEANUP
    ========================= */
    document.getElementById("cameraModal")
      .addEventListener("hidden.bs.modal", function() {

        console.log("🛑 Stop camera");

        if (stream) {
          stream.getTracks().forEach(track => track.stop());
          stream = null;
        }

        if (intervalId) {
          clearInterval(intervalId);
          intervalId = null;
        }

      });

    $("#btnVerify").on("click", function() {

      if (!verifiedUser) {
        alert("Wajah belum dikenali");
        return;
      }

      fetch("controller/visit/verifyFace.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            id_visit: currentVisitId,
            id_patient: verifiedUser
          })
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.status === "success") {
            alert("✅ Verifikasi berhasil");
            $("#cameraModal").modal("hide");
          } else {
            alert("❌ Verifikasi gagal");
          }
        });

    });

  });
</script>

</html>