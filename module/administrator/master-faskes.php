<?php
$title = 'Master Faskes';
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
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Master Faskes</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal col-1">Nomor PKS</th>
                          <th class="text-dark fw-normal col-1">Kode Faskes</th>
                          <th scope="col" class="text-dark fw-normal">Nama Faskes (Klinik)</th>
                          <th class="text-dark fw-normal">PIC</th>
                          <th class="text-dark fw-normal">Mulai</th>
                          <th class="text-dark fw-normal">Berakhir</th>
                          <th class="text-dark fw-normal">Biaya</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Status</th>
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
<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_faskes" id="id_faskes">
        <div class="row">
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required" id="comp_code">No. PKS</label>
              <input type="text" id="contract_number" name="contract_number" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Nama Klinik (Faskes)</label>
              <input type="text" id="faskes_name" name="faskes_name" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label ">Nama PIC</label>
              <input type="text" id="pic_name" name="pic_name" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label ">No.Hp PIC</label>
              <input type="text" id="pic_phone" name="pic_phone" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Mulai Kontrak</label>
              <input type="date" required id="contract_start" name="contract_start" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Selesai Kontrak</label>
              <input type="date" required id="contract_end" name="contract_end" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Nominal Kontrak (IDR)</label>
              <input type="number" required id="contract_amount" name="contract_amount" class="form-control">
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Alamat</label>
              <textarea name="faskes_address" rows="3" id="faskes_address" class="form-control" required></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  const apiUrl = 'controller/master/faskesController';
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
                	<a class="btn btn-info" href="module/administrator/master-faskes-detail?no=${row.order_number}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_faskes}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_faskes}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "pks": row.contract_number,
              "code": row.faskes_code,
              "name": row.faskes_name,
              "pic": row.pic_name ? `${row.pic_name} (${row.pic_phone})` : '-',
              "start": row.contract_start,
              "end": row.contract_end,
              "amount": row.contract_amount,
              "status": `
                <label class="switch">
                  <input type="checkbox" class="toggle-status" 
                    data-id="${row.id_faskes}" 
                    ${row.faskes_status == '1' ? 'checked' : ''}>
                  <span class="slider round"></span>
                </label>
                `
            };
          });
        }
      },
      columns: [{
          data: "pks"
        },
        {
          data: "code"
        },
        {
          data: "name"
        },
        {
          data: "pic"
        },
        {
          data: "start"
        },
        {
          data: "end"
        },
        {
          data: "amount",
          render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
        }, {
          data: "status"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
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
      $('#id_faskes').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_faskes').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });
    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });

    $(document).on('change', '.toggle-status', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_faskes=${id}&faskes_status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message, 'error');
          }
        });
    });
  });
</script>

</html>