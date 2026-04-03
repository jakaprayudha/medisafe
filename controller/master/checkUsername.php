<?php
require '../../database/connect.php';
header("Content-Type: application/json");

$username = $_GET['username'] ?? '';
$id_user = $_GET['id_user'] ?? ''; // 🔥 biar tidak ngecek dirinya sendiri

$username = $koneksi->real_escape_string($username);

$query = "SELECT uid_user FROM ms_users 
          WHERE username='$username' 
          AND uid_user != '$id_user'
          LIMIT 1";

$result = $koneksi->query($query);

if ($result && $result->num_rows > 0) {
   echo json_encode([
      "exists" => true,
      "message" => "Username sudah digunakan"
   ]);
} else {
   echo json_encode([
      "exists" => false,
      "message" => "Username tersedia"
   ]);
}
