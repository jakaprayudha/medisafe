<?php
$title = 'Penunjang';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
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
                    <h5 class="card-title fw-semibold">Data Pemeriksaan Lab</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/formulir_lab?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-light"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th>Nama Pemeriksaan</th>
                          <th>Tanggal</th>
                          <th>Sumber</th>
                          <th>Keterangan</th>
                          <th class="text-center">Actions</th>
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
        <input type="hidden" name="id_inspection" id="id_inspection">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="mb-3">
            <label for="inspection_name" class="form-label required">Nama Pemeriksaan</label>
            <select name="inspection_name" id="inspection_name" class="form-select js-example-basic-item" required>
              <option value="">Select Option</option>
              <?php
              $getbarang = tampildata("SELECT * FROM laboratorium_detail WHERE status='1'");
              ?>
              <?php foreach ($getbarang as $barang): ?>
                <option value="<?= $barang['assemen']; ?>" data-harga="<?= $barang['tarif']; ?>"><?= $barang['assemen']; ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="inspection_date" class="form-label required">Tanggal Pemeriksaan</label>
            <input type="date" value="<?= date('Y-m-d') ?>" class="form-control" id="inspection_date" name="inspection_date" required>
          </div>
          <div class="mb-3">
            <label for="inspection_date" class="form-label required">Sumber Hasil</label>
            <input type="text" value="Lab Klinik" class="form-control" id="inspection_source" name="inspection_source" required>
          </div>

          <div class="mb-3">
            <label for="inspection_summary" class="form-label">Catatan</label>
            <textarea class="form-control" id="inspection_summary" name="inspection_summary" rows="5"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="hasilModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Input Hasil Lab</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Pemeriksaan</th>
              <th>Hasil</th>
              <th>Satuan</th>
              <th>Normal</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="hasilBody"></tbody>
        </table>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" id="saveHasil">Simpan</button>
      </div>

    </div>
  </div>
</div>
<script>
  const apiUrl = 'controller/visit/penunjang?no=<?= $_GET['no'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
            <div class="text-center">
              <div class="btn-group btn-group-sm" role="group">
                <a class="btn btn-info hasil-btn" data-id="${row.id_inspection}"
                data-kode="${row.inspection_name}">
                  <i class="fas fa-flask"></i>
                </a>
                <a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_inspection}">
                  <i class="fas fa-edit"></i>
                </a>
                <a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_inspection}">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </div>
          `,
              "name": row.inspection_name ?? "-",
              "tanggal": row.inspection_date ?? "-",
              "sumber": row.inspection_source ?? "-",
              "kesimpulan": row.inspection_summary ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "name",
          className: "text-wrap"
        },
        {
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "sumber",
          className: "text-wrap"
        },
        {
          data: "kesimpulan",
          className: "text-wrap"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
      ]
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_inspection').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });


    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this); // ✅ ambil langsung FormData
      let id = $('#id_inspection').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'POST' : 'POST', // biar gampang semua via POST
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
      fetch(apiUrl + `&id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field (kecuali file)
            for (let key in d) {
              if (key !== "inspection_results") {
                $(`[name="${key}"]`).val(d[key]);
              }
            }

            // kalau ada file lama → kasih link preview
            if (d.inspection_results) {
              $("#inspection_results").after(`
            <div id="oldFile" class="mt-2">
              <a href="${d.inspection_results}" target="_blank">Lihat File Lama</a>
            </div>
          `);
            } else {
              $("#oldFile").remove();
            }

            $('#id_inspection').val(d.id_inspection);
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
          fetch(apiUrl + `&id=${id}`, {
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
  });
</script>

<script>
  $('#programModal').on('shown.bs.modal', function() {
    $('#inspection_name').select2({
      dropdownParent: $('#programModal'),
      width: '100%'
    });
  });
  $(document).ready(function() {

    $('#inspection_name').select2({
      dropdownParent: $('#programModal'),
      width: '100%',
      tags: true, // bisa input manual
      placeholder: "Ketik atau pilih obat",

      createTag: function(params) {
        return {
          id: params.term,
          text: params.term,
          newOption: true
        }
      },

      templateResult: function(data) {
        let $result = $("<span></span>");
        $result.text(data.text);

        if (data.newOption) {
          $result.append(" <em>(baru)</em>");
        }

        return $result;
      }

    });

    // auto isi harga
    $('#inspection_name').on('change', function() {
      currentInspectionId = $(this).data('id');
      let harga = $(this).find(':selected').data('harga') || '';
      $('#harga').val(harga);
    });

  });
</script>
<script>
  let currentInspectionId = null;

  $(document).on('click', '.hasil-btn', function() {

    let kode = $(this).data('kode');
    let id = $(this).data('id');


    // console.log('KODE:', kode);
    // console.log('ID:', id);

    currentInspectionId = id;

    $('#hasilBody').html('<tr><td colspan="5">Loading...</td></tr>');

    fetch(`controller/lab/getLabItem?kode=${kode}&id_inspection=${id}`)
      .then(res => res.json())
      .then(res => {

        let html = '';

        res.data.forEach(item => {

          html += `
        <tr>
          <td>${item.assemen}</td>
          <td>
            <input 
              type="text" 
              class="form-control hasil-input" 
              data-id="${item.id}" 
              value="${item.hasil ?? ''}"
            >
          </td>
          <td>${item.satuan ?? '-'}</td>
          <td>${item.minimum ?? '-'} - ${item.maksimum ?? '-'}</td>
          <td>${item.catatan ?? '-'}</td>
        </tr>
        `;
        });

        $('#hasilBody').html(html);
        $('#hasilModal').modal('show');

      });

  });
  $('#saveHasil').on('click', function() {

    let btn = $(this);
    btn.prop('disabled', true).html('⏳ Menyimpan...');

    Swal.fire({
      title: 'Menyimpan hasil...',
      text: 'Mohon tunggu',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    let results = [];

    $('#hasilBody tr').each(function() {
      let id_item = $(this).find('.hasil-input').data('id');
      let hasil = $(this).find('.hasil-input').val();

      if (id_item) {
        results.push({
          id_item: id_item,
          hasil: hasil
        });
      }
    });

    fetch('controller/lab/saveResult.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_inspection: currentInspectionId,
          data: results
        })
      })
      .then(res => res.json())
      .then(res => {

        Swal.close();

        if (res.status === 'success') {
          Swal.fire('Berhasil', 'Hasil lab disimpan', 'success');
          $('#hasilModal').modal('hide');
        } else {
          Swal.fire('Gagal', res.message, 'error');
        }

      })
      .catch(() => {
        Swal.close();
        Swal.fire('Error', 'Terjadi kesalahan', 'error');
      })
      .finally(() => {
        btn.prop('disabled', false).html('Simpan');
      });

  });
</script>

</html>