<?php
/**
 * Kerangka bersama halaman form surat (bagian atas): head, header, filter, tabel, pembuka modal.
 * Halaman pemanggil menyiapkan $SF lalu:
 *   require '_kerangka_atas.php';  ?> ...field khusus surat... <?php  require '_kerangka_bawah.php';
 *
 * $SF = [
 *   'jenis' => 'sakit',                 // kunci NS_JENIS (controller/letter/nomorSurat.php)
 *   'judul' => 'Surat Keterangan Sakit',
 *   'sub'   => 'Keterangan istirahat ...',
 *   'ikon'  => 'fa-bed', 'warna' => 'danger',
 *   'kolom' => ['Periode istirahat', ...] // judul kolom tambahan tabel (render di JS: cfg.kolom)
 *   'modal' => 'modal-lg',
 * ]
 */
if (!isset($SF) || !is_array($SF)) { http_response_code(404); exit; }
$title = $SF['judul'];
require '../../controller/view.php';
require_once __DIR__ . '/../../controller/letter/nomorSurat.php';
date_default_timezone_set('Asia/Jakarta');
$SF_IDC = (string) ($_SESSION['id_customer'] ?? '');
$SF_ADMIN = in_array((string) ($_SESSION['roles'] ?? ''), ['admin', 'superadmin', 'administrator'], true);
session_write_close();
try {
  $SF_SET = nsSetting($koneksi, $SF_IDC, $SF['jenis']);
} catch (Throwable $e) {
  error_log('[form surat] ' . $e->getMessage());
  $SF_SET = ['ada' => false, 'mode' => null, 'format' => '', 'terakhir' => 0];
}
$sfH = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$SF_WARNA = $SF['warna'] ?? 'primary';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .sf-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .sf-mode { border-radius: 12px; border: 1px solid #e5eaef; padding: 10px 14px; font-size: .84rem; color: #1f2937; }
    .sf-mode.auto { border-color: #c7d7fe; background: #f5f8ff; }
    .sf-mode.manual { border-color: #a7f3d0; background: #f0fdf9; }
    .sf-mode.belum { border-color: #fde68a; background: #fffbeb; }
    .sf-mode code { color: #1e293b; background: rgba(255, 255, 255, .7); border-radius: 4px; padding: 1px 5px; }
    .sf-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 4px; color: #334155; }
    .sf-no { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .78rem; font-weight: 600; color: #111827; white-space: nowrap; }
    .sf-nw { white-space: nowrap; }
    .sf-dok { display: inline-block; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
    #sfCepat .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    #sfTable td { vertical-align: middle; }
    #sfTable > thead > tr > th, #sfTable > tbody > tr > td { padding: .6rem .55rem; }
    #sfTable > thead > tr > th:first-child, #sfTable > tbody > tr > td:first-child { padding-left: .9rem; }
    #sfTable > thead > tr > th:last-child, #sfTable > tbody > tr > td:last-child { padding-right: .9rem; width: 1%; white-space: nowrap; }
    .sf-nama { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sf-ket { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sf-sec { font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #64748b; margin: 18px 0 8px; display: flex; align-items: center; gap: 8px; }
    .sf-sec::after { content: ''; flex: 1; border-top: 1px solid #eef1f4; }
    .sf-sec:first-child { margin-top: 0; }
    .sf-pasien { border: 1px solid #e5eaef; border-radius: 12px; background: #f8fafc; padding: 10px 12px; font-size: .84rem; color: #1f2937; }
    .sf-hasil b { color: #111827; }
    .sf-hasil small { color: #475569; }
    .select2-container--open .select2-dropdown { z-index: 2000; }
    .sf-opsi { display: flex; flex-wrap: wrap; gap: 6px; }
    .sf-opsi input { display: none; }
    .sf-opsi label { border: 1px solid #e5eaef; border-radius: 20px; padding: 5px 12px; font-size: .8rem; cursor: pointer; background: #fff; color: #334155; }
    .sf-opsi input:checked + label { border-color: #5d87ff; background: #ecf2ff; color: #3554d1; font-weight: 600; }
    .badge.bg-warning-subtle { color: #1f2937 !important; background-color: #fef3c7 !important; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="module/admin/form-letter" class="btn btn-light btn-sm" title="Kembali ke daftar jenis surat"><i class="fas fa-arrow-left"></i></a>
                <span class="sf-icon bg-<?= $SF_WARNA ?>-subtle text-<?= $SF_WARNA ?>"><i class="fas <?= $sfH($SF['ikon']) ?>"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0"><?= $sfH($SF['judul']) ?></h5>
                  <div class="text-muted small"><?= $sfH($SF['sub'] ?? '') ?></div>
                </div>
                <button type="button" class="btn btn-primary" id="sfTambah" <?= $SF_SET['ada'] ? '' : 'disabled' ?>><i class="fas fa-plus me-1"></i>Buat surat</button>
              </div>
            </div>
          </div>

          <?php if (!$SF_SET['ada']): ?>
            <div class="sf-mode belum d-flex flex-wrap align-items-center gap-2 mb-3">
              <i class="fas fa-triangle-exclamation text-warning"></i>
              <div class="flex-grow-1"><b>Nomor surat belum diatur.</b> Surat belum bisa dibuat sampai pengaturan nomor surat disimpan.</div>
              <?php if ($SF_ADMIN): ?><a class="btn btn-sm btn-warning text-dark" href="module/admin/setting_surat">Atur nomor surat</a><?php else: ?><span class="small">Hubungi admin.</span><?php endif; ?>
            </div>
          <?php elseif ($SF_SET['mode'] === 'AUTO'): ?>
            <div class="sf-mode auto d-flex flex-wrap align-items-center gap-2 mb-3">
              <i class="fas fa-wand-magic-sparkles text-primary"></i>
              <div><b>Nomor otomatis</b> · format <code><?= $sfH($SF_SET['format']) ?></code> · nomor terakhir <b id="sfTerakhir"><?= (int) $SF_SET['terakhir'] ?></b></div>
            </div>
          <?php else: ?>
            <div class="sf-mode manual d-flex align-items-center gap-2 mb-3">
              <i class="fas fa-pen-to-square text-success"></i>
              <div><b>Nomor manual.</b> Isi nomor surat sendiri saat membuat surat (tidak boleh sama dengan surat lain).</div>
            </div>
          <?php endif; ?>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="d-flex align-items-center gap-1">
                  <input type="date" id="sfDari" class="form-control form-control-sm" style="width:145px" aria-label="Tanggal surat dari">
                  <span class="text-muted small">–</span>
                  <input type="date" id="sfSampai" class="form-control form-control-sm" style="width:145px" aria-label="Tanggal surat sampai">
                </div>
                <div class="btn-group btn-group-sm" id="sfCepat">
                  <button type="button" class="btn btn-light" data-h="0">Hari ini</button>
                  <button type="button" class="btn btn-light" data-h="6">7 hari</button>
                  <button type="button" class="btn btn-light active" data-h="29">30 hari</button>
                  <button type="button" class="btn btn-light" data-h="bulan">Bulan ini</button>
                </div>
                <span class="small text-muted" id="sfJumlah"></span>
                <div class="position-relative ms-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="sfCari" class="form-control form-control-sm ps-4" placeholder="Cari nomor, nama, RM…">
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="sfTable">
                  <thead><tr>
                    <th class="text-dark fw-normal">Nomor surat</th>
                    <th class="text-dark fw-normal">Tanggal</th>
                    <th class="text-dark fw-normal">Pasien</th>
                    <?php foreach ($SF['kolom'] ?? [] as $k): ?><th class="text-dark fw-normal"><?= $sfH($k) ?></th><?php endforeach; ?>
                    <th class="text-dark fw-normal text-end">Aksi</th>
                  </tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FORM SURAT -->
  <div class="modal fade" id="sfModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog <?= $sfH($SF['modal'] ?? 'modal-lg') ?> modal-dialog-scrollable">
      <form class="modal-content" id="sfForm" novalidate autocomplete="off">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0" id="sfJudulModal"></h5><div class="small text-muted" id="sfSubModal"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <input type="hidden" name="id_patient" id="id_patient">
          <input type="hidden" name="id_visit" id="id_visit">

          <div class="sf-sec">Pasien &amp; kunjungan</div>
          <div id="sfPilihWrap">
            <select id="sfPilih" class="form-select" style="width:100%"></select>
            <div class="form-text">Ketik minimal 2 huruf nama / RM / NIK / no. BPJS. Pilih kunjungan yang menjadi dasar surat.</div>
          </div>
          <div class="sf-pasien mt-2 d-none" id="sfPasien"></div>

          <div class="sf-sec">Surat</div>
          <div class="row g-3">
            <div class="col-md-6 <?= $SF_SET['mode'] === 'MANUAL' ? '' : 'd-none' ?>">
              <label class="form-label small fw-semibold" for="nomor_surat">Nomor surat <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nomor_surat" id="nomor_surat" maxlength="100" <?= $SF_SET['mode'] === 'MANUAL' ? 'required' : '' ?>>
            </div>
            <?php if ($SF_SET['mode'] === 'AUTO'): ?>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nomor surat</label>
                <div class="form-control bg-light text-muted" id="sfNomorAuto">Dibuat otomatis saat disimpan</div>
              </div>
            <?php endif; ?>
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="tanggal_surat">Tanggal surat <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tanggal_surat" id="tanggal_surat" required>
            </div>
          </div>
          <!-- field khusus surat -->
