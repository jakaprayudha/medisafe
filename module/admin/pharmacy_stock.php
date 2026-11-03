<?php

$title = 'Farmasi Stock';

require '../../controller/view.php';

$no = $_GET['no'] ?? '';

$id_customer = $_SESSION['id_customer'] ?? '';

?>

<!doctype html>

<html lang="en">

<head>

  <base href="../../">

  <?php
  require '../../assets/template/head.php';
  ?>

  <style>
    /* =====================================================
       STOCK FARMASI
    ===================================================== */

    .stock-card {
      border: 1px solid #e9ecef;
      border-radius: 16px;
      background: #ffffff;
      transition: all .2s ease;
    }

    .stock-card:hover {
      box-shadow: 0 8px 25px rgba(0, 0, 0, .06);
    }

    .stock-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
    }

    .stock-number {
      font-size: 28px;
      font-weight: 700;
      color: #102a43;
    }

    .stock-label {
      font-size: 13px;
      color: #6c757d;
    }

    .section-title {
      font-size: 18px;
      font-weight: 600;
      color: #102a43;
    }

    .table thead th {
      white-space: nowrap;
      font-size: 13px;
      font-weight: 600;
    }

    .table tbody td {
      vertical-align: middle;
      font-size: 14px;
    }

    .badge-stock {
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: 600;
    }

    .stock-info {
      background: #eef5ff;
    }

    .stock-info .stock-icon {
      background: #dbeafe;
      color: #2563eb;
    }

    .stock-in {
      background: #eefbf3;
    }

    .stock-in .stock-icon {
      background: #d9f7e5;
      color: #16a34a;
    }

    .stock-out {
      background: #fff5f5;
    }

    .stock-out .stock-icon {
      background: #ffe0e0;
      color: #dc2626;
    }

    .stock-total {
      background: #fff9eb;
    }

    .stock-total .stock-icon {
      background: #fff0bd;
      color: #d97706;
    }

    .form-section {
      background: #f8fafc;
      border: 1px solid #e9ecef;
      border-radius: 14px;
      padding: 20px;
    }

    .required {
      color: #dc3545;
    }
  </style>

</head>


<body>

  <!-- =====================================================
       BODY WRAPPER
  ===================================================== -->

  <div
    class="page-wrapper"
    id="main-wrapper"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed">


    <!-- =================================================
         SIDEBAR
    ================================================= -->

    <?php
    require 'sidebar.php';
    ?>


    <!-- =================================================
         MAIN WRAPPER
    ================================================= -->

    <div class="body-wrapper">


      <!-- =================================================
           NAVBAR
      ================================================= -->

      <?php
      require 'navbar.php';
      ?>


      <!-- =================================================
           CONTENT
      ================================================= -->

      <div class="body-wrapper-inner">

        <div class="container-fluid">


          <!-- =================================================
               HEADER
          ================================================= -->

          <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

              <h4 class="fw-semibold mb-1">
                Stock Farmasi
              </h4>

              <p class="text-muted mb-0">
                Kelola stock awal dan penerimaan barang farmasi
              </p>

            </div>


            <div class="d-flex gap-2">

              <a
                href="module/admin/pharmacy_details?no=<?= htmlspecialchars($no) ?>"
                class="btn btn-outline-info">

                <i class="fas fa-info-circle me-1"></i>

                Detail Farmasi

              </a>


              <button
                type="button"
                class="btn btn-primary"
                id="btnTambahPenerimaan">

                <i class="fas fa-plus me-1"></i>

                Tambah Penerimaan

              </button>

            </div>

          </div>


          <!-- =================================================
               INFORMASI FARMASI
          ================================================= -->

          <div class="card w-100 mb-4">

            <div class="card-body p-4">

              <div class="d-flex align-items-center">

                <div
                  class="stock-icon"
                  style="
                    background:#e8f1ff;
                    color:#2563eb;
                    margin-right:15px;
                  ">

                  <i class="fas fa-pills"></i>

                </div>


                <div>

                  <div class="text-muted small">
                    ID Farmasi
                  </div>

                  <h5
                    class="mb-0 fw-semibold"
                    id="pharmacyName">

                    Memuat...

                  </h5>

                  <small
                    class="text-muted"
                    id="pharmacyCode">
                  </small>

                </div>

              </div>

            </div>

          </div>


          <!-- =================================================
               SUMMARY STOCK
          ================================================= -->

          <div class="row g-3 mb-4">


            <!-- STOCK TERSEDIA -->

            <div class="col-xl-3 col-md-6">

              <div class="card stock-card stock-info h-100">

                <div class="card-body">

                  <div class="d-flex justify-content-between align-items-center">

                    <div>

                      <div class="stock-label">
                        Stock Tersedia
                      </div>

                      <div
                        class="stock-number"
                        id="totalStock">

                        0

                      </div>

                      <small
                        class="text-muted"
                        id="stockUnit">

                        Satuan

                      </small>

                    </div>


                    <div class="stock-icon">

                      <i class="fas fa-boxes-stacked"></i>

                    </div>

                  </div>

                </div>

              </div>

            </div>


            <!-- STOCK AWAL -->

            <div class="col-xl-3 col-md-6">

              <div class="card stock-card stock-total h-100">

                <div class="card-body">

                  <div class="d-flex justify-content-between align-items-center">

                    <div>

                      <div class="stock-label">
                        Stock Awal
                      </div>

                      <div
                        class="stock-number"
                        id="stockAwal">

                        0

                      </div>

                      <small class="text-muted">
                        Satuan
                      </small>

                    </div>


                    <div class="stock-icon">

                      <i class="fas fa-warehouse"></i>

                    </div>

                  </div>

                </div>

              </div>

            </div>


            <!-- TOTAL PENERIMAAN -->

            <div class="col-xl-3 col-md-6">

              <div class="card stock-card stock-in h-100">

                <div class="card-body">

                  <div class="d-flex justify-content-between align-items-center">

                    <div>

                      <div class="stock-label">
                        Total Penerimaan
                      </div>

                      <div
                        class="stock-number"
                        id="totalPenerimaan">

                        0

                      </div>

                      <small class="text-muted">
                        Satuan
                      </small>

                    </div>


                    <div class="stock-icon">

                      <i class="fas fa-arrow-down"></i>

                    </div>

                  </div>

                </div>

              </div>

            </div>


            <!-- TRANSAKSI -->

            <div class="col-xl-3 col-md-6">

              <div class="card stock-card h-100">

                <div class="card-body">

                  <div class="d-flex justify-content-between align-items-center">

                    <div>

                      <div class="stock-label">
                        Penerimaan
                      </div>

                      <div
                        class="stock-number"
                        id="jumlahPenerimaan">

                        0

                      </div>

                      <small class="text-muted">
                        Transaksi
                      </small>

                    </div>


                    <div
                      class="stock-icon"
                      style="
                        background:#f0eaff;
                        color:#6c4cff;
                      ">

                      <i class="fas fa-truck-loading"></i>

                    </div>

                  </div>

                </div>

              </div>

            </div>

          </div>


          <!-- =================================================
               SETTING STOCK AWAL
          ================================================= -->

          <div class="card w-100 mb-4">

            <div class="card-body p-4">

              <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                  <div class="section-title">

                    <i class="fas fa-box-open me-2"></i>

                    Stock Awal

                  </div>

                  <small class="text-muted">

                    Stock awal disimpan pada master farmasi

                  </small>

                </div>

              </div>


              <form id="stockAwalForm">

                <input
                  type="hidden"
                  name="id_pharmacy"
                  value="<?= htmlspecialchars($no) ?>">


                <input
                  type="hidden"
                  name="id_customer"
                  value="<?= htmlspecialchars($id_customer) ?>">


                <input
                  type="hidden"
                  name="action"
                  value="stock_awal">


                <div class="form-section">

                  <div class="row g-3 align-items-end">


                    <!-- STOCK AWAL -->

                    <div class="col-md-6">

                      <label class="form-label">

                        Stock Awal

                        <span class="required">*</span>

                      </label>

                      <input
                        type="number"
                        name="stock_awal"
                        id="inputStockAwal"
                        class="form-control"
                        min="0"
                        value="0"
                        required>

                    </div>


                    <!-- BUTTON -->

                    <div class="col-md-6">

                      <button
                        type="submit"
                        class="btn btn-primary w-100"
                        id="btnSimpanStockAwal">

                        <i class="fas fa-save me-1"></i>

                        Simpan Stock Awal

                      </button>

                    </div>

                  </div>

                </div>

              </form>

            </div>

          </div>


          <!-- =================================================
               RIWAYAT PENERIMAAN
          ================================================= -->

          <div class="card w-100">

            <div class="card-body p-4">


              <!-- HEADER -->

              <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                  <div class="section-title">

                    <i class="fas fa-truck-loading me-2"></i>

                    Riwayat Penerimaan Barang

                  </div>

                  <small class="text-muted">

                    Daftar penerimaan barang farmasi

                  </small>

                </div>


                <button
                  type="button"
                  class="btn btn-primary"
                  id="btnTambahPenerimaan2">

                  <i class="fas fa-plus me-1"></i>

                  Tambah

                </button>

              </div>


              <!-- TABLE -->

              <div class="table-responsive">

                <table
                  id="tablePenerimaan"
                  class="table table-hover align-middle"
                  width="100%">

                  <thead>

                    <tr>

                      <th width="50">
                        No
                      </th>

                      <th>
                        Tanggal
                      </th>

                      <th>
                        No. Faktur
                      </th>

                      <th>
                        Supplier
                      </th>

                      <th>
                        Batch
                      </th>

                      <th>
                        Expired
                      </th>

                      <th>
                        Jumlah
                      </th>

                      <th>
                        Harga Beli
                      </th>

                      <th>
                        Total
                      </th>

                      <th>
                        Keterangan
                      </th>

                      <th width="100">
                        Action
                      </th>

                    </tr>

                  </thead>


                  <tbody id="bodyPenerimaan">

                  </tbody>

                </table>

              </div>

            </div>

          </div>


        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
       MODAL TAMBAH PENERIMAAN
  ===================================================== -->

  <div
    class="modal fade"
    id="modalPenerimaan"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

      <form
        id="formPenerimaan"
        class="modal-content">


        <!-- HEADER -->

        <div class="modal-header">

          <h5 class="modal-title">

            <i class="fas fa-truck-loading me-2"></i>

            Tambah Penerimaan Barang

          </h5>


          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal">
          </button>

        </div>


        <!-- BODY -->

        <div class="modal-body">


          <input
            type="hidden"
            name="action"
            value="penerimaan">


          <input
            type="hidden"
            name="id"
            id="penerimaan_id">


          <input
            type="hidden"
            name="id_pharmacy"
            value="<?= htmlspecialchars($no) ?>">


          <input
            type="hidden"
            name="id_customer"
            value="<?= htmlspecialchars($id_customer) ?>">


          <div class="row g-3">


            <!-- TANGGAL -->

            <div class="col-md-6">

              <label class="form-label">

                Tanggal Penerimaan

                <span class="required">*</span>

              </label>

              <input
                type="date"
                name="tanggal"
                id="penerimaan_tanggal"
                class="form-control"
                value="<?= date('Y-m-d') ?>"
                required>

            </div>


            <!-- FAKTUR -->

            <div class="col-md-6">

              <label class="form-label">
                Nomor Faktur
              </label>

              <input
                type="text"
                name="nomor_faktur"
                id="nomor_faktur"
                class="form-control"
                placeholder="Nomor faktur / invoice">

            </div>


            <!-- SUPPLIER -->

            <div class="col-md-6">

              <label class="form-label">

                Supplier

                <span class="required">*</span>

              </label>

              <input
                type="text"
                name="supplier"
                id="supplier"
                class="form-control"
                placeholder="Nama supplier"
                required>

            </div>


            <!-- BATCH -->

            <div class="col-md-6">

              <label class="form-label">
                Nomor Batch
              </label>

              <input
                type="text"
                name="batch"
                id="batch"
                class="form-control"
                placeholder="Nomor batch">

            </div>


            <!-- EXPIRED -->

            <div class="col-md-6">

              <label class="form-label">
                Tanggal Expired
              </label>

              <input
                type="date"
                name="expired"
                id="expired"
                class="form-control">

            </div>


            <!-- JUMLAH -->

            <div class="col-md-6">

              <label class="form-label">

                Jumlah Diterima

                <span class="required">*</span>

              </label>

              <input
                type="number"
                name="jumlah"
                id="jumlah"
                class="form-control"
                min="1"
                value="1"
                required>

            </div>


            <!-- HARGA -->

            <div class="col-md-6">

              <label class="form-label">
                Harga Beli
              </label>

              <input
                type="number"
                name="harga_beli"
                id="harga_beli"
                class="form-control"
                min="0"
                value="0">

            </div>


            <!-- TOTAL -->

            <div class="col-md-6">

              <label class="form-label">
                Total
              </label>

              <input
                type="text"
                id="totalHarga"
                class="form-control"
                value="Rp 0"
                readonly>

            </div>


            <!-- KETERANGAN -->

            <div class="col-md-12">

              <label class="form-label">
                Keterangan
              </label>

              <textarea
                name="keterangan"
                id="keterangan"
                class="form-control"
                rows="3"
                placeholder="Keterangan penerimaan barang..."></textarea>

            </div>


          </div>

        </div>


        <!-- FOOTER -->

        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal">

            Batal

          </button>


          <button
            type="submit"
            class="btn btn-primary"
            id="btnSimpanPenerimaan">

            <i class="fas fa-save me-1"></i>

            Simpan Penerimaan

          </button>

        </div>


      </form>

    </div>

  </div>


  <?php
  require 'library.php';
  ?>


  <!-- =====================================================
       JAVASCRIPT
  ===================================================== -->

  <script>
    $(document).ready(function() {


      /* =====================================================
         CONFIG
      ===================================================== */

      const stockController =
        'controller/pharmacy/pharmacyStockController.php';

      const idPharmacy =
        '<?= htmlspecialchars($no) ?>';


      /* =====================================================
         FORMAT ANGKA
      ===================================================== */

      function formatNumber(number) {

        return parseInt(number || 0)
          .toLocaleString('id-ID');

      }


      /* =====================================================
         FORMAT CURRENCY
      ===================================================== */

      function formatCurrency(number) {

        return 'Rp ' +
          parseFloat(number || 0)
          .toLocaleString('id-ID');

      }


      /* =====================================================
         FORMAT DATE
      ===================================================== */

      function formatDate(date) {

        if (!date) {
          return '-';
        }

        const parts =
          date.split('-');

        if (parts.length !== 3) {
          return date;
        }

        return parts[2] +
          '-' +
          parts[1] +
          '-' +
          parts[0];

      }


      /* =====================================================
         LOAD DETAIL STOCK
      ===================================================== */

      function loadStock() {

        $.ajax({

          url: stockController,

          type: 'GET',

          dataType: 'json',

          data: {
            action: 'detail',
            id_pharmacy: idPharmacy
          },

          success: function(response) {

            if (!response.status) {

              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message ||
                  'Data stock tidak ditemukan.'
              });

              return;
            }


            const d =
              response.data;


            /* ================================
               INFORMASI OBAT
            ================================= */

            $('#pharmacyName').text(
              d.pharmacy_name_generic ||
              d.pharmacy_name_trade ||
              'Farmasi'
            );


            $('#pharmacyCode').text(
              d.pharmacy_code ?
              'Kode: ' + d.pharmacy_code :
              ''
            );


            /* ================================
               STOCK
            ================================= */

            $('#stockAwal').text(
              formatNumber(d.stok_awal)
            );


            $('#totalPenerimaan').text(
              formatNumber(d.stok_masuk)
            );


            $('#totalStock').text(
              formatNumber(d.stok_tersedia)
            );


            $('#stockUnit').text(
              d.pharmacy_unit ||
              'Satuan'
            );


            /* ================================
               INPUT STOCK AWAL
            ================================= */

            $('#inputStockAwal').val(
              d.stok_awal || 0
            );

          },

          error: function(xhr) {

            console.error(xhr.responseText);

            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Gagal mengambil data stock.'
            });

          }

        });

      }


      /* =====================================================
         LOAD RIWAYAT PENERIMAAN
      ===================================================== */

      function loadPenerimaan() {

        $.ajax({

          url: stockController,

          type: 'GET',

          dataType: 'json',

          data: {
            action: 'penerimaan',
            id_pharmacy: idPharmacy
          },

          success: function(response) {

            if (!response.status) {

              $('#bodyPenerimaan').html(`
                <tr>
                  <td colspan="11"
                      class="text-center text-muted py-4">

                    ${response.message || 'Data tidak tersedia'}

                  </td>
                </tr>
              `);

              $('#jumlahPenerimaan').text(0);

              return;
            }


            const data =
              response.data || [];


            $('#jumlahPenerimaan').text(
              formatNumber(data.length)
            );


            let html = '';


            if (data.length === 0) {

              html = `
                <tr>
                  <td colspan="11"
                      class="text-center text-muted py-4">

                    Belum ada penerimaan barang.

                  </td>
                </tr>
              `;

            } else {

              data.forEach(function(row, index) {


                const total =
                  (parseFloat(row.jumlah) || 0) *
                  (parseFloat(row.harga_beli) || 0);


                html += `

                  <tr>

                    <td>
                      ${index + 1}
                    </td>

                    <td>
                      ${formatDate(row.tanggal)}
                    </td>

                    <td>
                      ${row.nomor_faktur || '-'}
                    </td>

                    <td>
                      ${row.supplier || '-'}
                    </td>

                    <td>
                      ${row.batch || '-'}
                    </td>

                    <td>
                      ${formatDate(row.expired)}
                    </td>

                    <td>
                      <span class="badge bg-success-subtle text-success badge-stock">
                        ${formatNumber(row.jumlah)}
                      </span>
                    </td>

                    <td>
                      ${formatCurrency(row.harga_beli)}
                    </td>

                    <td>
                      <strong>
                        ${formatCurrency(total)}
                      </strong>
                    </td>

                    <td>
                      ${row.keterangan || '-'}
                    </td>

                    <td>

                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger btnDeletePenerimaan"
                        data-id="${row.id_penerimaan}"
                        title="Hapus">

                        <i class="fas fa-trash"></i>

                      </button>

                    </td>

                  </tr>

                `;

              });

            }


            $('#bodyPenerimaan').html(html);

          },

          error: function(xhr) {

            console.error(xhr.responseText);

            $('#bodyPenerimaan').html(`
              <tr>
                <td colspan="11"
                    class="text-center text-danger py-4">

                  Gagal mengambil riwayat penerimaan.

                </td>
              </tr>
            `);

          }

        });

      }


      /* =====================================================
         BUKA MODAL
      ===================================================== */

      function bukaModalPenerimaan() {

        $('#formPenerimaan')[0].reset();

        $('#penerimaan_id').val('');

        $('#penerimaan_tanggal')
          .val('<?= date('Y-m-d') ?>');

        $('#jumlah').val(1);

        $('#harga_beli').val(0);

        hitungTotal();

        $('#modalPenerimaan').modal('show');

      }


      /* =====================================================
         BUTTON TAMBAH
      ===================================================== */

      $('#btnTambahPenerimaan')
        .on('click', function() {

          bukaModalPenerimaan();

        });


      $('#btnTambahPenerimaan2')
        .on('click', function() {

          bukaModalPenerimaan();

        });


      /* =====================================================
         HITUNG TOTAL
      ===================================================== */

      function hitungTotal() {

        let jumlah =
          parseFloat($('#jumlah').val()) || 0;

        let harga =
          parseFloat($('#harga_beli').val()) || 0;

        let total =
          jumlah * harga;


        $('#totalHarga').val(
          formatCurrency(total)
        );

      }


      $('#jumlah, #harga_beli')
        .on('input', function() {

          hitungTotal();

        });


      /* =====================================================
         SIMPAN STOCK AWAL
      ===================================================== */

      $('#stockAwalForm')
        .on('submit', function(e) {

          e.preventDefault();


          const form =
            this;


          const stock =
            $('#inputStockAwal').val();


          if (
            stock === '' ||
            parseInt(stock) < 0
          ) {

            Swal.fire({
              icon: 'warning',
              title: 'Perhatian',
              text: 'Stock awal tidak valid.'
            });

            return;

          }


          Swal.fire({

            title: 'Simpan Stock Awal?',

            text: 'Stock awal akan disimpan pada master farmasi.',

            icon: 'question',

            showCancelButton: true,

            confirmButtonText: 'Ya, Simpan',

            cancelButtonText: 'Batal'

          }).then(function(result) {

            if (!result.isConfirmed) {
              return;
            }


            const button =
              $('#btnSimpanStockAwal');


            button
              .prop('disabled', true)
              .html(`
                <i class="fas fa-spinner fa-spin me-1"></i>
                Menyimpan...
              `);


            $.ajax({

              url: stockController,

              type: 'POST',

              dataType: 'json',

              data: $(form).serialize(),

              success: function(response) {

                if (!response.status) {

                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message ||
                      'Stock awal gagal disimpan.'
                  });

                  return;
                }


                Swal.fire({

                  icon: 'success',

                  title: 'Berhasil',

                  text: response.message,

                  timer: 1500,

                  showConfirmButton: false

                });


                loadStock();

              },

              error: function(xhr) {

                console.error(xhr.responseText);

                Swal.fire({

                  icon: 'error',

                  title: 'Error',

                  text: 'Terjadi kesalahan pada server.'

                });

              },

              complete: function() {

                button
                  .prop('disabled', false)
                  .html(`
                    <i class="fas fa-save me-1"></i>
                    Simpan Stock Awal
                  `);

              }

            });

          });

        });


      /* =====================================================
         SIMPAN PENERIMAAN
      ===================================================== */

      $('#formPenerimaan')
        .on('submit', function(e) {

          e.preventDefault();


          const form =
            this;


          const jumlah =
            $('#jumlah').val();


          if (
            !jumlah ||
            parseInt(jumlah) <= 0
          ) {

            Swal.fire({

              icon: 'warning',

              title: 'Perhatian',

              text: 'Jumlah penerimaan harus lebih dari 0.'

            });

            return;

          }


          Swal.fire({

            title: 'Simpan Penerimaan?',

            text: 'Data penerimaan barang akan disimpan.',

            icon: 'question',

            showCancelButton: true,

            confirmButtonText: 'Ya, Simpan',

            cancelButtonText: 'Batal'

          }).then(function(result) {

            if (!result.isConfirmed) {
              return;
            }


            const button =
              $('#btnSimpanPenerimaan');


            button
              .prop('disabled', true)
              .html(`
                <i class="fas fa-spinner fa-spin me-1"></i>
                Menyimpan...
              `);


            $.ajax({

              url: stockController,

              type: 'POST',

              dataType: 'json',

              data: $(form).serialize(),

              success: function(response) {

                if (!response.status) {

                  Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text: response.message ||
                      'Penerimaan gagal disimpan.'

                  });

                  return;

                }


                $('#modalPenerimaan')
                  .modal('hide');


                Swal.fire({

                  icon: 'success',

                  title: 'Berhasil',

                  text: response.message,

                  timer: 1500,

                  showConfirmButton: false

                });


                loadStock();

                loadPenerimaan();

              },

              error: function(xhr) {

                console.error(xhr.responseText);

                Swal.fire({

                  icon: 'error',

                  title: 'Error',

                  text: 'Terjadi kesalahan pada server.'

                });

              },

              complete: function() {

                button
                  .prop('disabled', false)
                  .html(`
                    <i class="fas fa-save me-1"></i>
                    Simpan Penerimaan
                  `);

              }

            });

          });

        });


      /* =====================================================
         DELETE PENERIMAAN
      ===================================================== */

      $(document)
        .on('click', '.btnDeletePenerimaan', function() {


          const id =
            $(this).data('id');


          Swal.fire({

            title: 'Hapus Penerimaan?',

            text: 'Data penerimaan akan dihapus.',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Ya, Hapus',

            cancelButtonText: 'Batal',

            confirmButtonColor: '#dc3545'

          }).then(function(result) {

            if (!result.isConfirmed) {
              return;
            }


            $.ajax({

              url: stockController +
                '?id_penerimaan=' +
                encodeURIComponent(id),

              type: 'DELETE',

              dataType: 'json',

              success: function(response) {

                if (!response.status) {

                  Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text: response.message ||
                      'Data gagal dihapus.'

                  });

                  return;

                }


                Swal.fire({

                  icon: 'success',

                  title: 'Berhasil',

                  text: response.message,

                  timer: 1200,

                  showConfirmButton: false

                });


                loadStock();

                loadPenerimaan();

              },

              error: function(xhr) {

                console.error(xhr.responseText);

                Swal.fire({

                  icon: 'error',

                  title: 'Error',

                  text: 'Gagal menghapus data.'

                });

              }

            });

          });

        });


      /* =====================================================
         INITIAL LOAD
      ===================================================== */

      loadStock();

      loadPenerimaan();

      hitungTotal();


    });
  </script>


</body>

</html>