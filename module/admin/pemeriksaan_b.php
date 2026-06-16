<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor WHERE visit_ID='$no' AND nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
if ($data) {
  $tanggal_lahir = new DateTime($data['patient_datebirth']);
  $tanggal_visit = new DateTime($data['visit_date']);

  $usia = $tanggal_lahir->diff($tanggal_visit);
}

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
            <?php
            require 'menu_rmeb.php';
            ?>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <form id="formPemeriksaan" class="p-4 border rounded shadow-sm" method="POST">
                    <input type="hidden" name="nomor_rm" value="<?= $rm ?>">
                    <input type="hidden" name="nomor_visit" value="<?= $no ?>">
                    <h4 class="mb-3">Form Pemeriksaan Medis</h4>
                    <!-- Data Pasien -->
                    <div class="row">
                      <div class="col-3">
                        <div class="mb-3">
                          <label for="patient_name" class="form-label">Nama Pasien</label>
                          <input type="text" value="<?= $data['patient_name'] ?>" id="patient_name" readonly name="patient_name" class="form-control bg-light">
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="mb-3">
                          <label for="patient_gender" class="form-label">Gender</label>
                          <input type="text" value="<?= $data['patient_gender'] ?>" id="patient_gender" name="patient_gender" class="form-control bg-light" readonly>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="mb-3">
                          <label for="usia" class="form-label">Usia</label>
                          <input type="text" value="<?php echo  $usia->y . " tahun " . $usia->m . " bulan " . $usia->d . " hari"; ?>" id="usia" name="usia" class="form-control bg-light" readonly>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="mb-3">
                          <label for="doctor_name" class="form-label">Dokter</label>
                          <input type="text" value="<?= $data['doctor_name'] ?>" id="doctor_name" name="dokter" class="form-control bg-light" readonly>
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="visit_notes" class="form-label">Catatan Khusus</label>
                      <input type="text" id="visit_notes" value="<?= $data['visit_notes'] ?>" name="visit_notes" class="form-control bg-light" readonly>
                    </div>

                    <hr>
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
                    <hr>

                    <!-- Pemeriksaan oleh Dokter -->
                    <h5>Pemeriksaan Dokter</h5>
                    <div class="mb-3">
                      <label for="kerangka" class="form-label">Kerangka Anamnesa</label>
                      <select name="kerangka" id="kerangka" class="form-select">
                        <option value="">PILIH</option>
                        <?php
                        $getanamnesa = tampildata("SELECT * FROM ms_framework_anamnesa WHERE anamnesa_status='1'");
                        foreach ($getanamnesa as $anamnesa) {
                          echo '<option value="' . $anamnesa['id_anamnesa'] . '">' . $anamnesa['anamnesa_name'] . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div id="anamnesaCheckboxContainer" class="alert alert-primary" role="alert" style="display:none;">
                      Pilih Anamnesa :
                      <hr>
                      <!-- Checkbox akan diisi via AJAX -->
                    </div>

                    <div class="mb-3">
                      <label for="anamnesa_text" class="form-label">Anamnesa</label>
                      <textarea id="anamnesa_text" name="anamnesa_text" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="pemeriksaan_fisik" class="form-label">Pemeriksaan Fisik</label>
                      <textarea id="pemeriksaan_fisik" name="pemeriksaan_fisik" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="analyst" class="form-label">Analyst</label>
                      <textarea id="analyst" name="analyst" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="diagnosa" class="form-label">Diagnosa</label>
                      <textarea id="diagnosa" name="diagnosa" rows="2" class="form-control"></textarea>
                    </div>
                    <div id="terapiCheckboxContainer" class="alert alert-success" role="alert" style="display:none;">
                      Pilih Terapi :
                      <hr>
                      <!-- Checkbox akan diisi via AJAX -->
                    </div>
                    <div class="alert alert-info" role="alert">
                      <div class="row">
                        <div class="col-lg-12 d-flex align-items-stretch">
                          <div class="card w-100">
                            <div class="card-body p-4">
                              <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Data Permintaan Farmasi</h5>
                                <!-- Grup tombol di sisi kanan -->
                                <div class="d-flex ms-auto gap-2">
                                  <button class="btn btn-primary" type="button" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                                </div>
                              </div>
                              <div class="table-responsive" data-simplebar>
                                <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                                  <thead>
                                    <tr>
                                      <th class="text-dark fw-normal">Item</th>
                                      <th scope="col" class="text-dark fw-normal">Qty</th>
                                      <th scope="col" class="text-dark fw-normal">Signa</th>
                                      <th scope="col" class="text-dark fw-normal">Harga</th>
                                      <th scope="col" class="text-dark fw-normal">Catatan</th>
                                      <th scope="col" class="text-dark fw-normal text-center">Status</th>
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
                    <div class="mb-3">
                      <label for="riwayat_konsumsi" class="form-label">Riwayat Konsumsi Obat</label>
                      <textarea id="riwayat_konsumsi" name="riwayat_konsumsi" rows="2" class="form-control"></textarea>
                    </div>
                    <!-- Tombol Submit -->
                    <div class="d-grid">
                      <button type="submit" name="simpan_pemeriksaan" class="btn btn-primary">Simpan Pemeriksaan</button>
                    </div>
                  </form>
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
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Nama Item <span class="text-danger">*</span> </label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select js-example-basic-item" required>
                <option value="">Select Option</option>
                <?php
                $getbarang = tampildata("SELECT * FROM ms_pharmacy WHERE pharmacy_status='1'");
                ?>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>" data-harga="<?= $barang['pharmacy_sale']; ?>"><?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Harga Dasar</label>
              <input type="number" id="harga" name="harga" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Qty</label>
              <input type="number" id="qty" name="qty" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Signa</label>
              <input type="text" id="signa" name="signa" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="5"></textarea>
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
  $(document).ready(function() {
    $('#id_pharmacy').select2({
      dropdownParent: $('#programModal'),
      width: '100%'
    });

    $('#id_pharmacy').on('change', function() {
      let harga = $(this).find(':selected').data('harga') || 0;
      $('#harga').val(harga);
    });
  });
</script>
<script>
  const apiUrl = 'controller/visit/permintaanFarmasi?no=<?= $_GET['no'] ?>';

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
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_permintaan_farmasi}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_permintaan_farmasi}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "nama": row.pharmacy_name_generic + '/' + row.pharmacy_name_trade ?? "-",
              "qty": row.qty ?? "-",
              "signa": row.signa ?? "-",
              "harga": row.harga ?? "-",
              "catatan": row.catatan_permintaan ?? "-",
              "status": row.status_permintaan === '1' ?
                '<span class="badge bg-success text-center d-block">Selesai</span>' : '<span class="badge bg-danger text-center d-block">Belum proses</span>'
            };
          });
        }
      },
      columns: [{
          data: "nama"
        },
        {
          data: "qty"
        },
        {
          data: "signa"
        },
        {
          data: "harga"
        },
        {
          data: "catatan"
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
      $('#id_permintaan_farmasi').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_permintaan_farmasi').val();

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

            // isi otomatis field biasa
            for (let key in d) {
              if (key !== "id_pharmacy" && key !== "harga") { // skip select & harga
                $(`[name="${key}"]`).val(d[key]);
              }
            }

            // isi dropdown select2
            $('#id_pharmacy').val(d.id_pharmacy).trigger("change");

            // isi harga langsung dari response DB
            $('#harga').val(d.harga);

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
  $(document).ready(function() {
    $('#kerangka').on('change', function() {
      let idAnamnesa = $(this).val();

      let anamnesaContainer = $('#anamnesaCheckboxContainer');
      let terapiContainer = $('#terapiCheckboxContainer');

      // kosongkan container
      anamnesaContainer.find('.anamnesa-item').remove();
      terapiContainer.find('.terapi-item').remove();

      if (idAnamnesa) {
        anamnesaContainer.show();
        terapiContainer.show();

        // ==== Load Anamnesa Detail ====
        $.ajax({
          url: 'controller/visit/getDetails.php',
          type: 'GET',
          data: {
            id_anamnesa: idAnamnesa
          },
          dataType: 'json',
          success: function(res) {
            if (res.status === 'success') {
              res.data.forEach(function(ass) {
                let html = `
                                      <div class="d-flex align-items-center mb-2 anamnesa-item">
                                        <div class="form-check me-2" style="min-width: 300px;">
                                          <input class="form-check-input check-ass" type="checkbox" value="${ass.id_ass}" id="check_${ass.id_ass}">
                                          <label class="form-check-label" for="check_${ass.id_ass}">${ass.ass_name}</label>
                                        </div>
                                        <input type="text" class="form-control form-control-sm input-ass flex-grow-1" 
                                              placeholder="Isi detail..." disabled data-ass-id="${ass.id_ass}" name="anamnesa[${ass.id_ass}]">
                                      </div>`;
                anamnesaContainer.append(html);
              });

              // bind event checkbox
              $('.check-ass').on('change', function() {
                let assId = $(this).val();
                let input = $(`.input-ass[data-ass-id="${assId}"]`);
                input.prop('disabled', !$(this).is(':checked'));
                if (!$(this).is(':checked')) input.val('');
              });
            }
          }
        });

        // ==== Load Terapi ====
        $.ajax({
          url: 'controller/visit/getTherapi.php',
          type: 'GET',
          data: {
            id_anamnesa: idAnamnesa
          },
          dataType: 'json',
          success: function(res) {
            if (res.status === 'success') {
              res.data.forEach(function(terapi) {
                let html = `
                                    <div class="d-flex align-items-center mb-2 terapi-item">
                                      <div class="form-check me-2" style="min-width: 300px;">
                                        <input class="form-check-input check-terapi" 
                                              type="checkbox" 
                                              value="${terapi.id_terapi}" 
                                              id="terapi_${terapi.id_terapi}">
                                        <label class="form-check-label" for="terapi_${terapi.id_terapi}">
                                          ${terapi.terapi_name}
                                        </label>
                                      </div>
                                      <input type="text" 
                                            class="form-control form-control-sm input-terapi flex-grow-1" 
                                            data-terapi-id="${terapi.id_terapi}" 
                                            name="terapi[${terapi.id_terapi}]" 
                                            placeholder="Isi detail..." 
                                            disabled>
                                    </div>`;
                terapiContainer.append(html);
              });

              // bind event checkbox terapi
              $('.check-terapi').on('change', function() {
                let terapiId = $(this).val();
                let input = $(`.input-terapi[data-terapi-id="${terapiId}"]`);
                input.prop('disabled', !$(this).is(':checked'));
                if (!$(this).is(':checked')) input.val('');
              });
            }
          }
        });

      } else {
        anamnesaContainer.hide();
        terapiContainer.hide();
      }
    });
  });
</script>
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
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {
    const nomorVisit = $('input[name="nomor_visit"]').val();

    // Load data awal
    loadVisitData(nomorVisit);

    // Submit form pemeriksaan
    $('#formPemeriksaan').on('submit', function(e) {
      e.preventDefault();

      // Buat formData manual supaya lebih fleksibel
      let formData = $(this).serializeArray();

      // Hapus semua field terapi dulu (biar tidak ikut yang uncheck)
      formData = formData.filter(f => !f.name.startsWith("terapi"));

      // Ambil hanya terapi yang dicentang
      $('.check-terapi:checked').each(function() {
        let terapiId = $(this).val();
        let detail = $(`.input-terapi[data-terapi-id="${terapiId}"]`).val();
        formData.push({
          name: `terapi[${terapiId}]`,
          value: detail
        });
      });

      $.ajax({
        url: 'controller/visit/savePemeriksaan.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
          $('button[name="simpan_pemeriksaan"]')
            .prop('disabled', true)
            .text('Menyimpan...');
        },
        success: function(res) {
          if (res.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: res.message,
              timer: 2000,
              showConfirmButton: false
            });
            loadVisitData($('input[name="nomor_visit"]').val());
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: res.message
            });
          }
        },
        error: function(xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan: ' + error
          });
        },
        complete: function() {
          $('button[name="simpan_pemeriksaan"]')
            .prop('disabled', false)
            .text('Simpan Pemeriksaan');
        }
      });
    });
  });

  function loadVisitData(nomor_visit) {
    $.ajax({
      url: 'controller/visit/getasesment.php',
      type: 'GET',
      data: {
        nomor_visit: nomor_visit
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          let data = res.pemeriksaan;

          // isi form pemeriksaan
          $('#kondisi_masuk').val(data.kondisi_masuk);
          $('#tekanan_darah').val(data.tekanan_darah);
          $('#suhu').val(data.suhu);
          $('#nadi').val(data.nadi);
          $('#respirasi').val(data.respirasi);
          $('#tinggi').val(data.tinggi);
          $('#berat').val(data.berat);
          $('#analyst').val(data.analyst);
          $('#riwayat_konsumsi').val(data.riwayat_konsumsi);
          $('#pemeriksaan_fisik').val(data.pemeriksaan_fisik);
          $('#bmi').val(data.bmi);
          $('#bmi_ket').val(data.bmi_ket);
          $('#diagnosa').val(data.diagnosa);
          $('#anamnesa_text').val(data.anamnesa);

          // render anamnesa
          let anamnesaContainer = $('#anamnesaCheckboxContainer');
          anamnesaContainer.show().find('.anamnesa-item').remove();

          res.anamnesa.forEach(function(a) {
            anamnesaContainer.append(`
              <div class="d-flex align-items-center mb-2 anamnesa-item">
                <div class="form-check me-2" style="min-width: 300px;">
                  <input class="form-check-input check-ass" 
                         type="checkbox" 
                         value="${a.id_anamnesa_detail}" 
                         id="check_${a.id_anamnesa_detail}" checked>
                  <label class="form-check-label" for="check_${a.id_anamnesa_detail}">
                    ${a.ass_name}
                  </label>
                </div>
                <input type="text" 
                       class="form-control form-control-sm input-ass flex-grow-1" 
                       data-ass-id="${a.id_anamnesa_detail}" 
                       name="anamnesa[${a.id_anamnesa_detail}]" 
                       value="${a.detail}">
              </div>
            `);
          });

          // render terapi
          let terapiContainer = $('#terapiCheckboxContainer');
          terapiContainer.show().find('.terapi-item').remove();

          res.terapi.forEach(function(t) {
            terapiContainer.append(`
              <div class="d-flex align-items-center mb-2 terapi-item">
                <div class="form-check me-2" style="min-width: 300px;">
                  <input class="form-check-input check-terapi" 
                         type="checkbox" 
                         value="${t.id_terapi}" 
                         id="terapi_${t.id_terapi}" checked>
                  <label class="form-check-label" for="terapi_${t.id_terapi}">
                    ${t.terapi_name}
                  </label>
                </div>
                <input type="text" 
                       class="form-control form-control-sm input-terapi flex-grow-1" 
                       data-terapi-id="${t.id_terapi}" 
                       name="terapi[${t.id_terapi}]" 
                       value="${t.detail}">
              </div>
            `);
          });

          // event handler checkboxes
          $('.check-ass').on('change', function() {
            let assId = $(this).val();
            let input = $(`.input-ass[data-ass-id="${assId}"]`);
            input.prop('disabled', !$(this).is(':checked'));
            if (!$(this).is(':checked')) input.val('');
          });

          $('.check-terapi').on('change', function() {
            let terapiId = $(this).val();
            let input = $(`.input-terapi[data-terapi-id="${terapiId}"]`);
            input.prop('disabled', !$(this).is(':checked'));
            if (!$(this).is(':checked')) input.val('');
          });
        }
      }
    });
  }
</script>

</html>