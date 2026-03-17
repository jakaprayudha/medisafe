<?php
$title = "Reset Password";
require 'controller/auth.php';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medisafe | <?= $title ?></title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/icon_medisafe.png" />
  <!-- <script src="assets/js/sweet-alert/sweetalert.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
  <link rel="stylesheet" href="assets/css/login.css">
  <style>
    /* center text tambahan */
    .form-extra.center {
      text-align: center;
      margin-top: 15px;
    }

    .form-extra.center span {
      font-size: 14px;
      color: #555;
      margin-right: 5px;
    }

    .form-extra.center a {
      font-weight: bold;
      color: #0f9b8e;
      text-decoration: none;
    }
  </style>
</head>

<body>

  <div class="login-container">
    <div class="login-card">

      <!-- LEFT -->
      <div class="login-left">
        <img src="assets/images/logos/medisafe_logo.png" class="logo" alt="">

        <h2>Reset Password 🔐</h2>
        <p>Masukkan username anda untuk melakukan reset </p>

        <form method="POST">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
          </div>

          <button type="submit" name="reset" class="btn-login">
            Reset Password
          </button>

          <div class="form-extra center">
            <span>Sudah punya password?</span>
            <a href="index">Sign In</a>
          </div>
        </form>
      </div>

      <!-- RIGHT -->
      <div class="login-right">
        <div class="overlay">
          <h3>Secure Access 🔒</h3>
          <p>Pastikan akun Anda tetap aman dengan proses reset yang cepat dan terpercaya.</p>
        </div>
      </div>

    </div>
  </div>

</body>
<?php
require 'assets/template/library.php';
?>

</html>