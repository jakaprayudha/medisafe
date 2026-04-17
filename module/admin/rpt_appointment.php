<?php
$title = 'Laporan Appointment';
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
                    <h5 class="card-title fw-semibold">Data Appointment</h5>
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
                          <th scope="col" class="text-dark fw-normal">No.BPJS</th>
                          <th class="text-dark fw-normal">Tanggal</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Poli</th>
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
</body>

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
    var today = new Date().toISOString().split('T')[0];

    // default tanggal
    $('#fromDate').val(today);
    $('#toDate').val(today);

    const apiUrl = 'controller/report/appointmentController.php';

    // =============================
    // INIT DATATABLE
    // =============================
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        data: function(d) {
          // 🔥 INI KUNCI FILTER
          d.fromDate = $('#fromDate').val();
          d.toDate = $('#toDate').val();
          d.doctor = $('#doctorSelect').val();
          d.provider = $('#providerSelect').val();
          d.poli = $('#poliSelect').val();
        },
        dataSrc: function(json) {
          return json.data || [];
        }
      },
      columns: [{
          data: "visit_status",
          render: function(data) {

            if (data == 4) {
              return '<span class="badge bg-success col-12">Selesai</span>';
            } else {
              return '<span class="badge bg-warning col-12 text-dark">Belum Selesai</span>';
            }

          }
        },
        {
          data: "noKartu"
        },
        {
          data: null,
          render: function(row) {
            return row.visit_date + " " + (row.visit_time ?? "");
          }
        },
        {
          data: "patient_name_pcare"
        },
        {
          data: "id_doctor"
        },
        {
          data: "id_poli"
        },
        {
          data: "provider_name",
          render: function(data) {
            return data ? data : '-';
          }
        }
      ]
    });

    // =============================
    // APPLY FILTER
    // =============================
    $('#btnApplyFilter').on('click', function() {
      table.ajax.reload();
      $('#filterModal').modal('hide');
    });

    // =============================
    // RESET FILTER
    // =============================
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val(today);
      $('#doctorSelect').val('');
      $('#providerSelect').val('');
      $('#poliSelect').val('');

      table.ajax.reload();
    });
    $('#btnPrint').on('click', function() {
      let fromDate = $('#fromDate').val();
      let toDate = $('#toDate').val();
      let doctor = $('#doctorSelect').val();
      let provider = $('#providerSelect').val();

      let url = `module/report/print_appointment.php?fromDate=${fromDate}&toDate=${toDate}&doctor=${doctor}&provider=${provider}`;

      window.open(url, '_blank');
    });
  });
</script>


</html>