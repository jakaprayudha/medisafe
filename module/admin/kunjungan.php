<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
// $check = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient WHERE pasien_visit.visit_ID='$no' AND ms_patient.nomor_rm='$rm'");
// $data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
// if ($data) {
//   $tanggal_lahir = new DateTime($data['patient_datebirth']);
//   $tanggal_visit = new DateTime($data['visit_date']);

//   $usia = $tanggal_lahir->diff($tanggal_visit);
// }

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
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
                    <input type="hidden" name="id_patient" id="id_patient">
                    <input type="hidden" id="terapiObat" name="terapiObat">
                    <input type="hidden" id="terapiNonObat" name="terapiNonObat">
                    <input type="hidden" id="bmhp" name="bmhp">
                    <input type="hidden" id="tglDaftar" name="tglDaftar">
                    <input type="hidden" name="typeRujukan" id="typeRujukan" value="normal">
                    <input type="hidden" name="noKartu" id="noKartu">
                    <input type="hidden" name="kdPoli" id="kode_poli">
                    <input type="hidden" name="nmPoli" id="nama_poli">
                    <input type="hidden" name="kdDokter" id="kdDokter">
                    <input type="hidden" name="nmDokter" id="nmDokter">
                    <input type="hidden" name="noKunjungan" id="noKunjungan">
                    <input type="hidden" name="kdTkp" value="10">
                    <h4 class="mb-3">Form Pemeriksaan Medis</h4>
                    <!-- Data Pasien -->
                    <?php
                    require 'card-pasien.php';
                    ?>

                    <div class="mb-3">
                      <label for="visit_notes" class="form-label">Catatan Screening</label>
                      <input type="text" id="visit_notes" name="visit_notes" class="form-control bg-light" readonly>
                    </div>

                    <hr>
                    <!-- Pemeriksaan oleh Perawat -->
                    <h5>Pemeriksaan Vital Sign (Perawat)</h5>
                    <div class="row g-2">
                      <div class="col-md-2">
                        <label for="tinggiBadan">Tinggi Badan <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input
                            type="number"
                            class="form-control"
                            name="tinggiBadan"
                            id="tinggiBadan"
                            min="30"
                            max="250"
                            step="0.1"
                            required>
                          <span class="input-group-text">CM</span>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <label for="beratBadan">Berat Badan <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input
                            type="number"
                            class="form-control"
                            name="beratBadan"
                            id="beratBadan"
                            min="1"
                            max="300"
                            step="0.1"
                            required>
                          <span class="input-group-text">Kg</span>
                        </div>
                      </div>

                      <!-- Lingkar Perut -->
                      <div class="col-md-2">
                        <label for="lingkarPerut">Lingkar Perut</label>
                        <div class="input-group mb-2">
                          <input
                            type="number"
                            class="form-control"
                            name="lingkarPerut"
                            id="lingkarPerut"
                            min="30"
                            max="200"
                            step="0.1">
                          <span class="input-group-text">CM</span>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <label for="sistole">Sistole <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input type="number"
                            class="form-control"
                            name="sistole"
                            id="sistole"
                            min="50"
                            max="300"
                            step="1"
                            required>
                          <span class="input-group-text">mmHg</span>
                        </div>
                      </div>

                      <!-- Diastole -->
                      <div class="col-md-2">
                        <label for="diastole">Diastole <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input type="number"
                            class="form-control"
                            name="diastole"
                            id="diastole"
                            min="30"
                            max="200"
                            step="1"
                            required>
                          <span class="input-group-text">mmHg</span>
                        </div>
                      </div>

                      <!-- Respiratory Rate -->
                      <div class="col-md-2">
                        <label for="respRate">Respiratory Rate <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input type="number"
                            class="form-control"
                            name="respRate"
                            id="respRate"
                            min="5"
                            max="60"
                            step="1"
                            required>
                          <span class="input-group-text">/Minute</span>
                        </div>
                      </div>

                      <!-- Heart Rate -->
                      <div class="col-md-2">
                        <label for="heartRate">Heart Rate <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input type="number"
                            class="form-control"
                            name="heartRate"
                            id="heartRate"
                            min="30"
                            max="220"
                            step="1"
                            required>
                          <span class="input-group-text">BPM</span>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <label for="suhu">Suhu <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                          <input
                            type="number"
                            class="form-control"
                            name="suhu"
                            id="suhu"
                            placeholder="Suhu"
                            min="25"
                            max="45"
                            step="0.1">
                          <span class="input-group-text"><b>℃</b></span>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <label for="saturasi">Saturasi</label>
                        <div class="input-group mb-2">
                          <input type="number" id="saturasi" name="saturasi" class="form-control">
                          <span class="input-group-text"><b>(Spo2 %)</b></span>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <label for="bmi">BMI</label>
                        <div class="input-group mb-2">
                          <input type="text" id="bmi" name="bmi" readonly class="form-control bg-light">
                          <span class="input-group-text"><b>kg/m2</b></span>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <label for="bmiKet">Keterangan BMI</label>
                        <div class="input-group mb-2">
                          <input type="text" id="bmiKet" name="bmi_keterangan" readonly class="form-control bg-light">
                          <span class="input-group-text"><b>Ket</b></span>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="kondisi_masuk" class="form-label"> Prognosa <span class="text-danger">*</span></label>
                        <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label for="kdSadar" class="form-label">Kesadaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="kdSadar" id="kdSadar" required>
                        </select>
                      </div>
                    </div>
                    <hr>
                    <h5>Pemeriksaan Dokter</h5>
                    <div class="mb-3">
                      <label for="keluhan_penyerta" class="form-label required">Keluhan Utama</label>
                      <textarea id="keluhan_penyerta" name="keluhan_penyerta" rows="2" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="keluhan_penyerta" class="form-label">Amnanesa</label>
                      <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" rows="2" required></textarea>
                    </div>
                    <div class="col-12" id="formalergi">
                      <div class="row">
                        <div class="col-md-4">
                          <label class="form-label form-label-sm">
                            Alergi Makan
                          </label>
                          <select class="form-select" name="alergiMakan" id="alergiMakan"></select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label form-label-sm">
                            Alergi Udara
                          </label>
                          <select class="form-select" name="alergiUdara" id="alergiUdara"></select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label form-label-sm">
                            Alergi Obat
                          </label>
                          <select class="form-select" name="alergiObat" id="alergiObat"></select>
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="col-12">
                      <div class="row g-3">
                        <div class="col-md-12">
                          <label class="form-label fw-semibold">
                            Diagnosa Utama <span class="text-danger">*</span>
                          </label>
                          <select id="diag1" name="diag1" class="form-select" required></select>
                          <input type="hidden" id="nmDiag1" name="nmDiag1">
                          <input type="hidden" id="kdnonSpesialis1">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">
                            Diagnosa Sekunder 1
                          </label>
                          <select id="diag2" name="diag2" class="form-select"></select>
                          <input type="hidden" id="nmDiag2" name="nmDiag2">
                          <input type="hidden" id="kdnonSpesialis2">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">
                            Diagnosa Sekunder 2
                          </label>
                          <select id="diag3" name="diag3" class="form-select"></select>
                          <input type="hidden" id="nmDiag3" name="nmDiag3">
                          <input type="hidden" id="kdnonSpesialis3">
                        </div>

                      </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                      <label for="tindakan" class="form-label">Tindakan / Terapi / Instruksi / Rencana Rawat</label>
                      <textarea id="tindakan" name="tindakan" rows="2" class="form-control"><?= @$data['tindakan'] ?></textarea>
                    </div>

                    <!-- <div class="mb-3">
                      <label for="edukasi" class="form-label">Edukasi</label>
                      <textarea id="edukasi" name="edukasi" rows="2" class="form-control"><?= @$data['edukasi'] ?></textarea>
                    </div> -->
                    <div class="col-12">
                      <div class="row mb-3">
                        <div class="col-2">
                          <label for="" class="col-form-label col-form-label-sm">Status Pulang<span class="text-danger">*</span></label>
                        </div>
                        <div class="col-10">
                          <select class="form-select" id="kdStatusPulang" name="kdStatusPulang">
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 d-none" id="rujukvertikal">
                      <hr>
                      <div class="row mb-3">
                        <div class="col-2">
                          <label class="col-form-label col-form-label-sm">
                            Status Rujukan <span class="text-danger">*</span>
                          </label>
                        </div>
                        <div class="col-10">
                          <div class="row g-2">
                            <div class="col-md-6">
                              <input type="radio" class="btn-check" name="kdStatusRujuk"
                                id="rujukSpesialis" value="SP" required>
                              <label class="btn btn-outline-primary w-100 text-start p-3"
                                for="rujukSpesialis">
                                <strong>Rujukan Spesialis</strong><br>
                                <small class="subtext">
                                  Rujukan ke sub spesialis sesuai indikasi medis
                                </small>
                              </label>
                            </div>
                            <div class="col-md-6">
                              <input type="radio" class="btn-check" name="kdStatusRujuk"
                                id="rujukKhusus" value="KH" required>
                              <label class="btn btn-outline-success w-100 text-start p-3"
                                for="rujukKhusus">
                                <strong>Rujukan Khusus</strong><br>
                                <small class="subtext">
                                  Rujukan dengan kriteria atau program khusus
                                </small>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 d-none" id="formrujukanvertikal">
                      <!-- Kategori -->
                      <div class="row mb-3">
                        <div class="col-2">
                          <label for="kdKategori" class="col-form-label">
                            Kategori <span class="text-danger">*</span>
                          </label>
                        </div>
                        <div class="col-5">
                          <label for="kdKategori" class="form-label fw-semibold">
                            Spesialis
                          </label>
                          <select class="form-select"
                            id="kdKategori"
                            name="kdKategori"
                            required>
                          </select>
                          <input type="hidden" id="nmKategori" name="nmKategori">
                        </div>
                        <div class="col-2 d-none" id="subspesialis">
                          <label for="kdKategori" class="form-label fw-semibold">
                            Sub Spesialis
                          </label>
                          <select class="form-select"
                            id="kdsubspesialis"
                            name="kdSubSpesialis1">
                          </select>
                          <input type="hidden" id="nmSubSpesialis1" name="nmSubSpesialis1">
                        </div>
                        <div class="col-3 d-none" id="sarana">
                          <div class="form-check mb-2">
                            <input class="form-check-input"
                              type="checkbox"
                              id="useSarana"
                              value="1">
                            <label class="form-check-label fw-semibold" for="useSarana">
                              Sarana
                            </label>
                          </div>
                          <select class="form-select"
                            id="kdSarana">
                          </select>
                          <input type="hidden"
                            name="kdSarana"
                            id="kdSaranaHidden"
                            value="9">
                        </div>
                        <div class="col-5">
                          <label for="kdKategori" class="form-label fw-semibold">
                            Tgl Kunjung
                          </label>
                          <div class="input-group">
                            <input type="date"
                              class="form-control"
                              id="tglRujukan"
                              name="tglRujukan"
                              required>
                            <button type="button" id="btnCariFaskes"
                              class="btn btn-primary">
                              <i class="bi bi-search"></i> Cari Faskes
                            </button>
                          </div>
                        </div>
                      </div>
                      <!-- Alasan -->
                      <div class="row mb-3 d-none" id="alasanrujuk">
                        <div class="col-2">
                          <label for="alasanRujukan" class="col-form-label">
                            Alasan <span class="text-danger">*</span>
                          </label>
                        </div>
                        <div class="col-10">
                          <textarea class="form-control"
                            id="alasanRujukan"
                            name="alasanRujukan"
                            rows="3"
                            placeholder="Masukkan alasan rujukan..."
                            required></textarea>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="row mb-3">
                          <div class="col-2">
                            <label for="" class="col-form-label col-form-label-sm">Faskes<span class="text-danger">*</span></label>
                          </div>
                          <div class="col-10">
                            <input type="text" id="nmfaskes" name="nmfaskes" class="form-control" readonly>
                            <input type="hidden" name="kdppk" id="kdfaskes">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 d-none" id="formTacc">
                      <div class="row mb-3">
                        <div class="col-2">
                          <label for="kdKategori" class="col-form-label">
                            TACC <span class="text-danger">*</span>
                          </label>
                        </div>
                        <div class="col-3">
                          <label for="kdTacc" class="form-label fw-semibold">
                            TACC
                          </label>
                          <select class="form-select" id="kdTacc" name="kdTacc">
                            <option value="-1">Tanpa TACC</option>
                            <option value="1">Time</option>
                            <option value="2">Age</option>
                            <option value="3">Complication</option>
                            <option value="4">Comorbidity</option>
                          </select>
                        </div>
                        <div class="col-7">
                          <label for="alasanTacc" class="form-label fw-semibold">
                            Alasan
                          </label>
                          <input type="text" class="form-control"
                            id="alasanTacc"
                            name="alasanTacc">
                          </input>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 d-none" id="noLaporanPolisi">
                      <div class="row mb-3">
                        <div class="col-2">
                          <label for="kdKategori" class="col-form-label">
                            Nomor LP <span class="text-danger">*</span>
                          </label>
                        </div>
                        <div class="col-10">
                          <input type="text"
                            class="form-control"
                            placeholder="Masukan Nomor Laporan Polisi Disini..."
                            id="nomorLp"
                            name="nomorLp">
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="btn-print d-none">
                      <button type="button" class="btn btn-info" id="btn-print">
                        Print
                      </button>
                    </div>
                    <hr>
                    <div class="d-grid">
                      <button type="button" id="simpan_pemeriksaan" class="btn btn-primary">Simpan Pemeriksaan</button>
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

  <div class="modal fade" id="modalFaskes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
      <div class="modal-content shadow-lg rounded-3 border-0">
        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-hospital fs-3"></i>
            Daftar Fasilitas Kesehatan
          </h5>
          <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal">X</button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle text-center" id="tableRujukan">
              <thead class="table-dark text-white">
                <tr>
                  <th>No</th>
                  <th>Faskes</th>
                  <th>Kelas</th>
                  <th>Kantor Cabang</th>
                  <th>Alamat</th>
                  <th>Telp</th>
                  <th>Jarak</th>
                  <th>Total Rujukan</th>
                  <th>Kapasitas</th>
                  <th>%</th>
                  <th>Jadwal</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data akan diisi JS -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script src="controller/admisi/helper.js"></script>
  <script src="controller/doctor/kunjunganrj.js"></script>
</body>




</html>