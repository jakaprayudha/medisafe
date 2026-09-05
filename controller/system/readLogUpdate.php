<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../../database/connect.php';

// Pastikan user login
if (!isset($_SESSION['id_customer'])) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan.'
   ]);
   exit;
}

$id_user = $_SESSION['id_customer'];

// Ambil ID update
$id_update = isset($_POST['id_update'])
   ? (int) $_POST['id_update']
   : 0;

if ($id_update <= 0) {
   echo json_encode([
      'status' => 'error',
      'message' => 'ID update tidak valid.'
   ]);
   exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah update sudah pernah dibaca user
|--------------------------------------------------------------------------
*/

$check = $koneksi->prepare("
    SELECT id
    FROM system_update_read
    WHERE id_update = ?
      AND id_user = ?
    LIMIT 1
");

$check->bind_param(
   "ii",
   $id_update,
   $id_user
);

$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {

   /*
    |--------------------------------------------------------------------------
    | Belum pernah dibaca → insert
    |--------------------------------------------------------------------------
    */

   $insert = $koneksi->prepare("
        INSERT INTO system_update_read
        (
            id_update,
            id_user,
            read_at
        )
        VALUES (?, ?, NOW())
    ");

   $insert->bind_param(
      "ii",
      $id_update,
      $id_user
   );

   $insert->execute();

   $insert->close();
}

$check->close();

echo json_encode([
   'status' => 'success',
   'message' => 'Update ditandai sudah dibaca.'
]);
