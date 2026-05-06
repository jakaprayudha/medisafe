<?php
$title = 'Monitoring Visit';
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
                    <h5 class="card-title fw-semibold">Data Monitoring RME</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Faskes (Klinik)</th>
                          <th scope="col" class="text-dark fw-normal">Total Visit</th>
                          <th scope="col" class="text-dark fw-normal">Diagnosa</th>
                          <th scope="col" class="text-dark fw-normal">Obat</th>
                          <th scope="col" class="text-dark fw-normal">Laboratorium</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
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

<div class="modal fade" id="modalPatient" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detail Pasien</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Nama Pasien</th>
              <th>Diagnosa</th>
              <th>Obat</th>
              <th>Lab</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="dokterBody"></tbody>
        </table>
      </div>

    </div>
  </div>
</div>
<script>
  const apiUrl = 'controller/master/RMEpatientIDSHController';
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
            return {
              "actions": `
                      <div class="text-end">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-primary detail-btn" href="javascript:;" data-id="${row.id_customer}">
											<i class="fas fa-user"></i>
									</a>
								</div>
							</div>
                    `,
              "name": row.clinic_name,
              total_patient: `<span class="badge-box badge-blue">
                ${row.total_patient}
              </span>`,

              total_diagnosa: `<span class="badge-box badge-green">
                  ${row.total_diagnosa}
              </span>`,

              total_obat: `<span class="badge-box badge-purple">
                  ${row.total_obat}
              </span>`,

              total_lab: `<span class="badge-box badge-red">
                  ${row.total_lab}
              </span>`,

            };
          });
        }
      },
      columns: [{
          data: "name"
        },
        {
          data: "total_patient"
        },
        {
          data: "total_diagnosa"
        },
        {
          data: "total_obat"
        },
        {
          data: "total_lab"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
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

    $(document).on('click', '.detail-btn', function() {

      let id_customer = $(this).data('id');

      $('#dokterBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

      fetch(`controller/master/RMEpatientIDSHDetailController.php?id_customer=${id_customer}`)
        .then(res => res.json())
        .then(res => {

          let html = '';

          res.data.forEach(d => {

            // 🔥 ICON CHECK
            const check = '<span class="badge bg-success">✔</span>';
            const cross = '<span class="badge bg-danger">✖</span>';

            let diagnosa = d.diagnosa ? check : cross;
            let obat = d.ada_obat == 1 ? check : cross;
            let lab = d.ada_lab == 1 ? check : cross;

            let status = '';
            let badge = '';

            if (!d.diagnosa && !d.ada_obat && !d.ada_lab) {
              status = 'Tidak Lengkap';
              badge = 'badge bg-danger';
            } else if (!d.diagnosa) {
              status = 'Diagnosa kosong';
              badge = 'badge bg-warning';
            } else if (!d.ada_obat) {
              status = 'Tidak ada obat';
              badge = 'badge bg-info';
            } else if (!d.ada_lab) {
              status = 'Tidak ada lab';
              badge = 'badge bg-primary';
            } else {
              status = 'Lengkap';
              badge = 'badge bg-success';
            }

            html += `
    <tr>
      <td>${d.patient_name}</td>
      <td>${diagnosa}</td>
      <td>${obat}</td>
      <td>${lab}</td>
      <td><span class="${badge}">${status}</span></td>
    </tr>
  `;
          });

          $('#dokterBody').html(html);

          $('#modalPatient').modal('show');

        });

    });


  });
</script>

</html>