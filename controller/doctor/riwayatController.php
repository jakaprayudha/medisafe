<?php
include '../../database/connect.php';
header('Content-Type: application/json');

session_start();

// =======================
// 🔒 VALIDASI SESSION
// =======================
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session id_customer tidak ditemukan'
   ]);
   exit;
}

// =======================
// ROUTING METHOD
// =======================
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
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

// =======================
// 🔹 GET DATA (LIST)
// =======================
function getData()
{
   global $koneksi, $id_customer;

   $fromDate = $_GET['fromDate'] ?? null;
   $toDate   = $_GET['toDate'] ?? null;

   $query = "SELECT 
      pasien_visit.*, 
      ms_patient.*, 
      ms_provider.provider_name
   FROM pasien_visit
   INNER JOIN ms_patient 
      ON ms_patient.id_patient = pasien_visit.id_patient
   LEFT JOIN ms_provider
      ON ms_provider.id_provider = pasien_visit.id_provider
   WHERE pasien_visit.id_customer = ?
   AND ms_patient.id_customer = ?";

   $params = [$id_customer, $id_customer];
   $types  = "ii";

   // 🔹 filter tanggal
   if ($fromDate && $toDate) {
      $query .= " AND DATE(pasien_visit.visit_date) BETWEEN ? AND ?";
      $params[] = $fromDate;
      $params[] = $toDate;
      $types   .= "ss";
   }

   $query .= " ORDER BY pasien_visit.visit_date ASC";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare failed: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param($types, ...$params);
   $stmt->execute();

   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   $stmt->close();

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}

// =======================
// 🔹 GET BY ID
// =======================
function getID($iduser)
{
   global $koneksi, $id_customer;

   $query = "SELECT * 
   FROM pasien_visit 
   WHERE id_visit = ? 
   AND id_customer = ?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param("si", $iduser, $id_customer);
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
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare gagal.'
      ]);
   }
}

// =======================
// 🔹 UPDATE
// =======================
function updateData()
{
   global $koneksi, $id_customer;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_visit'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      return;
   }

   $id = $_PUT['id_visit'];

   $allowedFields = [
      'id_doctor',
      'id_poli',
      'visit_notes'
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

   $query = "UPDATE pasien_visit 
   SET " . implode(',', $fields) . " 
   WHERE id_visit=? AND id_customer=?";

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
            'message' => 'Update gagal: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Query error: ' . $koneksi->error
      ]);
   }
}

// =======================
// 🔹 DELETE
// =======================
function deleteData()
{
   global $koneksi, $id_customer;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      return;
   }

   $query = "DELETE FROM pasien_visit 
   WHERE id_visit = ? 
   AND id_customer = ?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param("si", $id, $id_customer);

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
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare gagal.'
      ]);
   }
}
