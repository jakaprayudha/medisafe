<?php
$title = 'Video Display';
require __DIR__ . '/../../controller/view.php';
require_once __DIR__ . '/../../controller/display/videoDisplay.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
$isAdmin = in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin', 'administrator'], true);
$_SESSION['display_video_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['display_video_csrf'];
$batasUpload = vdBatasUpload();

function sdJson(array $d, int $code = 200): void
{
   http_response_code($code);
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

function sdData(int $idc): array
{
   $dir = vdDirFaskes($idc);
   $tulis = is_dir($dir) ? is_writable($dir) : is_writable(vdRoot()) || (!is_dir(vdRoot()) && is_writable(dirname(vdRoot())));
   return [
      'video' => array_map(fn($v) => $v + ['url' => vdUrlFaskes($idc) . rawurlencode($v['file'])], vdDaftar($idc)),
      'bawaan' => array_map(fn($v) => $v + ['url' => 'video/' . rawurlencode($v['file'])], vdBawaan()),
      'sumber' => vdPlaylist($idc)['sumber'],
      'bisa_tulis' => $tulis,
      'batas' => vdBatasUpload(),
   ];
}

function sdUpload(array $file, int $idc, int $batas): string
{
   $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
   if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
      throw new RuntimeException('Ukuran video melebihi batas upload server (' . round($batas / 1048576) . ' MB).');
   }
   if ($err === UPLOAD_ERR_NO_FILE) {
      throw new RuntimeException('Pilih file MP4 terlebih dahulu.');
   }
   if ($err !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
      throw new RuntimeException('Upload tidak selesai. Silakan coba lagi.');
   }
   $size = (int) filesize($file['tmp_name']);
   if ($size <= 0 || $size > $batas) {
      throw new RuntimeException('Ukuran video harus lebih dari 0 dan maksimal ' . round($batas / 1048576) . ' MB.');
   }
   if (!class_exists('finfo')) {
      throw new RuntimeException('Ekstensi fileinfo PHP belum aktif, format video tidak dapat diperiksa.');
   }
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
   if (strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION)) !== 'mp4' || $mime !== 'video/mp4') {
      throw new RuntimeException('File bukan video MP4 yang valid.');
   }

   $dir = vdDirFaskes($idc);
   if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
      throw new RuntimeException('Folder video faskes gagal dibuat. Periksa izin tulis folder video.');
   }
   if (!is_writable($dir)) {
      throw new RuntimeException('PHP tidak memiliki izin tulis ke folder video.');
   }

   $nama = pathinfo(basename(str_replace('\\', '/', (string) $file['name'])), PATHINFO_FILENAME);
   $nama = trim(substr(preg_replace('/[^a-zA-Z0-9._-]+/', '-', $nama), 0, 80), '.-_') ?: 'video';
   $token = bin2hex(random_bytes(4));
   $final = "$nama-$token.mp4";
   $staging = "$dir/.upload-$token.tmp";
   if (!@move_uploaded_file($file['tmp_name'], $staging) || !@rename($staging, "$dir/$final")) {
      @unlink($staging);
      throw new RuntimeException('Video gagal disimpan ke folder video.');
   }
   return $final;
}

/* ---------------- API ---------------- */
if (($_GET['aksi'] ?? '') === 'data') {
   if (!$idc) sdJson(['success' => false, 'message' => 'Session faskes tidak ditemukan.'], 401);
   sdJson(['success' => true, 'data' => sdData($idc)]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
   if (!$idc) sdJson(['success' => false, 'message' => 'Session faskes tidak ditemukan.'], 401);
   if (!$isAdmin) sdJson(['success' => false, 'message' => 'Hanya admin yang dapat mengubah video display.'], 403);
   $postLimit = ini_parse_quantity((string) ini_get('post_max_size'));
   if (empty($_POST) && empty($_FILES) && $postLimit > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $postLimit) {
      sdJson(['success' => false, 'message' => 'Ukuran video melebihi batas upload server (' . round($batasUpload / 1048576) . ' MB).'], 413);
   }
   $tok = $_POST['csrf'] ?? '';
   if (!is_string($tok) || !hash_equals($csrf, $tok)) {
      sdJson(['success' => false, 'message' => 'Sesi formulir berakhir. Muat ulang halaman lalu coba lagi.'], 403);
   }

   try {
      switch ($_POST['action'] ?? '') {
         case 'upload':
            $f = sdUpload(is_array($_FILES['video'] ?? null) ? $_FILES['video'] : [], $idc, $batasUpload);
            vdSimpanUrutan($idc, vdDaftar($idc)); // video baru tercatat di akhir daftar putar
            sdJson(['success' => true, 'message' => "Video ditambahkan: $f", 'file' => $f, 'data' => sdData($idc)]);
            // no break
         case 'delete':
            $f = (string) ($_POST['filename'] ?? '');
            $dir = vdDirFaskes($idc);
            if (!vdNamaValid($f) || !is_file("$dir/$f") || is_link("$dir/$f")) {
               throw new RuntimeException('Video tidak ditemukan. Mungkin sudah dihapus.');
            }
            if (!@unlink("$dir/$f")) {
               error_log('[setting_display] gagal hapus ' . $f . ': ' . (error_get_last()['message'] ?? '-'));
               throw new RuntimeException('Video gagal dihapus. Periksa izin folder video di server.');
            }
            vdSimpanUrutan($idc, vdDaftar($idc));
            sdJson(['success' => true, 'message' => "Video dihapus: $f", 'data' => sdData($idc)]);
            // no break
         case 'urutan':
            $items = json_decode((string) ($_POST['items'] ?? ''), true);
            if (!is_array($items)) throw new RuntimeException('Data urutan tidak valid.');
            $ada = array_column(vdDaftar($idc), 'file');
            $baru = [];
            foreach ($items as $it) {
               $f = (string) ($it['file'] ?? '');
               if (in_array($f, $ada, true) && !isset($baru[$f])) {
                  $baru[$f] = ['file' => $f, 'aktif' => !empty($it['aktif'])];
               }
            }
            if (count($baru) !== count($ada)) {
               sdJson(['success' => false, 'message' => 'Daftar video berubah di tempat lain. Daftar dimuat ulang.', 'data' => sdData($idc)], 409);
            }
            vdSimpanUrutan($idc, array_values($baru));
            sdJson(['success' => true, 'message' => 'Daftar putar diperbarui.', 'data' => sdData($idc)]);
            // no break
         default:
            throw new RuntimeException('Aksi tidak dikenal.');
      }
   } catch (RuntimeException $e) {
      sdJson(['success' => false, 'message' => $e->getMessage(), 'data' => sdData($idc)], 422);
   }
}

$awal = $idc ? sdData($idc) : null;
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require __DIR__ . '/../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .vd-icon { width: 42px; height: 42px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .vd-stat { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .vd-val { font-size: 1.2rem; font-weight: 700; color: #2a3547; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vd-label { font-size: .75rem; color: #6c757d; }
    .vd-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .vd-pill { display: inline-flex; align-items: center; gap: 5px; font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 20px; white-space: nowrap; }

    /* dropzone */
    .vd-drop { border: 1.5px dashed #b9c8e6; background: #f7faff; border-radius: 14px; padding: 26px 16px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
    .vd-drop:hover, .vd-drop.over { border-color: #5d87ff; background: #ecf2ff; }
    .vd-drop.disabled { cursor: not-allowed; opacity: .6; }
    .vd-queue-item { border: 1px solid #eef1f4; border-radius: 10px; padding: 8px 12px; }
    .vd-queue-item .progress { height: 6px; }

    /* daftar putar */
    .vd-item { border: 1px solid #e5eaef; border-radius: 12px; padding: 10px; display: flex; gap: 12px; align-items: center; background: #fff; transition: border-color .15s, opacity .15s, box-shadow .15s; }
    .vd-item + .vd-item { margin-top: 8px; }
    .vd-item.off { opacity: .55; background: #fafbfc; }
    .vd-item.playing { border-color: #5d87ff; box-shadow: 0 0 0 3px rgba(93, 135, 255, .12); }
    .vd-item.dragging { opacity: .4; }
    .vd-item.drop-before { box-shadow: 0 -3px 0 #5d87ff; }
    .vd-item.drop-after { box-shadow: 0 3px 0 #5d87ff; }
    .vd-handle { cursor: grab; color: #adb5bd; padding: 0 2px; }
    .vd-no { width: 24px; text-align: center; font-weight: 700; color: #5a6a85; font-size: .85rem; flex-shrink: 0; }
    .vd-thumb { width: 112px; aspect-ratio: 16/9; border-radius: 8px; background: #101827; flex-shrink: 0; position: relative; overflow: hidden; cursor: pointer; }
    .vd-thumb video { width: 100%; height: 100%; object-fit: cover; display: block; pointer-events: none; }
    .vd-thumb .vd-dur { position: absolute; right: 4px; bottom: 4px; background: rgba(0, 0, 0, .7); color: #fff; font-size: .65rem; padding: 0 5px; border-radius: 4px; font-weight: 600; }
    .vd-thumb .vd-play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; opacity: 0; background: rgba(0, 0, 0, .35); transition: opacity .15s; }
    .vd-thumb:hover .vd-play { opacity: 1; }
    .vd-name { font-weight: 600; color: #2a3547; font-size: .88rem; overflow-wrap: anywhere; }
    .vd-actions .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }

    /* player */
    .vd-player { background: radial-gradient(ellipse at top, #243958, #101b2e); border-radius: 12px; aspect-ratio: 16/9; position: relative; overflow: hidden; }
    .vd-player video { width: 100%; height: 100%; display: block; object-fit: contain; }
    .vd-player-empty { position: absolute; inset: 0; display: flex; flex-direction: column; gap: 8px; align-items: center; justify-content: center; text-align: center; padding: 20px; color: #cbd5e1; font-size: .85rem; }
    @media (min-width: 1200px) { .vd-sticky { position: sticky; top: 90px; } }
    @media (max-width: 575.98px) {
      .vd-thumb { width: 84px; }
      .vd-item { flex-wrap: wrap; }
      .vd-actions { width: 100%; justify-content: flex-end; }
    }
    [hidden] { display: none !important; }
    #vdSaveState:empty { display: none; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require __DIR__ . '/navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="vd-icon bg-primary-subtle text-primary"><i class="fas fa-film"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Video Display</h5>
                  <div class="text-muted small">Video informasi yang diputar bergantian di layar antrean admisi faskes ini.</div>
                </div>
                <a href="module/display/display-admisi" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-external-link-alt me-1"></i>Buka Display Admisi
                </a>
              </div>
            </div>
          </div>

          <?php if (!$isAdmin): ?>
            <div class="alert alert-warning small"><i class="fas fa-lock me-1"></i>Anda hanya dapat melihat daftar video. Upload, hapus dan pengaturan urutan hanya untuk admin.</div>
          <?php endif; ?>
          <div class="alert alert-danger small" id="vdFolderWarn" hidden>
            <i class="fas fa-exclamation-triangle me-1"></i><strong>Folder video tidak dapat ditulis oleh server.</strong>
            Upload dan hapus akan gagal sampai izin tulis folder <code>video/</code> diperbaiki di server.
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="vd-stat d-flex align-items-center gap-3">
                <span class="vd-icon bg-primary-subtle text-primary"><i class="fas fa-play-circle"></i></span>
                <div class="min-w-0"><div class="vd-val" id="stAktif">-</div><div class="vd-label">Video diputar</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="vd-stat d-flex align-items-center gap-3">
                <span class="vd-icon bg-success-subtle text-success"><i class="fas fa-clock"></i></span>
                <div class="min-w-0"><div class="vd-val" id="stDurasi">-</div><div class="vd-label">Durasi satu putaran</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="vd-stat d-flex align-items-center gap-3">
                <span class="vd-icon bg-warning-subtle text-warning"><i class="fas fa-hdd"></i></span>
                <div class="min-w-0"><div class="vd-val" id="stUkuran">-</div><div class="vd-label">Total ukuran video</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="vd-stat d-flex align-items-center gap-3">
                <span class="vd-icon bg-info-subtle text-info"><i class="fas fa-tv"></i></span>
                <div class="min-w-0"><div class="vd-val" id="stSumber">-</div><div class="vd-label" id="stSumberKet">Sumber daftar putar</div></div>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <!-- DAFTAR PUTAR -->
            <div class="col-xl-7 order-2 order-xl-1">
              <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                  <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                    <div>
                      <h6 class="fw-semibold mb-1">Daftar putar</h6>
                      <div class="small text-muted">Diputar dari atas ke bawah lalu berulang. Seret <i class="fas fa-grip-vertical"></i> atau pakai tombol panah untuk mengubah urutan. Perubahan langsung tersimpan.</div>
                    </div>
                    <span class="vd-pill bg-light text-muted" id="vdSaveState"></span>
                  </div>
                  <div id="vdList"></div>
                </div>
              </div>

              <div class="card border-0 shadow-sm mb-0" id="vdBawaanCard" hidden>
                <div class="card-body">
                  <h6 class="fw-semibold mb-1">Video bawaan</h6>
                  <div class="small text-muted mb-3" id="vdBawaanKet"></div>
                  <div id="vdBawaan"></div>
                </div>
              </div>
            </div>

            <!-- UPLOAD + PRATINJAU -->
            <div class="col-xl-5 order-1 order-xl-2">
              <div class="vd-sticky">
                <?php if ($isAdmin): ?>
                  <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                      <h6 class="fw-semibold mb-3">Tambah video</h6>
                      <input type="file" id="vdFile" accept=".mp4,video/mp4" multiple hidden>
                      <div class="vd-drop" id="vdDrop" role="button" tabindex="0" aria-label="Pilih atau seret video MP4">
                        <div class="text-primary mb-2"><i class="fas fa-cloud-upload-alt fa-2x"></i></div>
                        <div class="fw-semibold">Seret video ke sini atau klik untuk memilih</div>
                        <div class="small text-muted mt-1">MP4 · maks. <span id="vdBatas"><?= round($batasUpload / 1048576) ?></span> MB per video · bisa beberapa sekaligus</div>
                      </div>
                      <div id="vdQueue" class="d-flex flex-column gap-2 mt-3"></div>
                    </div>
                  </div>
                <?php endif; ?>
                <div class="card border-0 shadow-sm mb-0">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="fw-semibold mb-0">Pratinjau</h6>
                      <button type="button" class="btn btn-sm btn-light" id="vdStop" hidden><i class="fas fa-stop me-1"></i>Tutup</button>
                    </div>
                    <div class="vd-player">
                      <video id="vdPlayer" controls playsinline preload="metadata" hidden></video>
                      <div class="vd-player-empty" id="vdPlayerEmpty">
                        <i class="fas fa-play-circle fa-2x"></i>
                        <span>Klik gambar video di daftar putar untuk melihat pratinjau.</span>
                      </div>
                    </div>
                    <div class="small text-muted mt-2 text-break" id="vdPlayerName"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/library.php'; ?>
  <script>
    (function() {
      const ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
      const CSRF = <?= json_encode($csrf) ?>;
      const API = 'module/admin/setting_display.php';
      let D = <?= json_encode($awal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
      const durasi = {}; // url -> detik
      let sibuk = 0;
      let nowPlaying = null;

      const $ = s => document.querySelector(s);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const mb = b => (b / 1048576).toLocaleString('id-ID', { maximumFractionDigits: b < 10485760 ? 1 : 0 }) + ' MB';
      const jam = s => {
        s = Math.round(s || 0);
        const h = Math.floor(s / 3600), m = Math.floor(s % 3600 / 60), d = s % 60;
        return (h ? h + ':' + String(m).padStart(2, '0') : m) + ':' + String(d).padStart(2, '0');
      };
      const namaTampil = f => f.replace(/-[0-9a-f]{8,24}\.mp4$/i, '.mp4');

      function post(fd, onProgress) {
        fd.append('csrf', CSRF);
        return new Promise((resolve, reject) => {
          const x = new XMLHttpRequest();
          x.open('POST', API);
          x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          if (onProgress) x.upload.onprogress = onProgress;
          x.onload = () => {
            let j = null;
            try { j = JSON.parse(x.responseText); } catch (e) {}
            if (j && j.data) D = j.data;
            if (x.status >= 200 && x.status < 300 && j && j.success) resolve(j);
            else {
              const e = new Error((j && j.message) || (x.status === 413 ? 'Ukuran video ditolak server.' : /session/i.test(x.responseText) ? 'Sesi login berakhir. Silakan login ulang.' : 'Server tidak merespons dengan benar (HTTP ' + x.status + ').'));
              e.code = x.status;
              reject(e);
            }
          };
          x.onerror = () => reject(new Error('Koneksi terputus.'));
          x.send(fd);
        });
      }

      /* ---------- render ---------- */
      function render() {
        if (!D) {
          $('#vdList').innerHTML = '<div class="text-danger small">Session faskes tidak ditemukan. Muat ulang halaman.</div>';
          return;
        }
        $('#vdFolderWarn').hidden = !ADMIN || D.bisa_tulis;
        const list = D.video;
        if (!list.length) {
          $('#vdList').innerHTML = `<div class="text-center text-muted py-5">
            <i class="fas fa-video-slash fa-2x mb-2 d-block"></i>
            <div class="fw-semibold text-dark">Belum ada video untuk faskes ini</div>
            <div class="small">${D.bawaan.length ? 'Display sementara memutar video bawaan di bawah.' : 'Layar display hanya menampilkan antrean.'}${ADMIN ? ' Tambahkan video lewat panel "Tambah video".' : ''}</div></div>`;
        } else {
          $('#vdList').innerHTML = list.map((v, i) => `
            <div class="vd-item ${v.aktif ? '' : 'off'} ${nowPlaying === v.url ? 'playing' : ''}" data-i="${i}" ${ADMIN ? 'draggable="true"' : ''}>
              ${ADMIN ? '<span class="vd-handle" title="Seret untuk mengubah urutan"><i class="fas fa-grip-vertical"></i></span>' : ''}
              <span class="vd-no">${v.aktif ? list.slice(0, i + 1).filter(x => x.aktif).length : '–'}</span>
              <div class="vd-thumb" data-play="${esc(v.url)}" data-name="${esc(v.file)}" title="Pratinjau">
                <i data-thumb="${esc(v.url)}"></i>
                <span class="vd-dur" ${durasi[v.url] < 0 ? 'style="background:#fa896b"' : ''}>${durasi[v.url] < 0 ? 'rusak' : durasi[v.url] ? jam(durasi[v.url]) : '…'}</span>
                <span class="vd-play"><i class="fas fa-play"></i></span>
              </div>
              <div class="min-w-0 flex-grow-1">
                <div class="vd-name" title="${esc(v.file)}">${esc(namaTampil(v.file))}</div>
                <div class="small text-muted">${mb(v.ukuran)} · diunggah ${new Date(v.diubah * 1000).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</div>
                <span class="vd-pill mt-1 ${v.aktif ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">${v.aktif ? 'Diputar' : 'Dijeda'}</span>
              </div>
              ${ADMIN ? `<div class="vd-actions d-flex align-items-center gap-1">
                <div class="form-check form-switch m-0 me-1" title="${v.aktif ? 'Jeda (tidak diputar)' : 'Putar lagi'}">
                  <input class="form-check-input" type="checkbox" role="switch" data-aktif ${v.aktif ? 'checked' : ''} aria-label="Putar video ini">
                </div>
                <button type="button" class="btn btn-light btn-sm" data-geser="-1" ${i === 0 ? 'disabled' : ''} title="Naikkan"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="btn btn-light btn-sm" data-geser="1" ${i === list.length - 1 ? 'disabled' : ''} title="Turunkan"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="btn btn-light btn-sm text-danger" data-hapus title="Hapus"><i class="fas fa-trash-alt"></i></button>
              </div>` : ''}
            </div>`).join('');
        }

        // video bawaan
        const b = D.bawaan;
        $('#vdBawaanCard').hidden = !b.length;
        if (b.length) {
          $('#vdBawaanKet').innerHTML = D.sumber === 'bawaan' ?
            '<span class="text-warning fw-semibold">Sedang diputar</span> karena faskes ini belum punya video aktif. Begitu ada video sendiri yang aktif, video bawaan tidak diputar lagi.' :
            'Tidak diputar, karena faskes ini sudah punya video sendiri. Video bawaan dikelola tim Medisafe.';
          $('#vdBawaan').innerHTML = b.map(v => `
            <div class="vd-item ${D.sumber === 'bawaan' ? '' : 'off'}">
              <div class="vd-thumb" data-play="${esc(v.url)}" data-name="${esc(v.file)}">
                <i data-thumb="${esc(v.url)}"></i>
                <span class="vd-dur" ${durasi[v.url] < 0 ? 'style="background:#fa896b"' : ''}>${durasi[v.url] < 0 ? 'rusak' : durasi[v.url] ? jam(durasi[v.url]) : '…'}</span>
                <span class="vd-play"><i class="fas fa-play"></i></span>
              </div>
              <div class="min-w-0 flex-grow-1"><div class="vd-name">${esc(namaTampil(v.file))}</div><div class="small text-muted">${mb(v.ukuran)}</div></div>
            </div>`).join('');
        }

        // elemen <video> thumbnail dipakai ulang agar file tidak diunduh ulang setiap render
        document.querySelectorAll('[data-thumb]').forEach(ph => ph.replaceWith(thumb(ph.dataset.thumb)));
        statistik();
      }

      const thumbs = new Map();
      function thumb(url) {
        let el = thumbs.get(url);
        if (el && el.isConnected) el = el.cloneNode(); // url sama tampil 2x (jarang)
        if (!el) {
          el = document.createElement('video');
          el.src = url + '#t=1';
          el.preload = 'metadata';
          el.muted = true;
          el.playsInline = true;
          el.addEventListener('loadedmetadata', () => {
            durasi[url] = el.duration;
            document.querySelectorAll('[data-play="' + CSS.escape(url) + '"] .vd-dur').forEach(x => x.textContent = jam(el.duration));
            statistik();
          }, { once: true });
          el.addEventListener('error', () => {
            durasi[url] = -1; // rusak / tidak dapat dibaca
            document.querySelectorAll('[data-play="' + CSS.escape(url) + '"] .vd-dur').forEach(x => {
              x.textContent = 'rusak';
              x.style.background = '#fa896b';
            });
            statistik();
          }, { once: true });
          thumbs.set(url, el);
        }
        return el;
      }

      function statistik() {
        if (!D) return;
        const main = D.sumber === 'faskes' ? D.video.filter(v => v.aktif) : D.bawaan;
        $('#stAktif').textContent = main.length + (D.sumber === 'faskes' && D.video.length !== main.length ? ' / ' + D.video.length : '');
        const ds = main.map(v => durasi[v.url]);
        $('#stDurasi').textContent = !main.length ? '-' : (ds.every(x => x) ? jam(ds.reduce((a, x) => a + Math.max(0, x), 0)) : '…');
        $('#stUkuran').textContent = mb(D.video.reduce((a, v) => a + v.ukuran, 0));
        $('#stSumber').textContent = D.sumber === 'faskes' ? 'Video faskes' : (D.bawaan.length ? 'Video bawaan' : 'Tidak ada');
        $('#stSumberKet').textContent = D.sumber === 'faskes' ? 'Sumber daftar putar' : (D.bawaan.length ? 'Belum ada video aktif sendiri' : 'Display tanpa video');
      }

      /* ---------- pratinjau ---------- */
      const player = $('#vdPlayer');
      function putar(url, name) {
        nowPlaying = url;
        player.src = url;
        player.hidden = false;
        $('#vdPlayerEmpty').hidden = true;
        $('#vdStop').hidden = false;
        $('#vdPlayerName').textContent = namaTampil(name);
        player.play().catch(() => {});
        document.querySelectorAll('.vd-item').forEach(el => el.classList.toggle('playing', !!el.querySelector(`[data-play="${CSS.escape(url)}"]`)));
        if (window.innerWidth < 1200) player.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      function stop() {
        nowPlaying = null;
        player.pause();
        player.removeAttribute('src');
        player.load();
        player.hidden = true;
        $('#vdPlayerEmpty').hidden = false;
        $('#vdStop').hidden = true;
        $('#vdPlayerName').textContent = '';
        document.querySelectorAll('.vd-item.playing').forEach(el => el.classList.remove('playing'));
      }
      $('#vdStop').addEventListener('click', stop);
      player.addEventListener('error', () => {
        if (player.getAttribute('src')) $('#vdPlayerName').textContent = 'Video tidak dapat diputar. File mungkin rusak atau sudah dihapus.';
      });
      document.addEventListener('click', e => {
        const t = e.target.closest('[data-play]');
        if (t) putar(t.dataset.play, t.dataset.name);
      });

      /* ---------- urutan & aktif (langsung disimpan) ---------- */
      let antre = Promise.resolve();
      function simpanUrutan(items) {
        const sebelum = D.video;
        D.video = items;
        render();
        const st = $('#vdSaveState');
        st.className = 'vd-pill bg-light text-muted';
        st.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:.7rem;height:.7rem"></span> Menyimpan…';
        const fd = new FormData();
        fd.append('action', 'urutan');
        fd.append('items', JSON.stringify(items.map(v => ({ file: v.file, aktif: v.aktif }))));
        antre = antre.then(() => post(fd)).then(() => {
          st.className = 'vd-pill bg-success-subtle text-success';
          st.innerHTML = '<i class="fas fa-check"></i> Tersimpan';
          render();
        }).catch(e => {
          if (e.code !== 409) D.video = sebelum;
          st.className = 'vd-pill bg-danger-subtle text-danger';
          st.innerHTML = '<i class="fas fa-times"></i> Gagal';
          render();
          Swal.fire('Gagal menyimpan urutan', e.message, 'error');
        });
      }

      if (ADMIN) {
        $('#vdList').addEventListener('click', async e => {
          const item = e.target.closest('.vd-item');
          if (!item) return;
          const i = +item.dataset.i;
          const list = D.video.slice();
          const g = e.target.closest('[data-geser]');
          if (g) {
            const j = i + +g.dataset.geser;
            if (j < 0 || j >= list.length) return;
            [list[i], list[j]] = [list[j], list[i]];
            simpanUrutan(list);
          } else if (e.target.closest('[data-hapus]')) {
            hapus(list[i]);
          }
        });
        $('#vdList').addEventListener('change', e => {
          if (!e.target.matches('[data-aktif]')) return;
          const i = +e.target.closest('.vd-item').dataset.i;
          const list = D.video.map((v, k) => k === i ? Object.assign({}, v, { aktif: e.target.checked }) : v);
          if (!list.some(v => v.aktif) && list.length) {
            Swal.fire({
              icon: 'warning',
              title: 'Semua video dijeda',
              text: D.bawaan.length ? 'Display akan memutar video bawaan sampai ada video faskes yang aktif lagi.' : 'Display tidak akan memutar video apa pun.',
              timer: 3500
            });
          }
          simpanUrutan(list);
        });

        // seret & lepas
        let dari = null;
        $('#vdList').addEventListener('dragstart', e => {
          const it = e.target.closest('.vd-item');
          if (!it) return;
          dari = +it.dataset.i;
          it.classList.add('dragging');
          e.dataTransfer.effectAllowed = 'move';
          e.dataTransfer.setData('text/plain', String(dari));
        });
        $('#vdList').addEventListener('dragover', e => {
          const it = e.target.closest('.vd-item');
          if (dari === null || !it) return;
          e.preventDefault();
          document.querySelectorAll('.drop-before,.drop-after').forEach(x => x.classList.remove('drop-before', 'drop-after'));
          const r = it.getBoundingClientRect();
          it.classList.add(e.clientY < r.top + r.height / 2 ? 'drop-before' : 'drop-after');
        });
        $('#vdList').addEventListener('drop', e => {
          const it = e.target.closest('.vd-item');
          if (dari === null || !it) return;
          e.preventDefault();
          let ke = +it.dataset.i + (it.classList.contains('drop-after') ? 1 : 0);
          const list = D.video.slice();
          const [x] = list.splice(dari, 1);
          if (ke > dari) ke--;
          list.splice(ke, 0, x);
          const ubah = ke !== dari;
          dari = null;
          if (ubah) simpanUrutan(list); else render();
        });
        $('#vdList').addEventListener('dragend', () => {
          dari = null;
          document.querySelectorAll('.dragging,.drop-before,.drop-after').forEach(x => x.classList.remove('dragging', 'drop-before', 'drop-after'));
        });
      }

      async function hapus(v) {
        const aktifLain = D.video.filter(x => x.aktif && x.file !== v.file).length;
        const r = await Swal.fire({
          icon: 'warning',
          title: 'Hapus video ini?',
          html: `<b>${esc(namaTampil(v.file))}</b> akan dihapus permanen dari server.` +
            (v.aktif && !aktifLain ? `<div class="small text-warning mt-2">Ini video aktif terakhir. ${D.bawaan.length ? 'Display akan kembali memutar video bawaan.' : 'Display tidak akan memutar video.'}</div>` : '') +
            '<div class="small text-muted mt-2">Tip: matikan sakelar untuk menjeda tanpa menghapus.</div>',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#dc3545',
          focusCancel: true,
          reverseButtons: true
        });
        if (!r.isConfirmed) return;
        if (nowPlaying && nowPlaying.endsWith('/' + encodeURIComponent(v.file))) stop();
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('filename', v.file);
        sibuk++;
        try {
          await post(fd);
          render();
          Swal.fire({ icon: 'success', title: 'Video dihapus', timer: 1400, showConfirmButton: false });
        } catch (e) {
          render();
          Swal.fire('Gagal menghapus', e.message, 'error');
        } finally {
          sibuk--;
        }
      }

      /* ---------- upload (antre, satu per satu) ---------- */
      if (ADMIN) {
        const input = $('#vdFile'), drop = $('#vdDrop'), queue = $('#vdQueue');
        let antrean = [], jalan = false;

        function cekFile(f) {
          if (!/\.mp4$/i.test(f.name) || (f.type && f.type !== 'video/mp4')) return 'Bukan file MP4.';
          if (!f.size) return 'File kosong.';
          if (f.size > D.batas) return 'Lebih dari ' + Math.round(D.batas / 1048576) + ' MB (batas server).';
          return null;
        }

        function tambah(files) {
          [...files].forEach(f => {
            const el = document.createElement('div');
            el.className = 'vd-queue-item';
            const err = cekFile(f);
            el.innerHTML = `<div class="d-flex justify-content-between gap-2 small">
                <span class="fw-semibold text-truncate">${esc(f.name)}</span><span class="text-muted text-nowrap">${mb(f.size)}</span></div>
              <div class="progress mt-1 ${err ? 'd-none' : ''}"><div class="progress-bar" style="width:0%"></div></div>
              <div class="small mt-1 ${err ? 'text-danger' : 'text-muted'}" data-st>${err ? '<i class="fas fa-times-circle me-1"></i>' + esc(err) : 'Menunggu…'}</div>`;
            queue.prepend(el);
            if (!err) antrean.push({ f, el });
          });
          jalankan();
        }

        async function jalankan() {
          if (jalan) return;
          jalan = true;
          sibuk++;
          while (antrean.length) {
            const { f, el } = antrean.shift();
            const bar = el.querySelector('.progress-bar'), st = el.querySelector('[data-st]');
            st.textContent = 'Mengunggah…';
            const fd = new FormData();
            fd.append('action', 'upload');
            fd.append('video', f);
            try {
              await post(fd, e => {
                if (!e.lengthComputable) return;
                const p = Math.round(e.loaded / e.total * 100);
                bar.style.width = p + '%';
                st.textContent = p < 100 ? 'Mengunggah… ' + p + '%' : 'Memeriksa & menyimpan…';
              });
              bar.classList.add('bg-success');
              bar.style.width = '100%';
              st.className = 'small mt-1 text-success';
              st.innerHTML = '<i class="fas fa-check-circle me-1"></i>Ditambahkan ke akhir daftar putar';
              render();
            } catch (e) {
              bar.classList.add('bg-danger');
              st.className = 'small mt-1 text-danger';
              st.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + esc(e.message);
              render();
            }
          }
          sibuk--;
          jalan = false;
          setTimeout(() => queue.querySelectorAll('.vd-queue-item').forEach(el => {
            if (el.querySelector('.text-success')) el.remove();
          }), 6000);
        }

        drop.addEventListener('click', () => input.click());
        drop.addEventListener('keydown', e => {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
        });
        input.addEventListener('change', () => { tambah(input.files); input.value = ''; });
        ['dragenter', 'dragover'].forEach(t => drop.addEventListener(t, e => {
          if (![...e.dataTransfer.types].includes('Files')) return;
          e.preventDefault();
          drop.classList.add('over');
        }));
        ['dragleave', 'drop'].forEach(t => drop.addEventListener(t, () => drop.classList.remove('over')));
        drop.addEventListener('drop', e => {
          if (!e.dataTransfer.files.length) return;
          e.preventDefault();
          tambah(e.dataTransfer.files);
        });
        // cegah browser membuka file bila dilepas di luar area
        window.addEventListener('dragover', e => { if ([...e.dataTransfer.types].includes('Files')) e.preventDefault(); });
        window.addEventListener('drop', e => { if ([...e.dataTransfer.types].includes('Files')) e.preventDefault(); });
      }

      window.addEventListener('beforeunload', e => {
        if (sibuk > 0) { e.preventDefault(); e.returnValue = ''; }
      });

      render();
    })();
  </script>
</body>

</html>
