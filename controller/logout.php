<?php
session_start();

// catat logout sebelum session dihapus (koneksi DB hanya dibuka bila masih login)
if (!empty($_SESSION['uid_user'])) {
   try {
      require_once __DIR__ . '/../database/connect.php';
      require_once __DIR__ . '/../utility/activityLog.php';
      if (isset($koneksi) && $koneksi instanceof mysqli) {
         logAktivitas($koneksi, 'auth', 'logout', ['tipe' => 'user', 'id' => $_SESSION['uid_user'], 'label' => $_SESSION['username'] ?? null],
            !empty($_SESSION['switch_dari']['username']) ? 'Logout saat masuk sebagai akun lain' : '');
      }
   } catch (Throwable $e) {
      error_log('[logout] ' . $e->getMessage());
   }
}

// Hapus semua session
$_SESSION = [];

// Hancurkan session di server
session_destroy();

// Arahkan kembali ke halaman login
header("Location: ../index");
exit;
