
<?php
session_start();
include '../../database/connect.php';

header('Content-Type: application/json');

// ✅ ambil id_customer dari session
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session id_customer tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// =======================
// 🔹 GET DATA (by RM)
// =======================
function getData()
{
   global $koneksi, $id_customer;

   $visit_ID = $_GET['visit'] ?? '';

   if (!$visit_ID) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter visit wajib diisi'
      ]);
      return;
   }

   // 🔥 1. ambil id_patient dari visit
   $getPatient = $koneksi->prepare("
      SELECT id_patient 
      FROM pasien_visit 
      WHERE visit_ID = ? 
      AND id_customer = ?
      LIMIT 1
   ");

   $getPatient->bind_param("ss", $visit_ID, $id_customer);
   $getPatient->execute();
   $resultPatient = $getPatient->get_result();

   if ($resultPatient->num_rows === 0) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Visit tidak ditemukan'
      ]);
      return;
   }

   $rowPatient = $resultPatient->fetch_assoc();
   $id_patient = $rowPatient['id_patient'];

   // 🔥 2. ambil semua riwayat berdasarkan id_patient
   $stmt = $koneksi->prepare("
      SELECT * 
      FROM pasien_visit 
      WHERE id_patient = ?
      AND id_customer = ?
      ORDER BY visit_date ASC
   ");

   $stmt->bind_param("ss", $id_patient, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   $data = [];
   while ($row = $result->fetch_assoc()) {
      $data[] = $row;
   }

   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// =======================
// 🔹 GET BY ID (billing)
// =======================
function getID($iduser)
{
   global $koneksi, $id_customer;

   $query = "SELECT * 
   FROM pasien_billing 
   WHERE id_billing = ? 
   AND id_customer = ?";

   if ($stmt = $koneksi->prepare($query)) {

      $stmt->bind_param("ss", $iduser, $id_customer);
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
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
