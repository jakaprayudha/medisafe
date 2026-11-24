<?php
/**
 * Penanda operasi — gambar lokasi tindakan pada template tubuh. Setiap simpan menjadi versi baru (tidak menimpa).
 * Logika gambar/simpan di controller/visit/operationMarker.js, API di controller/visit/operationMarkerController.php.
 */
$title = 'Penanda Operasi';
require '../../controller/view.php';
require_once __DIR__ . '/../../controller/visit/operationMarkerStore.php';
$_SESSION['operation_marker_token'] ??= bin2hex(random_bytes(32));

$rme = $_GET['rme'] ?? 'a';
$menus = [
   'a' => 'menu_rme.php',
   'b' => 'menu_rmeb.php',
   'c' => 'menu_rme_inap.php'
];
if (!is_string($rme) || !isset($menus[$rme])) {
   http_response_code(400);
   exit('Jenis RME tidak valid.');
}
$markers = operationMarkerTypes();
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$no = is_string($_GET['no'] ?? null) ? $_GET['no'] : '';
$rm = is_string($_GET['rm'] ?? null) ? $_GET['rm'] : '';
?>
<!doctype html>
<html lang="id">
<head>
   <base href="../../">
   <?php require '../../assets/template/head.php'; ?>
   <style>
      .po .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
      .po .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fee2e2; color: #991b1b; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
      .po .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
      .po .kepala small { color: #475569; }
      .po .tabs .nav-link { border: 1px solid #e2e8f0; margin: 0 6px 6px 0; color: #1f2937; font-weight: 600; padding: 6px 14px; }
      .po .tabs .nav-link.active { background: #0f172a; border-color: #0f172a; color: #fff; }
      .po .tabs .titik { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; margin-left: 6px; vertical-align: middle; }
      .po .kanvas-wrap { position: relative; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; display: flex; justify-content: center; align-items: flex-start; min-height: 320px; }
      .po .marker-canvas { display: block; max-width: 100%; max-height: 74vh; width: auto; height: auto; touch-action: none; cursor: crosshair; background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
      .po .marker-canvas.mode-text { cursor: text; }
      .po .muat { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #475569; font-size: .9rem; }
      @media (min-width: 992px) { .po .sisi { position: sticky; top: 90px; } }
      .po .panel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; background: #fff; }
      .po .panel + .panel { margin-top: 12px; }
      .po .lbl { font-size: .72rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
      .po .seg { display: flex; gap: 6px; }
      .po .seg button { flex: 1; border: 1px solid #cbd5e1; background: #fff; border-radius: 8px; padding: 7px 6px; font-weight: 600; color: #1f2937; font-size: .85rem; }
      .po .seg button.on { background: #0f172a; border-color: #0f172a; color: #fff; }
      .po .warna { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
      .po .warna button { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #cbd5e1; padding: 0; }
      .po .warna button.on { box-shadow: 0 0 0 2px #0f172a; }
      .po .warna input[type=color] { width: 32px; height: 32px; padding: 2px; border-radius: 8px; }
      .po .contoh { display: inline-block; border-radius: 50%; vertical-align: middle; }
      .po .sumber { font-size: .8rem; border-radius: 8px; padding: 6px 10px; background: #f1f5f9; color: #334155; }
      .po .sumber.lanjut { background: #e0f2fe; color: #075985; }
      .po table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
      .po table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
      .po table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
      .po table.t tr.aktif td { background: #f0f9ff; }
      .po table.t tr:last-child td { border-bottom: 0; }
      .po .aksi { white-space: nowrap; }
      .po .aksi .btn { padding: 3px 9px; font-weight: 600; }
      .po .tag { font-size: .68rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; display: inline-block; white-space: nowrap; }
      .po .tag-baru { background: #dcfce7; color: #14532d; }
      .po .catatan { font-size: .8rem; color: #334155; white-space: pre-line; max-width: 340px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
      .po .kosong { text-align: center; padding: 26px 10px; color: #475569; }
   </style>
</head>
<body>
   <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
      data-sidebar-position="fixed" data-header-position="fixed">
      <?php require 'sidebar.php'; ?>
      <div class="body-wrapper">
         <?php require 'navbar.php'; ?>
         <div class="body-wrapper-inner">
            <div class="container-fluid po">
               <?php require $menus[$rme]; ?>
               <?php require 'card-pasien.php'; ?>
               <div class="card">
                  <div class="card-body p-4">
                     <div class="kepala mb-3">
                        <span class="ic"><i class="ti ti-scissors"></i></span>
                        <div class="flex-grow-1">
                           <h5>Penanda Operasi</h5>
                           <small>Tandai lokasi tindakan pada gambar. Setiap simpan menjadi <b>versi baru</b> — versi lama tidak ditimpa.</small>
                        </div>
                        <a class="btn btn-outline-dark" target="_blank" rel="noopener" id="po-cetak-semua"
                           href="module/admin/print/formulir_penanda_operasi?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak semua versi</a>
                     </div>
                     <div class="alert alert-danger d-none" id="po-batal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — penanda operasi tidak bisa disimpan.</div>

                     <ul class="nav nav-pills tabs mb-2" role="tablist" aria-label="Gambar penanda operasi">
                        <?php foreach ($markers as $key => $label): ?>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link <?= $key === 'wanita' ? 'active' : '' ?>"
                                 id="marker-tab-<?= $h($key) ?>" data-bs-toggle="pill" data-bs-target="#marker-<?= $h($key) ?>" data-marker="<?= $h($key) ?>"
                                 role="tab" aria-controls="marker-<?= $h($key) ?>" aria-selected="<?= $key === 'wanita' ? 'true' : 'false' ?>">
                                 <?= $h($label) ?><span class="titik d-none" title="Ada perubahan belum disimpan"></span>
                              </button>
                           </li>
                        <?php endforeach; ?>
                     </ul>

                     <div class="row g-3">
                        <div class="col-lg-8 col-xl-9">
                           <div class="tab-content">
                              <?php foreach ($markers as $key => $label): ?>
                                 <div class="tab-pane fade <?= $key === 'wanita' ? 'show active' : '' ?>" id="marker-<?= $h($key) ?>" role="tabpanel" aria-labelledby="marker-tab-<?= $h($key) ?>">
                                    <div class="kanvas-wrap">
                                       <div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat gambar <?= $h($label) ?>…</div>
                                       <canvas id="canvas-<?= $h($key) ?>" class="marker-canvas d-none" aria-label="Gambar penanda operasi <?= $h($label) ?>" data-marker="<?= $h($key) ?>"></canvas>
                                    </div>
                                 </div>
                              <?php endforeach; ?>
                           </div>
                        </div>
                        <div class="col-lg-4 col-xl-3">
                          <div class="sisi">
                           <div class="panel">
                              <div class="lbl">Alat</div>
                              <div class="seg mb-3" id="po-alat">
                                 <button type="button" data-alat="stroke" class="on"><i class="ti ti-pencil me-1"></i>Coretan</button>
                                 <button type="button" data-alat="text"><i class="ti ti-typography me-1"></i>Teks</button>
                              </div>
                              <div id="po-teks-wrap" class="mb-3 d-none">
                                 <label class="lbl d-block" for="po-teks">Teks</label>
                                 <input id="po-teks" type="text" maxlength="200" class="form-control form-control-sm" placeholder="mis. Insisi 5 cm">
                                 <div class="form-text">Klik gambar untuk menempatkan teks.</div>
                              </div>
                              <div class="lbl">Warna</div>
                              <div class="warna mb-3" id="po-warna">
                                 <button type="button" data-warna="#dc2626" style="background:#dc2626" class="on" title="Merah"></button>
                                 <button type="button" data-warna="#2563eb" style="background:#2563eb" title="Biru"></button>
                                 <button type="button" data-warna="#16a34a" style="background:#16a34a" title="Hijau"></button>
                                 <button type="button" data-warna="#111827" style="background:#111827" title="Hitam"></button>
                                 <input type="color" id="po-warna-lain" value="#dc2626" title="Warna lain" class="form-control form-control-color">
                              </div>
                              <label class="lbl d-flex align-items-center" for="po-ukuran">Ukuran <span class="ms-auto text-dark" id="po-ukuran-nilai">4</span></label>
                              <div class="d-flex align-items-center gap-2 mb-3">
                                 <input id="po-ukuran" type="range" min="1" max="20" value="4" class="form-range">
                                 <span style="width:24px;height:24px" class="d-inline-flex align-items-center justify-content-center"><span class="contoh" id="po-contoh"></span></span>
                              </div>
                              <div class="d-flex gap-2">
                                 <button type="button" id="po-urung" class="btn btn-outline-dark btn-sm flex-fill" title="Urungkan (Ctrl+Z)"><i class="ti ti-arrow-back-up me-1"></i>Urungkan</button>
                                 <button type="button" id="po-bersih" class="btn btn-outline-danger btn-sm flex-fill"><i class="ti ti-eraser me-1"></i>Bersihkan</button>
                              </div>
                           </div>
                           <div class="panel">
                              <div class="sumber mb-2" id="po-sumber">Gambar baru</div>
                              <label for="po-catatan" class="lbl d-block">Catatan / keterangan penanda</label>
                              <textarea id="po-catatan" class="form-control mb-2" rows="4" maxlength="5000" placeholder="Lokasi, sisi (kanan/kiri), jenis tindakan…"></textarea>
                              <button type="button" id="po-simpan" class="btn btn-primary w-100" disabled><i class="ti ti-device-floppy me-1"></i>Simpan versi baru</button>
                              <div class="small text-muted mt-2">Bukan pengganti laporan operasi lengkap.</div>
                           </div>
                          </div>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="card">
                  <div class="card-body p-4">
                     <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-history fs-5"></i><h6 class="mb-0 fw-bold">Versi tersimpan</h6><span class="small text-muted" id="po-jumlah"></span>
                     </div>
                     <div class="table-responsive">
                        <table class="t">
                           <thead><tr><th>Aksi</th><th>Gambar &amp; versi</th><th>Penanda</th><th>Catatan</th><th>Dicatat</th></tr></thead>
                           <tbody id="po-riwayat"><tr><td colspan="5" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</td></tr></tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php require 'library.php'; ?>
   <script>
      window.operationMarkerConfig = <?= json_encode([
         'visit' => $no, 'rm' => $rm, 'types' => $markers,
         'token' => $_SESSION['operation_marker_token']
      ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
   </script>
   <script src="controller/visit/operationMarker.js?v=<?= filemtime(__DIR__ . '/../../controller/visit/operationMarker.js') ?>"></script>
</body>
</html>
