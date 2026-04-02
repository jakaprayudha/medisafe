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

// Function untuk Create



function getData()
{
   global $koneksi;

   // Ambil parameter tanggal (opsional)
   $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : null;
   $toDate   = isset($_GET['toDate']) ? $_GET['toDate'] : null;

   // Base query dengan filter source_hub = 'Rawat Inap'
   $query = "SELECT 
      pasien_visit.*, 
      ms_patient.*, 
      ms_doctor.*, 
      ms_room.*,
      ms_room_bed.*
   FROM pasien_visit
   INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
   INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID
   INNER JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room
   INNER JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed
   WHERE pasien_visit.status_rawatinap = 1";

   // Jika ada filter tanggal (contoh pakai visit_date)
   if ($fromDate && $toDate) {
      $query .= " AND DATE(pasien_visit.visit_date) BETWEEN ? AND ?";
   }

   $query .= " ORDER BY pasien_visit.visit_date ASC";

   if ($stmt = $koneksi->prepare($query)) {
      if ($fromDate && $toDate) {
         $stmt->bind_param("ss", $fromDate, $toDate);
      }

      $stmt->execute();
      $result = $stmt->get_result();
      $data = $result->fetch_all(MYSQLI_ASSOC);
      $stmt->close();

      header('Content-Type: application/json');
      echo json_encode([
         'status' => 'success',
         'data' => $data,
      ]);
   } else {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);
   }
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
