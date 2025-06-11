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
   if ($datauser['username'] == $username) {
      $passwordDB = $datauser['password'];
      if ($passwordDB == $password) {
         $_SESSION['uid_user'] = $datauser['uid_user'];
         $_SESSION['username'] = $datauser['username'];
         $_SESSION['roles'] = $datauser['roles'];
         $_SESSION['status'] = $datauser['status_user'];
         $_SESSION['fullname'] = $datauser['fullname'];
         if ($datauser['roles'] == 'admin') {
            // echo "<script>
            // alert('Selamat datang, Admin !');
            // window.location.href = 'module/admin/';
            //  </script>";
            $_SESSION["sukses"] = 'Selamat Anda Berhasil Login Sebagai Admin';
            $_SESSION['redirectlogin'] = 'module/admin';
         } else if ($datauser['roles'] == 'Waiters') {
            $_SESSION["sukses"] = 'Selamat Anda Berhasil Login Sebagai Waiters';
            $_SESSION['redirectlogin'] = 'module/waiter';
         } else if ($datauser['roles'] == 'Cassier') {
            $_SESSION["sukses"] = 'Selamat Anda Berhasil Login Sebagai Cassier';
            $_SESSION['redirectlogin'] = 'module/cassier';
         }
      } else {
         $_SESSION["error"] = 'Password Salah';
         $_SESSION['redirectlogin'] = '';
      }
   } else {
      $_SESSION["error"] = 'Username Tidak Ada';
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
