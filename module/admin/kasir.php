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
          <ul class="nav nav-tabs" id="tabStatus">
            <li class="nav-item">
              <a class="nav-link active" data-status="belum" href="javascript:void(0)">Belum Bayar</a>

            </li>
            <li class="nav-item">
              <a class="nav-link" data-status="lunas" href="javascript:void(0)">Bayar</a>
            </li>
          </ul>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Pembayaran Pasien</h5>
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
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th scope="col" class="text-dark fw-normal">Tanggal</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
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
</body>



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
            <input type="date" id="fromDate" name="fromDate" max="" class="form-control">
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



<script>
  let currentFilter = 'belum';

  $(document).ready(function() {

    // =========================
    // 🔥 INIT DATE
    // =========================
    var today = new Date().toISOString().split("T")[0];

    $('#fromDate').val(today);
    $('#toDate').val('');

    $('#fromDate').attr('max', today);

    // =========================
    // 🔥 LOAD FILTER OPTION
    // =========================
    $('#filterModal').on('show.bs.modal', function() {
      loadDoctors();
      loadProviders();
      loadPoli();
    });

    function loadDoctors() {
      $.getJSON('controller/visit/getdoctor', function(res) {
        let html = '<option value="">Semua Dokter</option>';
        res.forEach(d => {
          html += `<option value="${d.id_doctor}">${d.doctor_name}</option>`;
        });
        $('#doctorSelect').html(html);
      });
    }

    function loadProviders() {
      $.getJSON('controller/visit/getprovider', function(res) {
        let html = '<option value="">Semua Metode Pembayaran</option>';
        res.forEach(p => {
          html += `<option value="${p.id_provider}">${p.provider_name}</option>`;
        });
        $('#providerSelect').html(html);
      });
    }

    function loadPoli() {
      $.getJSON('controller/visit/getpoli', function(res) {
        let html = '<option value="">Semua Poliklinik</option>';
        res.forEach(p => {
          html += `<option value="${p.id_poli}">${p.poli_name}</option>`;
        });
        $('#poliSelect').html(html);
      });
    }

    // =========================
    // 🔥 DATATABLE
    // =========================
    const apiUrl = 'controller/visit/kasirController';

    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
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
        },

        dataSrc: function(json) {

          let fromDate = $('#fromDate').val();
          let toDate = $('#toDate').val();

          let filtered = json.data.filter(function(row) {

            let status = Number(row.status_bayar);

            let statusMatch = false;

            if (currentFilter === 'lunas') {
              statusMatch = status === 1;
            } else {
              statusMatch = status !== 1;
            }

            // 🔥 FILTER TANGGAL
            let rowDate = row.visit_date ?
              row.visit_date.substring(0, 10) :
              '';

            let dateMatch = true;

            if (fromDate && rowDate < fromDate) dateMatch = false;
            if (toDate && rowDate > toDate) dateMatch = false;

            return statusMatch && dateMatch;
          });

          return filtered.map(function(row) {

            return {
              "actions": `
                <div class="text-center">
                  ${
                    row.status_bayar == 1
                      ? `
                      <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-success" disabled>
                          <i class="fas fa-check-circle"></i>
                        </button>

                        <a href="module/print/struk_billing?no=${row.visit_ID}&rm=${row.nomor_rm}"
                          target="_blank"
                          class="btn btn-sm btn-dark">
                          <i class="fas fa-receipt me-1"></i> Invoice
                        </a>
                      </div>
                    `
                      : `<a href="module/admin/kasir_detail?no=${row.visit_ID}&rm=${row.nomor_rm}" 
                          class="btn btn-sm btn-primary">
                          <i class="fas fa-file me-2"></i> Bayar
                        </a>`
                  }
                </div>
              `,
              "registrasi": (row.visit_date ?? '-') + ' ' + (row.visit_time ?? ''),
              "nomor_rm": row.nomor_rm ?? "-",
              "nama": row.patient_name ?? "-",
              "gender": row.patient_gender ?? "-",
              "dokter": row.id_doctor ?? "-",
              "layanan": row.id_poli ?? "-",
              "provider": row.provider_name ?? "-"
            };
          });
        }
      },

      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        },
        {
          data: "registrasi"
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
        }
      ]
    });

    // =========================
    // 🔥 FILTER BUTTON
    // =========================
    $('#btnApplyFilter, #btnFilter').on('click', function() {
      table.ajax.reload();
      $('#filterModal').modal('hide');
    });

    // =========================
    // 🔄 RESET
    // =========================
    $('#btnReset').on('click', function() {
      $('#fromDate').val(today);
      $('#toDate').val('');
      $('#doctorSelect').val('');
      $('#providerSelect').val('');
      $('#poliSelect').val('');
      table.ajax.reload();
    });

    // =========================
    // 🔥 TAB FILTER
    // =========================
    $('#tabStatus .nav-link').on('click', function(e) {
      e.preventDefault();

      $('#tabStatus .nav-link').removeClass('active');
      $(this).addClass('active');

      currentFilter = $(this).data('status'); // 🔥 langsung pakai

      table.ajax.reload();
    });

  });
</script>


</html>