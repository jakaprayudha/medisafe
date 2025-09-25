<?php
session_start();
$title = 'List Pasien';
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
                    <h5 class="card-title fw-semibold">Data Pasien</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <!-- Tombol -->
                      <button type="button" class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Kembali
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">TTL</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Agama</th>
                          <th scope="col" class="text-dark fw-normal">No.Handphone</th>
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
        <input type="hidden" name="id_visit" id="id_visit">
        <input type="hidden" name="id_patient" id="id_patient"> <!-- 🔹 dari klik add -->
        <input type="hidden" name="user" value="<?= $_SESSION['fullname'] ?>" id="user">
        <div class="row">
          <div class="col-6">
            <!-- Data Registrasi -->
            <h5>Registrasi Layanan</h5>
            <div class="mb-3">
              <label class="form-label required">Layanan</label>
              <select name="source_hub" id="source_hub" class="form-select" required>
                <option value="Poliklinik">Poliklinik</option>
                <option value="UGD">UGD</option>
                <option value="Rawat Inap">Rawat Inap</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label required">Layanan (Poli)</label>
              <select name="id_poli" id="id_poli" class="form-select" required>
                <option value="">PILIH</option>
                <?php
                $getpoli = tampildata("SELECT * FROM ms_poli WHERE poli_status='1'");
                foreach ($getpoli as $poli) :
                ?>
                  <option value="<?= $poli['id_poli'] ?>"><?= $poli['poli_name'] ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label required">Dokter</label>
              <select name="id_doctor" id="id_doctor" class="form-select" required>
                <option value="">PILIH</option>
                <?php
                $getdoc = tampildata("SELECT * FROM ms_doctor WHERE doctor_status='1'");
                foreach ($getdoc as $doc) :
                ?>
                  <option value="<?= $doc['id_doctor'] ?>"><?= $doc['doctor_name'] ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="visit_notes" id="visit_notes" class="form-control" rows="5"></textarea>
            </div>
          </div>
          <div class="col-6">
            <!-- Pemeriksaan oleh Perawat -->
            <h5>Pemeriksaan Vital Sign (Perawat)</h5>
            <div class="row g-2">
              <div class="col-md-4">
                <label for="kondisi_masuk" class="form-label">Kondisi Masuk <span class="text-danger">*</span></label>
                <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
                  <option value="Baik">Baik</option>
                  <option value="Lemah">Lemah</option>
                  <option value="Sedang">Sedang</option>
                  <option value="Buruk">Buruk</option>
                  <option value="Gawat Darurat">Gawat Darurat</option>
                  <option value="Tidak Sadar">Tidak Sadar</option>
                </select>
              </div>
              <div class="col-md-4">
                <label for="tekanan_darah" class="form-label">Tekanan Darah (mmHg) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  id="tekanan_darah"
                  name="tekanan_darah"
                  class="form-control"
                  maxlength="7"
                  required>
              </div>
              <script>
                document.getElementById("tekanan_darah").addEventListener("input", function() {
                  let value = this.value.replace(/[^\d]/g, ''); // hanya angka
                  if (value.length > 3) {
                    value = value.slice(0, 3) + '/' + value.slice(3, 6); // sisipkan '/'
                  }
                  this.value = value;
                });
              </script>
              <div class="col-md-4">
                <label for="suhu" class="form-label">Suhu (°C) <span class="text-danger">*</span></label>
                <input type="number" step="0.1" id="suhu" required name="suhu" class="form-control">
              </div>
              <div class="col-md-4">
                <label for="nadi" class="form-label">Nadi (x/menit) <span class="text-danger">*</span></label>
                <input type="number" id="nadi" name="nadi" required class="form-control">
              </div>
              <div class="col-md-4 mt-2">
                <label for="respirasi" class="form-label">Respirasi (x/menit) <span class="text-danger">*</span></label>
                <input type="number" id="respirasi" name="respirasi" required class="form-control">
              </div>
              <div class="col-md-4 mt-2">
                <label for="tinggi" class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                <input type="number" id="tinggi" name="tinggi" required class="form-control">
              </div>
              <div class="col-md-4 mt-2">
                <label for="berat" class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
                <input type="number" id="berat" name="berat" required class="form-control">
              </div>
              <div class="col-md-4 mt-2">
                <label for="bmi" class="form-label">BMI <span class="text-danger">*</span></label>
                <input type="number" readonly id="bmi" name="bmi" required class="form-control bg-light">
              </div>
              <div class="col-md-4 mt-2">
                <label class="form-label">Keterangan</label>
                <input type="text" id="bmi_ket" name="bmi_ket" readonly class="form-control bg-light">
              </div>
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
  function hitungBMI() {
    let tinggi = parseFloat(document.getElementById("tinggi").value);
    let berat = parseFloat(document.getElementById("berat").value);
    let bmiField = document.getElementById("bmi");
    let ketField = document.getElementById("bmi_ket");

    if (tinggi > 0 && berat > 0) {
      let tinggiM = tinggi / 100; // ubah cm ke meter
      let bmi = (berat / (tinggiM * tinggiM)).toFixed(1);
      bmiField.value = bmi;

      let ket = "";
      if (bmi < 18.5) ket = "Kurus (Underweight)";
      else if (bmi >= 18.5 && bmi < 25) ket = "Normal";
      else if (bmi >= 25 && bmi < 30) ket = "Berat Badan Lebih (Overweight)";
      else ket = "Obesitas";

      ketField.value = ket;
    } else {
      bmiField.value = "";
      ketField.value = "";
    }
  }

  document.getElementById("tinggi").addEventListener("input", hitungBMI);
  document.getElementById("berat").addEventListener("input", hitungBMI);
</script>
<script>
  const apiUrl = 'controller/visit/addListController';
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
									<a class="btn btn-primary edit-btn" href="javascript:;" data-id="${row.id_patient}">
											<i class="fas fa-plus"></i>
									</a>
								</div>
							</div>
                    `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "ttl": row.patient_datebirth + '/' + row.patient_place ?? "-",
              "gender": row.patient_gender ?? "-",
              "agama": row.patient_religion ?? "-",
              "phone": row.patient_phone ?? "-"
            };
          });
        }
      },
      columns: [{
          data: "rm"
        }, {
          data: "name"
        },
        {
          data: "ttl"
        },
        {
          data: "gender"
        },
        {
          data: "agama"
        },
        {
          data: "phone"
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
      $('#id_patient').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit hanya POST
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));

      fetch(apiUrl, {
          method: 'POST',
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

            $('#programModal .modal-title').text('Registrasi Data');
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
  });
</script>

</html>