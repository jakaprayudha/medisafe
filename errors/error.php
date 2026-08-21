<?php
session_start();

$code = isset($_SERVER['REDIRECT_STATUS']) && ctype_digit($_SERVER['REDIRECT_STATUS'])
   ? (int) $_SERVER['REDIRECT_STATUS']
   : 404;

$pages = [
   400 => ['title' => 'Permintaan Tidak Valid', 'message' => 'Permintaan yang dikirim ke server tidak dapat diproses. Periksa kembali data yang dimasukkan.', 'icon' => 'alert', 'accent' => 'warning'],
   401 => ['title' => 'Sesi Anda Berakhir', 'message' => 'Anda perlu masuk kembali untuk melanjutkan mengakses sistem.', 'icon' => 'lock', 'accent' => 'primary'],
   403 => ['title' => 'Akses Ditolak', 'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'icon' => 'shield', 'accent' => 'danger'],
   404 => ['title' => 'Halaman Tidak Ditemukan', 'message' => 'Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah.', 'icon' => 'search', 'accent' => 'primary'],
   500 => ['title' => 'Terjadi Kesalahan pada Server', 'message' => 'Sistem sedang mengalami gangguan tak terduga. Tim kami sudah diberi tahu dan sedang menanganinya.', 'icon' => 'server', 'accent' => 'danger'],
   502 => ['title' => 'Layanan Tidak Tersedia', 'message' => 'Server sedang tidak dapat merespons. Silakan coba beberapa saat lagi.', 'icon' => 'cloud', 'accent' => 'danger'],
   503 => ['title' => 'Sedang Dalam Perbaikan', 'message' => 'Sistem sedang dalam pemeliharaan terjadwal. Mohon coba kembali beberapa saat lagi.', 'icon' => 'tool', 'accent' => 'warning'],
];

$page = $pages[$code] ?? ['title' => 'Terjadi Kesalahan', 'message' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.', 'icon' => 'alert', 'accent' => 'warning'];

http_response_code($code);

$isLoggedIn = isset($_SESSION['username']);
$homeUrl = $isLoggedIn ? '/module/admin' : '/';
$homeLabel = $isLoggedIn ? 'Kembali ke Dashboard' : 'Kembali ke Login';

function iconPaths(string $type): string
{
   $icons = [
      'search' => '<circle cx="10" cy="10" r="6"/><line x1="14.5" y1="14.5" x2="20" y2="20"/>',
      'lock' => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
      'shield' => '<path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6z"/><line x1="9" y1="12" x2="15" y2="12"/>',
      'server' => '<rect x="4" y="4" width="16" height="6" rx="1.5"/><rect x="4" y="14" width="16" height="6" rx="1.5"/><circle cx="8" cy="7" r="1" fill="currentColor" stroke="none"/><circle cx="8" cy="17" r="1" fill="currentColor" stroke="none"/>',
      'cloud' => '<path d="M7 18a4 4 0 0 1-.5-7.97A5 5 0 0 1 16.2 8.1 4.5 4.5 0 0 1 17.5 17"/><line x1="3" y1="21" x2="21" y2="3"/>',
      'tool' => '<path d="M14.5 6.5l3 3-6 6-3-3z"/><path d="M6 18l2.5-2.5"/><path d="M17 4l3 3-2 2-3-3z"/>',
      'alert' => '<path d="M12 4l9 15H3z"/><line x1="12" y1="10" x2="12" y2="14"/><circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="none"/>',
   ];
   return $icons[$type] ?? $icons['alert'];
}

function renderBadgeIcon(string $type): string
{
   return '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . iconPaths($type) . '</svg>';
}
?>
<!doctype html>
<html lang="id">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Medisafe | <?= htmlspecialchars($page['title']) ?></title>
   <link rel="shortcut icon" type="image/png" href="/assets/images/logos/icon_medisafe.png" />
   <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
   <div class="error-container">
      <div class="error-card">

         <div class="error-content">
            <img src="/assets/images/logos/medisafe_logo.png" class="error-logo" alt="Medisafe">

            <span class="error-badge accent-<?= $page['accent'] ?>">
               <?= renderBadgeIcon($page['icon']) ?>
            </span>

            <div class="error-code">Error <?= $code ?></div>
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <p><?= htmlspecialchars($page['message']) ?></p>

            <div class="error-actions">
               <a href="<?= htmlspecialchars($homeUrl) ?>" class="btn-error btn-error-primary"><?= htmlspecialchars($homeLabel) ?></a>
               <a href="javascript:history.back()" class="btn-error btn-error-secondary">Kembali</a>
            </div>
         </div>

         <div class="error-visual">
            <svg class="error-illustration" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
               <defs>
                  <filter id="blob-blur" x="-50%" y="-50%" width="200%" height="200%">
                     <feGaussianBlur stdDeviation="26" />
                  </filter>
                  <linearGradient id="screen-grad" x1="0" y1="0" x2="1" y2="1">
                     <stop offset="0" stop-color="#5D87FF" />
                     <stop offset="1" stop-color="#4066E0" />
                  </linearGradient>
               </defs>

               <!-- ambient blobs -->
               <circle cx="90" cy="90" r="70" fill="#5D87FF" opacity="0.16" filter="url(#blob-blur)" />
               <circle cx="320" cy="300" r="90" fill="#13DEB9" opacity="0.16" filter="url(#blob-blur)" />

               <!-- floating decorations -->
               <g class="float-slow">
                  <rect x="52" y="250" width="34" height="16" rx="8" fill="#13DEB9" opacity="0.85" transform="rotate(-30 69 258)" />
                  <rect x="60" y="255" width="34" height="16" rx="8" fill="#ffffff" opacity="0.9" transform="rotate(-30 77 263)" />
               </g>
               <g class="float-fast">
                  <path d="M330 95 h10 v10 h10 v10 h-10 v10 h-10 v-10 h-10 v-10 h10 z" fill="#FFAE1F" opacity="0.9" />
               </g>
               <circle class="float-slow" cx="70" cy="150" r="5" fill="#5D87FF" opacity="0.6" />
               <circle class="float-fast" cx="345" cy="230" r="4" fill="#FA896B" opacity="0.7" />

               <!-- monitor -->
               <rect x="110" y="120" width="200" height="150" rx="16" fill="#ffffff" stroke="#EAEFF4" stroke-width="2" />
               <rect x="128" y="138" width="164" height="90" rx="10" fill="url(#screen-grad)" />
               <polyline points="138,185 160,185 172,165 184,205 196,175 208,185 282,185" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
               <rect x="185" y="270" width="30" height="14" fill="#EAEFF4" />
               <rect x="150" y="284" width="100" height="10" rx="5" fill="#EAEFF4" />

               <!-- badge duplicate on illustration -->
               <circle cx="290" cy="255" r="26" fill="#ffffff" />
               <circle cx="290" cy="255" r="22" class="accent-fill-<?= $page['accent'] ?>" />
               <g transform="translate(278,243)" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?= iconPaths($page['icon']) ?></g>
            </svg>
         </div>

      </div>
   </div>
</body>

</html>
