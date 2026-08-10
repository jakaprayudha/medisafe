<?php
$title = 'Laporan Farmasi';
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

    /* biarkan scroll tetap jalan */
    .dataTables_scrollBody {
      overflow-x: auto !important;
      overflow-y: hidden !important;
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

    /* 🔥 freeze kolom pertama */
    #periodeTable th:first-child,
    #periodeTable td:first-child {
      position: sticky;
      left: 0;
      z-index: 5;
      background: #fff;
    }

    /* header lebih tinggi z-index */
    #periodeTable thead th:first-child {
      z-index: 6;
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
                    <h5 class="card-title fw-semibold">Data Farmasi</h5>
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
                      <div class="d-flex ms-auto gap-2">
                        <button id="btnExcel" class="btn btn-success">
                          <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button id="btnPrint" class="btn btn-primary">
                          <i class="fas fa-print"></i> Print
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal">Kode Obat</th>
                          <th scope="col" class="text-dark fw-normal">Nama Obat</th>
                          <th scope="col" class="text-dark fw-normal">Kategori</th>
                          <th scope="col" class="text-dark fw-normal text-center">Satuan</th>
                          <th scope="col" class="text-dark fw-normal text-end">Stok Awal</th>
                          <th scope="col" class="text-dark fw-normal text-end">Masuk</th>
                          <th scope="col" class="text-dark fw-normal text-end">Keluar</th>
                          <th scope="col" class="text-dark fw-normal text-end">Stok Akhir</th>
                          <th scope="col" class="text-dark fw-normal text-end">Stok Min</th>
                          <th scope="col" class="text-dark fw-normal text-end">Stok Max</th>
                          <th scope="col" class="text-dark fw-normal text-end">Nilai Stok</th>
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
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>
            Tutup
          </button>

          <button
            type="button"
            class="btn btn-primary"
            id="btnApplyFilter">
            <i class="fas fa-filter me-1"></i>
            Terapkan Filter
          </button>
        </div>

      </div>
    </div>
  </div>
</body>

<script>
  $(document).ready(function() {

    // =============================
    // DEFAULT DATE
    // =============================

    let today = new Date().toISOString().split('T')[0];

    $('#fromDate').val(today);
    $('#toDate').val(today);

    const apiUrl = 'controller/report/farmasiController.php';


    // =============================
    // DATATABLE
    // =============================

    let table = $('#periodeTable').DataTable({

      processing: true,
      serverSide: false,
      scrollX: true,

      ajax: {

        url: apiUrl,
        type: "GET",

        data: function(d) {

          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();

        },

        dataSrc: function(json) {

          if (json.status !== 'success') {

            console.error(
              json.message || 'Gagal mengambil data'
            );

            if (typeof Swal !== 'undefined') {

              Swal.fire({
                icon: 'error',
                title: 'Gagal Mengambil Data',
                text: json.message || 'Terjadi kesalahan.'
              });

            }

            return [];
          }

          return json.data || [];
        }
      },


      // =============================
      // URUTKAN NAMA OBAT
      // =============================

      order: [
        [2, 'asc']
      ],


      // =============================
      // COLUMNS
      // =============================

      columns: [

        // =============================
        // 1. STATUS
        // =============================

        {
          data: "status_stock",
          className: "text-center",

          render: function(data, type, row) {

            if (data === 'Habis') {

              return `
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                Habis
                            </span>
                        `;

            } else if (data === 'Di Bawah Minimum') {

              return `
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-arrow-down me-1"></i>
                                Di Bawah Minimum
                            </span>
                        `;

            } else if (data === 'Di Atas Maksimum') {

              return `
                            <span class="badge bg-info text-dark">
                                <i class="fas fa-arrow-up me-1"></i>
                                Di Atas Maksimum
                            </span>
                        `;

            } else {

              return `
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Normal
                            </span>
                        `;
            }
          }
        },


        // =============================
        // 2. KODE OBAT
        // =============================

        {
          data: "pharmacy_code",

          render: function(data) {

            return data ?
              data :
              '-';

          }
        },


        // =============================
        // 3. NAMA OBAT
        // =============================

        {
          data: null,

          render: function(data, type, row) {

            let generic =
              row.pharmacy_name_generic || '-';

            let trade =
              row.pharmacy_name_trade || '';

            if (trade) {

              return `
                            <div>
                                <strong>
                                    ${generic}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    ${trade}
                                </small>
                            </div>
                        `;

            }

            return `
                        <strong>
                            ${generic}
                        </strong>
                    `;
          }
        },


        // =============================
        // 4. KATEGORI
        // =============================

        {
          data: "pharmacy_category",

          render: function(data) {

            return data ?
              data :
              '-';

          }
        },


        // =============================
        // 5. SATUAN
        // =============================

        {
          data: "pharmacy_unit",

          className: "text-center",

          render: function(data) {

            return data ?
              data :
              '-';

          }
        },


        // =============================
        // 6. STOK AWAL
        // =============================

        {
          data: "stok_awal",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            return value.toLocaleString('id-ID');

          }
        },


        // =============================
        // 7. STOK MASUK
        // =============================

        {
          data: "stok_masuk",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            if (value <= 0) {
              return '0';
            }

            return `
                        <span class="text-success fw-semibold">
                            +${value.toLocaleString('id-ID')}
                        </span>
                    `;
          }
        },


        // =============================
        // 8. STOK KELUAR
        // =============================

        {
          data: "stok_keluar",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            if (value <= 0) {
              return '0';
            }

            return `
                        <span class="text-danger fw-semibold">
                            -${value.toLocaleString('id-ID')}
                        </span>
                    `;
          }
        },


        // =============================
        // 9. STOK AKHIR
        // =============================

        {
          data: "stok_akhir",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            return `
                        <strong>
                            ${value.toLocaleString('id-ID')}
                        </strong>
                    `;
          }
        },


        // =============================
        // 10. STOK MINIMUM
        // =============================

        {
          data: "stok_min",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            return value.toLocaleString('id-ID');

          }
        },


        // =============================
        // 11. STOK MAKSIMUM
        // =============================

        {
          data: "stok_max",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            return value.toLocaleString('id-ID');

          }
        },


        // =============================
        // 12. NILAI STOK
        // =============================

        {
          data: "nilai_stok",

          className: "text-end",

          render: function(data) {

            let value =
              parseFloat(data) || 0;

            return `
                        <strong>
                            Rp ${value.toLocaleString('id-ID')}
                        </strong>
                    `;
          }
        }

      ],


      // =============================
      // CREATED ROW
      // =============================

      createdRow: function(row, data) {

        // Tandai stok habis
        if (data.status_code === 'habis') {

          $(row).addClass('table-danger');

        }

        // Tandai stok di bawah minimum
        else if (data.status_code === 'minimum') {

          $(row).addClass('table-warning');

        }

      }

    });


    // =============================
    // APPLY FILTER
    // =============================

    $('#btnApplyFilter').on('click', function() {

      let fromDate = $('#fromDate').val();
      let toDate = $('#toDate').val();


      // Validasi tanggal

      if (!fromDate || !toDate) {

        if (typeof Swal !== 'undefined') {

          Swal.fire({
            icon: 'warning',
            title: 'Periode Belum Lengkap',
            text: 'Silakan pilih tanggal mulai dan tanggal akhir.'
          });

        }

        return;
      }


      // Validasi range

      if (fromDate > toDate) {

        if (typeof Swal !== 'undefined') {

          Swal.fire({
            icon: 'warning',
            title: 'Periode Tidak Valid',
            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
          });

        }

        return;
      }


      // Reload DataTable

      table.ajax.reload();


      // Tutup modal

      $('#filterModal').modal('hide');

    });


    // =============================
    // RESET FILTER
    // =============================

    $('#btnReset').on('click', function() {

      $('#fromDate').val(today);
      $('#toDate').val(today);

      table.ajax.reload();

    });


    // =============================
    // EXCEL
    // =============================

    $('#btnExcel').on('click', function() {

      let fromDate =
        $('#fromDate').val();

      let toDate =
        $('#toDate').val();


      if (!fromDate || !toDate) {

        Swal.fire({
          icon: 'warning',
          title: 'Periode Belum Lengkap',
          text: 'Silakan pilih periode terlebih dahulu.'
        });

        return;
      }


      let url =
        `module/report/export_farmasi.php` +
        `?fromDate=${encodeURIComponent(fromDate)}` +
        `&toDate=${encodeURIComponent(toDate)}`;


      window.location.href = url;

    });


    // =============================
    // PRINT
    // =============================

    $('#btnPrint').on('click', function() {

      let fromDate =
        $('#fromDate').val();

      let toDate =
        $('#toDate').val();


      if (!fromDate || !toDate) {

        Swal.fire({
          icon: 'warning',
          title: 'Periode Belum Lengkap',
          text: 'Silakan pilih periode terlebih dahulu.'
        });

        return;
      }


      let url =
        `module/report/print_farmasi.php` +
        `?fromDate=${encodeURIComponent(fromDate)}` +
        `&toDate=${encodeURIComponent(toDate)}`;


      window.open(url, '_blank');

    });


  });
</script>

</html>