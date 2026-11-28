<?php
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../../utility/activityLog.php';
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
// akun admin faskes ini (dulu: user PERTAMA faskes apa pun perannya, lalu UPDATE ... WHERE id_customer=?
// menimpa nama/username/password SEMUA user faskes tersebut)
$cekUser = $koneksi->prepare("SELECT id_user, uid_user, fullname, username, roles FROM ms_users WHERE id_customer = ? AND roles = 'admin' ORDER BY id_user LIMIT 1");
$cekUser->bind_param('i', $id_customer);
$cekUser->execute();
$existing = $cekUser->get_result()->fetch_assoc();
$cekUser->close();

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
         WHERE id_user=? AND id_customer=?
      ");
      $idAdmin = (int) $existing['id_user'];
      $stmt->bind_param("sssii", $nama, $username, $password_hash, $idAdmin, $id_customer);
   } else {
      // tanpa ubah password
      $stmt = $koneksi->prepare("
         UPDATE ms_users 
         SET fullname=?, username=? 
         WHERE id_user=? AND id_customer=?
      ");
      $idAdmin = (int) $existing['id_user'];
      $stmt->bind_param("ssii", $nama, $username, $idAdmin, $id_customer);
   }

   if ($stmt->execute()) {
      [$l, $b] = logBeda($existing, ['fullname' => $nama, 'username' => $username]);
      logAktivitas($koneksi, 'user', 'ubah', ['tipe' => 'user', 'id' => $existing['uid_user'], 'label' => $username],
         'Akun admin faskes diubah dari menu superadmin' . (!empty($password) ? ' (password diganti)' : ''), $l ?: null, $b ?: null, $id_customer);
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
   logAktivitas($koneksi, 'user', 'tambah', ['tipe' => 'user', 'id' => $uid_user, 'label' => $username], 'Akun admin faskes dibuat dari menu superadmin',
      null, ['fullname' => $nama, 'username' => $username, 'roles' => $roles], $id_customer);
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
