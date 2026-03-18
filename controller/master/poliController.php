<?php
session_start();
include '../../database/connect.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// 🔥 VALIDASI SESSION GLOBAL
if (!isset($_SESSION['id_customer'])) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);
   exit;
}

$id_customer = $_SESSION['id_customer'];

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
}

/* =========================
   CREATE
========================= */
function createData($id_customer)
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      return;
   }

   $allowedFields = ['poli_code', 'poli_name', 'poli_queue'];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = trim($_POST[$f]);
      }
   }

   // wajib
   $fields[] = 'id_customer';
   $values[] = $id_customer;

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   // semua string + id_customer int
   $types = str_repeat('s', count($fields) - 1) . 'i';

   $stmt = $koneksi->prepare("INSERT INTO ms_poli ($columns) VALUES ($placeholders)");

   if (!$stmt) {
      echo json_encode(['status' => 'error', 'message' => $koneksi->error]);
      return;
   }

   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambahkan.']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

/* =========================
   READ ALL (FILTER FASKES)
========================= */
function getData($id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare("SELECT * FROM ms_poli WHERE id_customer=? ORDER BY poli_name ASC");
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

/* =========================
   READ BY ID (SAFE MULTI TENANT)
========================= */
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare("SELECT * FROM ms_poli WHERE id_poli=? AND id_customer=?");
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

/* =========================
   UPDATE
========================= */
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_poli'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_poli'];

   // toggle status
   if (isset($_PUT['poli_status'])) {
      $status = $_PUT['poli_status'];

      $stmt = $koneksi->prepare("UPDATE ms_poli SET poli_status=? WHERE id_poli=? AND id_customer=?");
      $stmt->bind_param("iii", $status, $id, $id_customer);

      echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
      $stmt->close();
      return;
   }

   $allowedFields = ['poli_code', 'poli_name', 'poli_queue'];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {
         $fields[] = "$f=?";
         $values[] = trim($_PUT[$f]);
      }
   }

   if (empty($fields)) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada update']);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_poli SET " . implode(',', $fields) . " WHERE id_poli=? AND id_customer=?";
   $stmt = $koneksi->prepare($query);

   $stmt->bind_param($types, ...$values);

   echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);

   $stmt->close();
}

/* =========================
   DELETE (SAFE)
========================= */
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
      return;
   }

   $stmt = $koneksi->prepare("DELETE FROM ms_poli WHERE id_poli=? AND id_customer=?");
   $stmt->bind_param("ii", $id, $id_customer);

   echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);

   $stmt->close();
}
