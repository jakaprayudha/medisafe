<?php
$title = 'Riwayat';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
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
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Riwayat</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th scope="col" class="text-dark fw-normal">Dokter</th>
                          <th scope="col" class="text-dark fw-normal">Layanan</th>
                          <th scope="col" class="text-dark fw-normal">Rawat Inap</th>
                          <th>Actions</th>
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


  <div class="modal fade" id="modalRME" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Detail RME</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-0">
          <iframe id="iframeRME"
            style="width:100%; height:80vh; border:0;"></iframe>
        </div>

      </div>
    </div>
  </div>
  <?php
  require 'library.php';
  ?>
</body>


</html>

<script>
  const visitID = '<?= $_GET['no'] ?? '' ?>';
  const apiUrl = 'controller/visit/riwayat?visit=' + visitID;

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          if (!json || json.status !== 'success') {
            console.error('API error:', json);
            return [];
          }

          return json.data.map(function(row) {
            return {
              "tanggal": (row.visit_date ?? '-') + ' ' + (row.visit_time ?? '-'),
              "dokter": row.id_doctor ?? "-",
              "layanan": row.id_poli ?? "-",
              "status_rawatinap": row.status_rawatinap,
              "actions": row.visit_ID
            };
          });
        }
      },
      columns: [{
          data: "tanggal"
        },
        {
          data: "dokter"
        },
        {
          data: "layanan"
        },
        {
          data: "status_rawatinap",
          render: function(data) {

            if (parseInt(data) === 1) {
              return `<span class="text-success fw-bold">✔</span>`;
            }

            return `<span class="text-muted">-</span>`;
          }
        },
        {
          data: "actions",
          render: function(data, type, row) {
            return `
              <button class="btn btn-sm btn-primary btn-rme" 
                      data-visit="${data}">
                <i class="bi bi-eye"></i> Lihat RME
              </button>
            `;
          }
        }
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        let total = api
          .column(2, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });
  });

  $(document).on('click', '.btn-rme', function() {

    let visit = $(this).data('visit');

    $('#iframeRME').attr('src', `module/admin/rmeView?visit=${visit}`);
    $('#modalRME').modal('show');

  });

  $('#modalRME').on('hidden.bs.modal', function() {
    $('#iframeRME').attr('src', '');
  });
</script>