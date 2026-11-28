<?php
session_start();
require 'database/connect.php';
require_once __DIR__ . '/../utility/security.php';
require_once __DIR__ . '/../utility/activityLog.php';

const MSG_LOGIN_GAGAL = 'Username atau Password salah';

if (isset($_POST['login'])) {

   $username = trim($_POST['username'] ?? '');
   $password = $_POST['password'] ?? '';
   $_SESSION['redirectlogin'] = '';

   if (loginLockRemaining($koneksi, $username) > 0) {
      // 🔒 Terkunci: ditolak walau password benar
      $_SESSION["error"] = 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam ' . LOGIN_LOCK_MINUTES . ' menit.';
      logAktivitas($koneksi, 'auth', 'login_terkunci', ['tipe' => 'user', 'label' => $username], 'Login ditolak: akun terkunci sementara', null, null, 0);
      return;
   }

   $st = $koneksi->prepare("SELECT * FROM ms_users WHERE username = ?");
   $st->bind_param('s', $username);
   $st->execute();
   $datauser = $st->get_result()->fetch_assoc();
   $st->close();

   // Pesan sama untuk username tidak ada / password salah (anti user enumeration)
   if (!$datauser || !verifyPassword($password, (string) $datauser['password'])) {
      loginRecordFailure($koneksi, $username);
      $_SESSION["error"] = MSG_LOGIN_GAGAL;
      logAktivitas($koneksi, 'auth', 'login_gagal', ['tipe' => 'user', 'id' => $datauser['uid_user'] ?? null, 'label' => $username],
         $datauser ? 'Password salah' : 'Username tidak terdaftar', null, null, $datauser ? (int) $datauser['id_customer'] : 0);
      return;
   }

   // Status akun hanya dibuka ke pemilik kredensial yang valid
   if ($datauser['status'] == '0') {
      $_SESSION["error"] = 'Akun anda dinonaktifkan, silakan hubungi admin.';
      logAktivitas($koneksi, 'auth', 'login_nonaktif', ['tipe' => 'user', 'id' => $datauser['uid_user'], 'label' => $username], 'Login ditolak: akun nonaktif', null, null, (int) $datauser['id_customer']);
      return;
   }

   loginClearFailures($koneksi, $username);

   // Upgrade hash md5 lama ke bcrypt
   if (passwordNeedsRehash((string) $datauser['password'])) {
      $newHash = hashPassword($password);
      $up = $koneksi->prepare("UPDATE ms_users SET password = ? WHERE uid_user = ?");
      $up->bind_param('ss', $newHash, $datauser['uid_user']);
      $up->execute();
      $up->close();
   }

   session_regenerate_id(true);
   unset($_SESSION['switch_dari'], $_SESSION['kode_dokter']); // login baru: bukan sesi switch user

   if ($datauser['roles'] == 'dokter') {
      $fullname = $datauser['fullname'];
      $kdDokter = $datauser['kdDokter'];
      $dq = $koneksi->prepare("SELECT doctor_code FROM ms_doctor WHERE doctor_name = ? OR doctor_code = ?");
      $dq->bind_param('ss', $fullname, $kdDokter);
      $dq->execute();
      $dok = $dq->get_result()->fetch_assoc();
      $dq->close();
      $_SESSION['kode_dokter'] = $dok['doctor_code'] ?? null;
   }

   $_SESSION['uid_user'] = $datauser['uid_user'];
   $_SESSION['username'] = $datauser['username'];
   $_SESSION['roles'] = $datauser['roles'];
   $_SESSION['id_customer'] = $datauser['id_customer'];
   $_SESSION['status'] = $datauser['status'];
   $_SESSION['fullname'] = $datauser['fullname'];

   logAktivitas($koneksi, 'auth', 'login', ['tipe' => 'user', 'id' => $datauser['uid_user'], 'label' => $datauser['username']], 'Login sebagai ' . $datauser['roles']);
   $_SESSION["sukses"] = 'Selamat Anda Berhasil Login Sebagai ' . $datauser['roles'];
   $_SESSION['redirectlogin'] = 'module/admin';
?>
   <script>
      const configData = {
         id_customer: <?= json_encode($datauser['id_customer']) ?>,
         id_user: <?= json_encode($datauser['uid_user']) ?>
      };
      localStorage.setItem("rs_config", JSON.stringify(configData));
   </script>
<?php
}

// Reset password mandiri dinonaktifkan: tanpa verifikasi identitas = pengambilalihan akun.
// Reset dilakukan admin lewat menu User (password wajib memenuhi policy).
if (isset($_POST['reset'])) {
   $_SESSION["error"] = 'Reset password mandiri dinonaktifkan. Silakan hubungi admin klinik Anda.';
   $_SESSION['redirectlogin'] = 'index';
}
