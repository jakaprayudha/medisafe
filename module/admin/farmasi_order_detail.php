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
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient LEFT JOIN permintaan_pharmacy ON permintaan_pharmacy.id_visit = pasien_visit.visit_ID  WHERE pasien_visit.visit_ID='$no'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
// if ($data) {
//   $patient_datebirth = new DateTime($data['patient_datebirth']);
//   $tanggal_visit = new DateTime($data['visit_date']);

//   $usia = $patient_datebirth->diff($tanggal_visit);
// }

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .info-item {
      display: flex;
      align-items: center;
      gap: 14px;
      background: #f0fdfa;
      padding: 14px 16px;
      border-radius: 12px;
    }

    .info-item i {
      font-size: 28px;
      color: #0f766e;
    }

    .info-item .label {
      font-size: 13px;
      color: #64748b;
    }

    .info-item .value {
      font-size: 16px;
      font-weight: 600;
      color: #0f172a;
    }

    .accordion-button {
      min-height: 42px;
      font-size: 14px;
    }

    .accordion-item .btn {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .dropdown-menu {
      border-radius: 10px;
    }

    .dropdown-item {
      font-weight: 500;
    }

    .dropdown-item:hover {
      background: #f1f5f9;
    }

    .status-fill-danger {
      background: #dc3545 !important;
      color: #fff !important;
    }

    .status-fill-primary {
      background: #0d6efd !important;
      color: #fff !important;
    }

    .status-fill-success {
      background: #198754 !important;
      color: #fff !important;
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
            <div class="col-12">
              <div class="card border-0 shadow-sm">
                <div class="card-body">

                  <!-- HEADER -->
                  <div class="d-flex justify-content-between align-items-center mb-3">

                    <!-- 🔹 KIRI -->
                    <div>
                      <h5 class="fw-bold mb-0">
                        💊 Permintaan Farmasi
                      </h5>
                    </div>

                    <!-- 🔹 KANAN -->
                    <div class="dropdown" style="min-width:180px;">

                      <!-- 🔹 BUTTON -->
                      <button class="btn w-100 text-start d-flex justify-content-between align-items-center status-fill-danger"
                        type="button"
                        id="dropdownStatus"
                        data-bs-toggle="dropdown">

                        <span id="selectedStatus">🔴 Waiting</span>
                        <i class="fas fa-chevron-down"></i>

                      </button>
                      <!-- 🔹 MENU -->
                      <ul class="dropdown-menu w-100 shadow">

                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 text-danger status-item" data-value="1" href="javasript:;">
                            🔴 Waiting
                          </a>
                        </li>

                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 text-primary status-item" data-value="2" href="javascript:;">
                            🔵 Persiapan
                          </a>
                        </li>

                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 text-success status-item" data-value="3" href="javascript:;">
                            🟢 Selesai
                          </a>
                        </li>

                      </ul>

                    </div>

                  </div>

                  <hr class="my-3">

                  <!-- INFO GRID -->
                  <div class="row g-3">

                    <div class="col-md-6">
                      <div class="info-item">
                        <div class="label text-muted">Nama Pasien</div>
                        <div class="value">
                          <?= $data['patient_name_pcare'] ?? '-' ?>
                        </div>
                      </div>
                    </div>


                    <div class="col-md-6">
                      <div class="info-item">
                        <div class="label text-muted">Nama Dokter</div>
                        <div class="value">
                          <?= $data['id_doctor'] ?? '-' ?>
                        </div>
                      </div>
                    </div>
                  </div>


                  <!-- CATATAN -->
                  <?php if (!empty($data['catatan_permintaan'])): ?>
                    <hr class="my-3">
                    <div>
                      <div class="label text-muted">Catatan</div>
                      <div class="value"><?= $data['catatan_permintaan'] ?></div>
                    </div>
                  <?php endif; ?>
                  <!-- 🔥 TOMBOL CALL -->
                  <div class="mt-4 text-end">
                    <button class="btn btn-light" onclick="window.history.back()">
                      <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button
                      class="btn btn-warning btn-call"
                      data-antrian="<?= $data['visit_antrian'] ?? '-' ?>"
                      data-nama="<?= $data['patient_name'] ?>"
                      data-poli="Farmasi"
                      data-visit="<?= $data['visit_ID'] ?>"
                      data-dokter="<?= $data['id_doctor'] ?>"
                      data-obat="<?= $data['tipe_obat'] ?? 'Obat siap diambil' ?>">

                      <i class="ti ti-volume"></i> Panggil Pasien
                    </button>
                  </div>

                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Permintaan Farmasi</h5>
                  </div>
                  <div class="accordion" id="accordionExample">
                    <!-- 🔥 AUTO RENDER BY JS -->
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
        <input type="hidden" name="id_pharmacy_details" id="id_pharmacy_details">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Nama Item <span class="text-danger">*</span> </label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select js-example-basic-item" required>
                <option value="">Select Option</option>
                <?php
                $getbarang = tampildata("SELECT * FROM ms_pharmacy WHERE pharmacy_status='1'");
                ?>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>" data-harga="<?= $barang['pharmacy_sale']; ?>"><?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Harga Dasar</label>
              <input type="number" id="harga" name="harga" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Qty</label>
              <input type="number" id="qty" name="qty" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Signa</label>
              <input type="text" id="signa" name="signa" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="5"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

</html>

<script>
  const visit = "<?= $_GET['no'] ?>";
  const rm = "<?= $_GET['rm'] ?>";

  // 🔥 LOAD SEMUA TIKET
  $(document).ready(function() {
    loadTiket();
  });

  // ============================
  // 🔥 LOAD TIKET
  // ============================
  function loadTiket() {

    fetch(`controller/farmasi/getTiketByVisit?no=${visit}`)
      .then(res => res.json())
      .then(res => {

        let html = '';


        // 🔥 SET STATUS GLOBAL (ambil dari tiket terakhir / prioritas selesai)
        let hasWaiting = false;
        let hasPersiapan = false;
        let hasSelesai = false;

        res.data.forEach(t => {
          if (t.status_permintaan == 1) hasWaiting = true;
          if (t.status_permintaan == 2) hasPersiapan = true;
          if (t.status_permintaan == 3) hasSelesai = true;
        });

        // 🔥 PRIORITAS LOGIC
        let finalStatus = 3; // default selesai

        if (hasPersiapan) {
          finalStatus = 2;
        } else if (hasWaiting) {
          finalStatus = 1;
        } else {
          finalStatus = 3;
        }

        // 🔥 APPLY
        setStatusUI(finalStatus);

        res.data.forEach((tiket, i) => {

          let collapseId = `collapse${tiket.id_permintaan_farmasi}`;

          // 🔥 FORMAT TANGGAL
          let tgl = new Date(tiket.created_at).toLocaleString('id-ID');

          html += `
        <div class="accordion-item mb-3 border rounded shadow-sm">

          <!-- 🔥 HEADER -->
          <div class="px-3 py-2 bg-light border-bottom">

            <div class="d-flex justify-content-between align-items-center">

              <div>
                <div class="fw-bold">
                  ${tgl} • ${tiket.tipe_obat ?? '-'}
                </div>

                <small class="text-muted">
                  Jumlah ${tiket.rck_jumlah ?? '-'} • Satuan ${tiket.rck_satuan ?? '-'} • Signa ${tiket.rck_signa ?? '-'} 
                </small>
              </div>

             <span class="badge 
              ${tiket.status_permintaan == 1 ? 'bg-danger' : 
                tiket.status_permintaan == 2 ? 'bg-primary' : 
                'bg-success'}">

              ${tiket.status_permintaan == 1 ? 'Waiting' : 
                tiket.status_permintaan == 2 ? 'Persiapan' : 
                'Selesai'}

            </span>

            </div>

            <!-- 🔥 RACIKAN INFO -->
            ${tiket.tipe_obat === 'racikan' ? `
              <div class="mt-2 small text-muted">
                <b>Racikan:</b> 
                ${tiket.rck_jumlah ?? '-'} ${tiket.rck_satuan ?? ''} 
                (${tiket.rck_signa ?? '-'})
              </div>
            ` : ''}

          </div>

          <!-- 🔥 BODY (SELALU SHOW) -->
          <div class="accordion-collapse show">
            <div class="accordion-body">

              <div class="d-flex justify-content-end mb-2 gap-2">

                <a href="module/print/struk_obat?no=${visit}&rm=${rm}&id=${tiket.id_permintaan_farmasi}" target="_blank">
                  <button class="btn btn-sm btn-outline-info">
                    <i class="fas fa-print"></i>
                  </button>
                </a>

                <a href="module/print/resep?no=${visit}&rm=${rm}&id=${tiket.id_permintaan_farmasi}" target="_blank">
                  <button class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-file-medical"></i>
                  </button>
                </a>

                <button class="btn btn-sm btn-primary btnTambah" data-id="${tiket.id_permintaan_farmasi}">
                  <i class="fas fa-plus"></i>
                </button>

              </div>

              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Signa</th>
                    <th>Catatan</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="detail-${tiket.id_permintaan_farmasi}">
                  <tr><td colspan="6" class="text-center">Loading...</td></tr>
                </tbody>
              </table>

            </div>
          </div>

        </div>
        `;
        });

        $('#accordionExample').html(html);

        loadAllDetail(res.data);

      });
  }

  // ============================
  // 🔥 LOAD DETAIL PER TIKET
  // ============================
  function loadAllDetail(tikets) {

    tikets.forEach(t => {

      fetch(`controller/visit/permintaanFarmasiDetails?no=${t.id_permintaan_farmasi}`)
        .then(res => res.json())
        .then(res => {

          let html = '';

          res.data.forEach(row => {

            html += `
          <tr>
            <td>${row.pharmacy_name_generic ?? ''}</td>
            <td>${row.qty}</td>
            <td>${row.signa}</td>
            <td>${row.catatan_permintaan ?? '-'}</td>
            <td class='col-1'>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-warning edit-btn" data-id="${row.id_pharmacy_details}">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger delete-btn" data-id="${row.id_pharmacy_details}">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          `;
          });

          $(`#detail-${t.id_permintaan_farmasi}`).html(html);

        });

    });

  }

  // ============================
  // 🔥 TAMBAH (MULTI TIKET)
  // ============================
  $(document).on('click', '.btnTambah', function() {

    let id = $(this).data('id');

    $('#programForm')[0].reset();
    $('#id_pharmacy_details').val('');
    $('#id_visit').val(id); // 🔥 penting

    $('#programModal .modal-title').text('Tambah Data');
    $('#programModal').modal('show');

  });
</script>
<script>
  $(document).on('click', '.btn-call', function() {

    const noAntrian = $(this).data('antrian');
    const nama = $(this).data('nama');
    const poli = $(this).data('poli');
    const visit = $(this).data('visit');
    const dokter = $(this).data('dokter');
    const obat = $(this).data('obat');

    callPatient(noAntrian, nama, poli, visit, dokter, obat);

    // 🔥 disable biar gak double klik
    $(this).prop('disabled', true);
  });

  function callPatient(noAntrian, namaPasien, poli, visitID, id_doctor, obat) {

    /* =========================
       SUARA SAJA (NO API CALL)
    ========================= */
    if ('speechSynthesis' in window) {

      speechSynthesis.cancel();

      const text = `
        pasien ${namaPasien}, dipersilahkan untuk ambil obat
      `;

      const utterance = new SpeechSynthesisUtterance(text);

      utterance.lang = 'id-ID';
      utterance.rate = 0.9;
      utterance.pitch = 1;
      utterance.volume = 1;

      // 🔥 ambil voice Indonesia kalau ada
      const voices = speechSynthesis.getVoices();
      const indo = voices.find(v => v.lang === 'id-ID');
      if (indo) utterance.voice = indo;

      speechSynthesis.speak(utterance);
    }

    // ❌ TIDAK ADA FETCH / UPDATE STATUS
  }
</script>
<script>
  let selectedValue = '0';

  $(document).on('click', '.status-item', function(e) {
    e.preventDefault();

    let text = $(this).text().trim();
    let value = $(this).data('value');

    selectedValue = value;

    let btn = $('#dropdownStatus');

    btn.removeClass('status-fill-danger status-fill-primary status-fill-success');

    if (value == '1') {
      btn.addClass('status-fill-danger');
    } else if (value == '2') {
      btn.addClass('status-fill-primary');
    } else if (value == '3') {
      btn.addClass('status-fill-success');
    }

    $('#selectedStatus').text(text);

    // 🔥 HIT API UPDATE STATUS
    fetch("controller/farmasi/approveTiketOrder.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          visit: "<?= $_GET['no'] ?>",
          status: value
        })
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          console.log('Status updated all tiket');
          loadTiket();
        }
      });

  });
</script>

<script>
  function setStatusUI(status) {

    let btn = $('#dropdownStatus');

    btn.removeClass('status-fill-danger status-fill-primary status-fill-success');

    let label = '';

    if (status == 1) {
      btn.addClass('status-fill-danger');
      label = '🔴 Waiting';
    } else if (status == 2) {
      btn.addClass('status-fill-primary');
      label = '🔵 Persiapan';
    } else if (status == 3) {
      btn.addClass('status-fill-success');
      label = '🟢 Selesai';
    }

    $('#selectedStatus').text(label);
  }
</script>