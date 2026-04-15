<?php
$title = 'CPPT';
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
  <style>
    .cppt-text {
      max-width: 400px;
      white-space: normal;
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
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">CPPT</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/formulir_cppt?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
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
                          <th>Profesi</th>
                          <th scope="col" class="text-dark fw-normal">CPPT</th>
                          <th scope="col" class="text-dark fw-normal">Instruksi</th>
                          <th>Verifikasi</th>
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
<div class="modal fade" id="programModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_cppt" id="id_cppt">
        <input type="hidden" name="visit_ID" id="visit_ID" value="<?= $_GET['no'] ?>">
        <input type="hidden" name="id_patient" id="id_patient" value="<?= $datapatient['id_patient'] ?>">
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
              <label for="cppt_profesi" class="form-label">
                Profesi <span class="text-danger">*</span>
              </label>
              <select name="cppt_profesi" class="form-select" id="cppt_profesi" required>
                <option value="">Select Option</option>
                <option value="Dokter">Dokter</option>
                <option value="Perawat">Perawat</option>
                <option value="Apoteker">Apoteker</option>
                <option value="Paramedis">Paramedis</option>
                <option value="Tenaga Medis Lainnya">Tenaga Medis Lainnya</option>
                <option value="Tenaga Kesehatan Lainnya">Tenaga Kesehatan Lainnya</option>
              </select>
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
          <!-- Subjective -->
          <div class="col-6">
            <div class="mb-3">
              <label for="subjective" class="form-label">
                Subjective (S) <span class="text-danger">*</span>
              </label>
              <textarea name="subjective" class="form-control" id="subjective" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Subjective</strong> → keluhan utama atau perasaan pasien yang disampaikan secara verbal.<br>
                Contoh: <em>Pasien mengeluh nyeri kepala sejak 2 hari, mual, dan tidak nafsu makan.</em>
              </small>
            </div>
          </div>

          <!-- Objective -->
          <div class="col-6">
            <div class="mb-3">
              <label for="objective" class="form-label">
                Objective (O) <span class="text-danger">*</span>
              </label>
              <textarea name="objective" class="form-control" id="objective" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Objective</strong> → hasil pemeriksaan fisik, tanda vital, dan hasil penunjang yang terukur.<br>
                Contoh: <em>TD: 120/80 mmHg, N: 80x/menit, S: 37°C, RR: 20x/menit, pemeriksaan laboratorium normal.</em>
              </small>
            </div>
          </div>

          <!-- Analysis -->
          <div class="col-6">
            <div class="mb-3">
              <label for="analysis" class="form-label">
                Assessment / Analysis (A) <span class="text-danger">*</span>
              </label>
              <textarea name="analysis" class="form-control" id="analysis" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Assessment</strong> → interpretasi hasil pemeriksaan dan kesimpulan sementara (diagnosa kerja).<br>
                Contoh: <em>Diagnosis: Gastritis akut.</em>
              </small>
            </div>
          </div>

          <!-- Planning -->
          <div class="col-6">
            <div class="mb-3">
              <label for="planning" class="form-label">
                Planning (P) <span class="text-danger">*</span>
              </label>
              <textarea name="planning" class="form-control" id="planning" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Planning</strong> → rencana tindakan medis/keperawatan, pemeriksaan penunjang, terapi, atau edukasi.<br>
                Contoh: <em>Rencana terapi: Omeprazole 20 mg, edukasi pola makan, kontrol 3 hari lagi.</em>
              </small>
            </div>
          </div>

          <!-- Instruction -->
          <div class="col-12">
            <div class="mb-3">
              <label for="instruction" class="form-label">
                Instruksi <span class="text-danger">*</span>
              </label>
              <textarea name="instruction" class="form-control" id="instruction" rows="3"></textarea>
              <small class="form-text text-muted">
                <strong>Instruksi</strong> → catatan atau perintah khusus dari dokter/perawat untuk tindak lanjut.<br>
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
<?php
$id_patient = $datapatient['id_patient'];
?>
<script>
  const apiUrl = 'controller/visit/cpptController?no=<?= $_GET['no'] ?>&id_patient=<?= $id_patient ?>';

  function readMore(text, limit = 30) {
    if (!text) return "-";

    if (text.length <= limit) return text;

    const shortText = text.substring(0, limit);

    return `
    <span class="short-text">${shortText}...</span>
    <span class="full-text d-none">${text}</span>
    <a href="javascript:;" class="read-more text-primary"> Selengkapnya</a>
  `;
  }
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
              "tanggal": row.cppt_date + " " + row.cppt_time ?? "-",
              "profesi": row.cppt_profesi ?? "-",
              "cppt": `
                <div class="cppt-text">
                  <strong>S : </strong>${readMore(row.subjective)}<br>
                  <strong>O : </strong>${readMore(row.objective)}<br>
                  <strong>A : </strong>${readMore(row.analysis)}<br>
                  <strong>P : </strong>${readMore(row.planning)}
                </div>
              `,
              "instruksi": row.instruction ?? "-",
              "verifikasi": (() => {

                // ✅ kalau sudah diverifikasi
                if (row.verifikasi == 1) {
                  return '<span class="badge bg-success">✔️ Sudah Diverifikasi</span>';
                }

                // ❌ kalau yang input dokter → tidak perlu tombol
                if (row.cppt_profesi === "Dokter") {
                  return '<span class="badge bg-info">Input Dokter</span>';
                }

                // 🔥 selain dokter → tampil tombol verifikasi
                return `
                  <div class="d-flex flex-column gap-1">
                    <span class="badge bg-danger">❌ Belum Diverifikasi</span>
                    <button class="btn btn-sm btn-success verify-btn" data-id="${row.id_cppt}">
                      ✔️ Verifikasi
                    </button>
                  </div>
                `;

              })(),
            };
          });
        }
      },
      columns: [{
          data: "tanggal",
          className: "text-wrap"
        },
        {
          data: "profesi",
          className: "text-wrap"
        },
        {
          data: "cppt",
          className: "text-wrap"
        },
        {
          data: "instruksi",
          className: "text-wrap"
        },
        {
          data: "verifikasi",
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

    $(document).on("click", ".verify-btn", function() {

      let id = $(this).data("id");

      Swal.fire({
        title: "Verifikasi data ini?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Verifikasi",
        cancelButtonText: "Batal"
      }).then((result) => {

        if (result.isConfirmed) {

          fetch(apiUrl + `&verify=${id}`, {
              method: "POST"
            })
            .then(res => res.json())
            .then(res => {
              if (res.status === "success") {
                Swal.fire("Berhasil!", "Data sudah diverifikasi", "success");
                table.ajax.reload(null, false);
              }
            });

        }

      });

    });

    $(document).on("click", ".read-more", function() {

      const parent = $(this).closest("div");

      parent.find(".short-text").toggleClass("d-none");
      parent.find(".full-text").toggleClass("d-none");

      if ($(this).text().includes("Selengkapnya")) {
        $(this).text(" Sembunyikan");
      } else {
        $(this).text(" Selengkapnya");
      }

    });
  });
</script>

</html>