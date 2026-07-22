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
function getData(){
   global $koneksi;
   $role = $_SESSION['roles'] ?? null;
   $id_customer = $_SESSION['id_customer'] ?? null;
   if (!$id_customer) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Session tidak ditemukan'
      ]);
      exit;
   }
   $fromDate   = $_GET['fromDate'] ?? null;
   $toDate     = $_GET['toDate'] ?? null;
   $doctorName = $_GET['doctorName'] ?? null;
   $kdDokter   = $_GET['kdDokter'] ?? null;
   $query = "
        SELECT
            pasien_visit.*,
            ms_patient.*,
            ms_provider.provider_name,
            ap.status AS status_panggil
        FROM pasien_visit
        LEFT JOIN ms_patient
            ON ms_patient.id_patient = pasien_visit.id_patient
        LEFT JOIN ms_provider
            ON ms_provider.id_provider = pasien_visit.id_provider
        LEFT JOIN antrian_poli ap
            ON ap.nomor_visit = pasien_visit.visit_ID
            AND ap.id_customer = pasien_visit.id_customer
        WHERE pasien_visit.source_hub <> 'Rawat Inap'
        AND pasien_visit.id_customer = ?
    ";
   $params = [];
   $types  = "";
   // id_customer
   $params[] = $id_customer;
   $types .= "s";
   // Filter tanggal
   if (!empty($fromDate) && !empty($toDate)) {
      $query .= " AND pasien_visit.visit_date BETWEEN ? AND ?";
      $params[] = $fromDate;
      $params[] = $toDate;
      $types .= "ss";
   }
   // Filter dokter
   if ($role != "admin" && (!empty($doctorName) || !empty($kdDokter))) {
      $query .= " AND (";
      $filter = [];
      if (!empty($doctorName)) {
         $doctorNameClean = preg_replace('/^dr\.?\s*/i', '', $doctorName);
         $filter[] = "REPLACE(LOWER(pasien_visit.id_doctor),'dr. ','') LIKE ?";
         $params[] = "%" . strtolower($doctorNameClean) . "%";
         $types .= "s";
      }
      if (!empty($kdDokter)) {
         $filter[] = "pasien_visit.code_doctor = ?";
         $params[] = $kdDokter;
         $types .= "s";
      }
      $query .= implode(" OR ", $filter);
      $query .= ")";
   }
   // ==========================
   // ORDER ANTRIAN
   // ==========================
   $query .= "
        ORDER BY
        CASE
            WHEN pasien_visit.visit_status = 0 THEN 1
            WHEN pasien_visit.visit_status = 1 THEN 2
            WHEN pasien_visit.visit_status = 4 THEN 3
            ELSE 4
        END,
        LEFT(pasien_visit.visit_antrian,1),
        CAST(
            SUBSTRING(pasien_visit.visit_antrian,2)
            AS UNSIGNED
        ),
        pasien_visit.visit_time ASC
    ";
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
   $result = $stmt->get_result();
   $data = [];
   while ($row = $result->fetch_assoc()) {
      $data[] = $row;
   }
   $stmt->close();
   echo json_encode([
      "status" => "success",
      "data" => $data
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
