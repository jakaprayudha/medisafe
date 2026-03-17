<?php
$title = "Login";
require_once 'controller/auth.php';
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
</head>

<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-left">
        <img src="assets/images/logos/medisafe_logo.png" class="logo" alt="">
        <h2>Welcome Back 👋</h2>
        <p>Medisafe Clinic Management System</p>

        <form method="POST">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
          </div>

          <div class="form-extra">
            <a href="reset">Lupa Password?</a>
          </div>

          <button type="submit" name="login" class="btn-login">
            Sign In
          </button>
        </form>
      </div>

      <div class="login-right">
        <div class="overlay">
          <h3>Healthy System 🌿</h3>
          <p>Mengelola data pasien, dokter, dan layanan klinik secara terintegrasi, cepat, dan akurat untuk mendukung pelayanan kesehatan yang optimal.</p>
        </div>
      </div>

    </div>
  </div>

</body>
<?php
require 'assets/template/library.php';
?>

</html>