<?php
error_reporting(0);
session_start();
require 'database/connect.php';
if (isset($_POST['login'])) {

   $username = $_POST['username'];
   $password = md5($_POST['password']);

   $param = "SELECT * FROM ms_users WHERE username = '$username'";
   $sql = mysqli_query($koneksi, $param);
   $datauser = mysqli_fetch_array($sql);

   // ❌ USER TIDAK ADA
   if (!$datauser) {
      $_SESSION["error"] = 'Username Tidak Ada';
      $_SESSION['redirectlogin'] = '';
      return;
   }

   // ❌ USER NONAKTIF
   if ($datauser['status'] == '0') {
      $_SESSION["error"] = 'Akun anda dinonaktifkan, silakan hubungi admin.';
      $_SESSION['redirectlogin'] = '';
      return;
   }

   // 🔐 CEK PASSWORD
   if ($datauser['password'] == $password) {
      $_SESSION['uid_user'] = $datauser['uid_user'];
      $_SESSION['username'] = $datauser['username'];
      $_SESSION['roles'] = $datauser['roles'];
      $_SESSION['id_customer'] = $datauser['id_customer'];
      $_SESSION['status'] = $datauser['status'];
      $_SESSION['fullname'] = $datauser['fullname'];

      $_SESSION["sukses"] = 'Selamat Anda Berhasil Login Sebagai ' . $datauser['roles'];
      $_SESSION['redirectlogin'] = 'module/admin';
?>
      <script>
         const configData = {
            id_customer: "<?php echo $datauser['id_customer'] ?>",
         };
         localStorage.setItem("rs_config", JSON.stringify(configData));
      </script>
<?php
   } else {
      $_SESSION["error"] = 'Password Salah';
      $_SESSION['redirectlogin'] = '';
   }
}

if (isset($_POST['reset'])) {
   $username = $_POST['username'];
   $param = "SELECT * FROM ms_users WHERE username = '$username'";
   $sql = mysqli_query($koneksi, $param);
   $datauser = mysqli_fetch_array($sql);
   if ($datauser['username'] == $username) {
      $passwordNew = rand(1111, 9999);
      $password = md5($passwordNew);
      $param = "UPDATE ms_users SET password = '$password' WHERE username = '$username'";
      $sql = mysqli_query($koneksi, $param);
      if ($sql) {
         $_SESSION["sukses"] = 'Password Baru : ' . $passwordNew;
         $_SESSION['redirectlogin'] = 'index';
      }
   } else {
      $_SESSION["error"] = 'Username Tidak Ada';
      $_SESSION['redirectlogin'] = 'reset';
   }
}
