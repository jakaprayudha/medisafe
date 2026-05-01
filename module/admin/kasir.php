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



<script>
  let currentFilter = 'belum'; // default
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

    const apiUrl = 'controller/visit/kasirController';
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
        },
        dataSrc: function(json) {
          let filtered = json.data.filter(function(row) {
            let status = parseInt(row.status_bayar || 0);

            return currentFilter === 'lunas' ?
              status === 1 // ✅ sudah bayar
              :
              status !== 1; // ❌ belum bayar
          });

          return filtered.map(function(row) {

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
                ${
                  row.status_bayar == 1
                    ? `<button class="btn btn-sm btn-success" disabled>
                        <i class="fas fa-check-circle me-2"></i> Lunas
                      </button>`
                    : `<a href="module/admin/kasir_detail?no=${row.visit_ID}&rm=${row.nomor_rm}" 
                        class="btn btn-sm btn-primary">
                        <i class="fas fa-file me-2"></i> Bayar
                      </a>`
                }
              </div>
            `,
              "registrasi": row.visit_date + ' ' + row.visit_time ?? "-",
              "nomor_rm": row.nomor_rm ?? "-",
              "nama": `
                ${row.patient_name ?? "-"}
              `,
              "gender": row.patient_gender ?? "-",
              "dokter": row.id_doctor ?? "-",
              "layanan": row.id_poli ?? "-",
              "provider": row.provider_name ?? "-",
              "status": row.status_bayar == 1 ?
                '<span class="badge bg-success text-center d-block">Lunas</span>' : '<span class="badge bg-danger text-center d-block">Belum Bayar</span>'
            };
          });
        }
      },
      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        }, {
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

    $('#tabStatus .nav-link').on('click', function(e) {
      e.preventDefault();

      // reset active
      $('#tabStatus .nav-link').removeClass('active');
      $(this).addClass('active');

      // ambil status
      let status = $(this).data('status');

      // mapping
      currentFilter = (status === 'selesai') ? 'lunas' : 'belum';

      console.log("FILTER:", currentFilter);

      table.ajax.reload();
    });


  });
</script>


</html>