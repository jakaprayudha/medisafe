<?php
session_start();
include '../../database/connect.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      createData();
      break;

   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;

   case 'PUT':
      updateData();
      break;

   case 'DELETE':
      deleteData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// ================= CREATE =================
function createData()
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

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);

   $types = str_repeat('s', count($values));

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
function getData()
{
   global $koneksi;

   $query = "SELECT * FROM ms_provider ORDER BY provider_name DESC";

   $result = $koneksi->query($query);
   $data = $result->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}

// ================= READ BY ID =================
function getID($id)
{
   global $koneksi;

   $query = "SELECT * FROM ms_provider WHERE id_provider = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("i", $id);
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
function updateData()
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
         "UPDATE ms_provider SET provider_status=? WHERE id_provider=?"
      );

      $stmt->bind_param("ii", $status, $id);

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

   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE ms_provider SET " . implode(',', $fields) . " 
             WHERE id_provider=?";

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
function deleteData()
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

   $query = "DELETE FROM ms_provider WHERE id_provider = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("i", $id);

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
