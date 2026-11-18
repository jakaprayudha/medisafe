<?php
$title = "Login";
require_once 'controller/auth.php';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medisafe | <?= $title ?></title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/icon_medisafe.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" integrity="sha384-YB/DdIkloKoRpclWB8bNcYXWakt57USgtQPDzvnIDHYU0lasD5eWlXVo1S4ODukY" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="assets/css/login.css?v=2">
</head>

<body>
  <div class="auth">
    <?php
    $brandBadge = 'Sistem Informasi Klinik';
    $brandTitle = 'Kelola klinik <span>lebih cepat</span> &amp; terintegrasi';
    require 'assets/template/auth-brand.php';
    ?>

    <main class="auth-main">
      <div class="auth-card">
        <img src="assets/images/logos/medisafe_logo.png" class="mobile-logo" alt="Medisafe">

        <div class="auth-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <path d="m10 17 5-5-5-5M15 12H3" />
          </svg>
        </div>
        <h2>Selamat datang kembali</h2>
        <p class="auth-sub">Masuk ke akun Medisafe Anda untuk melanjutkan.</p>

        <form method="POST" id="authForm" autocomplete="on" novalidate>
          <input type="hidden" name="login" value="1">

          <div class="field">
            <div class="field-head"><label for="username">Username</label></div>
            <div class="input-wrap">
              <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <input type="text" id="username" name="username" placeholder="Masukkan username" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
            </div>
          </div>

          <div class="field">
            <div class="field-head">
              <label for="password">Password</label>
              <a href="reset">Lupa password?</a>
            </div>
            <div class="input-wrap">
              <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
              <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan password" aria-pressed="false">
                <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                  <path d="m1 1 22 22" />
                </svg>
              </button>
            </div>
            <div class="hint" id="capsHint">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v4M12 17h.01" />
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              </svg>
              Caps Lock aktif
            </div>
          </div>

          <div class="form-row">
            <label class="check"><input type="checkbox" id="rememberUser"> Ingat username</label>
          </div>

          <button type="submit" class="btn-auth" id="btnSubmit">
            <span class="spinner"></span>
            <span class="label-text">Masuk</span>
            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </button>
        </form>

        <div class="secure">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          </svg>
          Akses hanya untuk tenaga kesehatan &amp; staf klinik terdaftar
        </div>
      </div>
    </main>
  </div>

  <script src="assets/js/auth-form.js?v=1"></script>
</body>
<?php
require 'assets/template/library.php';
?>

</html>
