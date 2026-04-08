<?php
include '../../database/connect.php';

header('Content-Type: application/json');
session_start();

// 🔥 AMBIL SESSION
$id_customer = $_SESSION['id_customer'] ?? null;

// 🔐 VALIDASI
if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
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

// ================= GET DATA =================
function getData($id_customer)
{
   global $koneksi;

   $today = date('Y-m-d');

   $fromDate = $_GET['fromDate'] ?? $today;
   $toDate   = $_GET['toDate'] ?? $today;
   $doctor   = $_GET['doctor'] ?? null;
   $provider = $_GET['provider'] ?? null;

   $query = "SELECT 
      pasien_visit.*, 
      ms_patient.patient_name, ms_patient.nomor_rm, 
      ms_patient.patient_gender, ms_patient.patient_datebirth,
      ms_provider.provider_name
   FROM pasien_visit
   LEFT JOIN ms_patient 
      ON ms_patient.id_patient = pasien_visit.id_patient
   LEFT JOIN ms_provider 
      ON ms_provider.id_provider = pasien_visit.id_provider
   WHERE pasien_visit.id_customer = ? 
   AND pasien_visit.visit_status = 4";

   $params = [$id_customer];
   $types  = "i";

   // 🔥 FILTER TANGGAL
   $query .= " AND DATE(pasien_visit.visit_date) BETWEEN ? AND ?";
   $params[] = $fromDate;
   $params[] = $toDate;
   $types .= "ss";

   // 🔥 FILTER DOKTER
   if (!empty($doctor)) {
      $query .= " AND pasien_visit.id_doctor = ?";
      $params[] = $doctor;
      $types .= "s";
   }

   // 🔥 FILTER PROVIDER
   if (!empty($provider)) {
      $query .= " AND pasien_visit.id_provider = ?";
      $params[] = $provider;
      $types .= "s";
   }

   $query .= " ORDER BY pasien_visit.visit_date ASC";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param($types, ...$params);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ]);
}

// ================= GET BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM pasien_visit 
       WHERE id_visit=? AND id_customer=?"
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
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan'
      ]);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_visit'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
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
         'message' => 'Tidak ada perubahan'
      ]);
      return;
   }

   // 🔥 TAMBAH ID & CUSTOMER (PENTING)
   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE pasien_visit SET " . implode(',', $fields) . " 
             WHERE id_visit=? AND id_customer=?";

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

   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID kosong'
      ]);
      return;
   }

   $stmt = $koneksi->prepare(
      "DELETE FROM pasien_visit 
       WHERE id_visit=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil dihapus'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menghapus'
      ]);
   }

   $stmt->close();
}
