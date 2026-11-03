<?php

$title = 'Setting Video Display';
require __DIR__ . '/../../controller/view.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$videoDir = dirname(__DIR__, 2) . '/video';
$videoUrl = 'video/';
$displayExtensions = ['mp4'];
$maxVideoBytes = 100 * 1024 * 1024;
$_SESSION['display_video_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['display_video_csrf'];

function displayVideoEscape(string $value): string
{
   return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function displayVideoIsFile(string $dir, string $name, array $extensions): bool
{
   if ($name === '' || strpbrk($name, "/\\\0") !== false) {
      return false;
   }
   if (!in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $extensions, true)) {
      return false;
   }
   $path = $dir . '/' . $name;
   return !is_link($path) && is_file($path);
}

function displayVideoJson(bool $success, string $message, int $status = 200): void
{
   http_response_code($status);
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   echo json_encode(['success' => $success, 'message' => $message], JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

function displayVideoUpload(array $file, string $dir, int $maxBytes): string
{
   $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
   if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
      throw new RuntimeException('Ukuran video melebihi batas upload PHP.');
   }
   if ($error === UPLOAD_ERR_NO_FILE) {
      throw new RuntimeException('Pilih satu file MP4 terlebih dahulu.');
   }
   if ($error !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_string($file['name'] ?? null)) {
      throw new RuntimeException('Upload tidak selesai. Silakan pilih file dan coba lagi.');
   }

   $tmp = $file['tmp_name'];
   if (!is_uploaded_file($tmp)) {
      throw new RuntimeException('File upload tidak valid.');
   }
   $size = filesize($tmp);
   if ($size === false || $size === 0 || $size > $maxBytes) {
      throw new RuntimeException('Ukuran video harus lebih dari 0 dan maksimal 100 MB.');
   }
   if (!class_exists('finfo')) {
      throw new RuntimeException('Aktifkan ekstensi fileinfo PHP untuk memeriksa format video.');
   }
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
   if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'mp4' || $mime !== 'video/mp4') {
      throw new RuntimeException('Pilih video MP4 yang valid.');
   }

   if (!is_dir($dir) && !@mkdir($dir, 0755) && !is_dir($dir)) {
      throw new RuntimeException('Folder video gagal dibuat. Periksa izin folder proyek.');
   }
   if (!is_writable($dir)) {
      throw new RuntimeException('PHP tidak memiliki izin tulis ke folder video.');
   }

   $name = pathinfo(basename(str_replace('\\', '/', $file['name'])), PATHINFO_FILENAME);
   $name = trim(substr(preg_replace('/[^a-zA-Z0-9._-]+/', '-', $name), 0, 100), '.-_');
   $token = bin2hex(random_bytes(12));
   $filename = ($name ?: 'video') . '-' . $token . '.mp4';
   $stagingPath = $dir . '/.upload-' . $token . '.tmp';

   // File baru masuk playlist setelah proses simpan selesai.
   if (!@move_uploaded_file($tmp, $stagingPath)) {
      @unlink($stagingPath);
      throw new RuntimeException('Video gagal disimpan ke folder video.');
   }
   if (!@rename($stagingPath, $dir . '/' . $filename)) {
      @unlink($stagingPath);
      throw new RuntimeException('Video gagal dipublikasikan ke folder video.');
   }

   return $filename;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
   $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
   $postLimit = ini_parse_quantity((string) ini_get('post_max_size'));
   if (empty($_POST) && empty($_FILES) && $postLimit > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $postLimit) {
      if ($isAjax) displayVideoJson(false, 'Ukuran video melebihi post_max_size PHP. Sesuaikan batas upload server.', 413);
      http_response_code(413);
      exit('Ukuran permintaan melebihi post_max_size PHP. Kembali dan pilih video yang lebih kecil.');
   }
   $requestToken = $_POST['csrf'] ?? '';
   if (!is_string($requestToken) || !hash_equals($csrf, $requestToken)) {
      if ($isAjax) displayVideoJson(false, 'Sesi formulir berakhir. Muat ulang halaman, lalu coba lagi.', 403);
      http_response_code(403);
      exit('Sesi formulir tidak valid. Muat ulang halaman pengaturan.');
   }

   try {
      switch ($_POST['action'] ?? '') {
         case 'upload':
            $upload = $_FILES['video'] ?? [];
            if (!is_array($upload)) throw new RuntimeException('Data upload tidak valid.');
            $filename = displayVideoUpload($upload, $videoDir, $maxVideoBytes);
            $message = 'Video ditambahkan: ' . $filename;
            break;
         case 'delete':
            $filename = $_POST['filename'] ?? '';
            if (!is_string($filename) || !displayVideoIsFile($videoDir, $filename, $displayExtensions)) {
               throw new RuntimeException('Video tidak ditemukan.');
            }
            clearstatcache(true, $videoDir);
            if (!is_writable($videoDir)) {
               throw new RuntimeException('PHP tidak memiliki izin tulis pada folder video. Periksa panel Izin folder dan berikan akses tulis serta akses masuk folder kepada pengguna PHP.');
            }
            error_clear_last();
            if (!@unlink($videoDir . '/' . $filename)) {
               $deleteError = error_get_last();
               error_log('Setting video: gagal hapus ' . $filename . ' — ' . ($deleteError['message'] ?? 'unknown'));
               throw new RuntimeException('Video gagal dihapus. Periksa pemilik folder, ACL, atau file yang terkunci. Detail kegagalan dicatat pada log PHP.');
            }
            $message = 'Video dihapus: ' . $filename;
            break;
         default:
            throw new RuntimeException('Aksi tidak dikenal.');
      }
      if ($isAjax) displayVideoJson(true, $message);
      $_SESSION['display_video_notice'] = ['type' => 'success', 'message' => $message];
   } catch (RuntimeException $error) {
      if ($isAjax) displayVideoJson(false, $error->getMessage(), 422);
      $_SESSION['display_video_notice'] = ['type' => 'danger', 'message' => $error->getMessage()];
   }

   $returnUrl = $_SERVER['REQUEST_URI'] ?? '';
   if (!str_starts_with($returnUrl, '/') || str_starts_with($returnUrl, '//') || strpbrk($returnUrl, "\\\r\n") !== false) {
      $returnUrl = 'setting-video.php';
   }
   header('Location: ' . $returnUrl, true, 303);
   exit;
}

$notice = $_SESSION['display_video_notice'] ?? null;
unset($_SESSION['display_video_notice']);
$entries = is_dir($videoDir) ? @scandir($videoDir) : [];
$videoFiles = [];
foreach ($entries ?: [] as $entry) {
   if (displayVideoIsFile($videoDir, $entry, $displayExtensions)) {
      $videoFiles[] = $entry;
   }
}
natcasesort($videoFiles);
$totalVideoBytes = 0;
foreach ($videoFiles as $filename) $totalVideoBytes += (int) @filesize($videoDir . '/' . $filename);
$folderWritable = is_dir($videoDir) && is_writable($videoDir);
$phpAccount = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid()) : false;
$phpGroup = function_exists('posix_getgrgid') ? posix_getgrgid(posix_getegid()) : false;
$folderPermissions = is_dir($videoDir) ? @fileperms($videoDir) : false;
?>
<!doctype html>
<html lang="id">
<head>
   <base href="../../">
   <?php require __DIR__ . '/../../assets/template/head.php'; ?>
   <style>
      .video-settings { --vs-border: #e5e7eb; --vs-blue: #0d6efd; }
      .video-settings .setting-header { display: flex; align-items: center; gap: 15px; }
      .video-settings .setting-header-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #e7f1ff; color: var(--vs-blue); flex-shrink: 0; }
      .video-settings .vs-card { background: #fff; border: 1px solid var(--vs-border); border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 5px 18px rgba(0,0,0,.03); }
      .video-settings .vs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
      .video-settings .vs-grid > * { min-width: 0; }
      .video-settings .vs-upload { border: 2px dashed #b6cff6; background: #f7faff; border-radius: 12px; padding: 24px 18px; text-align: center; margin: 20px 0; }
      .video-settings .vs-upload-icon { color: var(--vs-blue); display: block; margin-bottom: 12px; }
      .video-settings .vs-upload input { margin-top: 18px; background: #fff; }
      .video-settings .vs-kicker { font-size: 11px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #64748b; margin-bottom: 6px; }
      .video-settings .vs-player { background: #101827; border-radius: 10px; aspect-ratio: 16/9; position: relative; overflow: hidden; margin-top: 20px; }
      .video-settings .vs-player video { width: 100%; height: 100%; display: block; object-fit: contain; }
      .video-settings .vs-player-empty { position: absolute; inset: 0; display: flex; flex-direction: column; gap: 10px; align-items: center; justify-content: center; text-align: center; padding: 20px; color: #cbd5e1; }
      .video-settings [hidden] { display: none !important; }
      .video-settings .vs-filename { overflow-wrap: anywhere; }
      .video-settings .vs-help { background: #eff6ff; border-radius: 8px; padding: 12px 15px; color: #315681; font-size: 13px; }
      .video-settings .vs-table th { background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; padding: 15px; }
      .video-settings .vs-table td { padding: 16px 15px; vertical-align: middle; }
      .video-settings .vs-empty { padding: 40px 15px; text-align: center; color: #64748b; }
      .video-settings .vs-file-icon { width: 38px; height: 38px; background: #eff6ff; color: var(--vs-blue); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
      .video-settings .vs-status { font-size: 12px; color: #047857; background: #ecfdf5; border-radius: 6px; padding: 5px 9px; }
      @media (max-width: 991px) { .video-settings .vs-grid { grid-template-columns: 1fr; gap: 0; } }
      @media (max-width: 575px) { .video-settings .vs-card { padding: 18px; } }
      .video-settings { color: #243247; }
      .video-settings .vs-hero { padding: 28px; border-top: 3px solid #2563eb; }
      .video-settings h5 { font-size: 24px; letter-spacing: -.5px; color: #14253e; }
      .video-settings h6 { font-size: 16px; color: #14253e; }
      .video-settings .vs-card { border-color: #e4eaf2; border-radius: 16px; box-shadow: 0 6px 24px rgba(22,42,70,.04); }
      .video-settings .vs-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
      .video-settings .vs-stat { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; padding: 18px 22px; }
      .video-settings .vs-stat strong { display: block; font-size: 23px; margin-top: 7px; color: #14253e; }
      .video-settings .vs-stat span { font-size: 12px; color: #64748b; }
      .video-settings .vs-grid { grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); }
      .video-settings .btn { border-radius: 8px; font-weight: 600; }
      .video-settings .vs-upload { padding: 30px 20px; border-width: 1px; border-color: #a9bfe1; }
      .video-settings .vs-player { background: radial-gradient(ellipse at top, #243958, #101b2e); }
      .video-settings .vs-table { min-width: 650px; }
      .video-settings .vs-table tbody tr:hover { background: #fafcff; }
      .video-settings .vs-permissions { border: 1px solid #e4eaf2; border-radius: 12px; background: #fff; padding: 16px 20px; margin-bottom: 24px; }
      .video-settings .vs-permissions summary { cursor: pointer; font-weight: 600; }
      .video-settings .vs-permissions dl { display: grid; grid-template-columns: 150px 1fr; gap: 8px 16px; margin: 18px 0 0; font-size: 13px; }
      .video-settings .vs-permissions dd { margin: 0; overflow-wrap: anywhere; }
      .vs-progress-track { width: 100%; height: 10px; background: #e9eef6; border-radius: 99px; overflow: hidden; margin: 22px 0 12px; }
      .vs-progress-fill { width: 0; height: 100%; background: #2563eb; transition: width .15s; }
      .vs-progress-value { font-size: 28px; font-weight: 700; color: #1e3a5f; }
      .vs-progress-note { font-size: 14px; color: #64748b; margin-top: 10px; }
      @media (max-width: 991px) { .video-settings .vs-grid { grid-template-columns: 1fr; } }
      @media (max-width: 575px) {
         .video-settings .vs-stats { grid-template-columns: 1fr; gap: 10px; }
         .video-settings .vs-stat { padding: 14px 18px; }
         .video-settings .vs-permissions dl { grid-template-columns: 1fr; }
      }
   </style>
</head>
<body>
   <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
      data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
      <?php require __DIR__ . '/sidebar.php'; ?>
      <div class="body-wrapper">
         <?php require __DIR__ . '/navbar.php'; ?>
         <div class="body-wrapper-inner">
            <div class="container-fluid video-settings">
               <div class="vs-card vs-hero">
                  <div class="setting-header">
                     <div class="setting-header-icon" aria-hidden="true">
                        <iconify-icon icon="material-symbols:video-settings-outline" width="27"></iconify-icon>
                     </div>
                     <div>
                        <div class="vs-kicker">Pengaturan / Display Admisi</div>
                        <h5 class="fw-semibold mb-1">Pengaturan Video Display</h5>
                        <div class="text-muted small">Tambahkan dan kelola video informasi untuk layar antrean pasien.</div>
                     </div>
                  </div>
               </div>
               <?php if ($notice): ?>
                  <div class="alert alert-<?= displayVideoEscape($notice['type']) ?>" role="status">
                     <?= displayVideoEscape($notice['message']) ?>
                  </div>
               <?php endif; ?>

               <div id="sweetAlertError" class="alert alert-danger" role="alert" hidden></div>
               <div class="vs-grid">
                  <section class="vs-card" aria-labelledby="uploadTitle">
                     <h6 class="fw-semibold mb-1" id="uploadTitle">Upload Video</h6>
                     <div class="text-muted small">Pilih video baru untuk ditambahkan ke daftar putar.</div>
                     <form method="post" enctype="multipart/form-data" id="displayUploadForm" novalidate>
                        <input type="hidden" name="csrf" value="<?= displayVideoEscape($csrf) ?>">
                        <input type="hidden" name="action" value="upload">
                        <div class="vs-upload">
                           <span class="vs-upload-icon" aria-hidden="true"><iconify-icon icon="material-symbols:upload-file-outline" width="42"></iconify-icon></span>
                           <label for="displayVideoFile" class="fw-semibold">Pilih video dari perangkat Anda</label>
                           <div class="text-muted small mt-1">Format MP4 · Maksimal 100 MB per video</div>
                           <input type="file" class="form-control" id="displayVideoFile" name="video"
                              accept=".mp4,video/mp4" aria-describedby="selectedVideoInfo" required>
                           <div class="text-muted small mt-2 vs-filename" id="selectedVideoInfo" aria-live="polite">Belum ada file dipilih.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="uploadVideoButton">
                           <iconify-icon icon="material-symbols:upload" class="me-1" aria-hidden="true"></iconify-icon>Upload Video
                        </button>
                        <div class="form-text mt-2">Ukuran upload juga mengikuti batas PHP pada server.</div>
                     </form>
                  </section>

                  <section class="vs-card" aria-labelledby="previewTitle">
                     <h6 class="fw-semibold mb-1" id="previewTitle">Pratinjau Video</h6>
                     <div class="text-muted small">Periksa video sebelum ditampilkan di ruang tunggu.</div>
                     <div class="vs-player">
                        <video id="settingsVideoPreview" controls playsinline preload="metadata" hidden></video>
                        <div class="vs-player-empty" id="settingsPreviewEmpty">
                           <iconify-icon icon="material-symbols:play-circle-outline" width="44" aria-hidden="true"></iconify-icon>
                           <span>Pilih file atau klik Pratinjau pada daftar video.</span>
                        </div>
                     </div>
                     <div class="small text-muted mt-3 vs-filename" id="previewVideoName" aria-live="polite">Belum ada video dipilih.</div>
                  </section>
               </div>

               <section class="vs-card" aria-labelledby="videoListTitle">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                     <div>
                        <h6 class="fw-semibold mb-1" id="videoListTitle">Daftar Video</h6>
                        <div class="text-muted small">Video diputar berurutan berdasarkan nama file pada display admisi.</div>
                     </div>
                     <span class="badge bg-primary"><?= count($videoFiles) ?> video</span>
                  </div>
                  <div class="table-responsive">
                     <table class="table vs-table mb-0">
                        <thead><tr><th scope="col">Video</th><th scope="col">Ukuran</th><th scope="col">Format</th><th scope="col" class="text-end">Aksi</th></tr></thead>
                        <tbody>
                           <?php if (!$videoFiles): ?>
                              <tr><td colspan="4"><div class="vs-empty">
                                 <iconify-icon icon="material-symbols:video-library-outline" width="38" aria-hidden="true"></iconify-icon>
                                 <div class="fw-semibold mt-2"><?= $entries === false ? 'Folder video tidak dapat dibaca' : 'Belum ada video' ?></div>
                                 <div class="small mt-1"><?= $entries === false ? 'Periksa izin baca folder video di server.' : 'Upload video pertama melalui formulir di atas.' ?></div>
                              </div></td></tr>
                           <?php endif; ?>
                           <?php foreach ($videoFiles as $filename): ?>
                              <?php $size = @filesize($videoDir . '/' . $filename); ?>
                              <tr>
                                 <td><div class="d-flex align-items-center gap-3">
                                    <span class="vs-file-icon" aria-hidden="true"><iconify-icon icon="material-symbols:movie-outline" width="22"></iconify-icon></span>
                                    <span class="vs-filename"><?= displayVideoEscape($filename) ?></span>
                                 </div></td>
                                 <td class="text-nowrap"><?= $size === false ? '—' : number_format($size / 1048576, 2, ',', '.') . ' MB' ?></td>
                                 <td><span class="vs-status"><?= displayVideoEscape(strtoupper(pathinfo($filename, PATHINFO_EXTENSION))) ?></span></td>
                                 <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-preview-video
                                       data-url="<?= displayVideoEscape($videoUrl . rawurlencode($filename)) ?>"
                                       data-name="<?= displayVideoEscape($filename) ?>">Pratinjau</button>
                                    <form method="post" class="d-inline" data-delete-display-video>
                                       <input type="hidden" name="csrf" value="<?= displayVideoEscape($csrf) ?>">
                                       <input type="hidden" name="action" value="delete">
                                       <input type="hidden" name="filename" value="<?= displayVideoEscape($filename) ?>">
                                       <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
                  <div class="vs-help mt-3">Muat ulang halaman display admisi setelah menambah atau menghapus video agar daftar putarnya diperbarui.</div>
               </section>
            </div>
         </div>
      </div>
   </div>
   <?php require __DIR__ . '/library.php'; ?>
   <script>
      (async function () {
         const input = document.getElementById('displayVideoFile');
         const info = document.getElementById('selectedVideoInfo');
         const player = document.getElementById('settingsVideoPreview');
         const empty = document.getElementById('settingsPreviewEmpty');
         const nameLabel = document.getElementById('previewVideoName');
         const uploadForm = document.getElementById('displayUploadForm');
         const loadError = document.getElementById('sweetAlertError');
         let objectUrl = null;
         let busy = false;
         let swalReady;

         function getSwal() {
            if (window.Swal && typeof window.Swal.fire === 'function') return Promise.resolve(window.Swal);
            if (!swalReady) swalReady = new Promise(function (resolve, reject) {
               const script = document.createElement('script');
               script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js';
               script.onload = function () {
                  if (window.Swal && typeof window.Swal.fire === 'function') resolve(window.Swal);
                  else reject(new Error('SweetAlert2 tidak tersedia.'));
               };
               script.onerror = function () { script.remove(); reject(new Error('SweetAlert2 gagal dimuat. Pastikan library.php memuat SweetAlert2 atau koneksi internet tersedia.')); };
               document.head.appendChild(script);
            }).catch(function (error) { swalReady = null; throw error; });
            return swalReady;
         }
         async function requireSwal() {
            try { const swal = await getSwal(); loadError.hidden = true; return swal; }
            catch (error) { loadError.textContent = error.message; loadError.hidden = false; return null; }
         }
         function resetPreview() {
            player.pause();
            player.removeAttribute('src');
            player.load();
            player.hidden = true;
            empty.hidden = false;
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
         }
         function preview(url, name) {
            player.src = url;
            player.hidden = false;
            empty.hidden = true;
            nameLabel.textContent = name;
            player.load();
         }
         function fileError(file) {
            if (!file) return 'Pilih video terlebih dahulu.';
            if (!/\.mp4$/i.test(file.name)) return 'Format video harus MP4.';
            if (file.size === 0 || file.size > 100 * 1024 * 1024) return 'Ukuran video harus lebih dari 0 dan maksimal 100 MB.';
            return '';
         }
         function setBusy(value) {
            busy = value;
            input.disabled = value;
            document.querySelectorAll('#displayUploadForm button, [data-delete-display-video] button').forEach(function (button) { button.disabled = value; });
         }
         function sendForm(formData, progress) {
            return new Promise(function (resolve, reject) {
               const xhr = new XMLHttpRequest();
               xhr.open('POST', window.location.href);
               xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
               xhr.setRequestHeader('Accept', 'application/json');
               xhr.upload.onprogress = progress || null;
               xhr.onload = function () {
                  let data;
                  try { data = JSON.parse(xhr.responseText); }
                  catch (_) {
                     reject(new Error(xhr.status === 413 ? 'Ukuran video ditolak server. Periksa batas upload PHP dan web server.' : 'Server tidak memberikan respons JSON. Periksa sesi login dan log PHP sebelum mencoba lagi.'));
                     return;
                  }
                  if (xhr.status >= 200 && xhr.status < 300 && data.success) resolve(data);
                  else reject(new Error(data.message || 'Permintaan gagal diproses.'));
               };
               xhr.onerror = function () { reject(new Error('Koneksi terputus. Muat ulang daftar video untuk memeriksa hasil sebelum mencoba lagi.')); };
               xhr.onabort = function () { reject(new Error('Permintaan dibatalkan. Muat ulang daftar video untuk memeriksa hasilnya.')); };
               xhr.send(formData);
            });
         }
         input.addEventListener('change', async function () {
            resetPreview();
            nameLabel.textContent = 'Belum ada video dipilih.';
            const file = input.files[0];
            if (!file) { info.textContent = 'Belum ada file dipilih.'; return; }
            const error = fileError(file);
            if (error) {
               info.textContent = error;
               const swal = await requireSwal();
               if (swal) await swal.fire({ icon: 'warning', title: 'Periksa file video', text: error });
               return;
            }
            info.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(2) + ' MB';
            objectUrl = URL.createObjectURL(file);
            preview(objectUrl, file.name + ' — belum diupload');
         });
         document.querySelectorAll('[data-preview-video]').forEach(function (button) {
            button.addEventListener('click', function () {
               resetPreview();
               preview(button.dataset.url, button.dataset.name);
               player.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
         });
         player.addEventListener('error', function () {
            if (player.getAttribute('src')) nameLabel.textContent = 'Pratinjau gagal. Periksa format atau alamat file video.';
         });
         uploadForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (busy) return;
            const swal = await requireSwal();
            if (!swal || busy) return;
            const error = fileError(input.files[0]);
            if (error) { await swal.fire({ icon: 'warning', title: 'Periksa file video', text: error }); return; }
            // Ambil FormData sebelum input dinonaktifkan.
            const data = new FormData(uploadForm);
            setBusy(true);
            try {
               swal.fire({
                  title: 'Mengupload video',
                  html: '<div class="vs-progress-track" role="progressbar" aria-label="Progres upload" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="uploadProgressTrack"><div class="vs-progress-fill" id="uploadProgressFill"></div></div><div class="vs-progress-value" id="uploadProgressValue">0%</div><div class="vs-progress-note" id="uploadProgressNote">Mengirim video ke server…</div>',
                  showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false,
                  didOpen: function () {
                     // Mulai sesudah elemen progres tersedia.
                     sendForm(data, function (event) {
                        const note = document.getElementById('uploadProgressNote');
                        if (!event.lengthComputable) { note.textContent = 'Mengirim video… ukuran total belum tersedia.'; return; }
                        const percent = Math.min(100, Math.round(event.loaded / event.total * 100));
                        document.getElementById('uploadProgressFill').style.width = percent + '%';
                        document.getElementById('uploadProgressValue').textContent = percent + '%';
                        document.getElementById('uploadProgressTrack').setAttribute('aria-valuenow', percent);
                        if (event.loaded >= event.total) note.textContent = 'Upload terkirim. Server sedang memeriksa dan menyimpan video…';
                     }).then(async function (result) {
                        await swal.fire({ icon: 'success', title: 'Video berhasil ditambahkan', text: result.message, confirmButtonText: 'Selesai' });
                        setBusy(false);
                        window.location.reload();
                     }).catch(async function (error) {
                        await swal.fire({ icon: 'error', title: 'Upload gagal', text: error.message, confirmButtonText: 'Tutup' });
                     }).finally(function () { setBusy(false); });
                  }
               });
            } catch (error) {
               setBusy(false);
               await swal.fire({ icon: 'error', title: 'Upload gagal', text: error.message });
            }
         });
         document.querySelectorAll('[data-delete-display-video]').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
               event.preventDefault();
               if (busy) return;
               const swal = await requireSwal();
               if (!swal || busy) return;
               const confirmed = await swal.fire({
                  icon: 'warning', title: 'Hapus video ini?', text: form.elements.filename.value + ' akan dihapus dari daftar putar.',
                  showCancelButton: true, confirmButtonText: 'Ya, hapus video', cancelButtonText: 'Batal',
                  confirmButtonColor: '#dc3545', focusCancel: true
               });
               if (!confirmed.isConfirmed || busy) return;
               const data = new FormData(form);
               setBusy(true);
               resetPreview();
               nameLabel.textContent = 'Belum ada video dipilih.';
               swal.fire({ title: 'Menghapus video…', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: function () { swal.showLoading(); } });
               try {
                  const result = await sendForm(data);
                  await swal.fire({ icon: 'success', title: 'Video berhasil dihapus', text: result.message, confirmButtonText: 'Selesai' });
                  setBusy(false);
                  window.location.reload();
               } catch (error) {
                  await swal.fire({ icon: 'error', title: 'Video gagal dihapus', text: error.message, confirmButtonText: 'Tutup' });
               } finally { setBusy(false); }
            });
         });
         window.addEventListener('beforeunload', function (event) {
            if (busy) { event.preventDefault(); event.returnValue = ''; }
         });
         const notice = <?= json_encode($notice, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
         if (notice) {
            const swal = await requireSwal();
            if (swal) await swal.fire({ icon: notice.type === 'success' ? 'success' : 'error', title: notice.type === 'success' ? 'Berhasil' : 'Proses gagal', text: notice.message });
         }
      })();
   </script>
</body>
</html>
