<?php
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
?>
<!doctype html>
<html lang="id">
<head>
   <base href="../../">
   <?php require '../../assets/template/head.php'; ?>
   <style>
      .marker-canvas {
         width: 100%;
         height: auto;
         touch-action: none;
         cursor: crosshair;
         display: block;
      }
      .marker-workspace {
         max-width: 900px;
         margin: auto;
      }
      .marker-tool {
         max-width: 180px;
      }
   </style>
</head>
<body>
   <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
      data-sidebar-position="fixed" data-header-position="fixed">
      <?php require 'sidebar.php'; ?>
      <div class="body-wrapper">
         <?php require 'navbar.php'; ?>
         <div class="body-wrapper-inner">
            <div class="container-fluid">
               <?php require $menus[$rme]; ?>
               <?php require 'card-pasien.php'; ?>
               <div class="card">
                  <div class="card-body">
                     <h5 class="card-title fw-semibold mb-3">Penanda Operasi</h5>
                     <div id="marker-message" class="alert d-none" role="alert"></div>
                     <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                        <div>
                           <label for="marker-tool" class="form-label">Alat</label>
                           <select id="marker-tool" class="form-select marker-tool">
                              <option value="stroke">Coretan</option>
                              <option value="text">Teks</option>
                           </select>
                        </div>
                        <div>
                           <label for="marker-color" class="form-label">Warna</label>
                           <input id="marker-color" type="color" value="#dc3545" class="form-control form-control-color">
                        </div>
                        <div>
                           <label for="marker-size" class="form-label">Ukuran garis</label>
                           <input id="marker-size" type="number" min="1" max="20" value="4" class="form-control marker-tool">
                        </div>
                        <div>
                           <label for="marker-text" class="form-label">Teks (klik gambar untuk menempatkan)</label>
                           <input id="marker-text" type="text" maxlength="200" class="form-control" placeholder="Contoh: lokasi tindakan">
                        </div>
                        <button type="button" id="marker-undo" class="btn btn-outline-secondary">Urungkan</button>
                        <button type="button" id="marker-clear" class="btn btn-outline-danger">Bersihkan</button>
                     </div>
                     <ul class="nav nav-pills mb-3" role="tablist" aria-label="Gambar penanda operasi">
                        <?php foreach ($markers as $key => $label): ?>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link <?= $key === 'wanita' ? 'active' : '' ?>"
                                 id="marker-tab-<?= $key ?>" data-bs-toggle="pill" data-bs-target="#marker-<?= $key ?>"
                                 role="tab" aria-controls="marker-<?= $key ?>" aria-selected="<?= $key === 'wanita' ? 'true' : 'false' ?>">
                                 <?= $label ?>
                              </button>
                           </li>
                        <?php endforeach; ?>
                     </ul>
                     <div class="tab-content">
                        <?php foreach ($markers as $key => $label): ?>
                           <div class="tab-pane fade text-center <?= $key === 'wanita' ? 'show active' : '' ?>"
                              id="marker-<?= $key ?>" role="tabpanel" aria-labelledby="marker-tab-<?= $key ?>">
                              <div class="marker-workspace">
                                 <canvas id="canvas-<?= $key ?>" class="marker-canvas border"
                                    aria-label="Gambar penanda operasi <?= $label ?>" data-marker="<?= $key ?>"></canvas>
                              </div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                     <div class="mt-3">
                        <label for="marker-notes" class="form-label">Catatan operasi / keterangan penanda</label>
                        <textarea id="marker-notes" class="form-control" rows="3" maxlength="5000"></textarea>
                     </div>
                     <p class="text-muted small mt-2">Setiap simpan membuat versi baru. Dokumen lama tidak ditimpa. Dokumen penanda ini bukan pengganti laporan operasi lengkap.</p>
                     <button type="button" id="marker-save" class="btn btn-primary">Simpan Versi Baru</button>
                     <a class="btn btn-outline-primary" target="_blank" rel="noopener"
                        href="module/admin/print/formulir_penanda_operasi?<?= htmlspecialchars(http_build_query(['no' => $_GET['no'] ?? '', 'rm' => $_GET['rm'] ?? '']), ENT_QUOTES, 'UTF-8') ?>">Cetak / PDF Semua Versi</a>
                  </div>
               </div>
               <div class="card">
                  <div class="card-body">
                     <h5 class="card-title">Dokumen Penanda Operasi Tersimpan</h5>
                     <div class="table-responsive">
                        <table class="table">
                           <thead><tr><th>Versi</th><th>Gambar</th><th>Tanggal</th><th>Pencatat</th><th>Aksi</th></tr></thead>
                           <tbody id="marker-history"></tbody>
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
         'visit' => $_GET['no'] ?? '', 'rm' => $_GET['rm'] ?? '', 'types' => $markers,
         'token' => $_SESSION['operation_marker_token']
      ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
   </script>
   <script src="controller/visit/operationMarker.js?v=<?= filemtime(__DIR__ . '/../../controller/visit/operationMarker.js') ?>"></script>
</body>
</html>
