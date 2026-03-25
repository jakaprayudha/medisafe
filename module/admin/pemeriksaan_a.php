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
            require 'menu_rme.php';
            ?>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <form id="formPemeriksaan" class="p-4 border rounded shadow-sm" method="POST">
                    <input type="hidden" name="nomor_rm" value="<?= $rm ?>">
                    <input type="hidden" name="nomor_visit" value="<?= $no ?>">
                    <input type="hidden" name="id_patient" id="id_patient" value="<?= $data['id_patient'] ?>" hidden>
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
                      <label for="visit_notes" class="form-label">Catatan Screening</label>
                      <input type="text" id="visit_notes" value="<?= $data['catatan_screening'] ?>" name="visit_notes" class="form-control bg-light" readonly>
                    </div>

                    <hr>
                    <!-- Pemeriksaan oleh Perawat -->
                    <h5>Pemeriksaan Vital Sign (Perawat)</h5>
                    <div class="row g-2">
                      <div class="col-md-4">
                        <label for="kondisi_masuk" class="form-label">Kondisi Masuk <span class="text-danger">*</span></label>
                        <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
                          <option value="<?= @$data['kondisi_masuk'] ?>"><?= @$data['kondisi_masuk'] ?></option>
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

                          value="<?= @$data['tekanan_darah'] ?>"
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
                        <input type="number" value="<?= $data['suhu'] ?>" step="0.1" id="suhu" required name="suhu" class="form-control">
                      </div>
                      <div class="col-md-4">
                        <label for="nadi" class="form-label">Nadi (x/menit) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $data['nadi'] ?>" id="nadi" name="nadi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="respirasi" class="form-label">Respirasi (x/menit) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $data['respirasi'] ?>" id="respirasi" name="respirasi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="tinggi" class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $data['tinggi_badan'] ?>" id="tinggi" name="tinggi" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="berat" class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
                        <input type="number" value="<?= $data['berat_badan'] ?>" id="berat" name="berat" required class="form-control">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="bmi" class="form-label">BMI</label>
                        <input type="text" value="<?= $data['bmi'] ?>" id="bmi" name="bmi" readonly class="form-control bg-light">
                      </div>
                      <div class="col-md-4 mt-2">
                        <label for="bmi_keterangan" class="form-label">Keterangan BMI </label>
                        <input type="text" value="<?= $data['bmi_keterangan'] ?>" id="bmi_keterangan" name="bmi_keterangan" readonly class="form-control bg-light">
                      </div>
                    </div>

                    <hr>

                    <!-- Pemeriksaan oleh Dokter -->
                    <h5>Pemeriksaan Dokter</h5>
                    <div class="mb-3">
                      <label for="keluhan_utama" class="form-label">Keluhan Utama</label>
                      <textarea id="keluhan_utama" name="keluhan_utama" rows="2" class="form-control"><?= @$data['anamnesa'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="keluhan_penyerta" class="form-label">Keluhan Penyerta</label>
                      <textarea id="keluhan_penyerta" name="keluhan_penyerta" rows="2" class="form-control"><?= @$data['keluhan_penyerta'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="riwayat_alergi" class="form-label">Riwayat Alergi</label>
                      <textarea id="riwayat_alergi" name="riwayat_alergi" rows="2" class="form-control"><?= @$data['riwayat_alergi'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="riwayat_penyakit_pribadi" class="form-label">Riwayat Penyakit Pribadi</label>
                      <textarea id="riwayat_penyakit_pribadi" name="riwayat_penyakit_pribadi" rows="2" class="form-control"><?= @$data['riwayat_penyakit_pribadi'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="riwayat_penyakit_sekarang" class="form-label">Riwayat Penyakit Keluarga</label>
                      <textarea id="riwayat_penyakit_sekarang" name="riwayat_penyakit_sekarang" rows="2" class="form-control"><?= @$data['riwayat_penyakit_sekarang'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="riwayat_pengobatan" class="form-label">Riwayat Pengobatan</label>
                      <textarea id="riwayat_pengobatan" name="riwayat_pengobatan" rows="2" class="form-control"><?= @$data['riwayat_pengobatan'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="pemeriksaan_fisik" class="form-label">Pemeriksaan Fisik</label>
                      <textarea id="pemeriksaan_fisik" name="pemeriksaan_fisik" rows="2" class="form-control"><?= @$data['pemeriksaan_fisik'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="pemeriksaan_fungsional" class="form-label">Pemeriksaan Fungsional</label>
                      <textarea id="pemeriksaan_fungsional" name="pemeriksaan_fungsional" rows="2" class="form-control"><?= @$data['pemeriksaan_fungsional'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="diagnosa" class="form-label">Diagnosa</label>
                      <textarea id="diagnosa" name="diagnosa" rows="2" class="form-control"><?= @$data['diagnosa'] ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="tindakan" class="form-label">Tindakan / Terapi / Instruksi / Rencana Rawat</label>
                      <textarea id="tindakan" name="tindakan" rows="2" class="form-control"><?= @$data['tindakan'] ?></textarea>
                    </div>

                    <div class="mb-3">
                      <label for="edukasi" class="form-label">Edukasi</label>
                      <textarea id="edukasi" name="edukasi" rows="2" class="form-control"><?= @$data['edukasi'] ?></textarea>
                    </div>

                    <div class="mb-3">
                      <label for="cara_keluar" class="form-label">Cara Keluar <span class="text-danger">*</span></label>
                      <select name="cara_keluar" id="cara_keluar" class="form-select" required>
                        <option value="<?= @$data['kondisi_keluar'] ?>"><?= @$data['kondisi_keluar'] ?></option>
                        <option value="Pulang">Pulang</option>
                        <option value="Rujuk">Rujuk</option>
                        <option value="Rawat Inap">Rawat Inap</option>
                        <option value="Meninggal">Meninggal</option>
                      </select>
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