<?php
include '../../database/connect.php';

header('Content-Type: application/json');

session_start();

$id_customer = $_SESSION['id_customer'] ?? null;

// 🔐 VALIDASI SESSION
if (!$id_customer) {
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
   $count = 0; // 🔥 TAMBAH INI
   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      return;
   }

   // 🔥 CEK USERNAME DUPLIKAT
   $check = $koneksi->prepare(
      "SELECT COUNT(*) FROM ms_users WHERE username=? AND id_customer=?"
   );
   $check->bind_param("si", $_POST['username'], $id_customer);
   $check->execute();
   $check->bind_result($count);
   $check->fetch();
   $check->close();

   if ($count > 0) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Username sudah digunakan!'
      ]);
      return;
   }

   $fields = ['uid_user', 'id_customer', 'fullname', 'username', 'password', 'roles', 'path', 'kdDokter'];
   $values = [
      generateUserUID($koneksi),
      $id_customer,
      $_POST['fullname'],
      $_POST['username'],
      md5($_POST['password']),
      $_POST['roles'],
      $_POST['path'],
      $_POST['kdDokter']
   ];

   $types = "sissssss";

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   $stmt = $koneksi->prepare("INSERT INTO ms_users ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'User berhasil ditambahkan']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

// ================= GENERATE UID =================
function generateUserUID($koneksi)
{
   do {
      $random = mt_rand(100000, 999999);
      $uid = "USR-" . md5($random);
      $count = 0; // 🔥 TAMBAH INI
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

   // ================= 🔥 TOGGLE STATUS =================
   if (isset($_GET['toggle_status']) && isset($_PUT['status'])) {

      $stmt = $koneksi->prepare(
         "UPDATE ms_users 
          SET status=? 
          WHERE id_user=? AND id_customer=?"
      );

      $stmt->bind_param("iii", $_PUT['status'], $id, $id_customer);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success']);
      } else {
         echo json_encode(['status' => 'error']);
      }

      $stmt->close();
      return;
   }

   // ================= 🔥 CEK USERNAME DUPLIKAT =================
   if (isset($_PUT['username'])) {
      $count = 0; // 🔥 TAMBAH INI
      $check = $koneksi->prepare(
         "SELECT COUNT(*) FROM ms_users 
          WHERE username=? AND id_user!=? AND id_customer=?"
      );
      $check->bind_param("sii", $_PUT['username'], $id, $id_customer);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();

      if ($count > 0) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Username sudah digunakan!'
         ]);
         return;
      }
   }

   // ================= 🔥 AMBIL PASSWORD LAMA =================
   $stmtOld = $koneksi->prepare(
      "SELECT password FROM ms_users 
       WHERE id_user=? AND id_customer=?"
   );
   $oldPassword = '';
   $stmtOld->bind_param("ii", $id, $id_customer);
   $stmtOld->execute();
   $stmtOld->bind_result($oldPassword);
   $stmtOld->fetch();
   $stmtOld->close();

   // ================= 🔥 UPDATE FIELD =================
   $fields = [];
   $values = [];

   // fullname
   if (isset($_PUT['fullname'])) {
      $fields[] = "fullname=?";
      $values[] = $_PUT['fullname'];
   }

   // username
   if (isset($_PUT['username'])) {
      $fields[] = "username=?";
      $values[] = $_PUT['username'];
   }

   // password (opsional)
   if (isset($_PUT['password']) && trim($_PUT['password']) != '') {
      $fields[] = "password=?";
      $values[] = md5($_PUT['password']);
   }

   // path
   if (isset($_PUT['path'])) {
      $fields[] = "path=?";
      $values[] = $_PUT['path'];
   }

   // ❌ roles TIDAK DIUPDATE (sengaja di skip)

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada perubahan'
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
         'message' => 'Data berhasil diupdate'
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
