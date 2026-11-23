<?php
$title = 'Permintaan Farmasi';
require '../../controller/view.php';
$rme = (string) ($_GET['rme'] ?? '');
$noVisit = (string) ($_GET['no'] ?? '');
$noRm = (string) ($_GET['rm'] ?? '');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .pf .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .pf .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0e7ff; color: #3730a3; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .pf .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .pf .kepala small { color: #475569; }
    .pf .alur { display: flex; flex-wrap: wrap; gap: 6px; font-size: .76rem; color: #334155; }
    .pf .alur span { background: #f1f5f9; border-radius: 999px; padding: 2px 10px; }
    .pf .alur span b { color: #1d4ed8; }
    .pf .chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .pf .chip { border: 1px solid #cbd5e1; background: #fff; color: #1f2937; border-radius: 999px; padding: 5px 12px; font-size: .82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
    .pf .chip .n { background: #e2e8f0; color: #0f172a; border-radius: 999px; padding: 0 7px; font-size: .74rem; }
    .pf .chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    .pf .chip.on .n { background: #fff; color: #1e293b; }
    .pf .chip .dot { width: 8px; height: 8px; border-radius: 50%; }
    .pf .pf-cari { width: 100%; }
    @media (min-width: 768px) { .pf .pf-cari { width: 300px; } }
    .pf .tiket { background: #fff; border: 1px solid #e2e8f0; border-left: 5px solid #64748b; border-radius: 14px; padding: 14px 16px; height: 100%; display: flex; flex-direction: column; }
    .pf .tiket[data-st="0"] { border-left-color: #334155; }
    .pf .tiket[data-st="1"] { border-left-color: #d97706; }
    .pf .tiket[data-st="2"] { border-left-color: #2563eb; }
    .pf .tiket[data-st="3"] { border-left-color: #059669; }
    .pf .tiket .atas { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
    .pf .tiket .aksi { display: flex; flex-wrap: wrap; gap: 6px; }
    .pf .tiket .aksi .btn { font-weight: 600; }
    .pf .tiket .judul { font-weight: 700; color: #0f172a; font-size: 1rem; }
    .pf .tiket .meta { font-size: .76rem; color: #475569; }
    .pf .st { font-size: .74rem; font-weight: 700; border-radius: 6px; padding: 3px 9px; white-space: nowrap; color: #fff; }
    .pf .st-0 { background: #334155; } .pf .st-1 { background: #b45309; } .pf .st-2 { background: #1d4ed8; } .pf .st-3 { background: #047857; }
    .pf .tipe { font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; border: 1px solid; }
    .pf .tipe-Racikan { color: #92400e; border-color: #f59e0b; background: #fffbeb; }
    .pf .tipe-Non { color: #1e3a8a; border-color: #93c5fd; background: #eff6ff; }
    .pf .tipe-BMHP { color: #581c87; border-color: #d8b4fe; background: #faf5ff; }
    .pf .langkah { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin: 4px 0 10px; }
    .pf .langkah div { font-size: .68rem; color: #64748b; text-align: center; }
    .pf .langkah div::before { content: ''; display: block; height: 5px; border-radius: 3px; background: #e2e8f0; margin-bottom: 3px; }
    .pf .langkah div.ok { color: #0f172a; font-weight: 600; }
    .pf .langkah div.ok::before { background: #059669; }
    .pf .langkah div.kini::before { background: #2563eb; }
    .pf .obat { width: 100%; font-size: .84rem; border-collapse: collapse; }
    .pf .obat th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding: 4px 6px; }
    .pf .obat td { border-bottom: 1px dashed #e5e7eb; padding: 5px 6px; vertical-align: top; color: #1f2937; }
    .pf .obat tr:last-child td { border-bottom: 0; }
    .pf .obat .dagang { font-size: .72rem; color: #64748b; }
    .pf .racik { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 6px 10px; font-size: .8rem; color: #78350f; margin-bottom: 8px; }
    .pf .catatan { background: #f8fafc; border-radius: 10px; padding: 6px 10px; font-size: .8rem; color: #334155; margin-top: 8px; white-space: pre-line; }
    .pf .kosong-obat { border: 1px dashed #f59e0b; background: #fffbeb; color: #92400e; border-radius: 10px; padding: 10px; font-size: .82rem; text-align: center; }
    .pf .kosong { text-align: center; padding: 40px 10px; color: #475569; }
    .ti-spin { display: inline-block; animation: pfPutar .8s linear infinite; }
    @keyframes pfPutar { to { transform: rotate(360deg); } }
    .pf .kosong i { font-size: 2.4rem; color: #94a3b8; }
    #programModal .tipe-pilih { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    #programModal .tipe-pilih label { border: 2px solid #e2e8f0; border-radius: 12px; padding: 10px; cursor: pointer; text-align: center; height: 100%; }
    #programModal .tipe-pilih label i { font-size: 1.4rem; display: block; color: #475569; }
    #programModal .tipe-pilih label b { display: block; font-size: .88rem; color: #0f172a; }
    #programModal .tipe-pilih label small { color: #64748b; font-size: .7rem; }
    #programModal .btn-check:checked + label { border-color: #2563eb; background: #eff6ff; }
    #programModal .btn-check:checked + label i { color: #2563eb; }
    @media (max-width: 575.98px) { #programModal .tipe-pilih { grid-template-columns: 1fr; } }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid pf">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php require 'card-pasien.php'; ?>

          <div class="card w-100">
            <div class="card-body p-3 p-md-4">
              <div class="kepala">
                <span class="ic"><i class="ti ti-pill"></i></span>
                <div class="flex-grow-1">
                  <h5>Permintaan Farmasi</h5>
                  <div class="alur mt-1">
                    <span><b>1</b> Buat tiket</span><span><b>2</b> Isi rincian obat / racikan</span><span><b>3</b> Kirim ke farmasi</span>
                  </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-outline-dark" target="_blank" href="module/admin/print/print_all_resep?no=<?= $h(urlencode($noVisit)) ?>&rm=<?= $h(urlencode($noRm)) ?>"><i class="ti ti-printer me-1"></i>Cetak Resep</a>
                  <button type="button" class="btn btn-success d-none" id="btnKirimObat"><i class="ti ti-send me-1"></i>Kirim Semua <span class="badge bg-white text-success ms-1" id="btnKirimObatCount">0</span></button>
                  <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Tiket</button>
                </div>
              </div>

              <div class="alert alert-danger d-flex align-items-start gap-2 peringatanAlergi d-none" role="alert">
                <i class="ti ti-alert-triangle fs-5"></i>
                <div><strong>Peringatan alergi obat:</strong> <span id="desAplergi"></span></div>
              </div>
              <div class="alert alert-warning d-none" id="alertBatal" role="alert"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah <b>dibatalkan</b>. Tiket baru tidak dapat dibuat.</div>

              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="chips" id="filterStatus">
                  <button type="button" class="chip on" data-f="all">Semua <span class="n" data-n="all">0</span></button>
                  <button type="button" class="chip" data-f="0"><span class="dot" style="background:#334155"></span>Belum dikirim <span class="n" data-n="0">0</span></button>
                  <button type="button" class="chip" data-f="1"><span class="dot" style="background:#b45309"></span>Terkirim <span class="n" data-n="1">0</span></button>
                  <button type="button" class="chip" data-f="2"><span class="dot" style="background:#1d4ed8"></span>Persiapan <span class="n" data-n="2">0</span></button>
                  <button type="button" class="chip" data-f="3"><span class="dot" style="background:#047857"></span>Selesai <span class="n" data-n="3">0</span></button>
                </div>
                <div class="ms-md-auto d-flex gap-2 pf-cari">
                  <input type="search" class="form-control" id="customSearch" placeholder="Cari obat / catatan...">
                  <button type="button" class="btn btn-outline-dark" id="btnMuatUlang" title="Muat ulang"><i class="ti ti-refresh"></i></button>
                </div>
              </div>

              <div class="row g-3" id="tiketList">
                <div class="col-12 kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat tiket...</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="programModalTitle">Tiket Permintaan Farmasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $h($noVisit) ?>">
        <p class="small text-muted mb-2">Buat tiket dulu, lalu isi rincian obat di tiket tersebut.</p>
        <label class="form-label fw-semibold">Tipe obat <span class="text-danger">*</span></label>
        <div class="tipe-pilih mb-3">
          <div>
            <input type="radio" class="btn-check" name="tipe_obat" id="tipeNon" value="Non Racikan" checked>
            <label for="tipeNon"><i class="ti ti-pill"></i><b>Non Racikan</b><small>Obat jadi / paten</small></label>
          </div>
          <div>
            <input type="radio" class="btn-check" name="tipe_obat" id="tipeRacikan" value="Racikan">
            <label for="tipeRacikan"><i class="ti ti-flask"></i><b>Racikan</b><small>Puyer, kapsul, salep racik</small></label>
          </div>
          <div>
            <input type="radio" class="btn-check" name="tipe_obat" id="tipeBmhp" value="BMHP">
            <label for="tipeBmhp"><i class="ti ti-first-aid-kit"></i><b>BMHP</b><small>Bahan medis habis pakai</small></label>
          </div>
        </div>
        <div class="row g-2 racikan-field">
          <div class="col-6">
            <label class="form-label" for="rck_jumlah">Jumlah dibuat</label>
            <input type="number" name="rck_jumlah" id="rck_jumlah" class="form-control" min="1" max="10000" step="1" placeholder="10">
          </div>
          <div class="col-6">
            <label class="form-label" for="rck_satuan">Satuan</label>
            <input type="text" id="rck_satuan" name="rck_satuan" class="form-control" list="rckSatuanList" maxlength="100" placeholder="Pulvis">
          </div>
          <div class="col-12">
            <label class="form-label" for="rck_signa">Signa</label>
            <input type="text" name="rck_signa" id="rck_signa" class="form-control" maxlength="255" placeholder="3x1 sesudah makan">
            <div class="form-text">Opsional — bisa dilengkapi nanti di Form Racikan.</div>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label" for="catatan_permintaan">Catatan untuk farmasi</label>
          <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="3" maxlength="5000" placeholder="Opsional, mis. pasien sulit menelan tablet"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSimpanTiket"><i class="ti ti-device-floppy me-1"></i>Buat Tiket</button>
      </div>
    </form>
  </div>
</div>
<!-- 🧪 Modal Form Racikan -->
<style>
  #racikanModal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
  }

  #racikanModal .modal-header {
    background: linear-gradient(135deg, #5d87ff 0%, #49beff 100%);
    color: #fff;
    border: 0;
  }

  #racikanModal .modal-header .btn-close {
    filter: invert(1) brightness(2);
  }

  #racikanModal .modal-body {
    background: #f6f8fb;
  }

  #racikanModal .rck-card {
    background: #fff;
    border: 1px solid #e5eaef;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 16px;
  }

  #racikanModal .rck-step {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
  }

  #racikanModal .rck-step-no {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #5d87ff;
    color: #fff;
    font-weight: 600;
    font-size: .85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  #racikanModal .rck-step-title {
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
  }

  #racikanModal .rck-step-sub {
    font-size: .78rem;
    color: #6c757d;
    margin: 0;
  }

  #racikanModal .form-label {
    font-size: .8rem;
    font-weight: 600;
    color: #2a3547;
    margin-bottom: 4px;
  }

  #racikanModal .req {
    color: #fa896b;
  }

  #racikanModal .rck-help {
    font-size: .75rem;
    color: #6c757d;
  }

  /* pilihan tipe racik */
  #racikanModal .tipe-option {
    display: block;
    border: 2px solid #e5eaef;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    height: 100%;
    transition: all .15s;
  }

  #racikanModal .btn-check:checked+.tipe-option {
    border-color: #5d87ff;
    background: #ecf2ff;
  }

  #racikanModal .tipe-option .tipe-nama {
    font-weight: 600;
  }

  #racikanModal .tipe-option .tipe-desc {
    font-size: .75rem;
    color: #6c757d;
  }

  /* template */
  #racikanModal .rck-template {
    background: #fff8e6;
    border: 1px dashed #ffae1f;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
  }

  /* ringkasan */
  #racikanModal .rck-summary {
    background: #ecf2ff;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .85rem;
    color: #2a3547;
  }

  /* tabel bahan */
  #racikanModal .bahan-table th {
    font-size: .75rem;
    font-weight: 600;
    color: #5a6a85;
    background: #f6f8fb;
    white-space: nowrap;
  }

  #racikanModal .bahan-table td {
    vertical-align: middle;
  }

  #racikanModal .bahan-no {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e5eaef;
    font-size: .8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  #racikanModal .bahan-total {
    display: inline-block;
    font-weight: 600;
    color: #13a58b;
    white-space: nowrap;
  }

  #racikanModal .bahan-total small {
    display: block;
    font-weight: 400;
    color: #6c757d;
  }

  #racikanModal .bahan-empty {
    text-align: center;
    color: #6c757d;
    padding: 24px 0;
  }

  #racikanModal .select2-container .select2-selection--single {
    height: 38px;
    border-color: #dfe5ef;
  }

  #racikanModal .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
  }

  #racikanModal .select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px;
  }

  #racikanModal .is-invalid+.select2-container .select2-selection {
    border-color: #fa896b;
  }
</style>
<div class="modal fade" id="racikanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form id="racikanForm" class="modal-content" novalidate>
      <div class="modal-header">
        <div>
          <h5 class="modal-title text-white mb-0"><i class="fas fa-mortar-pestle me-2"></i>Form Racikan</h5>
          <small class="opacity-75" id="racikanInfo"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="rckId">

        <!-- TEMPLATE -->
        <div class="rck-template d-flex flex-wrap align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-bookmark text-warning fs-5"></i>
            <div>
              <div class="fw-semibold">Pakai racikan tersimpan?</div>
              <div class="rck-help">Pilih template untuk mengisi form otomatis.</div>
            </div>
          </div>
          <div class="flex-grow-1" style="min-width:260px">
            <select id="rckTemplate" class="form-select" style="width:100%"></select>
          </div>
        </div>

        <!-- 1. INFORMASI RACIKAN -->
        <div class="rck-card">
          <div class="rck-step">
            <span class="rck-step-no">1</span>
            <div>
              <p class="rck-step-title">Informasi Racikan</p>
              <p class="rck-step-sub">Nama racikan dan berapa banyak yang dibuat.</p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="rckNama">Nama Racikan <span class="req">*</span></label>
              <input type="text" id="rckNama" class="form-control" maxlength="255" placeholder="Contoh: Racikan Batuk Anak">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="rckJumlah">Jumlah yang Dibuat <span class="req">*</span></label>
              <div class="input-group">
                <input type="number" id="rckJumlah" class="form-control" min="1" step="1" placeholder="10">
                <input type="text" id="rckSatuan" class="form-control" list="rckSatuanList" maxlength="100" placeholder="Satuan, mis. Pulvis" style="max-width:55%">
              </div>
              <datalist id="rckSatuanList">
                <option value="Pulvis">
                <option value="Kapsul">
                <option value="Salep">
                <option value="Krim">
                <option value="Sirup">
                <option value="Botol">
                <option value="Pot">
              </datalist>
              <div class="rck-help mt-1">Contoh: 10 Pulvis = dibuat 10 bungkus puyer.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Tipe Racik <span class="req">*</span></label>
              <div class="row g-2">
                <div class="col-md-6">
                  <input type="radio" class="btn-check" name="rckTipe" id="rckTipeDtd" value="DTD">
                  <label class="tipe-option" for="rckTipeDtd">
                    <div class="tipe-nama"><i class="fas fa-cube me-1 text-primary"></i> DTD</div>
                    <div class="tipe-desc">Jumlah bahan diisi <b>untuk 1 bungkus</b>. Total dihitung otomatis (per bungkus × jumlah dibuat).</div>
                  </label>
                </div>
                <div class="col-md-6">
                  <input type="radio" class="btn-check" name="rckTipe" id="rckTipeNonDtd" value="NON_DTD" checked>
                  <label class="tipe-option" for="rckTipeNonDtd">
                    <div class="tipe-nama"><i class="fas fa-cubes me-1 text-primary"></i> Non DTD</div>
                    <div class="tipe-desc">Jumlah bahan diisi <b>total untuk semua bungkus</b>, lalu dibagi rata.</div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. ATURAN PAKAI -->
        <div class="rck-card">
          <div class="rck-step">
            <span class="rck-step-no">2</span>
            <div>
              <p class="rck-step-title">Aturan Pakai</p>
              <p class="rck-step-sub">Isi frekuensi, signatura akan terisi otomatis.</p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Frekuensi Pemberian</label>
              <div class="input-group">
                <input type="number" id="rckFrekKali" class="form-control" min="0" step="any" placeholder="3">
                <span class="input-group-text">kali ×</span>
                <input type="number" id="rckFrekDosis" class="form-control" min="0" step="any" placeholder="1">
                <select id="rckFrekPeriode" class="form-select">
                  <option value="">Periode</option>
                  <option value="Hari">per Hari</option>
                  <option value="Minggu">per Minggu</option>
                  <option value="Bulan">per Bulan</option>
                </select>
              </div>
              <div class="rck-help mt-1">Contoh: 3 kali × 1 per Hari.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="rckSigna">Signatura <span class="req">*</span></label>
              <input type="text" id="rckSigna" class="form-control" maxlength="255" placeholder="Contoh: 3x1 sesudah makan">
              <div class="rck-help mt-1">Ketik <b>-</b> (strip) jika tidak ada signatura.</div>
            </div>
          </div>
        </div>

        <!-- 3. BAHAN -->
        <div class="rck-card">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div class="rck-step mb-2">
              <span class="rck-step-no">3</span>
              <div>
                <p class="rck-step-title">Bahan Racikan <span class="badge bg-light text-dark ms-1" id="rckBahanCount">0</span></p>
                <p class="rck-step-sub" id="rckTipeHelp"></p>
              </div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="rckTambahBahan">
              <i class="fas fa-plus me-1"></i> Tambah Bahan
            </button>
          </div>

          <div class="table-responsive">
            <table class="table bahan-table align-top mb-0">
              <thead>
                <tr>
                  <th style="width:40px">#</th>
                  <th style="min-width:260px">Nama Obat <span class="req">*</span></th>
                  <th style="width:150px" id="rckQtyHeader">Jumlah <span class="req">*</span></th>
                  <th style="width:130px">Total Diambil</th>
                  <th style="min-width:200px">Keterangan</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody id="rckBahanList"></tbody>
            </table>
          </div>
        </div>

        <!-- RINGKASAN -->
        <div class="rck-summary" id="rckSummary"></div>
      </div>
      <div class="modal-footer bg-white">
        <div class="form-check me-auto">
          <input class="form-check-input" type="checkbox" id="rckSimpanTemplate">
          <label class="form-check-label" for="rckSimpanTemplate">
            <i class="fas fa-bookmark text-warning me-1"></i> Simpan sebagai template racikan
          </label>
          <div class="rck-help">Bisa dipakai lagi untuk pasien lain. Nama yang sama akan ditimpa.</div>
        </div>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="rckSimpan"><i class="fas fa-save me-1"></i> Simpan Racikan</button>
      </div>
    </form>
  </div>
</div>
  <?php require 'library.php'; ?>
<script>
  $(function() {
    const rckApi = 'controller/farmasi/racikan.php';
    const $modal = $('#racikanModal');
    const $list = $('#rckBahanList');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
    const tipeRacik = () => $('input[name="rckTipe"]:checked').val() || 'NON_DTD';
    const angka = (v) => Math.round(v * 10000) / 10000;
    const fmt = (v) => Number(v).toLocaleString('id-ID', {
      maximumFractionDigits: 4
    });

    // select2 obat (ajax) untuk satu baris bahan
    function initSelectObat($select) {
      $select.select2({
        dropdownParent: $modal,
        width: '100%',
        placeholder: 'Ketik nama obat...',
        ajax: {
          url: rckApi,
          dataType: 'json',
          delay: 250,
          data: params => ({
            obat: 1,
            q: params.term || ''
          }),
          processResults: data => ({
            results: data.results || []
          })
        }
      });
    }

    function kosongkanBahan() {
      $list.find('.bahan-obat').each(function() {
        if ($(this).data('select2')) $(this).select2('destroy');
      });
      $list.empty();
    }

    function tambahBahan(b = {}, buka = false) {
      $list.find('.bahan-empty-row').remove();
      const $row = $(`
        <tr class="bahan-row">
          <td><span class="bahan-no"></span></td>
          <td><select class="form-select bahan-obat"></select></td>
          <td>
            <div class="input-group">
              <input type="number" class="form-control bahan-qty" min="0" step="any" placeholder="mis. 1">
            </div>
          </td>
          <td><span class="bahan-total">-</span></td>
          <td><input type="text" class="form-control bahan-catatan" maxlength="1000" placeholder="Opsional"></td>
          <td class="text-end">
            <button type="button" class="btn btn-light text-danger bahan-hapus" title="Hapus bahan">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>`);
      $row.attr('data-id', b.id || '');
      const $select = $row.find('.bahan-obat');
      if (b.id_pharmacy) {
        $select.append(new Option(b.nama || ('#' + b.id_pharmacy), b.id_pharmacy, true, true));
      }
      $row.find('.bahan-qty').val(b.qty ?? '');
      $row.find('.bahan-catatan').val(b.catatan || '');
      $list.append($row);
      initSelectObat($select);
      perbarui();
      if (buka) $select.select2('open');
    }

    // hitung total per bahan + label + ringkasan
    function perbarui() {
      const dtd = tipeRacik() === 'DTD';
      const jumlah = parseFloat($('#rckJumlah').val()) || 0;
      const satuan = $('#rckSatuan').val().trim() || 'bungkus';
      const $rows = $list.find('.bahan-row');

      $('#rckQtyHeader').html((dtd ? 'Jumlah / 1 Bungkus' : 'Jumlah Total') + ' <span class="req">*</span>');
      $('#rckTipeHelp').html(dtd ?
        'DTD: isi jumlah bahan untuk <b>1 bungkus</b>, total dihitung otomatis.' :
        'Non DTD: isi jumlah <b>total</b> bahan untuk seluruh racikan.');
      $('#rckBahanCount').text($rows.length);

      if (!$rows.length) {
        $list.html(`<tr class="bahan-empty-row"><td colspan="6" class="bahan-empty">
          <i class="fas fa-pills fs-4 d-block mb-2"></i>Belum ada bahan. Klik <b>Tambah Bahan</b>.</td></tr>`);
      }

      $rows.each(function(i) {
        $(this).find('.bahan-no').text(i + 1);
        const qty = parseFloat($(this).find('.bahan-qty').val()) || 0;
        let total = '-';
        if (qty > 0) {
          const t = dtd ? (jumlah > 0 ? angka(qty * jumlah) : null) : qty;
          if (t === null) {
            total = '<span class="text-muted fw-normal">isi jumlah dibuat</span>';
          } else {
            total = Number.isInteger(t) ? fmt(t) :
              `${fmt(Math.ceil(t))}<small>${fmt(t)} dibulatkan ke atas</small>`;
          }
        }
        $(this).find('.bahan-total').html(total);
      });

      // ringkasan
      const nama = $('#rckNama').val().trim();
      const kali = $('#rckFrekKali').val(),
        dosis = $('#rckFrekDosis').val(),
        periode = $('#rckFrekPeriode').val();
      const frek = kali && dosis && periode ? `${kali} × ${dosis} per ${periode}` : '';
      const signa = $('#rckSigna').val().trim();
      const bagian = [
        `<b>${esc(nama || 'Racikan tanpa nama')}</b>`,
        jumlah ? `${esc(jumlah)} ${esc(satuan)}` : '<span class="text-muted">jumlah belum diisi</span>',
        dtd ? 'DTD' : 'Non DTD',
        `${$rows.length} bahan`,
        frek ? esc(frek) : (signa ? `Signa: ${esc(signa)}` : '<span class="text-muted">aturan pakai belum diisi</span>')
      ];
      $('#rckSummary').html(`<i class="fas fa-clipboard-check me-1 text-primary"></i> ${bagian.join(' &nbsp;•&nbsp; ')}`);
    }

    function isiForm(d) {
      $('#rckNama').val(d.nama || '');
      $(d.tipe === 'DTD' ? '#rckTipeDtd' : '#rckTipeNonDtd').prop('checked', true);
      $('#rckJumlah').val(d.jumlah || '');
      $('#rckSatuan').val(d.satuan || '');
      $('#rckSigna').val(d.signa || '');
      $('#rckFrekKali').val(d.frek_kali || '');
      $('#rckFrekDosis').val(d.frek_dosis || '');
      $('#rckFrekPeriode').val(d.frek_periode || '');
      $modal.find('.is-invalid').removeClass('is-invalid');
      kosongkanBahan();
      (d.bahan || []).forEach(b => tambahBahan(b));
      if (!(d.bahan || []).length) tambahBahan();
      perbarui();
    }

    // template racikan
    $('#rckTemplate').select2({
      dropdownParent: $modal,
      width: '100%',
      allowClear: true,
      placeholder: 'Cari template racikan...',
      ajax: {
        url: rckApi,
        dataType: 'json',
        delay: 250,
        data: params => ({
          templates: 1,
          q: params.term || ''
        }),
        processResults: data => ({
          results: data.results || []
        })
      }
    }).on('select2:select', function(e) {
      fetch(`${rckApi}?template=${encodeURIComponent(e.params.data.id)}`)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message || 'Template tidak dapat dimuat.', 'error');
            return;
          }
          isiForm(res.data); // bahan lama diganti bahan dari template
        });
    });

    // buka form dari tombol "+ Racikan" di Actions
    $(document).on('click', '.racikan-btn', function() {
      const id = $(this).data('id');
      $('#rckId').val(id);
      $('#racikanInfo').text(`Resep #${id}`);
      $('#rckSimpanTemplate').prop('checked', false);
      $('#rckTemplate').val(null).trigger('change');
      kosongkanBahan();
      $list.html('<tr><td colspan="6" class="bahan-empty"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr>');
      $modal.modal('show');

      fetch(`${rckApi}?id=${encodeURIComponent(id)}`)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            $modal.modal('hide');
            Swal.fire('Gagal!', res.message || 'Data racikan tidak dapat dimuat.', 'error');
            return;
          }
          isiForm(res.data);
        })
        .catch(() => {
          $modal.modal('hide');
          Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error');
        });
    });

    $('#rckTambahBahan').on('click', () => tambahBahan({}, true));
    $list.on('click', '.bahan-hapus', function() {
      const $row = $(this).closest('.bahan-row');
      const $select = $row.find('.bahan-obat');
      if ($select.data('select2')) $select.select2('destroy');
      $row.remove();
      perbarui();
    });
    $list.on('input', '.bahan-qty', perbarui);
    $list.on('change', '.bahan-obat', function() {
      $(this).removeClass('is-invalid');
    });
    $modal.on('input change', '#rckNama, #rckJumlah, #rckSatuan, #rckSigna, #rckFrekKali, #rckFrekDosis, #rckFrekPeriode, input[name="rckTipe"]', function() {
      $(this).removeClass('is-invalid');
      perbarui();
    });

    // signatura otomatis dari frekuensi (kalau signatura masih kosong / hasil otomatis)
    $('#rckFrekKali, #rckFrekDosis').on('input', function() {
      const kali = $('#rckFrekKali').val(),
        dosis = $('#rckFrekDosis').val();
      const $signa = $('#rckSigna');
      if (kali && dosis && ($signa.val().trim() === '' || $signa.data('auto'))) {
        $signa.val(`${kali}x${dosis}`).data('auto', true);
        perbarui();
      }
    });
    $('#rckSigna').on('input', function() {
      $(this).data('auto', false);
    });

    function tandai($el) {
      $el.addClass('is-invalid');
      return false;
    }

    $('#racikanForm').on('submit', function(e) {
      e.preventDefault();
      $modal.find('.is-invalid').removeClass('is-invalid');

      // validasi di browser supaya field yang kurang langsung terlihat (validasi utama tetap di server)
      let valid = true;
      if (!$('#rckNama').val().trim()) valid = tandai($('#rckNama'));
      if (!(parseInt($('#rckJumlah').val(), 10) > 0)) valid = tandai($('#rckJumlah'));
      if (!$('#rckSatuan').val().trim()) valid = tandai($('#rckSatuan'));
      if (!$('#rckSigna').val().trim()) valid = tandai($('#rckSigna'));
      const $rows = $list.find('.bahan-row');
      $rows.each(function() {
        if (!$(this).find('.bahan-obat').val()) valid = tandai($(this).find('.bahan-obat'));
        if (!(parseFloat($(this).find('.bahan-qty').val()) > 0)) valid = tandai($(this).find('.bahan-qty'));
      });
      if (!$rows.length) {
        Swal.fire('Lengkapi data', 'Tambahkan minimal 1 bahan racikan.', 'warning');
        return;
      }
      if (!valid) {
        Swal.fire('Lengkapi data', 'Kolom bertanda merah wajib diisi.', 'warning');
        return;
      }

      const payload = {
        id: $('#rckId').val(),
        nama: $('#rckNama').val().trim(),
        tipe: tipeRacik(),
        jumlah: $('#rckJumlah').val(),
        satuan: $('#rckSatuan').val().trim(),
        signa: $('#rckSigna').val().trim(),
        frek_kali: $('#rckFrekKali').val(),
        frek_dosis: $('#rckFrekDosis').val(),
        frek_periode: $('#rckFrekPeriode').val(),
        simpan_template: $('#rckSimpanTemplate').is(':checked'),
        bahan: $rows.map(function() {
          return {
            id: $(this).attr('data-id') || null,
            id_pharmacy: $(this).find('.bahan-obat').val(),
            qty: $(this).find('.bahan-qty').val(),
            catatan: $(this).find('.bahan-catatan').val().trim()
          };
        }).get()
      };

      const $btn = $('#rckSimpan').prop('disabled', true);
      fetch(rckApi, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            $modal.modal('hide');
            Swal.fire('Berhasil!', res.message, 'success');
            muatTiket();
          } else {
            Swal.fire('Gagal!', res.message || 'Racikan gagal disimpan.', 'error');
          }
        })
        .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error'))
        .finally(() => $btn.prop('disabled', false));
    });
  });
</script>
<script>
  (function() {
    const NO_VISIT = <?= json_encode($noVisit) ?>;
    const NO_RM = <?= json_encode($noRm) ?>;
    const RME = <?= json_encode($rme !== '' ? $rme : 'c') ?>;
    const apiUrl = 'controller/visit/permintaanFarmasi?no=' + encodeURIComponent(NO_VISIT);
    const STATUS = {
      0: 'Belum dikirim',
      1: 'Terkirim ke farmasi',
      2: 'Persiapan',
      3: 'Selesai'
    };
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
    const angka = (v) => {
      const n = Number(v);
      return Number.isFinite(n) ? n.toLocaleString('id-ID', { maximumFractionDigits: 2 }) : esc(v);
    };
    const waktu = (s) => {
      const d = s ? new Date(String(s).replace(' ', 'T')) : null;
      return d && !isNaN(d) ? d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
    };

    let tiket = [];
    let filter = 'all';
    let kunjunganBatal = false;
    let alergi = '';
    let memuat = null;

    const $list = $('#tiketList');
    // nama dagang sering diisi '.' / '-' / sama dengan generik -> jangan ditampilkan
    const dagangBerarti = (it) => /[a-z0-9]{2,}/i.test(it.nama_dagang || '') && String(it.nama_dagang).trim().toLowerCase() !== String(it.nama || '').trim().toLowerCase();
    const isRacikan = (t) => String(t.tipe_obat || '').trim().toLowerCase() === 'racikan';
    const siapKirim = () => tiket.filter(t => +t.status_permintaan === 0 && t.jumlah_item > 0);

    function muatTiket() {
      if (memuat) memuat.abort();
      memuat = new AbortController();
      $('#btnMuatUlang i').addClass('ti-spin');
      return fetch(apiUrl, { signal: memuat.signal, cache: 'no-store' })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message || 'Gagal memuat tiket.');
          tiket = (res.data || []).map(t => ({ ...t, items: t.items || [], jumlah_item: +t.jumlah_item || 0 }));
          kunjunganBatal = !!(res.kunjungan && +res.kunjungan.visit_status === 99);
          $('#alertBatal').toggleClass('d-none', !kunjunganBatal);
          $('#btnTambah').prop('disabled', kunjunganBatal);
          render();
        })
        .catch(err => {
          if (err.name === 'AbortError') return;
          $list.html(`<div class="col-12 kosong text-danger"><i class="ti ti-alert-triangle d-block mb-2"></i>${esc(err.message || 'Terjadi kesalahan pada server.')}
            <div class="mt-2"><button type="button" class="btn btn-outline-dark btn-sm" onclick="muatTiket()">Coba lagi</button></div></div>`);
        })
        .finally(() => $('#btnMuatUlang i').removeClass('ti-spin'));
    }
    window.muatTiket = muatTiket;

    function kartu(t) {
      const st = +t.status_permintaan || 0;
      const id = +t.id_permintaan_farmasi;
      const rck = isRacikan(t);
      const tipeKls = rck ? 'Racikan' : (t.tipe_obat === 'BMHP' ? 'BMHP' : 'Non');
      const urlDetail = `module/admin/permintaan_farmasi_details?no=${encodeURIComponent(NO_VISIT)}&rm=${encodeURIComponent(NO_RM)}&rme=${encodeURIComponent(RME)}&id=${id}&type=${encodeURIComponent(t.tipe_obat || '')}`;

      let aksi = '';
      if (rck && st !== 3) {
        aksi += `<button type="button" class="btn btn-warning btn-sm racikan-btn" data-id="${id}"><i class="ti ti-flask me-1"></i>Form Racikan</button>`;
      }
      aksi += `<a class="btn btn-primary btn-sm" href="${urlDetail}"><i class="ti ti-${st === 3 ? 'list-details' : 'pencil'} me-1"></i>${st === 3 ? 'Lihat Obat' : (t.jumlah_item ? 'Ubah Obat' : 'Isi Obat')}</a>`;
      if (st === 0) {
        aksi += t.jumlah_item ?
          `<button type="button" class="btn btn-success btn-sm kirim-btn" data-id="${id}"><i class="ti ti-send me-1"></i>Kirim</button>` :
          `<button type="button" class="btn btn-success btn-sm" disabled title="Isi rincian obat terlebih dahulu"><i class="ti ti-send me-1"></i>Kirim</button>`;
      }
      if (st !== 3) {
        aksi += `<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="${id}" title="Hapus tiket"><i class="ti ti-trash"></i></button>`;
      }

      const langkah = ['Dibuat', 'Terkirim', 'Persiapan', 'Selesai'].map((l, i) => {
        const kls = i <= st ? 'ok' : (i === st + 1 ? 'kini' : '');
        return `<div class="${kls}">${l}</div>`;
      }).join('');

      let racik = '';
      if (rck) {
        const bagian = [
          t.rck_nama ? `<b>${esc(t.rck_nama)}</b>` : '<b>Racikan</b>',
          t.rck_jumlah ? `${esc(t.rck_jumlah)} ${esc(t.rck_satuan || '')}` : '',
          t.rck_tipe ? (t.rck_tipe === 'DTD' ? 'DTD' : 'Non DTD') : '',
          t.rck_signa ? `Signa: ${esc(t.rck_signa)}` : ''
        ].filter(Boolean);
        racik = `<div class="racik"><i class="ti ti-flask me-1"></i>${bagian.join(' &nbsp;•&nbsp; ')}</div>`;
      }

      const obat = t.items.length ? `
        <div class="table-responsive"><table class="obat">
          <thead><tr><th style="width:28px">#</th><th>Obat</th><th class="text-end">Qty</th>${rck ? '' : '<th>Signa</th>'}<th>Ket.</th></tr></thead>
          <tbody>${t.items.map((it, i) => `<tr>
            <td>${i + 1}</td>
            <td>${esc(it.nama || '-')}${dagangBerarti(it) ? `<div class="dagang">${esc(it.nama_dagang)}</div>` : ''}</td>
            <td class="text-end text-nowrap">${angka(rck && it.qty_racik != null ? it.qty_racik : it.qty)} <span class="text-muted small">${esc(it.satuan || '')}</span></td>
            ${rck ? '' : `<td>${esc(it.signa || '-')}</td>`}
            <td>${esc(it.catatan || '-')}</td>
          </tr>`).join('')}</tbody>
        </table></div>` :
        `<div class="kosong-obat"><i class="ti ti-alert-triangle me-1"></i>Belum ada obat. ${st === 3 ? '' : `<a href="${urlDetail}" class="fw-semibold">Isi rincian obat</a> sebelum dikirim.`}</div>`;

      return `
        <div class="col-12 col-xxl-6">
          <div class="tiket" data-st="${st}">
            <div class="atas">
              <div class="aksi">${aksi}</div>
              <span class="st st-${st}">${STATUS[st] || 'Belum dikirim'}</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="tipe tipe-${tipeKls}">${esc(t.tipe_obat || '-')}</span>
              <span class="judul">Tiket #${id}</span>
              <span class="meta ms-auto"><i class="ti ti-clock me-1"></i>${waktu(t.created_at)} · ${t.jumlah_item} obat</span>
            </div>
            <div class="langkah">${langkah}</div>
            ${racik}
            ${obat}
            ${t.catatan_permintaan ? `<div class="catatan"><b>Catatan:</b> ${esc(t.catatan_permintaan)}</div>` : ''}
          </div>
        </div>`;
    }

    function render() {
      const n = { all: tiket.length, 0: 0, 1: 0, 2: 0, 3: 0 };
      tiket.forEach(t => { n[+t.status_permintaan || 0]++; });
      Object.keys(n).forEach(k => $(`#filterStatus [data-n="${k}"]`).text(n[k]));

      const siap = siapKirim();
      $('#btnKirimObatCount').text(siap.length);
      $('#btnKirimObat').toggleClass('d-none', siap.length < 2);

      const q = String($('#customSearch').val() || '').trim().toLowerCase();
      const tampil = tiket.filter(t => {
        if (filter !== 'all' && String(+t.status_permintaan || 0) !== filter) return false;
        if (!q) return true;
        const teks = [t.tipe_obat, t.rck_nama, t.catatan_permintaan, t.id_permintaan_farmasi, ...t.items.map(it => `${it.nama} ${it.nama_dagang || ''} ${it.signa || ''} ${it.catatan || ''}`)].join(' ').toLowerCase();
        return teks.includes(q);
      });

      if (!tiket.length) {
        $list.html(`<div class="col-12 kosong"><i class="ti ti-pill d-block mb-2"></i><b>Belum ada permintaan farmasi.</b>
          <div class="small mb-3">Buat tiket untuk resep obat, racikan, atau BMHP pasien ini.</div>
          ${kunjunganBatal ? '' : '<button type="button" class="btn btn-primary" onclick="$(\'#btnTambah\').trigger(\'click\')"><i class="ti ti-plus me-1"></i>Tambah Tiket</button>'}</div>`);
        return;
      }
      if (!tampil.length) {
        $list.html('<div class="col-12 kosong"><i class="ti ti-search d-block mb-2"></i>Tidak ada tiket yang cocok dengan filter.</div>');
        return;
      }
      $list.html(tampil.map(kartu).join(''));
    }

    $('#filterStatus').on('click', '.chip', function() {
      filter = String($(this).data('f'));
      $('#filterStatus .chip').removeClass('on');
      $(this).addClass('on');
      render();
    });
    $('#customSearch').on('input', render);
    $('#btnMuatUlang').on('click', muatTiket);
    // status tiket diubah farmasi dari layar lain -> segarkan saat tab dibuka lagi
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') muatTiket();
    });

    // 🔹 Tambah tiket
    function aturRacikan() {
      const rck = $('input[name="tipe_obat"]:checked').val() === 'Racikan';
      $('.racikan-field').toggle(rck);
      if (!rck) $('.racikan-field input').val('');
    }
    $(document).on('change', 'input[name="tipe_obat"]', aturRacikan);

    $('#btnTambah').on('click', function() {
      if (kunjunganBatal) return;
      $('#programForm')[0].reset();
      $('#id_permintaan_farmasi').val('');
      aturRacikan();
      $('#programModal').modal('show');
    });

    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      const jml = $('#rck_jumlah').val();
      if (jml !== '' && !(Number.isInteger(+jml) && +jml >= 1 && +jml <= 10000)) {
        $('#rck_jumlah').addClass('is-invalid').trigger('focus');
        return;
      }
      $('#rck_jumlah').removeClass('is-invalid');
      const tipe = $('input[name="tipe_obat"]:checked').val();
      const $btn = $('#btnSimpanTiket').prop('disabled', true);
      fetch(apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams(new FormData(this))
        })
        .then(res => res.json())
        .then(data => {
          if (data.status !== 'success') {
            Swal.fire('Gagal!', data.message || 'Tiket gagal dibuat.', 'error');
            return;
          }
          $('#programModal').modal('hide');
          muatTiket();
          Swal.fire({
            icon: 'success',
            title: 'Tiket dibuat',
            text: tipe === 'Racikan' ? 'Lanjut isi bahan racikan sekarang?' : 'Lanjut isi rincian obat sekarang?',
            showCancelButton: true,
            confirmButtonText: tipe === 'Racikan' ? 'Isi racikan' : 'Isi obat',
            cancelButtonText: 'Nanti',
            reverseButtons: true
          }).then(r => {
            if (!r.isConfirmed || !data.id) return;
            if (tipe === 'Racikan') {
              $('<button class="racikan-btn d-none">').attr('data-id', data.id).appendTo('body').trigger('click').remove();
            } else {
              window.location.href = `module/admin/permintaan_farmasi_details?no=${encodeURIComponent(NO_VISIT)}&rm=${encodeURIComponent(NO_RM)}&rme=${encodeURIComponent(RME)}&id=${data.id}&type=${encodeURIComponent(tipe)}`;
            }
          });
        })
        .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error'))
        .finally(() => $btn.prop('disabled', false));
    });

    // 📤 Kirim obat
    $(document).on('click', '.kirim-btn', function() {
      kirimObat([$(this).data('id')]);
    });
    $('#btnKirimObat').on('click', function() {
      const ids = siapKirim().map(t => t.id_permintaan_farmasi);
      if (!ids.length) {
        Swal.fire('Info', 'Tidak ada tiket berisi obat yang belum dikirim.', 'info');
        return;
      }
      kirimObat(ids);
    });

    function kirimObat(ids) {
      const kosong = tiket.filter(t => +t.status_permintaan === 0 && !t.jumlah_item).length;
      const daftar = tiket.filter(t => ids.map(String).includes(String(t.id_permintaan_farmasi)));
      const html = `<div class="text-start small">
          ${daftar.map(t => `<div>• <b>Tiket #${esc(t.id_permintaan_farmasi)}</b> ${esc(t.tipe_obat || '')} — ${t.jumlah_item} obat</div>`).join('')}
          ${alergi ? `<div class="alert alert-danger py-2 mt-2 mb-0"><b>Alergi obat:</b> ${esc(alergi)}</div>` : ''}
          ${ids.length > 1 && kosong ? `<div class="text-muted mt-2">${kosong} tiket tanpa obat tidak ikut dikirim.</div>` : ''}
        </div>`;
      Swal.fire({
        title: ids.length > 1 ? `Kirim ${ids.length} tiket ke farmasi?` : 'Kirim tiket ke farmasi?',
        html,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, kirim',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({
          title: 'Sedang mengirim...',
          text: 'Mohon tunggu, pasien BPJS juga dikirim ke PCare.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => Swal.showLoading()
        });
        $.ajax({
          url: 'controller/farmasi/kirimObat.php',
          type: 'POST',
          contentType: 'application/json',
          dataType: 'json',
          data: JSON.stringify({ ids }),
          success: function(res) {
            if (res.status == 'success') {
              Swal.fire('Berhasil!', `${res.total || ids.length} tiket dikirim. ${res.message || ''}`, 'success');
            } else {
              Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
            }
            muatTiket();
          },
          error: function(xhr) {
            Swal.fire('Gagal!', (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.', 'error');
            muatTiket();
          }
        });
      });
    }

    // 🔹 Hapus tiket
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const t = tiket.find(x => String(x.id_permintaan_farmasi) === String(id)) || {};
      const terkirim = +t.status_permintaan > 0;
      Swal.fire({
        title: `Hapus tiket #${id}?`,
        html: `Tiket <b>${esc(t.tipe_obat || '')}</b> beserta <b>${t.jumlah_item || 0} rincian obat</b> akan dihapus.` +
          (terkirim ? '<div class="alert alert-warning py-2 mt-2 mb-0 small">Tiket ini <b>sudah dikirim ke farmasi</b>. Pastikan farmasi tahu tiket dibatalkan.</div>' : ''),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (!result.isConfirmed) return;
        fetch(apiUrl + '&id=' + encodeURIComponent(id), { method: 'DELETE' })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire('Berhasil!', data.message || 'Tiket dihapus.', 'success');
            } else {
              Swal.fire('Gagal!', data.message || 'Tiket gagal dihapus.', 'error');
            }
            muatTiket();
          })
          .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error'));
      });
    });

    // ⚠️ alergi obat
    $.ajax({
      url: 'controller/master/getAlergiObat.php',
      type: 'POST',
      data: { visit_id: NO_VISIT },
      dataType: 'json',
      success: function(response) {
        alergi = String(response.data?.desAlObat || '').trim();
        $('.peringatanAlergi').toggleClass('d-none', !(response.status === 'success' && alergi));
        $('#desAplergi').text(alergi);
      }
    });

    muatTiket();
  })();
</script>
</body>

</html>
