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

$query = $koneksi->query("SELECT * FROM pasien_resume WHERE nomor_visit = '$no'");
$dataresume = $query->fetch_assoc();
// Decode JSON dari kolom 'pemeriksaan'
@$datarme = json_decode($dataresume['pemeriksaan'], true);
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
                          <option value="<?= @$datarme['kondisi_masuk'] ?>"><?= @$datarme['kondisi_masuk'] ?></option>
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

                          value="<?= @$datarme['tekanan_darah'] ?>"
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
                        <input type="number" value="<?= $datarme['suhu'] ?>" step="0.1" id="suhu" required name="suhu" class="form-control">
                      </div>
                      <div class="col-md-4">
                        <label for="nadi" class="form-label">Nadi (x/menit) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $datarme['nadi'] ?>" id="nadi" name="nadi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="respirasi" class="form-label">Respirasi (x/menit) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $datarme['respirasi'] ?>" id="respirasi" name="respirasi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="tinggi" class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $datarme['tinggi'] ?>" id="tinggi" name="tinggi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="berat" class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $datarme['berat'] ?>" id="berat" name="berat" required class="form-control">
                      </div>
                    </div>

                    <hr>

                    <!-- Pemeriksaan oleh Dokter -->
                    <h5>Pemeriksaan Dokter</h5>
                    <div class="mb-3">
                      <label for="kerangka" class="form-label">Kerangka Anamnesa</label>
                      <select name="kerangka" id="kerangka" class="form-select" required>
                        <option value="">PILIH</option>
                        <?php
                        $getanamnesa = tampildata("SELECT * FROM ms_framework_anamnesa WHERE anamnesa_status='1'");
                        foreach ($getanamnesa as $anamnesa) {
                          echo '<option value="' . $anamnesa['id_anamnesa'] . '">' . $anamnesa['anamnesa_name'] . '</option>';
                        }
                        ?>
                      </select>
                    </div>

                    <div id="anamnesaCheckboxContainer" class="alert alert-primary" role="alert">
                      Pilih Anamnesa :
                      <hr>
                      <!-- Checkbox akan diisi via AJAX -->
                    </div>
                    <script>
                      $(document).ready(function() {
                        $('#kerangka').on('change', function() {
                          let idAnamnesa = $(this).val();
                          let container = $('#anamnesaCheckboxContainer');

                          container.find('.anamnesa-item').remove(); // kosongkan dulu

                          if (idAnamnesa) {
                            $.ajax({
                              url: 'controller/visit/getDetails.php', // endpoint untuk ambil detail
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
                                      <div class="form-check me-2" style="min-width: 180px;">
                                        <input class="form-check-input check-ass" type="checkbox" value="${ass.id_ass}" id="check_${ass.id_ass}">
                                        <label class="form-check-label" for="check_${ass.id_ass}">
                                          ${ass.ass_name}
                                        </label>
                                      </div>
                                      <input type="text" class="form-control form-control-sm input-ass flex-grow-1" placeholder="Isi detail..." disabled data-ass-id="${ass.id_ass}">
                                    </div>
                                  `;
                                    container.append(html);
                                  });

                                  // bind event checkbox
                                  $('.check-ass').on('change', function() {
                                    let assId = $(this).val();
                                    let input = $(`.input-ass[data-ass-id="${assId}"]`);
                                    if ($(this).is(':checked')) {
                                      input.prop('disabled', false);
                                      input.focus();
                                    } else {
                                      input.prop('disabled', true).val('');
                                    }
                                  });
                                }
                              }
                            });
                          }
                        });
                      });
                    </script>

                    <div class="mb-3">
                      <label for="keluhan_penyerta" class="form-label">Keluhan Penyerta</label>
                      <textarea id="keluhan_penyerta" name="keluhan_penyerta" rows="2" class="form-control"><?= @$datarme['keluhan_penyerta'] ?></textarea>
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



</html>