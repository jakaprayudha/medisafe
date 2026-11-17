<?php
$title = "Reset Password";
require 'controller/auth.php';
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
  <link rel="stylesheet" href="assets/css/login.css?v=2">
</head>

<body>
  <div class="auth">
    <?php
    $brandBadge = 'Keamanan Akun';
    $brandTitle = 'Akses akun Anda <span>kembali</span> dengan aman';
    $brandText = 'Atur ulang password dalam hitungan detik, lalu masuk kembali untuk melanjutkan pelayanan di klinik Anda.';
    require 'assets/template/auth-brand.php';
    ?>

    <main class="auth-main">
      <div class="auth-card">
        <img src="assets/images/logos/medisafe_logo.png" class="mobile-logo" alt="Medisafe">

        <div class="auth-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="7.5" cy="15.5" r="5.5" />
            <path d="m21 2-9.6 9.6M15.5 7.5l3 3L22 7l-3-3" />
          </svg>
        </div>
        <h2>Lupa password?</h2>
        <p class="auth-sub">Masukkan username Anda dan sistem akan membuatkan password baru.</p>

        <div class="note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 16v-4M12 8h.01" />
          </svg>
          <div>Password baru akan ditampilkan setelah reset. Catat password tersebut, lalu segera masuk dan gunakan untuk login.</div>
        </div>

        <form method="POST" id="authForm" novalidate>
          <input type="hidden" name="reset" value="1">

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

          <button type="submit" class="btn-auth" id="btnSubmit" style="margin-top:6px">
            <span class="spinner"></span>
            <span class="label-text">Reset Password</span>
            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </button>
        </form>

        <div class="divider">atau</div>
        <div class="alt-link">
          Sudah ingat password?
          <a href="index">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 12H5M11 18l-6-6 6-6" />
            </svg>
            Kembali ke Login
          </a>
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
