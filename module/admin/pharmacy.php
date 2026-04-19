<?php
$title = 'Farmasi';
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
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal text-center">No</th>
                          <th class="text-dark fw-normal">Kategori</th>
                          <th scope="col" class="text-dark fw-normal">Nama Generic/Trade</th>
                          <th scope="col" class="text-dark fw-normal">Golongan</th>
                          <th scope="col" class="text-dark fw-normal">Jenis</th>
                          <th scope="col" class="text-dark fw-normal">Sub Kategori</th>
                          <th scope="col" class="text-dark fw-normal">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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


<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Obat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="pharmacy_code" id="pharmacy_code">
        <input type="hidden" name="id_pharmacy" id="id_pharmacy">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Generic</label>

              <!-- Checkbox -->
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="use_bpjs">
                <label class="form-check-label" for="use_bpjs">
                  Ambil dari BPJS
                </label>
              </div>

              <!-- Input Manual -->
              <input type="text"
                id="pharmacy_name_generic"
                name="pharmacy_name_generic"
                class="form-control"
                required>

              <!-- Select BPJS -->
              <select id="kdObat" class="form-select d-none">
                <option value="">Pilih Obat BPJS</option>
              </select>
            </div>
          </div>

          <!-- NAMA DAGANG -->
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Nama Pabrikan/Dagang</label>
              <input type="text" id="pharmacy_name_trade" name="pharmacy_name_trade" class="form-control" required>
            </div>
          </div>

          <!-- KATEGORI -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Kategori</label>
              <select name="pharmacy_category" class="form-select" required>
                <option value="">PILIH</option>
                <option value="Obat">Obat</option>
                <option value="BMHP">BMHP</option>
                <option value="Alkes">Alkes</option>
              </select>
            </div>
          </div>

          <!-- SUB -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Sub Kategori</label>
              <input type="text" name="pharmacy_sub_category" class="form-control">
            </div>
          </div>

          <!-- GOLONGAN -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Golongan</label>
              <select name="pharmcy_golongan" class="form-select" required>
                <option value="">PILIH</option>
                <option value="Bebas">Bebas</option>
                <option value="Bebas Terbatas">Bebas Terbatas</option>
                <option value="Keras">Keras</option>
                <option value="Psikotropika">Psikotropika</option>
                <option value="Narkotika">Narkotika</option>
              </select>
            </div>
          </div>

          <!-- JENIS -->
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Jenis</label>
              <select name="pharmcy_jenis_drugs" class="form-select" required>
                <option value="">PILIH</option>
                <option value="Generik">Generik</option>
                <option value="Paten">Paten</option>
                <option value="Non-Generik">Non-Generik</option>
              </select>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="btnSimpanObat">Simpan</button>
      </div>

    </form>
  </div>
</div>

<script>
  const apiUrl = 'controller/master/pharmacyController';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {

              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
                	<a class="btn btn-info" href="module/admin/pharmacy_details?no=${row.pharmacy_number}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_pharmacy}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_pharmacy}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "category": row.pharmacy_category ?? "-",
              "name": [
                  row.pharmacy_name_generic,
                  row.pharmacy_name_trade
                ]
                .filter(v => v && v !== 'null')
                .join('/') || '-',
              "golongan": row.pharmcy_golongan ?? "-",
              "jenis": row.pharmcy_jenis_drugs ?? "-",
              "subcategory": row.pharmacy_sub_category ?? "-",
              "status": row.pharmacy_status === '1' ?
                '<span class="badge bg-success text-center d-block">Aktif</span>' : '<span class="badge bg-danger text-center d-block">Nonaktif</span>'
            };
          });
        }
      },
      columns: [{
          data: null,
          className: 'text-center',
          orderable: false,
          searchable: false,
          render: function(data, type, row, meta) {
            return meta.row + 1;
          }
        }, {
          data: "category"
        }, {
          data: "name"
        },
        {
          data: "golongan"
        },
        {
          data: "jenis"
        },
        {
          data: "subcategory"
        },
        {
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
      $('#id_pharmacy').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_pharmacy').val();
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
    $('#kdObat').select2({
      dropdownParent: $('#programModal'),
      placeholder: 'Cari obat...',
      minimumInputLength: 3,
      language: {
        inputTooShort: function(args) {
          return 'Ketik minimal ' + args.minimum + ' karakter';
        },
        searching: function() {
          return 'Sedang mencari obat...';
        },
        noResults: function() {
          return 'Obat tidak ditemukan';
        }
      },
      ajax: {
        url: 'controller/admisi/services/getObat.php',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            keyword: params.term
          };
        },
        processResults: function(data) {
          return {
            results: data.data.map(item => ({
              id: item.kdObat,
              text: item.nmObat
            }))
          };
        },
        cache: true
      }
    });

    function toggleBPJS() {
      if ($('#use_bpjs').is(':checked')) {
        $('#pharmacy_name_generic').val('');
        $('#pharmacy_name_generic').addClass('d-none');
        $('#kdObat').removeClass('d-none');
        $('#kdObat').next('.select2').show();
      } else {
        $('#kdObat').val(null).trigger('change');
        $('#pharmacy_name_generic').removeClass('d-none');
        $('#kdObat').addClass('d-none');
        $('#kdObat').next('.select2').hide();
      }
    }
    $('#use_bpjs').on('change', toggleBPJS);
    $('#programModal').on('shown.bs.modal', function() {
      toggleBPJS();
    });
    $('#kdObat').on('select2:select', function(e) {
      let data = e.params.data;
      console.log(data);
      $('#pharmacy_code').val(data.id);
      $('#pharmacy_name_generic').val(data.text);
    });
    // $(document).on('click', '#btnSimpanObat', function(e) {
    //   e.preventDefault();
    //   const btn = $(this);
    //   let id = $('#id_pharmacy').val();
    //   let data = $('#programForm').serialize();
    //   data += '&id=' + id;
    //   $.ajax({
    //     url: 'controller/admisi/services/insertObatMaster.php',
    //     type: "POST",
    //     data: data,
    //     dataType: 'json',
    //     beforeSend: function() {
    //       btn.prop('disabled', true).text('Menyimpan...');
    //     },
    //     success: function(res) {
    //       console.log(res);
    //       if (res.status === 'success') {
    //         alert('Data berhasil disimpan');
    //         $('#programModal').modal('hide');
    //       } else {
    //         alert(res.message || 'Gagal menyimpan');
    //       }
    //     },
    //     error: function(xhr) {
    //       console.error(xhr.responseText);
    //       alert('Terjadi error di server');
    //     },
    //     complete: function() {
    //       btn.prop('disabled', false).text('Simpan');
    //     }
    //   });
    // });
  });
</script>

</html>