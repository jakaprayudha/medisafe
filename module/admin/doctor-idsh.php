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
                    <h5 class="card-title fw-semibold">Data IDSH Dokter</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Dokter</th>
                          <th scope="col" class="text-dark fw-normal">NIK</th>
                          <th scope="col" class="text-dark fw-normal">IDSH</th>
                          <th scope="col" class="text-dark fw-normal col-2">Status</th>
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
  const apiUrl = 'controller/master/doctorIDSHDetailControllerByClinic?id_customer=<?= $_SESSION["id_customer"] ?? "" ?>';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {

            let status = '';
            let badge = '';

            if (!row.ada_nik && !row.ada_idsh) {
              status = 'Belum Lengkap';
              badge = 'badge bg-danger';
            } else if (!row.ada_nik) {
              status = 'NIK kosong';
              badge = 'badge bg-warning';
            } else if (!row.ada_idsh) {
              status = 'IDSH kosong';
              badge = 'badge bg-info';
            } else {
              status = 'Lengkap';
              badge = 'badge bg-success';
            }

            return {
              doctor_name: row.doctor_name ?? '-',
              doctor_nik: row.doctor_nik ?? '-',
              idsh: row.idsh ?? '-',
              status: `<span class="${badge}">${status}</span>`
            };
          });
        }
      },
      columns: [{
          data: "doctor_name"
        },
        {
          data: "doctor_nik"
        },
        {
          data: "idsh"
        },
        {
          data: "status"
        }
      ],
      // footerCallback: function(row, data, start, end, display) {
      //   var api = this.api();

      //   // Hitung total bobot
      //   let total = api
      //     .column(1, {
      //       page: 'current'
      //     })
      //     .data()
      //     .reduce((a, b) => {
      //       return (parseFloat(a) || 0) + (parseFloat(b) || 0);
      //     }, 0);

      //   // Tampilkan di footer
      //   $(api.column(3).footer()).html(total.toFixed(2) + " %");
      // }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });


  });
</script>

</html>