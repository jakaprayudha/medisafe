<?php
session_start();
include '../../database/connect.php';

header('Content-Type: application/json');

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
   ]);
   exit;
}

$id_customer = $_SESSION['id_customer'];

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

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   $allowedFields = [
      'provider_code',
      'provider_name'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   // 🔥 inject id_customer di akhir (lebih aman)
   $fields[] = 'id_customer';
   $values[] = $id_customer;

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);

   // semua string kecuali id_customer (int)
   $types = str_repeat('s', count($values) - 1) . 'i';

   $query = "INSERT INTO ms_provider ($columns) VALUES ($placeholders)";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => $stmt->error
         ]);
      }

      $stmt->close();
   }
}

// ================= READ ALL =================
function getData($id_customer)
{
   global $koneksi;

   $query = "SELECT * FROM ms_provider 
             WHERE id_customer = ? 
             ORDER BY provider_name DESC";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);

   $stmt->close();
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $query = "SELECT * FROM ms_provider 
             WHERE id_provider = ? 
             AND id_customer = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $result->fetch_assoc()
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_provider'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      return;
   }

   $id = $_PUT['id_provider'];

   // 🔥 TOGGLE STATUS
   if (isset($_PUT['provider_status'])) {
      $status = $_PUT['provider_status'];

      $stmt = $koneksi->prepare(
         "UPDATE ms_provider 
          SET provider_status=? 
          WHERE id_provider=? AND id_customer=?"
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
      return;
   }

   // 🔥 UPDATE NORMAL
   $allowedFields = [
      'provider_code',
      'provider_name'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {
         $fields[] = "$f=?";
         $values[] = $_PUT[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data diupdate.'
      ]);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_provider SET " . implode(',', $fields) . " 
             WHERE id_provider=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Update gagal'
         ]);
      }

      $stmt->close();
   }
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   $query = "DELETE FROM ms_provider 
             WHERE id_provider = ? 
             AND id_customer = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil dihapus.'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menghapus.'
      ]);
   }

   $stmt->close();
}
