<?php
$title = 'Persalinan (ANC)';
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
  <style>
    .anc-thumb {
      width: 56px;
      height: 56px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      border: 1px solid #e3e6f0;
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
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                      <h5 class="card-title fw-semibold mb-1">Persalinan / ANC (Antenatal Care)</h5>
                      <small class="text-muted">Catatan asuhan kehamilan, dapat menyertakan gambar (USG/dokumentasi)</small>
                    </div>
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Tanggal Kontrol</th>
                          <th scope="col" class="text-dark fw-normal">Usia Gestasi</th>
                          <th scope="col" class="text-dark fw-normal">Taksiran Persalinan</th>
                          <th class="text-dark fw-normal">TD</th>
                          <th class="text-dark fw-normal">BB</th>
                          <th class="text-dark fw-normal">TFU</th>
                          <th class="text-dark fw-normal">DJJ</th>
                          <th class="text-dark fw-normal text-center">Gambar</th>
                          <th>Catatan</th>
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

<!-- MODAL FORM ANC -->
<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="programForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Catatan ANC</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $no ?>">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label required">Tanggal Kontrol</label>
            <input type="date" class="form-control" id="tanggal_kontrol" name="tanggal_kontrol" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">HPHT</label>
            <input type="date" class="form-control" id="hpht" name="hpht">
          </div>
          <div class="col-md-4">
            <label class="form-label">Taksiran Persalinan</label>
            <input type="date" class="form-control" id="taksiran_persalinan" name="taksiran_persalinan">
          </div>

          <div class="col-md-4">
            <label class="form-label">Usia Gestasi</label>
            <input type="text" class="form-control" id="usia_gestasi" name="usia_gestasi" placeholder="mis. 32 minggu">
          </div>
          <div class="col-md-4">
            <label class="form-label">G / P / A</label>
            <div class="input-group">
              <input type="text" class="form-control" id="gravida" name="gravida" placeholder="G">
              <input type="text" class="form-control" id="para" name="para" placeholder="P">
              <input type="text" class="form-control" id="abortus" name="abortus" placeholder="A">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Presentasi</label>
            <select class="form-select" id="presentasi" name="presentasi">
              <option value="">-- Pilih --</option>
              <option value="Sefalik">Sefalik</option>
              <option value="Bokong">Bokong</option>
              <option value="Lintang">Lintang</option>
              <option value="Belum Menetap">Belum Menetap</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Tekanan Darah</label>
            <input type="text" class="form-control" id="tekanan_darah" name="tekanan_darah" placeholder="120/80">
          </div>
          <div class="col-md-3">
            <label class="form-label">Berat Badan (kg)</label>
            <input type="text" class="form-control" id="berat_badan" name="berat_badan" placeholder="kg">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tinggi Fundus (cm)</label>
            <input type="text" class="form-control" id="tinggi_fundus" name="tinggi_fundus" placeholder="cm">
          </div>
          <div class="col-md-3">
            <label class="form-label">DJJ (x/menit)</label>
            <input type="text" class="form-control" id="djj" name="djj" placeholder="mis. 140">
          </div>

          <div class="col-md-6">
            <label class="form-label">Protein Urine</label>
            <select class="form-select" id="protein_urine" name="protein_urine">
              <option value="">-- Pilih --</option>
              <option value="Negatif">Negatif</option>
              <option value="Trace">Trace</option>
              <option value="+1">+1</option>
              <option value="+2">+2</option>
              <option value="+3">+3</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Edema</label>
            <select class="form-select" id="edema" name="edema">
              <option value="">-- Pilih --</option>
              <option value="Tidak Ada">Tidak Ada</option>
              <option value="Ringan">Ringan</option>
              <option value="Sedang">Sedang</option>
              <option value="Berat">Berat</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Gambar (USG / dokumentasi)</label>
            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">JPG/PNG/WEBP, maks 5 MB.</small>
            <div id="gambarPreview" class="mt-2 d-none">
              <img id="gambarPreviewImg" class="img-fluid rounded border" style="max-height:180px;">
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL PREVIEW GAMBAR -->
<div class="modal fade" id="gambarModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gambar ANC</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="gambarFull" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>

<script>
  const apiUrl = 'controller/visit/persalinanAncController?no=<?= $no ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return (json.data || []).map(function(row) {
            return {
              "tanggal": row.tanggal_kontrol ?? "-",
              "usia": row.usia_gestasi ?? "-",
              "taksiran": row.taksiran_persalinan ?? "-",
              "td": row.tekanan_darah ?? "-",
              "bb": row.berat_badan ?? "-",
              "tfu": row.tinggi_fundus ?? "-",
              "djj": row.djj ?? "-",
              "gambar": row.gambar ?
                `<img src="uploads/persalinan/${row.gambar}" class="anc-thumb anc-view" data-file="${row.gambar}">` : '<span class="text-muted">-</span>',
              "catatan": row.catatan ?? "-",
              "actions": `
                <div class="text-center">
                  <a class="btn btn-sm btn-danger delete-btn" href="javascript:;" data-id="${row.id_anc}" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              `
            };
          });
        }
      },
      columns: [{
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "usia",
          className: "text-wrap"
        },
        {
          data: "taksiran",
          className: "text-wrap"
        },
        {
          data: "td"
        },
        {
          data: "bb"
        },
        {
          data: "tfu"
        },
        {
          data: "djj"
        },
        {
          data: "gambar",
          orderable: false
        },
        {
          data: "catatan",
          className: "text-wrap"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
      ]
    });

    // preview gambar saat pilih file
    $('#gambar').on('change', function() {
      const file = this.files[0];
      if (!file) {
        $('#gambarPreview').addClass('d-none');
        return;
      }
      $('#gambarPreviewImg').attr('src', URL.createObjectURL(file));
      $('#gambarPreview').removeClass('d-none');
    });

    // klik thumbnail -> lihat besar
    $(document).on('click', '.anc-view', function() {
      $('#gambarFull').attr('src', 'uploads/persalinan/' + $(this).data('file'));
      $('#gambarModal').modal('show');
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset();
      $('#tanggal_kontrol').val('<?= date('Y-m-d') ?>');
      $('#gambarPreview').addClass('d-none');
      $('#programModal .modal-title').text('Tambah Catatan ANC');
      $('#programModal').modal('show');
    });

    // 🔹 Submit
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new FormData(this);

      fetch(apiUrl, {
          method: 'POST',
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
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data ANC?',
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
              } else {
                Swal.fire('Gagal!', data.message, 'error');
              }
            });
        }
      });
    });
  });
</script>

</html>
