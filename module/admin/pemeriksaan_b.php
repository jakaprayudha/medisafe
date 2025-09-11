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
                    <div id="anamnesaCheckboxContainer" class="alert alert-primary" role="alert" style="display:none;">
                      Pilih Anamnesa :
                      <hr>
                      <!-- Checkbox akan diisi via AJAX -->
                    </div>

                    <div id="terapiCheckboxContainer" class="alert alert-success" role="alert" style="display:none;">
                      Pilih Terapi :
                      <hr>
                      <!-- Checkbox akan diisi via AJAX -->
                    </div>


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
                                  <input class="form-check-input check-terapi" type="checkbox" value="${terapi.id_terapi}" id="terapi_${terapi.id_terapi}">
                                  <label class="form-check-label" for="terapi_${terapi.id_terapi}">${terapi.terapi_name}</label>
                                </div>
                              <input type="text" class="form-control form-control-sm input-terapi flex-grow-1" 
                                  abled data-terapi-id="${terapi.id_terapi}" name="terapi[${terapi.id_terapi}]">`;
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
                    <div class="mb-3">
                      <label for="analyst" class="form-label">Analyst</label>
                      <textarea id="analyst" name="analyst" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="riwayat_konsumsi" class="form-label">Riwayat Konsumsi Obat</label>
                      <textarea id="riwayat_konsumsi" name="riwayat_konsumsi" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="pemeriksaan_fisik" class="form-label">Pemeriksaan Fisik</label>
                      <textarea id="pemeriksaan_fisik" name="pemeriksaan_fisik" rows="2" class="form-control"></textarea>
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
<script>
  $(document).ready(function() {
    // load kembali data yang baru saja disimpan
    loadVisitData($('input[name="nomor_visit"]').val());
    $('#formPemeriksaan').on('submit', function(e) {
      e.preventDefault(); // cegah reload page

      let formData = $(this).serialize(); // ambil semua data form

      $.ajax({
        url: 'controller/visit/savePemeriksaan.php', // file PHP controller kamu
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
          // optional: disable button supaya tidak double submit
          $('button[name="simpan_pemeriksaan"]').prop('disabled', true).text('Menyimpan...');
        },
        success: function(res) {
          if (res.status === 'success') {
            alert(res.message); // atau gunakan toast notification
            $('#formPemeriksaan')[0].reset(); // reset form jika perlu
            $('#anamnesaCheckboxContainer, #terapiCheckboxContainer').hide();
          } else {
            alert('Error: ' + res.message);
          }
        },
        error: function(xhr, status, error) {
          alert('Terjadi error: ' + error);
        },
        complete: function() {
          $('button[name="simpan_pemeriksaan"]').prop('disabled', false).text('Simpan Pemeriksaan');
        }
      });
    });
  });
</script>

<script>
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

          // render anamnesa
          let anamnesaContainer = $('#anamnesaCheckboxContainer');
          anamnesaContainer.show();
          anamnesaContainer.find('.anamnesa-item').remove();

          res.anamnesa.forEach(function(a) {
            let html = `
                    <div class="d-flex align-items-center mb-2 anamnesa-item">
                        <div class="form-check me-2" style="min-width: 300px;">
                            <input class="form-check-input check-ass" type="checkbox" value="${a.id_anamnesa_detail}" id="check_${a.id_anamnesa_detail}" checked>
                            <label class="form-check-label" for="check_${a.id_anamnesa_detail}">Anamnesa ${a.id_anamnesa_detail}</label>
                        </div>
                        <input type="text" class="form-control form-control-sm input-ass flex-grow-1" data-ass-id="${a.id_anamnesa_detail}" name="anamnesa[${a.id_anamnesa_detail}]" value="${a.detail}">
                    </div>`;
            anamnesaContainer.append(html);
          });

          // render terapi
          let terapiContainer = $('#terapiCheckboxContainer');
          terapiContainer.show();
          terapiContainer.find('.terapi-item').remove();

          res.terapi.forEach(function(t) {
            let html = `
                    <div class="d-flex align-items-center mb-2 terapi-item">
                        <div class="form-check me-2" style="min-width: 300px;">
                            <input class="form-check-input check-terapi" type="checkbox" value="${t.id_terapi}" id="terapi_${t.id_terapi}" checked>
                            <label class="form-check-label" for="terapi_${t.id_terapi}">Terapi ${t.id_terapi}</label>
                        </div>
                        <input type="text" class="form-control form-control-sm input-terapi flex-grow-1" data-terapi-id="${t.id_terapi}" name="terapi[${t.id_terapi}]" value="${t.detail}">
                    </div>`;
            terapiContainer.append(html);
          });

          // bind event checkbox seperti sebelumnya
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