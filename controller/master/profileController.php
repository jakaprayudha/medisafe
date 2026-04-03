<?php
require '../../database/connect.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   $id = $_POST['id_user'] ?? null;
   $fullname = $_POST['fullname'] ?? '';
   $username = $_POST['username'] ?? '';
   $password = $_POST['password'] ?? '';

   if (!$id || !$fullname || !$username) {
      echo json_encode([
         "success" => false,
         "message" => "Data tidak lengkap"
      ]);
      exit;
   }


   $fullname = $koneksi->real_escape_string($fullname);
   $username = $koneksi->real_escape_string($username);

   $updates = [];
   $updates[] = "fullname='$fullname'";
   $updates[] = "username='$username'";

   // 🔥 password optional
   if (!empty($password)) {
      $hash = md5($password);
      $updates[] = "password='$hash'";
   }

   $updates[] = "udpated_at = NOW()";

   $sql = "UPDATE ms_users 
           SET " . implode(", ", $updates) . " 
           WHERE uid_user='$id'";

   if ($koneksi->query($sql)) {
      echo json_encode([
         "success" => true,
         "message" => "Profile berhasil diupdate"
      ]);
   } else {
      echo json_encode([
         "success" => false,
         "message" => "Gagal update: " . $koneksi->error
      ]);
   }
}
