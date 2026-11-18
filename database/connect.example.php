<?php
date_default_timezone_set('Asia/Jakarta');

// 🔐 START SESSION (cookie aman: HttpOnly, SameSite, Secure bila HTTPS)
if (session_status() === PHP_SESSION_NONE) {
   session_set_cookie_params([
      'lifetime' => 0,
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
   ]);
   session_start();
}

// 🔌 DATABASE
$host = "localhost";
$uname = "root";
$password = "";
$database = "db_medisafe";

$koneksi = mysqli_connect($host, $uname, $password, $database);

// 🛡️ HALAMAN YANG TIDAK PERLU LOGIN (basename file)
$public_pages = ['index.php', 'auth.php', 'reset.php', 'verifikasi-surat.php'];

$current_page = basename($_SERVER['PHP_SELF']);

// 🚪 CEK SESSION
if (!in_array($current_page, $public_pages)) {

   if (!isset($_SESSION['uid_user'])) {

      // ❗ Destroy session
      session_unset();
      session_destroy();

      // 🔁 Redirect + alert
      echo "<script>
            alert('Session anda telah berakhir, silakan login kembali');
            window.location.href = 'index';
        </script>";
      exit;
   }
}
