<?php
$title = 'Pendaftaran Poliklinik';
require '../../controller/view.php';

$idcustomer = (int) ($_SESSION['id_customer'] ?? 0);
$st = $koneksi->prepare("SELECT 1 FROM setting_antrol WHERE id_customer = ? LIMIT 1");
$st->bind_param('i', $idcustomer);
$st->execute();
$useLocalDokter = (bool) $st->get_result()->fetch_row();
$st->close();
session_write_close();
date_default_timezone_set('Asia/Jakarta');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .pd-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .pd-cari { position: relative; }
    .pd-cari input { height: 52px; font-size: 1rem; padding-left: 46px; border-radius: 12px; }
    .pd-cari .fa-search { position: absolute; left: 17px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .pd-hasil { position: absolute; z-index: 50; left: 0; right: 0; top: calc(100% + 6px); background: #fff; border: 1px solid #e5eaef; border-radius: 12px; box-shadow: 0 12px 30px rgba(15, 23, 42, .12); max-height: 420px; overflow-y: auto; }
    .pd-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; }
    .pd-item:last-child { border-bottom: 0; }
    .pd-item.aktif, .pd-item:hover { background: #f5f8ff; }
    .pd-ava { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; background: #ecf2ff; color: #5d87ff; }
    .pd-ava.p { background: #fdeef5; color: #e05297; }
    .pd-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .74rem; background: #f1f5f9; border-radius: 5px; padding: 0 6px; color: #334155; }
    .pd-mark { background: #fff3c4; padding: 0 1px; border-radius: 2px; }
    .pd-kosong { padding: 18px; text-align: center; color: #64748b; font-size: .9rem; }
    .pd-sec { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
    .pd-info dt { font-size: .7rem; color: #94a3b8; font-weight: 400; margin-top: 8px; }
    .pd-info dd { margin: 0; font-weight: 600; color: #2a3547; font-size: .88rem; word-break: break-word; }
    .pd-bpjs { border-radius: 12px; padding: 12px 14px; border: 1px solid #e5eaef; }
    .pd-bpjs.ok { background: #f0fdf9; border-color: #a7f3d0; }
    .pd-bpjs.no { background: #fff5f5; border-color: #fecaca; }
    .pd-bpjs.info { background: #f8fafc; }
    #pdBpjsBox.ok, #pdBpjsBox.no { padding: 16px 18px; font-size: .95rem; color: #111827; }
    .pd-bpjs-judul { font-size: 1.05rem; font-weight: 700; }
    .pd-bpjs-noka { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .95rem; font-weight: 600; color: #111827; letter-spacing: .02em; }
    .pd-bpjs-row { display: flex; gap: 12px; padding: 3px 0; line-height: 1.4; }
    .pd-bpjs-row span { flex: 0 0 130px; color: #111827; }
    .pd-bpjs-row b { font-weight: 600; color: #111827; min-width: 0; overflow-wrap: anywhere; }
    .pd-bpjs-row + .pd-bpjs-row { border-top: 1px dashed rgba(17, 24, 39, .1); }
    .pd-bpjs .badge.bg-warning-subtle { color: #1f2937 !important; }
    .pd-seg { display: flex; gap: 6px; }
    .pd-seg input { display: none; }
    .pd-seg label { flex: 1; border: 1px solid #e5eaef; border-radius: 10px; padding: 10px 12px; cursor: pointer; background: #fff; font-size: .85rem; }
    .pd-seg label b { display: block; font-size: .9rem; color: #2a3547; }
    .pd-seg label small { color: #94a3b8; }
    .pd-seg input:checked + label { border-color: #5d87ff; background: #ecf2ff; }
    .pd-seg input:checked + label b { color: #5d87ff; }
    .pd-dokter { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; }
    .pd-dk { border: 1px solid #e5eaef; border-radius: 10px; padding: 9px 11px; cursor: pointer; background: #fff; display: flex; gap: 9px; align-items: center; text-align: left; width: 100%; }
    .pd-dk:hover:not(:disabled) { border-color: #b9c8e6; }
    .pd-dk.pilih { border-color: #5d87ff; background: #ecf2ff; }
    .pd-dk:disabled { opacity: .5; cursor: not-allowed; }
    .pd-dk .pd-ava { width: 32px; height: 32px; font-size: .72rem; }
    .pd-vital { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .pd-vital .vf { border: 1px solid #edf0f4; border-radius: 10px; padding: 7px 9px; background: #fafbfc; }
    .pd-vital .vf label { font-size: .7rem; color: #64748b; margin-bottom: 3px; display: block; }
    .pd-vital .vf.td { grid-column: span 2; }
    @media (max-width: 767.98px) { .pd-vital { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .pd-sticky { position: sticky; top: 90px; }
    .pd-sk { border-radius: 10px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: pdsk 1.2s infinite; }
    @keyframes pdsk { to { background-position: -200% 0; } }
    #pdForm .is-invalid + .select2 .select2-selection { border-color: #dc3545 !important; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php' ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar.php' ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="pd-icon bg-success-subtle text-success"><i class="fas fa-stethoscope"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Pendaftaran Poliklinik</h5>
                  <div class="text-muted small">Cari pasien, cek kepesertaan BPJS, lalu daftarkan ke poli &amp; dokter.</div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnCariBPJS"><i class="fas fa-id-card me-1"></i>Pasien baru (cek BPJS)</button>
                <a class="btn btn-outline-secondary btn-sm" href="module/admisi/registrasi-new"><i class="fas fa-user-plus me-1"></i>Pasien baru (manual)</a>
                <a class="btn btn-light btn-sm" href="module/admisi/registrasi-poliklinik"><i class="fas fa-list me-1"></i>Daftar registrasi</a>
              </div>
            </div>
          </div>

          <!-- CARI -->
          <div class="card border-0 shadow-sm mb-3" id="pdCariCard">
            <div class="card-body">
              <div class="pd-cari">
                <i class="fas fa-search"></i>
                <input type="search" id="pdCari" class="form-control" placeholder="Cari pasien: nama, nomor RM, NIK, atau no. BPJS…" autocomplete="off" autofocus>
                <div class="pd-hasil d-none" id="pdHasil"></div>
              </div>
              <div class="small text-muted mt-2"><i class="fas fa-keyboard me-1"></i>Gunakan ↑ ↓ lalu Enter untuk memilih. Pasien belum ada? Klik <b>Pasien baru</b> di atas.</div>
            </div>
          </div>

          <div id="pdArea" class="d-none">
            <div class="row g-3">
              <!-- PASIEN -->
              <div class="col-lg-4">
                <div class="pd-sticky">
                  <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="pd-ava" id="pdAva" style="width:52px;height:52px;font-size:1rem">-</span>
                        <div class="min-w-0 flex-grow-1">
                          <h5 class="mb-0 fw-semibold text-truncate" id="pdNama">-</h5>
                          <div class="small text-muted" id="pdSub">-</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" id="pdGanti" title="Ganti pasien"><i class="fas fa-rotate"></i></button>
                      </div>
                      <dl class="pd-info row g-0 mb-0">
                        <div class="col-6"><dt>NIK</dt><dd id="pdNik">-</dd></div>
                        <div class="col-6"><dt>No. BPJS</dt><dd id="pdBpjs">-</dd></div>
                        <div class="col-6"><dt>Tanggal lahir</dt><dd id="pdLahir">-</dd></div>
                        <div class="col-6"><dt>Jenis kelamin</dt><dd id="pdJk">-</dd></div>
                      </dl>
                      <label class="form-label small fw-semibold mt-3 mb-1" for="pdHp">No. HP <span class="text-muted fw-normal">(wajib untuk antrean BPJS)</span></label>
                      <input type="tel" inputmode="tel" id="pdHp" class="form-control form-control-sm" placeholder="08xxxxxxxxxx" maxlength="15">
                    </div>
                  </div>
                  <div class="pd-bpjs info mb-3" id="pdBpjsBox"></div>
                  <div class="alert alert-warning small d-none" id="pdHariIni"></div>
                </div>
              </div>

              <!-- FORM -->
              <div class="col-lg-8">
                <form id="pdForm" class="card border-0 shadow-sm" novalidate autocomplete="off">
                  <input type="hidden" name="noNik" id="noNIK">
                  <input type="hidden" name="nama" id="namapatient">
                  <input type="hidden" name="jnsKlamin" id="Kelamin">
                  <input type="hidden" name="tglLahir" id="tgllahir">
                  <input type="hidden" name="typePatient" id="typePasien">
                  <input type="hidden" name="noHp" id="nohp">
                  <input type="hidden" name="norm" id="norm">
                  <input type="hidden" name="SProlanis" id="SProlanis">
                  <input type="hidden" name="SPRB" id="SPRB">
                  <input type="hidden" name="id_patient" id="id_patient">
                  <input type="hidden" name="noKartu" id="noKartu">
                  <input type="hidden" name="kdProviderPeserta" id="kdProviderPeserta">
                  <input type="hidden" name="kunjSakit" id="kunjSakit" value="true">
                  <input type="hidden" name="kdTkp" id="kunjungan" value="10">
                  <input type="hidden" name="nmPoli" id="nmPoli">
                  <input type="hidden" name="kdDokter" id="kodedokter">
                  <input type="hidden" name="nmDokter" id="namadokter">
                  <input type="hidden" name="nmDokterPcare" id="namadokterPcare">
                  <input type="hidden" name="jampraktek" id="jampraktek">
                  <input type="hidden" name="paksa" id="paksa" value="0">
                  <div class="card-body">
                    <div class="pd-sec">Jenis kunjungan</div>
                    <div class="pd-seg mb-3" id="pdJenis">
                      <input type="radio" name="_jenis" id="jSakit" value="true" checked><label for="jSakit"><b><i class="fas fa-notes-medical me-1"></i>Kunjungan sakit</b><small>Berobat ke poli</small></label>
                      <input type="radio" name="_jenis" id="jSehat" value="false"><label for="jSehat"><b><i class="fas fa-heart me-1"></i>Kunjungan sehat</b><small>Promotif / preventif BPJS</small></label>
                    </div>

                    <div class="row g-3 mb-3">
                      <div class="col-md-4" id="pdTkpWrap">
                        <label class="form-label small fw-semibold" for="pdTkp">Perawatan</label>
                        <select class="form-select" id="pdTkp">
                          <option value="10">Rawat jalan</option>
                          <option value="50">Promotif preventif</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="tanggalKunjung">Tanggal kunjungan</label>
                        <input type="date" class="form-control" name="tglDaftar" id="tanggalKunjung">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="kodepoli">Poli tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" id="kodepoli" name="kdPoli"><option value="">Memuat…</option></select>
                      </div>
                    </div>

                    <div id="statusKsehat">
                      <div class="d-flex align-items-center mb-2">
                        <div class="pd-sec mb-0">Dokter <span class="text-danger">*</span></div>
                        <span class="small text-muted ms-2" id="pdDokterInfo"></span>
                      </div>
                      <div class="pd-dokter mb-3" id="pdDokter"><div class="small text-muted">Pilih poli terlebih dahulu.</div></div>
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold" for="kodeprov">Penjamin <span class="text-danger">*</span></label>
                          <select class="form-select" id="kodeprov" name="kdProv"></select>
                          <div class="form-text" id="pdTipeInfo"></div>
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label small fw-semibold" for="keluhan">Keluhan <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="keluhan" name="keluhan" rows="2" placeholder="Keluhan utama pasien"></textarea>
                    </div>

                    <a class="small fw-semibold text-decoration-none" data-bs-toggle="collapse" href="#pdVitalBox" role="button" aria-expanded="true"><i class="fas fa-heart-pulse me-1 text-danger"></i>Tanda vital &amp; antropometri <span class="text-danger fw-normal">(wajib diisi)</span> <i class="fas fa-chevron-down ms-1 small"></i></a>
                    <div class="collapse show mt-2" id="pdVitalBox">
                      <div class="pd-vital">
                        <div class="vf td"><label>Tekanan darah (mmHg) <span class="text-danger">*</span></label><div class="input-group input-group-sm"><input type="number" required inputmode="decimal" class="form-control" name="sistole" id="sistole" min="50" max="300"><span class="input-group-text">/</span><input type="number" required inputmode="decimal" class="form-control" name="diastole" id="diastole" min="20" max="200"></div></div>
                        <div class="vf"><label>Nadi (x/mnt) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" class="form-control form-control-sm" name="heartRate" id="heartRate" min="20" max="250"></div>
                        <div class="vf"><label>Respirasi (x/mnt) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" class="form-control form-control-sm" name="respRate" id="respRate" min="4" max="80"></div>
                        <div class="vf"><label>Suhu (°C) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" step="0.1" class="form-control form-control-sm" name="suhu" id="suhu" min="30" max="45"></div>
                        <div class="vf"><label>SpO₂ (%) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" class="form-control form-control-sm" name="saturasiOksigen" id="saturasiOksigen" min="50" max="100"></div>
                        <div class="vf"><label>Tinggi (cm) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" step="0.1" class="form-control form-control-sm" name="tinggiBadan" id="tinggiBadan" min="30" max="250"></div>
                        <div class="vf"><label>Berat (kg) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" step="0.1" class="form-control form-control-sm" name="beratBadan" id="beratBadan" min="0.5" max="350"></div>
                        <div class="vf"><label>Lingkar perut (cm) <span class="text-danger">*</span></label><input type="number" required inputmode="decimal" class="form-control form-control-sm" name="lingkarPerut" id="lingkarPerut" min="20" max="250"></div>
                        <div class="vf"><label>BMI (otomatis)</label><div class="fw-bold small pt-1" id="pdBmi">—</div><input type="hidden" name="bmi" id="bmi"><input type="hidden" name="bmiKet" id="bmiKet"></div>
                      </div>
                    </div>
                    <div class="small text-danger mt-3 d-none" id="pdError"></div>
                  </div>
                  <div class="card-footer bg-white d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted me-auto" id="pdRingkas"></span>
                    <button type="button" class="btn btn-light" id="pdReset">Batal</button>
                    <button class="btn btn-success px-4" type="submit" id="create"><i class="fas fa-check me-1"></i>Daftarkan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PASIEN BARU (CEK BPJS) -->
  <div class="modal fade" id="modalCariBPJS" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div><h5 class="modal-title mb-0"><i class="fas fa-id-card text-primary me-2"></i>Pasien baru — cek peserta BPJS</h5><div class="small text-muted">Data peserta diambil dari BPJS lalu disimpan sebagai pasien baru.</div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold" for="inputNIK">NIK (16 digit) atau no. BPJS (13 digit)</label>
          <div class="input-group">
            <input type="text" inputmode="numeric" id="inputNIK" class="form-control form-control-lg" placeholder="Ketik nomor…" maxlength="16">
            <button id="btnSearchBPJS" class="btn btn-primary px-4" type="button">Cek</button>
          </div>
          <div class="small text-muted mt-1" id="nbHint">&nbsp;</div>
          <form id="formBPJS" class="d-none mt-3">
            <input type="hidden" id="noKartuDaftar" name="noKartu">
            <input type="hidden" id="regNik" name="nik">
            <div class="pd-bpjs ok">
              <div class="fw-semibold mb-1" id="nbNama">-</div>
              <div class="small text-muted" id="nbDetail">-</div>
              <div class="small mt-1" id="nbStatus"></div>
            </div>
            <input type="hidden" id="regNama" name="nama"><input type="hidden" id="regTglLahir" name="tgl_lahir">
            <input type="hidden" id="regJnsKelamin" name="jenis_kelamin"><input type="hidden" id="regNoBPJS" name="no_bpjs">
          </form>
        </div>
        <div class="modal-footer">
          <a class="btn btn-link me-auto px-0 small" href="module/admisi/registrasi-new">Daftar manual (tanpa BPJS)</a>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button id="btnTambahPasien" type="button" class="btn btn-success d-none"><i class="fas fa-user-plus me-1"></i>Simpan &amp; daftarkan</button>
        </div>
      </div>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script src="controller/admisi/helper.js"></script>
  <script>window.USE_LOCAL_DOKTER = <?= $useLocalDokter ? 'true' : 'false' ?>;</script>
  <script src="controller/admisi/pendaftaran.js?v=<?= @filemtime(__DIR__ . '/../../controller/admisi/pendaftaran.js') ?>"></script>
</body>

</html>
