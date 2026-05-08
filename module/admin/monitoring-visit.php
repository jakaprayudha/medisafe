<?php
$title = 'Data Dokter';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .badge-box {
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .badge-blue {
      background: #e3f2fd;
      color: #0d6efd;
    }

    .badge-green {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .badge-purple {
      background: #f3e5f5;
      color: #7b1fa2;
    }

    .badge-red {
      background: #fdecea;
      color: #c62828;
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar-master.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data RME Pasien</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th>Nama Pasien</th>
                          <th>NIK</th>
                          <th>IDSH</th>
                          <th>Diagnosa</th>
                          <th>Obat</th>
                          <th>Lab</th>
                          <th>Status</th>
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
  require '../admin/library.php';
  ?>
</body>


<script>
  const apiUrl = 'controller/master/RMEpatientIDSHDetailControllerByClinic?id_customer=<?= $_SESSION["id_customer"] ?? "" ?>';

  $(document).ready(function() {

    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      autoWidth: false,

      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {

          const check = '<i class="fas fa-check text-success"></i>';
          const cross = '<i class="fas fa-times text-danger"></i>';

          return json.data.map(function(row) {

            let diagnosa = row.diagnosa && row.diagnosa !== '' ? check : cross;
            let obat = row.ada_obat == 1 ? check : cross;
            let lab = row.ada_lab == 1 ? check : cross;

            let status = '';
            let badge = '';

            if (!row.diagnosa && !row.ada_obat && !row.ada_lab) {
              status = 'Tidak Lengkap';
              badge = 'badge bg-danger';
            } else if (!row.diagnosa) {
              status = 'Diagnosa kosong';
              badge = 'badge bg-warning';
            } else if (!row.ada_obat) {
              status = 'Tidak ada obat';
              badge = 'badge bg-info';
            } else if (!row.ada_lab) {
              status = 'Tidak ada lab';
              badge = 'badge bg-primary';
            } else {
              status = 'Lengkap';
              badge = 'badge bg-success';
            }

            let rowClass = (!row.diagnosa || !row.ada_obat || !row.ada_lab) ?
              'table-danger' :
              'table-success';

            return {
              DT_RowClass: rowClass,
              patient_name: row.patient_name ?? '-',
              patient_nik: row.patient_nik ?? '-',
              idsh: row.idsh ?? '-',
              diagnosa: diagnosa,
              obat: obat,
              lab: lab,
              status: `<span class="${badge}">${status}</span>`
            };
          });
        }
      },

      columns: [{
          data: "patient_name"
        },
        {
          data: "patient_nik"
        },
        {
          data: "idsh"
        },
        {
          data: "diagnosa"
        },
        {
          data: "obat"
        },
        {
          data: "lab"
        },
        {
          data: "status"
        }
      ]
    });

  });
</script>

</html>