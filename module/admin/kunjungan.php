<?php
/**
 * Pemeriksaan Medis rawat jalan (dokter/perawat) + kunjungan PCare. Logika di controller/doctor/kunjunganrj.js
 * (ambil data: controller/admisi/services/doctor/getDataKunjungan.php, simpan: .../insertKunjungan.php).
 * Semua input/ID/name dipertahankan karena dipakai JS & backend; hanya tata letak yang diubah.
 * Perbaikan: no RM & id kunjungan dicetak mentah ke input (XSS); $_GET tanpa default; flatpickr dimuat dua kali
 * (sudah ada di head.php); label "Anamnese" menunjuk ke field lain; $data['tindakan'] tidak pernah didefinisikan;
 * tekan Enter di salah satu kolom men-submit form biasa -> halaman reload & isian hilang; Saturasi tanpa batas nilai
 * (0% bisa tersimpan).
 */
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .pm .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .pm .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #dbeafe; color: #1e3a8a; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .pm .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .pm .kepala small { color: #475569; }
    .pm .sec { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; background: #fff; }
    .pm .sec-h { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; }
    .pm .sec-h .no { width: 26px; height: 26px; border-radius: 8px; background: #dbeafe; color: #1e3a8a; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .82rem; }
    .pm .sec-h b { color: #0f172a; }
    .pm .sec-h small { color: #64748b; margin-left: auto; }
    .pm .sec-b { padding: 14px; }
    .pm .sec-b label { font-size: .82rem; font-weight: 600; color: #1f2937; margin-bottom: 4px; }
    .pm .sec-b .input-group-text { font-size: .78rem; min-width: 54px; justify-content: center; }
    .pm .screening { border: 1px solid #fde68a; background: #fffbeb; border-radius: 12px; padding: 10px 14px; margin-bottom: 14px; }
    .pm .screening label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #92400e; margin: 0; }
    .pm .screening input { border: 0; background: transparent !important; padding: 2px 0 0; font-weight: 600; color: #1f2937; box-shadow: none; }
    .pm .hitung { background: #f0f9ff !important; color: #0c4a6e; font-weight: 600; }
    .pm .alergi { border: 1px dashed #cbd5e1; border-radius: 10px; padding: 10px; height: 100%; }
    .pm .bar { position: sticky; bottom: 0; z-index: 6; background: #fff; border-top: 1px solid #e2e8f0; padding: 12px 0 2px; margin-top: 6px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .pm .bar #printActions { margin: 0 !important; }
    .pm .bar #simpan_pemeriksaan { margin-left: auto; min-width: 220px; font-weight: 600; }
    .pm .rujuk .row > [class*="col-2"] .col-form-label { font-weight: 600; }
    .pm #rujukvertikal, .pm #formTacc, .pm #noLaporanPolisi { border-top: 1px dashed #e2e8f0; padding-top: 12px; margin-top: 4px; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <?php require 'menu_rme.php'; ?>
            <div class="col-12">
              <?php require 'card-pasien.php'; ?>
            </div>
            <div class="col-12">
              <div class="card w-100">
                <div class="card-body p-4 pm">
                  <form id="formPemeriksaan" method="POST" novalidate onsubmit="return false;">
                    <input type="hidden" name="nomor_rm" value="<?= $h($rm) ?>">
                    <input type="hidden" name="nomor_visit" value="<?= $h($no) ?>">
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
                    <input type="hidden" name="status_pasien" id="status_pasien">

                    <div class="kepala">
                      <span class="ic"><i class="ti ti-stethoscope"></i></span>
                      <div class="flex-grow-1">
                        <h5>Form Pemeriksaan Medis</h5>
                        <small>Tanda vital oleh perawat, lalu anamnesis, diagnosis, dan status pulang oleh dokter. Pasien BPJS dikirim ke PCare saat disimpan.</small>
                      </div>
                    </div>

                    <div class="screening">
                      <label for="visit_notes">Catatan screening</label>
                      <input type="text" id="visit_notes" name="visit_notes" class="form-control bg-light" readonly placeholder="-">
                    </div>

                    <!-- 1. TANDA VITAL -->
                    <section class="sec">
                      <div class="sec-h"><span class="no">1</span><b>Pemeriksaan Vital Sign (Perawat)</b><small><span class="text-danger">*</span> wajib</small></div>
                      <div class="sec-b">
                        <div class="row g-3">
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="tinggiBadan">Tinggi Badan <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="decimal" class="form-control" name="tinggiBadan" id="tinggiBadan" min="30" max="250" step="0.1" required>
                              <span class="input-group-text">cm</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="beratBadan">Berat Badan <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="decimal" class="form-control" name="beratBadan" id="beratBadan" min="1" max="300" step="0.1" required>
                              <span class="input-group-text">kg</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="lingkarPerut">Lingkar Perut <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="decimal" class="form-control" name="lingkarPerut" id="lingkarPerut" min="30" max="200" step="0.1" required>
                              <span class="input-group-text">cm</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="bmi">BMI</label>
                            <div class="input-group">
                              <input type="text" id="bmi" name="bmi" readonly tabindex="-1" class="form-control hitung" required>
                              <span class="input-group-text">kg/m²</span>
                            </div>
                          </div>
                          <div class="col-12 col-md-6 col-xl-4">
                            <label for="bmiKet">Keterangan BMI</label>
                            <input type="text" id="bmiKet" name="bmi_keterangan" readonly tabindex="-1" class="form-control hitung" required>
                          </div>

                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="sistole">Sistole <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="numeric" class="form-control" name="sistole" id="sistole" min="50" max="300" step="1" required>
                              <span class="input-group-text">mmHg</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="diastole">Diastole <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="numeric" class="form-control" name="diastole" id="diastole" min="30" max="200" step="1" required>
                              <span class="input-group-text">mmHg</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="respRate">Respiratory Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="numeric" class="form-control" name="respRate" id="respRate" min="5" max="60" step="1" required>
                              <span class="input-group-text">/mnt</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="heartRate">Heart Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="numeric" class="form-control" name="heartRate" id="heartRate" min="30" max="220" step="1" required>
                              <span class="input-group-text">bpm</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="suhu">Suhu <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="decimal" class="form-control" name="suhu" id="suhu" min="25" max="45" step="0.1" required>
                              <span class="input-group-text">°C</span>
                            </div>
                          </div>
                          <div class="col-6 col-md-3 col-xl-2">
                            <label for="saturasi">Saturasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="number" inputmode="numeric" id="saturasi" name="saturasi" class="form-control" min="50" max="100" step="1" required>
                              <span class="input-group-text">SpO₂ %</span>
                            </div>
                          </div>

                          <div class="col-md-6 statuspasien">
                            <label for="kondisi_masuk">Prognosa <span class="text-danger">*</span></label>
                            <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required></select>
                          </div>
                          <div class="col-md-6 statuspasien">
                            <label for="kdSadar">Kesadaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="kdSadar" id="kdSadar" required></select>
                          </div>
                        </div>
                      </div>
                    </section>

                    <!-- 2. ANAMNESIS -->
                    <section class="sec">
                      <div class="sec-h"><span class="no">2</span><b>Anamnesis &amp; Alergi (Dokter)</b></div>
                      <div class="sec-b">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label for="keluhan_penyerta" class="required">Keluhan Utama</label>
                            <textarea id="keluhan_penyerta" name="keluhan_penyerta" rows="3" class="form-control" required></textarea>
                          </div>
                          <div class="col-md-6">
                            <label for="keluhan_utama" class="required">Anamnese</label>
                            <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" rows="3" required></textarea>
                          </div>
                          <div class="col-12" id="formalergi">
                            <div class="row g-3">
                              <div class="col-md-4">
                                <div class="alergi">
                                  <label for="alergiMakan">Alergi Makan</label>
                                  <select class="form-select mb-2" name="alergiMakan" id="alergiMakan"></select>
                                  <textarea class="form-control form-control-sm" name="ketAlergiMakan" id="ketAlergiMakan" rows="2" placeholder="Keterangan alergi makanan…" disabled></textarea>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="alergi">
                                  <label for="alergiUdara">Alergi Udara</label>
                                  <select class="form-select mb-2" name="alergiUdara" id="alergiUdara"></select>
                                  <textarea class="form-control form-control-sm" name="ketAlergiUdara" id="ketAlergiUdara" rows="2" placeholder="Keterangan alergi udara…" disabled></textarea>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="alergi">
                                  <label for="alergiObat">Alergi Obat</label>
                                  <select class="form-select mb-2" name="alergiObat" id="alergiObat"></select>
                                  <textarea class="form-control form-control-sm" name="ketAlergiObat" id="ketAlergiObat" rows="2" placeholder="Keterangan alergi obat…" disabled></textarea>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </section>

                    <!-- 3. DIAGNOSIS & TINDAKAN -->
                    <section class="sec">
                      <div class="sec-h"><span class="no">3</span><b>Diagnosis &amp; Tindakan</b></div>
                      <div class="sec-b">
                        <div class="row g-3">
                          <div class="col-md-12">
                            <label for="diag1">Diagnosa Utama <span class="text-danger">*</span></label>
                            <select id="diag1" name="diag1" class="form-select" required></select>
                            <input type="hidden" id="nmDiag1" name="nmDiag1">
                            <input type="hidden" id="kdnonSpesialis1">
                          </div>
                          <div class="col-md-6">
                            <label for="diag2">Diagnosa Sekunder 1</label>
                            <select id="diag2" name="diag2" class="form-select"></select>
                            <input type="hidden" id="nmDiag2" name="nmDiag2">
                            <input type="hidden" id="kdnonSpesialis2">
                          </div>
                          <div class="col-md-6">
                            <label for="diag3">Diagnosa Sekunder 2</label>
                            <select id="diag3" name="diag3" class="form-select"></select>
                            <input type="hidden" id="nmDiag3" name="nmDiag3">
                            <input type="hidden" id="kdnonSpesialis3">
                          </div>
                          <div class="col-12">
                            <label for="tindakan">Tindakan / Terapi / Instruksi / Rencana Rawat</label>
                            <textarea id="tindakan" name="tindakan" rows="3" class="form-control"></textarea>
                          </div>
                        </div>
                      </div>
                    </section>

                    <!-- 4. STATUS PULANG & RUJUKAN -->
                    <section class="sec rujuk" id="secPulang">
                      <div class="sec-h"><span class="no">4</span><b>Status Pulang &amp; Rujukan</b></div>
                      <div class="sec-b">
                        <div class="col-12" id="ToogleStPulang">
                          <div class="row mb-3">
                            <div class="col-md-2">
                              <label for="kdStatusPulang" class="col-form-label col-form-label-sm">Status Pulang<span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                              <select class="form-select" id="kdStatusPulang" name="kdStatusPulang"></select>
                            </div>
                          </div>
                        </div>
                        <div class="col-12 d-none" id="rujukvertikal">
                          <div class="row mb-3">
                            <div class="col-2">
                              <label class="col-form-label col-form-label-sm">Status Rujukan <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-10">
                              <div class="row g-2">
                                <div class="col-md-6">
                                  <input type="radio" class="btn-check" name="kdStatusRujuk" id="rujukSpesialis" value="SP" required>
                                  <label class="btn btn-outline-primary w-100 text-start p-3" for="rujukSpesialis">
                                    <strong>Rujukan Spesialis</strong><br>
                                    <small class="subtext">Rujukan ke sub spesialis sesuai indikasi medis</small>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <input type="radio" class="btn-check" name="kdStatusRujuk" id="rujukKhusus" value="KH" required>
                                  <label class="btn btn-outline-success w-100 text-start p-3" for="rujukKhusus">
                                    <strong>Rujukan Khusus</strong><br>
                                    <small class="subtext">Rujukan dengan kriteria atau program khusus</small>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- kolom col-N di baris ini diubah oleh changeCol() di kunjunganrj.js -->
                          <div class="row mb-3">
                            <div class="col-2">
                              <label for="kdKategori" class="col-form-label">Kategori <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-5">
                              <label for="kdKategori" class="form-label fw-semibold">Spesialis</label>
                              <select class="form-select" id="kdKategori" name="kdKategori" required></select>
                              <input type="hidden" id="nmKategori" name="nmKategori">
                            </div>
                            <div class="col-2 d-none" id="subspesialis">
                              <label for="kdsubspesialis" class="form-label fw-semibold">Sub Spesialis</label>
                              <select class="form-select" id="kdsubspesialis" name="kdSubSpesialis1"></select>
                              <input type="hidden" id="nmSubSpesialis1" name="nmSubSpesialis1">
                            </div>
                            <div class="col-3 d-none" id="sarana">
                              <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="useSarana" value="1">
                                <label class="form-check-label fw-semibold" for="useSarana">Sarana</label>
                              </div>
                              <select class="form-select" id="kdSarana"></select>
                              <input type="hidden" name="kdSarana" id="kdSaranaHidden" value="0">
                            </div>
                            <div class="col-5">
                              <label for="tglRujukan" class="form-label fw-semibold">Tgl Kunjung</label>
                              <div class="input-group">
                                <input type="date" class="form-control" id="tglRujukan" name="tglRujukan" required>
                                <button type="button" id="btnCariFaskes" class="btn btn-primary"><i class="bi bi-search"></i> Cari Faskes</button>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3 d-none" id="alasanrujuk">
                            <div class="col-2">
                              <label for="alasanRujukan" class="col-form-label">Alasan <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-10">
                              <textarea class="form-control" id="alasanRujukan" name="alasanRujukan" rows="3" placeholder="Masukkan alasan rujukan..." required></textarea>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="row mb-3">
                              <div class="col-2">
                                <label for="nmfaskes" class="col-form-label col-form-label-sm">Faskes<span class="text-danger">*</span></label>
                              </div>
                              <div class="col-10">
                                <input type="text" id="nmfaskes" name="nmfaskes" class="form-control" readonly placeholder="Klik Cari Faskes lalu pilih faskes tujuan">
                                <input type="hidden" name="kdppk" id="kdfaskes">
                                <input type="hidden" name="jadwal" id="jadwal">
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-12 d-none" id="formTacc">
                          <div class="row mb-3">
                            <div class="col-2">
                              <label for="kdTacc" class="col-form-label">TACC <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-3">
                              <label for="kdTacc" class="form-label fw-semibold">TACC</label>
                              <select class="form-select" id="kdTacc" name="kdTacc">
                                <option value="-1">Tanpa TACC</option>
                                <option value="1">Time</option>
                                <option value="2">Age</option>
                                <option value="3">Complication</option>
                                <option value="4">Comorbidity</option>
                              </select>
                            </div>
                            <div class="col-7">
                              <label for="alasanTacc" class="form-label fw-semibold">Alasan</label>
                              <input type="text" class="form-control" id="alasanTacc" name="alasanTacc">
                            </div>
                          </div>
                        </div>
                        <div class="col-12 d-none" id="noLaporanPolisi">
                          <div class="row mb-3">
                            <div class="col-2">
                              <label for="nomorLp" class="col-form-label">Nomor LP <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-10">
                              <input type="text" class="form-control" placeholder="Masukan Nomor Laporan Polisi Disini..." id="nomorLp" name="nomorLp">
                            </div>
                          </div>
                        </div>
                      </div>
                    </section>

                    <div class="bar">
                      <div class="d-flex flex-wrap align-items-center gap-2" id="printActions">
                        <button type="button" class="btn btn-outline-dark btn-printkunjungan d-none" id="btn-printkunjungan">
                          <i class="bi bi-printer-fill me-1"></i> Print Kunjungan</button>
                        <button type="button" class="btn btn-outline-dark btn-print d-none" id="btn-print">
                          <i class="bi bi-file-earmark-text-fill me-1"></i> Print Rujukan</button>
                      </div>
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
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-hospital fs-3"></i>
            Daftar Fasilitas Kesehatan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle text-center" id="tableRujukan">
              <thead class="table-dark text-white">
                <tr>
                  <th>Action</th>
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
  <script src="controller/admisi/helper.js?v=<?= filemtime(__DIR__ . '/../../controller/admisi/helper.js') ?>"></script>
  <script src="controller/doctor/kunjunganrj.js?v=<?= filemtime(__DIR__ . '/../../controller/doctor/kunjunganrj.js') ?>"></script>
</body>

</html>
