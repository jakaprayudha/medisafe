<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$id_customer = $_GET['no'] ?? $_SESSION['id_customer'] ?? null;

// 🔐 VALIDASI SESSION
if (!isset($id_customer)) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
   ]);
   exit;
}



$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      createData($id_customer);
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id'], $id_customer);
      } else {
         getData($id_customer);
      }
      break;
   case 'PUT':
      updateData($id_customer);
      break;
   case 'DELETE':
      deleteData($id_customer);
      break;
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      exit;
   }

   $allowedFields = [
      'fullname',
      'username',
      'password',
      'roles',
      'path'
   ];

   $fields = ['uid_user', 'id_customer'];
   $values = [generateUserUID($koneksi), $id_customer];
   $types  = "si";

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {

         if ($f === 'password') {
            $fields[] = $f;
            $values[] = md5($_POST[$f]);
            $types .= "s";
         } else {
            $fields[] = $f;
            $values[] = $_POST[$f];
            $types .= "s";
         }
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   $stmt = $koneksi->prepare("INSERT INTO ms_users ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

// ================= GENERATE UID =================
function generateUserUID($koneksi)
{
   $count = 0;

   do {
      $random = mt_rand(100000, 999999);
      $uid = "USR-" . md5($random);

      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_users WHERE uid_user=?");
      $check->bind_param("s", $uid);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $uid;
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_users 
       WHERE id_customer=? 
       ORDER BY username DESC"
   );

   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode(['status' => 'success', 'data' => $data]);

   $stmt->close();
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_users 
       WHERE id_user=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $res = $stmt->get_result();

   if ($res->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $res->fetch_assoc()
      ]);
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan']);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_user'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   $id = $_PUT['id_user'];
   $oldPassword = '';

   // ================= 🔥 TOGGLE STATUS =================
   if (isset($_GET['toggle_status']) && isset($_PUT['status'])) {

      $status = $_PUT['status'];

      $stmt = $koneksi->prepare(
         "UPDATE ms_users 
          SET status=? 
          WHERE id_user=? AND id_customer=?"
      );

      $stmt->bind_param("iii", $status, $id, $id_customer);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Status berhasil diupdate.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal update status.'
         ]);
      }

      $stmt->close();
      return; // ⛔ STOP di sini (penting)
   }

   // ================= 🔥 AMBIL PASSWORD LAMA =================
   $stmtOld = $koneksi->prepare(
      "SELECT password FROM ms_users 
       WHERE id_user=? AND id_customer=?"
   );

   $stmtOld->bind_param("ii", $id, $id_customer);
   $stmtOld->execute();
   $stmtOld->bind_result($oldPassword);
   $stmtOld->fetch();
   $stmtOld->close();

   // ================= 🔥 UPDATE NORMAL =================
   $allowedFields = [
      'fullname',
      'username',
      'password',
      'roles',
      'path'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {

         if ($f === 'password') {

            // skip kalau kosong
            if (trim($_PUT[$f]) === '') continue;

            $newPass = $_PUT[$f];

            // kalau beda → hash
            if ($newPass !== $oldPassword) {
               $newPass = md5($newPass);
            }

            $fields[] = "$f=?";
            $values[] = $newPass;
         } else {
            $fields[] = "$f=?";
            $values[] = $_PUT[$f];
         }
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada update'
      ]);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_users SET " . implode(',', $fields) . " 
             WHERE id_user=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil diperbarui.'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
      return;
   }

   $stmt = $koneksi->prepare(
      "DELETE FROM ms_users 
       WHERE id_user=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error']);
   }

   $stmt->close();
}
