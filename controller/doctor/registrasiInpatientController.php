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

// ======================================================
// GET DATA
// ======================================================
function getData()
{
   global $koneksi;

   $id_customer = $_SESSION['id_customer'] ?? null;

   if (!$id_customer) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Session tidak ditemukan'
      ]);
      exit;
   }

   // =========================
   // PARAMETER FILTER
   // =========================
   $fromDate = $_GET['fromDate'] ?? null;
   $toDate   = $_GET['toDate'] ?? null;

   // =========================
   // BASE QUERY
   // =========================
   $query = "SELECT 
      pasien_visit.*, 
      ms_patient.*, 
      ms_poli.*,
      ms_provider.provider_name,

      -- 🔥 STATUS CPPT
      CASE 
         WHEN EXISTS (
            SELECT 1 
            FROM visit_cppt vc 
            WHERE vc.visit_ID = pasien_visit.visit_ID
            LIMIT 1
         )
         THEN 1 ELSE 0
      END as status_cppt

   FROM pasien_visit

   INNER JOIN ms_patient 
      ON ms_patient.id_patient = pasien_visit.id_patient

   LEFT JOIN ms_poli 
      ON ms_poli.id_poli = pasien_visit.id_poli

   LEFT JOIN ms_provider
      ON ms_provider.id_provider = pasien_visit.id_provider

   WHERE pasien_visit.status_rawatinap = 1
   AND pasien_visit.id_customer = ?";

   // =============================
   // 🔥 FILTER TAB
   // =============================
   $tab = $_GET['tab'] ?? 'belum';

   if ($tab === 'belum') {

      // 🔴 BELUM DILAYANI
      $query .= "
         AND pasien_visit.visit_date_out IS NULL
         AND NOT EXISTS (
            SELECT 1
            FROM visit_cppt vc
            WHERE vc.visit_ID = pasien_visit.visit_ID
         )
      ";
   } elseif ($tab === 'selesai') {

      // 🟢 SUDAH DILAYANI
      $query .= "
         AND pasien_visit.visit_date_out IS NULL
         AND EXISTS (
            SELECT 1
            FROM visit_cppt vc
            WHERE vc.visit_ID = pasien_visit.visit_ID
         )
      ";
   } elseif ($tab === 'pulang') {

      // 🏠 PASIEN PULANG
      $query .= "
         AND pasien_visit.visit_date_out IS NOT NULL
      ";
   }

   // =========================
   // PREPARED PARAM
   // =========================
   $params = [$id_customer];
   $types  = "i";

   // =========================
   // FILTER TANGGAL
   // =========================
   if ($fromDate && $toDate) {

      $query .= " 
         AND DATE(pasien_visit.visit_date) 
         BETWEEN ? AND ?
      ";

      $params[] = $fromDate;
      $params[] = $toDate;

      $types .= "ss";
   }

   // =========================
   // ORDERING
   // =========================
   $query .= " ORDER BY pasien_visit.visit_date ASC";

   // =========================
   // PREPARE
   // =========================
   $stmt = $koneksi->prepare($query);

   if (!$stmt) {

      http_response_code(500);

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare failed: ' . $koneksi->error
      ]);

      return;
   }

   // =========================
   // BIND PARAM
   // =========================
   if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
   }

   // =========================
   // EXECUTE
   // =========================
   $stmt->execute();

   $result = $stmt->get_result();

   $data = $result->fetch_all(MYSQLI_ASSOC);

   $stmt->close();

   // =========================
   // RESPONSE
   // =========================
   header('Content-Type: application/json');

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}

// ======================================================
// GET BY ID
// ======================================================
function getID($iduser)
{
   global $koneksi;

   $query = "SELECT * FROM pasien_visit WHERE id_visit = ?";

   if ($stmt = $koneksi->prepare($query)) {

      $stmt->bind_param("s", $iduser);

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

// ======================================================
// UPDATE
// ======================================================
function updateData()
{
   global $koneksi;

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

   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE pasien_visit 
             SET " . implode(',', $fields) . " 
             WHERE id_visit=?";

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

// ======================================================
// DELETE
// ======================================================
function deleteData()
{
   global $koneksi;

   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);

      exit;
   }

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
