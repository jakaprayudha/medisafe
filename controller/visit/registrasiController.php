<?php
include '../../database/connect.php';
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
      // Update User
      updateData();
      break;

   case 'DELETE':
      // Delete User
      deleteData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

function getData()
{
   global $koneksi;

   if (session_status() === PHP_SESSION_NONE) {
      session_start();
   }

   header('Content-Type: application/json');

   $id_customer = $_SESSION['id_customer'] ?? null;
   if (!$id_customer) {
      http_response_code(401);
      echo json_encode([
         'status' => 'error',
         'message' => 'Session tidak valid / expired'
      ]);
      return;
   }

   $today = date('Y-m-d');

   // ================= PARAMETER =================
   $fromDate = !empty($_GET['fromDate']) ? $_GET['fromDate'] : $today;
   $toDate   = !empty($_GET['toDate']) ? $_GET['toDate'] : $today;
   $doctor   = !empty($_GET['doctor']) ? $_GET['doctor'] : null;
   $provider = !empty($_GET['provider']) ? $_GET['provider'] : null;
   $poli     = !empty($_GET['poli']) ? $_GET['poli'] : null;
   $tipe_pasien = !empty($_GET['tipe_pasien']) ? $_GET['tipe_pasien'] : null;

   $query = "SELECT 
      pasien_visit.*, 
      ms_patient.patient_name, ms_patient.nomor_rm, 
      ms_patient.patient_gender, ms_patient.patient_datebirth,
      ms_poli.poli_name,
      ms_provider.provider_name,
      ms_patient.patient_bpjs
   FROM pasien_visit
   LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   LEFT JOIN ms_poli ON ms_poli.id_poli = pasien_visit.id_poli
   LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider
   WHERE 1=1";

   $params = [];
   $types  = "";

   // 🔹 tenant/clinic
   $query .= " AND pasien_visit.id_customer = ?";
   $params[] = $id_customer;
   $types .= "i";

   // 🔹 tanggal
   $query .= " AND DATE(pasien_visit.visit_date) BETWEEN ? AND ?";
   $params[] = $fromDate;
   $params[] = $toDate;
   $types .= "ss";

   // 🔹 dokter
   if (!empty($doctor)) {
      $query .= " AND pasien_visit.id_doctor = ?";
      $params[] = $doctor;
      $types .= "s";
   }

   // 🔹 provider
   if (!empty($provider)) {
      $query .= " AND pasien_visit.id_provider = ?";
      $params[] = $provider;
      $types .= "s";
   }

   // 🔹 poli
   if (!empty($poli)) {
      $query .= " AND pasien_visit.id_poli = ?";
      $params[] = $poli;
      $types .= "s";
   }

   // 🔥 🔥 TIPE PASIEN
   if (!empty($tipe_pasien)) {
      $query .= " AND pasien_visit.source_hub = ?";
      $params[] = $tipe_pasien;
      $types .= "s";
   }

   $query .= " ORDER BY pasien_visit.visit_date ASC";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare failed: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param($types, ...$params);

   if (!$stmt->execute()) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Execute failed: ' . $stmt->error
      ]);
      return;
   }

   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   $stmt->close();

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ]);
}
// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM pasien_visit WHERE id_visit = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $data = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'data' => $data
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



function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_visit'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']);
      return;
   }

   $values[] = $id;
   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE pasien_visit SET " . implode(',', $fields) . " WHERE id_visit=?";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}



// Function untuk Delete User
function deleteData()
{
   global $koneksi;

   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $query = "DELETE FROM pasien_visit WHERE id_visit = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

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
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
