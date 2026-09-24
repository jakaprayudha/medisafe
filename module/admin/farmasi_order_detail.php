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
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient LEFT JOIN permintaan_pharmacy ON permintaan_pharmacy.id_visit = pasien_visit.visit_ID LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider  WHERE pasien_visit.visit_ID='$no'");
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
  <link
    href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
    rel="stylesheet" />

  <style>
    .select2-container {
      width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
      height: 38px !important;
      border: 1px solid #dee2e6 !important;
      border-radius: 6px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 36px !important;
      padding-left: 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px !important;
    }
  </style>
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

                    <!-- Nama Pasien -->
                    <div class="col-md-6">
                      <div class="info-card">
                        <div class="icon bg-primary-subtle text-primary">
                          <i class="bi bi-person"></i>
                        </div>
                        <div class="content">
                          <div class="label">Nama Pasien</div>
                          <div class="value">
                            <?= $data['patient_name_pcare'] ?? '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Nomor RM -->
                    <div class="col-md-3">
                      <div class="info-card">
                        <div class="icon bg-success-subtle text-success">
                          <i class="bi bi-folder2-open"></i>
                        </div>
                        <div class="content">
                          <div class="label">Nomor RM</div>
                          <div class="value">
                            <?= $data['nomor_rm'] ?? '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Gender -->
                    <div class="col-md-3">
                      <div class="info-card">
                        <div class="icon bg-secondary-subtle text-secondary">
                          <i class="bi bi-gender-ambiguous"></i>
                        </div>
                        <div class="content">
                          <div class="label">Gender</div>
                          <div class="value">
                            <?= $data['patient_gender'] ?? '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>


                    <!-- Nama Dokter -->
                    <div class="col-md-6">
                      <div class="info-card">
                        <div class="icon bg-info-subtle text-info">
                          <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="content">
                          <div class="label">Nama Dokter</div>
                          <div class="value">
                            <?= $data['id_doctor'] ?? '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Provider -->
                    <div class="col-md-3">
                      <div class="info-card">
                        <div class="icon bg-warning-subtle text-warning">
                          <i class="bi bi-hospital"></i>
                        </div>
                        <div class="content">
                          <div class="label">Provider</div>
                          <div class="value">
                            <?= $data['provider_name'] ?? '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="col-md-3">
                      <div class="info-card">
                        <div class="icon bg-danger-subtle text-danger">
                          <i class="bi bi-calendar-date"></i>
                        </div>
                        <div class="content">
                          <div class="label">Tanggal Lahir</div>
                          <div class="value">
                            <?= !empty($data['patient_datebirth']) ? date('d M Y', strtotime($data['patient_datebirth'])) : '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>

                  <style>
                    .info-card {
                      display: flex;
                      align-items: center;
                      gap: 14px;
                      background: #fff;
                      border-radius: 16px;
                      padding: 16px;
                      border: 1px solid #e9ecef;
                      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
                      transition: all .2s ease;
                      height: 100%;
                    }

                    .info-card:hover {
                      transform: translateY(-2px);
                      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
                    }

                    .info-card .icon {
                      width: 52px;
                      height: 52px;
                      border-radius: 14px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      font-size: 22px;
                      flex-shrink: 0;
                    }

                    .info-card .content {
                      flex: 1;
                      min-width: 0;
                    }

                    .info-card .label {
                      font-size: 12px;
                      color: #6c757d;
                      margin-bottom: 4px;
                      text-transform: uppercase;
                      letter-spacing: .5px;
                    }

                    .info-card .value {
                      font-size: 15px;
                      font-weight: 600;
                      color: #212529;
                      word-break: break-word;
                    }
                  </style>
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
                      data-poliasal="<?= $data['id_poli'] ?>"
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
  <script>
    $(document).ready(function() {

      $('#id_pharmacy').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Cari nama obat...',
        allowClear: true,
        dropdownParent: $('#programModal')
      });

    });
  </script>
  <script
    src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js">
  </script>

  <script>
    $(document).ready(function() {

      $('#id_pharmacy').select2({

        width: '100%',

        placeholder: 'Cari nama obat...',

        allowClear: true,

        dropdownParent: $('#programModal')

      });

    });
  </script>
</body>
<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- ID DETAIL ITEM -->
        <input type="hidden"
          name="id_pharmacy_details"
          id="id_pharmacy_details"
          value="">

        <!-- ID TIKET PERMINTAAN FARMASI -->
        <input type="hidden"
          name="id_permintaan_farmasi"
          id="id_permintaan_farmasi"
          value="">

        <!-- ID VISIT - hanya untuk konteks halaman -->
        <input type="hidden"
          name="id_visit"
          id="id_visit"
          value="<?= htmlspecialchars($_GET['no'] ?? '') ?>">

        <div class="row">

          <!-- NAMA ITEM -->
          <div class="col-12">
            <div class="mb-3">

              <label for="id_pharmacy" class="form-label">
                Nama Item
                <span class="text-danger">*</span>
              </label>

              <select
                name="id_pharmacy"
                id="id_pharmacy"
                class="form-select js-example-basic-item"
                required>
                <option value="">Select Option</option>

                <?php
                $id_customer = $_SESSION['id_customer'];

                $getbarang = tampildata("
                    SELECT *
                    FROM ms_pharmacy
                    WHERE pharmacy_status = '1'
                    AND id_customer = '$id_customer'
                    ORDER BY pharmacy_name_generic ASC
                ");
                ?>

                <?php foreach ($getbarang as $barang): ?>

                  <option
                    value="<?= htmlspecialchars($barang['id_pharmacy']); ?>"
                    data-harga="<?= htmlspecialchars($barang['pharmacy_sale']); ?>">
                    <?= htmlspecialchars($barang['pharmacy_name_generic']); ?>
                    /
                    <?= htmlspecialchars($barang['pharmacy_name_trade']); ?>
                  </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>


          <!-- HARGA -->
          <div class="col-6">
            <div class="mb-3">

              <label for="harga" class="form-label required">
                Harga Dasar
              </label>

              <input
                type="number"
                value="0"
                id="harga"
                name="harga"
                class="form-control"
                min="0"
                required>

            </div>
          </div>


          <!-- QTY -->
          <div class="col-6">
            <div class="mb-3">

              <label for="qty" class="form-label required">
                Qty
              </label>

              <input
                type="number"
                value="1"
                id="qty"
                name="qty"
                class="form-control"
                min="1"
                required>

            </div>
          </div>


          <!-- SIGNA -->
          <div class="col-12">
            <div class="mb-3">

              <label for="signa" class="form-label required">
                Signa
              </label>

              <input
                type="text"
                id="signa"
                name="signa"
                class="form-control"
                placeholder="Contoh: 3 x 1"
                required>

            </div>
          </div>


          <!-- CATATAN -->
          <div class="col-12">
            <div class="mb-3">

              <label for="catatan" class="form-label">
                Catatan
              </label>

              <textarea
                name="catatan"
                id="catatan"
                class="form-control"
                rows="5"></textarea>

            </div>
          </div>

        </div>
      </div>


      <div class="modal-footer">

        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal">
          Batal
        </button>

        <button
          type="submit"
          class="btn btn-primary">
          Simpan
        </button>

      </div>

    </form>
  </div>
</div>

</html>
<script src="controller/socket/socket.js"></script>
<script>
  const visit = "<?= $_GET['no'] ?>";
  const rm = "<?= $_GET['rm'] ?>";


  // ============================================================
  // LOAD SEMUA TIKET
  // ============================================================

  $(document).ready(function() {

    loadTiket();

  });


  // ============================================================
  // LOAD TIKET
  // ============================================================

  function loadTiket() {

    fetch(`controller/farmasi/getTiketByVisit?no=${visit}`)

      .then(res => res.json())

      .then(res => {

        let html = '';


        // ======================================================
        // SET STATUS GLOBAL
        // ======================================================

        let hasWaiting = false;
        let hasPersiapan = false;
        let hasSelesai = false;


        res.data.forEach(t => {

          if (t.status_permintaan == 1) {
            hasWaiting = true;
          }

          if (t.status_permintaan == 2) {
            hasPersiapan = true;
          }

          if (t.status_permintaan == 3) {
            hasSelesai = true;
          }

        });


        // ======================================================
        // PRIORITAS STATUS
        // ======================================================

        let finalStatus = 3;

        if (hasPersiapan) {

          finalStatus = 2;

        } else if (hasWaiting) {

          finalStatus = 1;

        } else {

          finalStatus = 3;

        }


        // ======================================================
        // APPLY STATUS
        // ======================================================

        setStatusUI(finalStatus);


        // ======================================================
        // LOOP TIKET
        // ======================================================

        res.data.forEach((tiket, i) => {

          /*
           * ID INI ADALAH ID TIKET PERMINTAAN FARMASI
           *
           * contoh:
           * id_permintaan_farmasi = 15
           *
           * Semua detail item yang ditambahkan
           * harus menggunakan ID tiket ini.
           */

          const idPermintaanFarmasi = tiket.id_permintaan_farmasi;

          const collapseId =
            `collapse${idPermintaanFarmasi}`;


          // ====================================================
          // VALIDASI ID TIKET
          // ====================================================

          if (
            idPermintaanFarmasi === null ||
            idPermintaanFarmasi === undefined ||
            idPermintaanFarmasi === ''
          ) {

            console.warn(
              'ID permintaan farmasi tidak ditemukan:',
              tiket
            );

          }


          // ====================================================
          // FORMAT TANGGAL
          // ====================================================

          let tgl = new Date(
            tiket.created_at
          ).toLocaleString('id-ID');


          // ====================================================
          // HTML TIKET
          // ====================================================

          html += `

            <div class="accordion-item mb-3 border rounded shadow-sm">

              <!-- HEADER -->

              <div class="px-3 py-2 bg-light border-bottom">

                <div class="d-flex justify-content-between align-items-center">

                  <div>

                    <div class="fw-bold">

                      ${tgl}
                      •
                      ${tiket.tipe_obat ?? '-'}

                    </div>

                    <small class="text-muted">

                      Jumlah ${tiket.rck_jumlah ?? '-'}
                      •
                      Satuan ${tiket.rck_satuan ?? '-'}
                      •
                      Signa ${tiket.rck_signa ?? '-'}

                      <br>

                      <strong class="text-danger">

                        Catatan :
                        ${tiket.catatan_permintaan ?? '-'}

                      </strong>

                    </small>

                  </div>


                  <!-- STATUS -->

                  <span class="badge

                    ${
                      tiket.status_permintaan == 1
                        ? 'bg-danger'
                        : tiket.status_permintaan == 2
                          ? 'bg-primary'
                          : 'bg-success'
                    }

                  ">

                    ${
                      tiket.status_permintaan == 1
                        ? 'Waiting'
                        : tiket.status_permintaan == 2
                          ? 'Persiapan'
                          : 'Selesai'
                    }

                  </span>

                </div>


                <!-- RACIKAN INFO -->

                ${
                  tiket.tipe_obat === 'racikan'
                    ? `

                      <div class="mt-2 small text-muted">

                        <b>Racikan:</b>

                        ${tiket.rck_jumlah ?? '-'}
                        ${tiket.rck_satuan ?? ''}

                        (${tiket.rck_signa ?? '-'})

                      </div>

                    `
                    : ''
                }

              </div>


              <!-- BODY -->

              <div class="accordion-collapse show">

                <div class="accordion-body">


                  <!-- ACTION -->

                  <div class="d-flex justify-content-end mb-2 gap-2">


                    <!-- PRINT STRUK -->

                    <a
                      href="module/print/struk_obat?no=${visit}&rm=${rm}&id=${idPermintaanFarmasi}"
                      target="_blank"
                    >

                      <button
                        type="button"
                        class="btn btn-sm btn-outline-info"
                      >

                        <i class="fas fa-print"></i>

                      </button>

                    </a>


                    <!-- PRINT RESEP -->

                    <a
                      href="module/print/resep?no=${visit}&rm=${rm}&id=${idPermintaanFarmasi}"
                      target="_blank"
                    >

                      <button
                        type="button"
                        class="btn btn-sm btn-outline-warning"
                      >

                        <i class="fas fa-file-medical"></i>

                      </button>

                    </a>


                    <!-- TAMBAH ITEM -->

                    <button
                      type="button"
                      class="btn btn-sm btn-primary btnTambah"
                      data-id="${idPermintaanFarmasi}"
                    >

                      <i class="fas fa-plus"></i>

                    </button>

                  </div>


                  <!-- TABLE DETAIL -->

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


                    <tbody
                      id="detail-${idPermintaanFarmasi}"
                    >

                      <tr>

                        <td
                          colspan="5"
                          class="text-center"
                        >

                          Loading...

                        </td>

                      </tr>

                    </tbody>

                  </table>


                </div>

              </div>

            </div>

          `;

        });


        // ======================================================
        // RENDER TIKET
        // ======================================================

        $('#accordionExample').html(html);


        // ======================================================
        // LOAD DETAIL
        // ======================================================

        loadAllDetail(res.data);

      })

      .catch(error => {

        console.error(
          'Gagal load tiket:',
          error
        );

      });

  }


  // ============================================================
  // LOAD DETAIL PER TIKET
  // ============================================================

  function loadAllDetail(tikets) {

    tikets.forEach(t => {

      const idPermintaanFarmasi =
        t.id_permintaan_farmasi;


      if (
        !idPermintaanFarmasi ||
        idPermintaanFarmasi === 'null'
      ) {

        console.warn(
          'Detail tidak dapat dimuat karena ID tiket kosong:',
          t
        );

        return;

      }


      fetch(
          `controller/visit/permintaanFarmasiAddOns?no=${idPermintaanFarmasi}`
        )

        .then(res => res.json())

        .then(res => {

          let html = '';


          if (
            !res.data ||
            res.data.length === 0
          ) {

            html = `

              <tr>

                <td
                  colspan="5"
                  class="text-center text-muted"
                >

                  Belum ada item

                </td>

              </tr>

            `;

          } else {

            res.data.forEach(row => {

              html += `

                <tr>

                  <td>

                    ${
                      row.pharmacy_name_generic
                        ? row.pharmacy_name_generic
                        : (row.pharmacy_name_trade ?? '')
                    }

                  </td>


                  <td>

                    ${row.qty}

                  </td>


                  <td>

                    ${row.signa}

                  </td>


                  <td>

                    ${row.catatan ?? '-'}

                  </td>


                  <td class="col-1">

                    <div class="btn-group btn-group-sm">


                      <!-- EDIT -->

                      <button
                        type="button"
                        class="btn btn-warning edit-btn"
                        data-id="${row.id_pharmacy_details}"
                      >

                        <i class="fas fa-edit"></i>

                      </button>


                      <!-- DELETE -->

                      <button
                        type="button"
                        class="btn btn-danger delete-btn"
                        data-id="${row.id_pharmacy_details}"
                      >

                        <i class="fas fa-trash"></i>

                      </button>


                    </div>

                  </td>

                </tr>

              `;

            });

          }


          $(
            `#detail-${idPermintaanFarmasi}`
          ).html(html);

        })

        .catch(error => {

          console.error(
            'Gagal load detail:',
            error
          );

          $(
            `#detail-${idPermintaanFarmasi}`
          ).html(`

            <tr>

              <td
                colspan="5"
                class="text-center text-danger"
              >

                Gagal memuat detail

              </td>

            </tr>

          `);

        });

    });

  }


  // ============================================================
  // TAMBAH ITEM
  // ============================================================

  $(document).on(
    'click',
    '.btnTambah',
    function() {

      /*
       * data-id berasal dari:
       *
       * data-id="${tiket.id_permintaan_farmasi}"
       *
       * Jadi ID ini adalah ID TIKET.
       */

      const idPermintaanFarmasi =
        $(this).attr('data-id');


      // ======================================================
      // VALIDASI ID TIKET
      // ======================================================

      if (
        !idPermintaanFarmasi ||
        idPermintaanFarmasi === 'null' ||
        idPermintaanFarmasi === 'undefined'
      ) {

        Swal.fire({
          icon: 'error',
          title: 'ID Tiket Tidak Ditemukan',
          text: 'ID permintaan farmasi tidak tersedia.'
        });

        return;

      }


      // ======================================================
      // RESET FORM
      // ======================================================

      $('#programForm')[0].reset();


      // ======================================================
      // RESET ID DETAIL
      // ======================================================

      $('#id_pharmacy_details').val('');


      // ======================================================
      // 🔥 SIMPAN ID TIKET
      // ======================================================

      $('#id_permintaan_farmasi')
        .val(idPermintaanFarmasi);


      /*
       * id_visit TETAP diisi dengan visit halaman.
       *
       * JANGAN diisi dengan id_permintaan_farmasi.
       */

      $('#id_visit').val(visit);


      // ======================================================
      // DEBUG
      // ======================================================

      console.log(
        'ID Visit:',
        $('#id_visit').val()
      );

      console.log(
        'ID Permintaan Farmasi / Tiket:',
        $('#id_permintaan_farmasi').val()
      );


      // ======================================================
      // RESET SELECT2
      // ======================================================

      $('#id_pharmacy')
        .val(null)
        .trigger('change');


      // ======================================================
      // RESET FIELD
      // ======================================================

      $('#harga').val(0);

      $('#qty').val(1);

      $('#signa').val('');

      $('#catatan').val('');


      // ======================================================
      // TITLE
      // ======================================================

      $('#programModal .modal-title')
        .text(
          'Tambah Item - Tiket #' +
          idPermintaanFarmasi
        );


      // ======================================================
      // SHOW MODAL
      // ======================================================

      $('#programModal').modal('show');

    }
  );


  // ============================================================
  // CHANGE PHARMACY
  // ============================================================

  $('#id_pharmacy').on(
    'change',
    function() {

      const selected =
        $(this).find('option:selected');


      const harga =
        selected.data('harga');


      if (
        harga !== undefined &&
        harga !== ''
      ) {

        $('#harga').val(harga);

      } else {

        $('#harga').val(0);

      }

    }
  );
</script>
<script>
  // ============================================================
  // SUBMIT FORM FARMASI
  // CREATE = POST
  // EDIT   = PUT
  // ============================================================

  $('#programForm').on('submit', function(e) {

    e.preventDefault();

    const form = this;

    // ==========================================================
    // AMBIL ID
    // ==========================================================

    const idDetail =
      $('#id_pharmacy_details').val().trim();

    const idPermintaanFarmasi =
      $('#id_permintaan_farmasi').val().trim();

    const idPharmacy =
      $('#id_pharmacy').val();

    const qty =
      $('#qty').val();

    const signa =
      $('#signa').val().trim();

    // ==========================================================
    // VALIDASI ID TIKET
    // ==========================================================

    if (
      !idPermintaanFarmasi ||
      !/^[0-9]+$/.test(idPermintaanFarmasi)
    ) {

      Swal.fire({
        icon: 'error',
        title: 'ID Tiket Tidak Valid',
        text: 'ID permintaan farmasi belum tersedia.'
      });

      console.error(
        'ID Permintaan Farmasi invalid:',
        idPermintaanFarmasi
      );

      return;
    }

    // ==========================================================
    // VALIDASI PHARMACY
    // ==========================================================

    if (!idPharmacy) {

      Swal.fire({
        icon: 'error',
        title: 'Item Belum Dipilih',
        text: 'Silakan pilih item farmasi terlebih dahulu.'
      });

      return;
    }

    // ==========================================================
    // VALIDASI QTY
    // ==========================================================

    if (
      !qty ||
      parseInt(qty) <= 0
    ) {

      Swal.fire({
        icon: 'error',
        title: 'Qty Tidak Valid',
        text: 'Qty harus lebih besar dari 0.'
      });

      return;
    }

    // ==========================================================
    // VALIDASI SIGNA
    // ==========================================================

    if (!signa) {

      Swal.fire({
        icon: 'error',
        title: 'Signa Belum Diisi',
        text: 'Silakan isi signa terlebih dahulu.'
      });

      return;
    }

    // ==========================================================
    // TENTUKAN MODE
    // ==========================================================

    const isEdit =
      idDetail !== '' &&
      /^[0-9]+$/.test(idDetail);

    const method =
      isEdit ? 'PUT' : 'POST';

    // ==========================================================
    // FORM DATA
    // ==========================================================

    const formData =
      new FormData(form);

    // Pastikan ID tiket selalu ikut
    formData.set(
      'id_permintaan_farmasi',
      idPermintaanFarmasi
    );

    // Pastikan ID detail ikut saat EDIT
    if (isEdit) {

      formData.set(
        'id_pharmacy_details',
        idDetail
      );

    } else {

      formData.delete(
        'id_pharmacy_details'
      );

    }

    // ==========================================================
    // DEBUG
    // ==========================================================

    console.log('======================================');
    console.log(
      'MODE:',
      isEdit ? 'EDIT / UPDATE' : 'TAMBAH / CREATE'
    );
    console.log(
      'METHOD:',
      method
    );
    console.log(
      'ID DETAIL:',
      idDetail
    );
    console.log(
      'ID TIKET:',
      idPermintaanFarmasi
    );
    console.log(
      'ID PHARMACY:',
      idPharmacy
    );
    console.log(
      'QTY:',
      qty
    );
    console.log(
      'SIGNA:',
      signa
    );
    console.log('======================================');

    for (
      const [key, value] of formData.entries()
    ) {

      console.log(
        key,
        ':',
        value
      );

    }

    // ==========================================================
    // BUTTON LOADING
    // ==========================================================

    const btnSubmit =
      $(form).find('button[type="submit"]');

    const originalText =
      btnSubmit.html();

    btnSubmit
      .prop('disabled', true)
      .html(`
        <span
          class="spinner-border spinner-border-sm me-1"
          role="status"
          aria-hidden="true">
        </span>
        ${isEdit ? 'Memperbarui...' : 'Menyimpan...'}
      `);

    // ==========================================================
    // PREPARE REQUEST
    // ==========================================================

    let requestBody;
    let headers = {};

    if (isEdit) {

      /*
       * ========================================================
       * PUT
       * ========================================================
       *
       * Controller PHP menggunakan:
       *
       * parse_str(
       *     file_get_contents("php://input"),
       *     $_PUT
       * );
       *
       * Jadi PUT harus dikirim sebagai
       * application/x-www-form-urlencoded.
       */

      requestBody =
        new URLSearchParams();

      for (
        const [key, value] of formData.entries()
      ) {

        requestBody.append(
          key,
          value
        );

      }

      headers = {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      };

    } else {

      /*
       * ========================================================
       * POST
       * ========================================================
       *
       * CREATE tetap menggunakan FormData.
       */

      requestBody =
        formData;

    }

    // ==========================================================
    // SEND
    // ==========================================================

    fetch(
        'controller/visit/permintaanFarmasiDetails.php', {
          method: method,
          headers: headers,
          body: requestBody
        }
      )

      // ========================================================
      // RESPONSE HTTP
      // ========================================================

      .then(res => {

        if (!res.ok) {

          throw new Error(
            `HTTP Error ${res.status}`
          );

        }

        return res.json();

      })

      // ========================================================
      // RESPONSE JSON
      // ========================================================

      .then(res => {

        console.log(
          'Response Controller:',
          res
        );

        // ======================================================
        // SUCCESS
        // ======================================================

        if (
          res.status === 'success'
        ) {

          $('#programModal')
            .modal('hide');

          // ====================================================
          // SWEET ALERT
          // ====================================================

          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message ||
              (
                isEdit ?
                'Item farmasi berhasil diperbarui.' :
                'Item farmasi berhasil ditambahkan.'
              ),
            timer: 1500,
            showConfirmButton: false,
            timerProgressBar: true
          });

          // ====================================================
          // RELOAD DETAIL TIKET
          // ====================================================

          loadAllDetail([{
            id_permintaan_farmasi: res.id_permintaan_farmasi ||
              idPermintaanFarmasi
          }]);

          // ====================================================
          // RESET FORM
          // ====================================================

          $('#id_pharmacy_details')
            .val('');

          $('#id_permintaan_farmasi')
            .val('');

          $('#id_visit')
            .val(visit);

          $('#id_pharmacy')
            .val(null)
            .trigger('change');

          $('#harga')
            .val(0);

          $('#qty')
            .val(1);

          $('#signa')
            .val('');

          $('#catatan')
            .val('');

          // Reset title
          $('#programModal .modal-title')
            .text('');

        } else {

          // ====================================================
          // ERROR DARI CONTROLLER
          // ====================================================

          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: res.message ||
              'Gagal menyimpan data.'
          });

        }

      })

      // ========================================================
      // CATCH
      // ========================================================

      .catch(error => {

        console.error(
          'Error submit farmasi:',
          error
        );

        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: error.message ||
            'Terjadi kesalahan saat menyimpan data.'
        });

      })

      // ========================================================
      // FINALLY
      // ========================================================

      .finally(() => {

        btnSubmit
          .prop('disabled', false)
          .html(originalText);

      });

  });
</script>
<script>
  $(document).on('click', '.btn-call', function() {
    const noAntrian = $(this).data('antrian');
    const nama = $(this).data('nama');
    const poli = $(this).data('poli');
    const poliasal = $(this).data('poliasal');
    const visit = $(this).data('visit');
    const dokter = $(this).data('dokter');
    const obat = $(this).data('obat');
    const text = `pasien ${nama}, dipersilahkan untuk mengambil obat`;
    const requestId = crypto.randomUUID();
    sessionStorage.setItem('requestId', requestId);
    $.ajax({
      url: 'controller/admisi/soundFarmasi.php',
      type: 'POST',
      data: {
        text: text,
        requestIdFarmasi: requestId,
        nama_pasien: nama,
        asalpoli: poliasal,
        visit_id: visit
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

  // function callPatient(noAntrian, namaPasien, poli, visitID, id_doctor, obat) {

  //   /* =========================
  //      SUARA SAJA (NO API CALL)
  //   ========================= */
  //   if ('speechSynthesis' in window) {

  //     speechSynthesis.cancel();

  //     const text = `
  //       pasien ${namaPasien}, dipersilahkan untuk mengambil obat`;

  //     const utterance = new SpeechSynthesisUtterance(text);

  //     utterance.lang = 'id-ID';
  //     utterance.rate = 0.9;
  //     utterance.pitch = 1;
  //     utterance.volume = 1;

  //     // 🔥 ambil voice Indonesia kalau ada
  //     const voices = speechSynthesis.getVoices();
  //     const indo = voices.find(v => v.lang === 'id-ID');
  //     if (indo) utterance.voice = indo;

  //     speechSynthesis.speak(utterance);
  //   }

  //   // ❌ TIDAK ADA FETCH / UPDATE STATUS
  // }
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

  $(document).on('click', '.delete-btn', function() {
    const id = $(this).data('id');

    if (!confirm('Yakin mau hapus data ini?')) return;

    fetch(`controller/visit/permintaanFarmasiAddOns?id=${id}`, {
        method: 'DELETE'
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          alert('Data berhasil dihapus');
          loadTiket(); // reload
        }
      });
  });
</script>

<script>
  // ============================================================
  // EDIT ITEM FARMASI
  // ============================================================

  $(document).on(
    'click',
    '.edit-btn',
    function() {

      // ========================================================
      // AMBIL ID DETAIL
      // ========================================================

      const id =
        $(this).data('id');


      // ========================================================
      // VALIDASI ID DETAIL
      // ========================================================

      if (
        !id ||
        id === 'null' ||
        id === 'undefined'
      ) {

        Swal.fire({
          icon: 'error',
          title: 'ID Detail Tidak Ditemukan',
          text: 'ID detail farmasi tidak tersedia.'
        });

        return;
      }


      // ========================================================
      // LOADING
      // ========================================================

      console.log(
        'Edit detail pharmacy:',
        id
      );


      // ========================================================
      // GET DETAIL
      // ========================================================

      fetch(
          `controller/visit/permintaanFarmasiAddOns?id=${id}`
        )

        .then(res => {

          if (!res.ok) {

            throw new Error(
              `HTTP Error ${res.status}`
            );

          }

          return res.json();

        })


        // ======================================================
        // RESPONSE
        // ======================================================

        .then(res => {

          console.log(
            'Response detail edit:',
            res
          );


          // ====================================================
          // VALIDASI RESPONSE
          // ====================================================

          if (
            res.status !== 'success' ||
            !res.data
          ) {

            Swal.fire({
              icon: 'error',
              title: 'Data Tidak Ditemukan',
              text: res.message ||
                'Data detail farmasi tidak ditemukan.'
            });

            return;
          }


          const d =
            res.data;


          // ====================================================
          // DEBUG DATA
          // ====================================================

          console.log(
            '======================================'
          );

          console.log(
            'ID DETAIL:',
            d.id_pharmacy_details
          );

          console.log(
            'ID TIKET:',
            d.id_permintaan_farmasi
          );

          console.log(
            'ID PHARMACY:',
            d.id_pharmacy
          );

          console.log(
            'QTY:',
            d.qty
          );

          console.log(
            'SIGNA:',
            d.signa
          );

          console.log(
            'CATATAN:',
            d.catatan
          );

          console.log(
            '======================================'
          );


          // ====================================================
          // VALIDASI ID TIKET
          // ====================================================

          if (
            !d.id_permintaan_farmasi ||
            !/^[0-9]+$/.test(
              String(d.id_permintaan_farmasi)
            )
          ) {

            Swal.fire({
              icon: 'error',
              title: 'ID Tiket Tidak Ditemukan',
              text: 'ID permintaan farmasi pada detail ini tidak tersedia.'
            });

            console.error(
              'id_permintaan_farmasi kosong:',
              d
            );

            return;
          }


          // ====================================================
          // RESET FORM
          // ====================================================

          $('#programForm')[0].reset();


          // ====================================================
          // ID DETAIL
          // ====================================================

          $('#id_pharmacy_details')
            .val(
              d.id_pharmacy_details
            );


          // ====================================================
          // 🔥 ID TIKET PERMINTAAN FARMASI
          // ====================================================

          $('#id_permintaan_farmasi')
            .val(
              d.id_permintaan_farmasi
            );


          // ====================================================
          // ID VISIT
          // ====================================================

          $('#id_visit')
            .val(visit);


          // ====================================================
          // PHARMACY
          // ====================================================

          $('#id_pharmacy')
            .val(d.id_pharmacy)
            .trigger('change');


          // ====================================================
          // HARGA
          // ====================================================

          if (
            d.harga !== undefined &&
            d.harga !== null
          ) {

            $('#harga')
              .val(d.harga);

          } else {

            $('#harga')
              .val(0);

          }


          // ====================================================
          // QTY
          // ====================================================

          $('#qty')
            .val(
              d.qty || 1
            );


          // ====================================================
          // SIGNA
          // ====================================================

          $('#signa')
            .val(
              d.signa || ''
            );


          // ====================================================
          // CATATAN
          // ====================================================

          $('#catatan')
            .val(
              d.catatan || ''
            );


          // ====================================================
          // TITLE
          // ====================================================

          $('#programModal .modal-title')
            .text(
              'Edit Item - Tiket #' +
              d.id_permintaan_farmasi
            );


          // ====================================================
          // DEBUG FINAL
          // ====================================================

          console.log(
            'ID tiket yang disimpan ke form:',
            $('#id_permintaan_farmasi').val()
          );


          // ====================================================
          // SHOW MODAL
          // ====================================================

          $('#programModal')
            .modal('show');

        })


        // ======================================================
        // ERROR
        // ======================================================

        .catch(error => {

          console.error(
            'Error mengambil data edit:',
            error
          );


          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message ||
              'Gagal mengambil data farmasi.'
          });

        });

    }
  );
</script>