<?php
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json');
requireRole(['administrator', 'superadmin']);

$input = $_POST;

$id_customer = isset($input['id_customer']) ? (int) $input['id_customer'] : null;
$nama = $input['nama'] ?? '';
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$roles = "admin";
$path = "admin";

if (!$id_customer || !$username) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

// ===============================
// 🔥 CEK USER EXIST
// ===============================
$cekUser = mysqli_query($koneksi, "
   SELECT * FROM ms_users WHERE id_customer='$id_customer' LIMIT 1
");

$existing = mysqli_fetch_assoc($cekUser);

// ===============================
// 🔥 GENERATE UID (HANYA SAAT INSERT)
// ===============================
function generateUID($koneksi, $username)
{
   do {
      $uid = md5($username . microtime(true) . rand(1000, 9999));
      $cek = mysqli_query($koneksi, "SELECT id_user FROM ms_users WHERE uid_user='$uid'");
   } while (mysqli_num_rows($cek) > 0);

   return $uid;
}

// ===============================
// 🔥 UPDATE MODE
// ===============================
if ($existing) {

   // kalau password diisi → update
   if (!empty($password)) {
      if ($err = passwordPolicyError($password)) {
         echo json_encode(['status' => 'error', 'message' => $err]);
         exit;
      }
      $password_hash = hashPassword($password);

      $stmt = $koneksi->prepare("
         UPDATE ms_users 
         SET fullname=?, username=?, password=? 
         WHERE id_customer=?
      ");
      $stmt->bind_param("sssi", $nama, $username, $password_hash, $id_customer);
   } else {
      // tanpa ubah password
      $stmt = $koneksi->prepare("
         UPDATE ms_users 
         SET fullname=?, username=? 
         WHERE id_customer=?
      ");
      $stmt->bind_param("ssi", $nama, $username, $id_customer);
   }

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'User berhasil diupdate'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   exit;
}

// ===============================
// 🔥 INSERT MODE
// ===============================
if (empty($password)) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Password wajib saat create'
   ]);
   exit;
}

if ($err = passwordPolicyError($password)) {
   echo json_encode(['status' => 'error', 'message' => $err]);
   exit;
}
$password_hash = hashPassword($password);
$uid_user = generateUID($koneksi, $username);

$stmt = $koneksi->prepare("
   INSERT INTO ms_users 
   (uid_user, id_customer, fullname, username, password, roles, path)
   VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("sisssss", $uid_user, $id_customer, $nama, $username, $password_hash, $roles, $path);

if ($stmt->execute()) {
   echo json_encode([
      'status' => 'success',
      'message' => 'User admin berhasil dibuat'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => $stmt->error
   ]);
}
