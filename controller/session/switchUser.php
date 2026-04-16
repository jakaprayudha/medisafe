<?php
session_start();
require '../../database/connect.php';

header('Content-Type: application/json');

// Hanya admin & superadmin yang bisa switch user
if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['admin', 'superadmin'])) {
   echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
   exit;
}

// GET: Ambil daftar user untuk Select2
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   $id_customer = $_SESSION['id_customer'];
   $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

   $query = "SELECT uid_user, fullname, username, roles FROM ms_users";
   if ($search !== '') {
      $query .= " AND (fullname LIKE '%$search%' OR username LIKE '%$search%' OR roles LIKE '%$search%')";
   }
   $query .= " ORDER BY fullname ASC";

   $result = mysqli_query($koneksi, $query);
   $users = [];
   while ($row = mysqli_fetch_assoc($result)) {
      $users[] = [
         'id' => $row['uid_user'],
         'text' => $row['fullname'] . ' (' . $row['roles'] . ' - ' . $row['username'] . ')'
      ];
   }

   echo json_encode(['results' => $users]);
   exit;
}

// POST: Switch ke user yang dipilih
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $uid_user = isset($_POST['uid_user']) ? mysqli_real_escape_string($koneksi, $_POST['uid_user']) : '';

   if (empty($uid_user)) {
      echo json_encode(['status' => 'error', 'message' => 'User tidak dipilih']);
      exit;
   }

   $id_customer = $_SESSION['id_customer'];
   $stmt = mysqli_prepare($koneksi, "SELECT * FROM ms_users WHERE uid_user = ? AND id_customer = ? AND status = '1' LIMIT 1");
   mysqli_stmt_bind_param($stmt, 'ss', $uid_user, $id_customer);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $datauser = mysqli_fetch_assoc($result);
   mysqli_stmt_close($stmt);

   if (!$datauser) {
      echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
      exit;
   }

   // Set session baru
   $_SESSION['uid_user'] = $datauser['uid_user'];
   $_SESSION['username'] = $datauser['username'];
   $_SESSION['roles'] = $datauser['roles'];
   $_SESSION['id_customer'] = $datauser['id_customer'];
   $_SESSION['status'] = $datauser['status'];
   $_SESSION['fullname'] = $datauser['fullname'];

   echo json_encode([
      'status' => 'success',
      'message' => 'Berhasil switch ke ' . $datauser['fullname'],
      'redirect' => 'module/admin'
   ]);
   exit;
}
