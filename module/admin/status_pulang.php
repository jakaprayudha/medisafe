<?php
$title = 'Status Pulang Rawat Inap';
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
    .form-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 25px;
    }

    .form-switch input {
      display: none;
    }

    .form-switch i {
      position: absolute;
      cursor: pointer;
      background: #ccc;
      border-radius: 25px;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      transition: .3s;
    }

    .form-switch i:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 2.5px;
      background: white;
      border-radius: 50%;
      transition: .3s;
    }

    .form-switch input:checked+i {
      background: #28a745;
    }

    .form-switch input:checked+i:before {
      transform: translateX(24px);
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
          <div class="alert alert-primary" role="alert">
            Ini merupakan fitur untuk mengubah status pasien rawat inap menjadi pulang atau aktif. Jika status diubah menjadi pulang, maka bed yang ditempati pasien akan otomatis menjadi kosong dan dapat digunakan untuk pasien lain. untuk mengubah status, cukup klik toggle di kolom status dan apabila toggle hijau berarti pasien sudah pulang, sedangkan jika toggle abu-abu berarti pasien masih aktif dirawat.
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <!-- Judul -->
                    <h5 class="card-title fw-semibold mb-0">Data Pasien Rawat Inap</h5>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th>Tanggal Masuk</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">DPJP</th>
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




<script>
  const apiUrl = 'controller/visit/ListBookingRanap';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                <div class="text-center d-flex justify-content-center">
                  <label class="form-switch">
                    <input type="checkbox" class="status-toggle"
                      data-id="${row.id_ranap}"
                      data-bed="${row.id_bed}"
                      ${row.status === 'pulang' ? 'checked' : ''}>
                    <i></i>
                  </label>
                </div>
              `,
              "date": row.ranap_date + ' ' + row.ranap_time ?? "-",
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "gender": row.patient_gender ?? "-",
              "dpjp": row.id_doctor ?? "-"
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
          data: "date"
        },
        {
          data: "rm"
        },
        {
          data: "name"
        },
        {
          data: "gender"
        },
        {
          data: "dpjp"
        },

      ]
    });

    // 🔹 Search
    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    $(document).on('change', '.status-toggle', function() {
      const id = $(this).data('id');
      const bed = $(this).data('bed');
      const isChecked = $(this).is(':checked');

      const status = isChecked ? 'pulang' : 'aktif';

      $.ajax({
        url: 'controller/visit/ListBookingRanap',
        type: 'PUT',
        data: {
          id_ranap: id,
          id_bed: bed,
          status: status
        },
        success: function(res) {
          console.log(res);
        },
        error: function(err) {
          alert('Gagal update status');
          console.log(err);
        }
      });
    });



  });
</script>


</html>