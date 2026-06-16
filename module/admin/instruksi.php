<?php
$title = 'Formulir Instruksi Dokter';
require '../../database/connect.php';
require '../../controller/view.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$patient = mysqli_query($koneksi, "SELECT nomor_rm, id_patient FROM ms_patient WHERE nomor_rm='$rm'");
$datapatient = mysqli_fetch_array($patient);
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
                    <h5 class="card-title fw-semibold">Formulir Instruksi Dokter</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/formulir_instruksi?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-outline-primary"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Tanggal</th>
                          <th>Perjalanan Penyakit</th>
                          <th scope="col" class="text-dark fw-normal">Permintaan Dokter & Pengobatan</th>
                          <th>K/U</th>
                          <th>Pemeriksaan Fisik</th>
                          <th>Diagnosa</th>
                          <th>Pengobatan</th>
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
  <div class="modal-dialog modal-xl">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_cppt" id="id_cppt">
        <input type="hidden" name="visit_ID" id="visit_ID" value="<?= $_GET['no'] ?>">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $_GET['rm'] ?>">
        <div class="row">
          <div class="col-3">
            <div class="mb-3">
              <label for="cppt_date" class="form-label">
                Tanggal <span class="text-danger">*</span>
              </label>
              <input type="date" value="<?= date('Y-m-d') ?>" name="cppt_date" class="form-control" id="cppt_date" required>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label for="cppt_time" class="form-label">
                Waktu <span class="text-danger">*</span>
              </label>
              <input type="time" value="<?= date('H:i') ?>" name="cppt_time" class="form-control" id="cppt_time" required>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label for="users_entry" class="form-label">
                User Entry <span class="text-danger">*</span>
              </label>
              <input type="text" value="<?= $_SESSION['fullname'] ?>" name="users_entry" class="form-control" id="users_entry" required>
            </div>
          </div>
          <!-- Perjalanan Penyakit -->
          <div class="col-6">
            <div class="mb-3">
              <label for="perjalanan" class="form-label">
                Perjalanan Penyakit <span class="text-danger">*</span>
              </label>
              <textarea name="perjalanan" class="form-control" id="perjalanan" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Perjalanan Penyakit</strong> berisi keluhan yang dirasakan pasien,
                perkembangan gejala sejak awal muncul, serta keluhan tambahan yang disampaikan selama perawatan.<br>
                <strong>Contoh:</strong>
                <em>Pasien mengeluh nyeri perut kanan bawah sejak 3 hari, demam, mual, dan nafsu makan menurun.
                  Keluhan memberat sejak pagi hari.</em>
              </small>
            </div>
          </div>

          <!-- Tindakan -->
          <div class="col-6">
            <div class="mb-3">
              <label for="tindakan" class="form-label">
                Permintaan Dokter & Pengobatan <span class="text-danger">*</span>
              </label>
              <textarea name="tindakan" class="form-control" id="tindakan" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong> Permintaan Dokter & Pengobatan</strong> → hasil pemeriksaan fisik, tanda vital, dan hasil penunjang yang terukur.<br>
                Contoh: <em>TD: 120/80 mmHg, N: 80x/menit, S: 37°C, RR: 20x/menit, pemeriksaan laboratorium normal.</em>
              </small>
            </div>
          </div>

          <!-- K/U -->
          <div class="col-6">
            <div class="mb-3">
              <label for="ku" class="form-label">
                K/U <span class="text-danger">*</span>
              </label>
              <textarea name="ku" class="form-control" id="ku" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>K/U</strong> → interpretasi hasil pemeriksaan dan kesimpulan sementara (diagnosa kerja).<br>
                Contoh: <em>Diagnosis: Gastritis akut.</em>
              </small>
            </div>
          </div>

          <!-- Pemeriksan Fisik -->
          <div class="col-6">
            <div class="mb-3">
              <label for="pemeriksaan_fisik" class="form-label">
                Pemeriksaan Fisik <span class="text-danger">*</span>
              </label>
              <textarea name="pemeriksaan_fisik" class="form-control" id="pemeriksaan_fisik" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Pemeriksaan Fisik</strong> → rencana tindakan medis/keperawatan, pemeriksaan penunjang, terapi, atau edukasi.<br>
                Contoh: <em>Rencana terapi: Omeprazole 20 mg, edukasi pola makan, kontrol 3 hari lagi.</em>
              </small>
            </div>
          </div>

          <!-- Diagnosa -->
          <div class="col-12">
            <div class="mb-3">
              <label for="diagnosa" class="form-label">
                Diagnosa <span class="text-danger">*</span>
              </label>
              <textarea name="diagnosa" class="form-control" id="diagnosa" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Instruksi</strong> → catatan atau perintah khusus dari dokter/perawat untuk tindak lanjut.<br>
                Contoh: <em>Observasi tanda vital setiap 4 jam, monitor intake-output cairan, follow up keluhan pasien.</em>
              </small>
            </div>
          </div>

          <!-- Pengobatan -->
          <div class="col-12">
            <div class="mb-3">
              <label for="pengobatan" class="form-label">
                Pengobatan <span class="text-danger">*</span>
              </label>
              <textarea name="pengobatan" class="form-control" id="pengobatan" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Pengobatan</strong> → catatan atau perintah khusus dari dokter/perawat untuk tindak lanjut.<br>
                Contoh: <em>Observasi tanda vital setiap 4 jam, monitor intake-output cairan, follow up keluhan pasien.</em>
              </small>
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
  const apiUrl = 'controller/ranap/instruksiController?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>';
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
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_cppt}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_cppt}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "tanggal": row.cppt_date + ' ' + row.cppt_time ?? "-",
              "perjalanan": row.perjalanan ?? "-",
              "tindakan": row.tindakan ?? "-",
              "ku": row.ku ?? "-",
              "pemeriksaan_fisik": row.pemeriksaan_fisik ?? "-",
              "diagnosa": row.diagnosa ?? "-",
              "pengobatan": row.pengobatan ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "perjalanan",
          className: "text-wrap"
        },
        {
          data: "tindakan",
          className: "text-wrap"
        },
        {
          data: "ku",
          className: "text-wrap"
        },
        {
          data: "pemeriksaan_fisik",
          className: "text-wrap"
        },
        {
          data: "diagnosa",
          className: "text-wrap"
        },
        {
          data: "pengobatan",
          className: "text-wrap"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        }
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
      $('#id_cppt').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_cppt').val();

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
      fetch(apiUrl + `&id=${id}`)
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

</html>