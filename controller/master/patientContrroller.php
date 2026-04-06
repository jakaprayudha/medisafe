<?php
include '../../database/connect.php';

header('Content-Type: application/json');

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired',
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
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   header('Content-Type: application/json');

   // ================== AMBIL DATA ==================
   $raw  = file_get_contents("php://input");
   $json = json_decode($raw, true);

   if (!empty($json)) {
      $data = $json;
   } else {
      $data = $_POST;
   }

   if (empty($data)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data kosong'
      ]);
      exit;
   }

   // ================== VALIDASI ==================
   $required = ['patient_name', 'patient_gender'];

   foreach ($required as $r) {
      if (empty($data[$r])) {
         echo json_encode([
            'status' => 'error',
            'message' => "$r wajib diisi"
         ]);
         exit;
      }
   }

   // ================== TRANSACTION ==================
   $koneksi->begin_transaction();

   try {

      // ================== AMBIL & LOCK NOMOR RM ==================
      $stmt = $koneksi->prepare(
         "SELECT nomor_rm_end FROM setting_clinic 
          WHERE id_customer=? FOR UPDATE"
      );
      $stmt->bind_param("i", $id_customer);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()) {
         $lastRM = (int)$row['nomor_rm_end'];
      } else {
         $lastRM = 0;

         $insert = $koneksi->prepare(
            "INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?,0)"
         );
         $insert->bind_param("i", $id_customer);
         $insert->execute();
         $insert->close();
      }
      $stmt->close();

      // ================== GENERATE NOMOR RM ==================
      $newRM   = $lastRM + 1;
      $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);

      // ================== GENERATE PATIENT NUMBER ==================
      do {
         $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4)));

         $check = $koneksi->prepare(
            "SELECT COUNT(*) FROM ms_patient WHERE patient_number=?"
         );
         $check->bind_param("s", $patientNumber);
         $check->execute();
         $check->bind_result($count);
         $check->fetch();
         $check->close();

      } while ($count > 0);

      // ================== UPDATE NOMOR RM ==================
      $update = $koneksi->prepare(
         "UPDATE setting_clinic 
          SET nomor_rm_end=? 
          WHERE id_customer=?"
      );
      $update->bind_param("ii", $newRM, $id_customer);
      $update->execute();
      $update->close();

      // ================== INSERT DATA PASIEN ==================
      $allowedFields = [
         'patient_name',
         'patient_gender',
         'patient_religion',
         'patient_datebirth',
         'patient_place',
         'patient_phone',
         'patient_address'
      ];

      $fields = ['patient_number', 'nomor_rm', 'id_customer'];
      $values = [$patientNumber, $nomorRM, $id_customer];
      $types  = "ssi";

      foreach ($allowedFields as $f) {
         if (isset($data[$f])) {
            $fields[] = $f;
            $values[] = $data[$f];
            $types   .= "s";
         }
      }

      $placeholders = implode(',', array_fill(0, count($fields), '?'));
      $columns      = implode(',', $fields);

      $stmt = $koneksi->prepare("INSERT INTO ms_patient ($columns) VALUES ($placeholders)");
      $stmt->bind_param($types, ...$values);

      if (!$stmt->execute()) {
         throw new Exception($stmt->error);
      }

      $stmt->close();

      // ================== COMMIT ==================
      $koneksi->commit();

      echo json_encode([
         'status' => 'success',
         'message' => 'Data pasien berhasil disimpan',
         'patient_number' => $patientNumber,
         'nomor_rm' => $nomorRM
      ]);

   } catch (Exception $e) {

      $koneksi->rollback();

      echo json_encode([
         'status' => 'error',
         'message' => $e->getMessage()
      ]);
   }
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   // SERVER-SIDE DATATABLE PARAMETERS
   $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
   $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
   $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
   $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
   
   // ORDERING
   $orderColumn = 0;
   $orderDir = 'ASC';
   
   if (isset($_GET['order'][0]['column'])) {
      $orderColumn = intval($_GET['order'][0]['column']);
      $orderDir = strtoupper($_GET['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
   }
   
   // MAP COLUMN INDEX TO FIELD NAME
   $columns = ['nomor_rm', 'patient_name', 'patient_datebirth', 'patient_gender', 'patient_religion', 'patient_phone', 'face_image', 'face_image'];
   $orderByField = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'patient_name';
   
   // BUILD WHERE CLAUSE
   $whereClause = "id_customer=?";
   $bindType = "i";
   $bindParams = [$id_customer];
   
   if (!empty($searchValue)) {
      $searchValue = "%{$searchValue}%";
      $whereClause .= " AND (nomor_rm LIKE ? OR patient_name LIKE ? OR patient_phone LIKE ?)";
      $bindType .= "sss";
      $bindParams[] = $searchValue;
      $bindParams[] = $searchValue;
      $bindParams[] = $searchValue;
   }
   
   // GET TOTAL RECORDS (all records for this customer)
   $totalStmt = $koneksi->prepare(
      "SELECT COUNT(*) as total FROM ms_patient WHERE id_customer=?"
   );
   $totalStmt->bind_param("i", $id_customer);
   $totalStmt->execute();
   $totalResult = $totalStmt->get_result()->fetch_assoc();
   $recordsTotal = $totalResult['total'];
   $totalStmt->close();
   
   // GET FILTERED RECORDS COUNT
   $filteredStmt = $koneksi->prepare(
      "SELECT COUNT(*) as total FROM ms_patient WHERE {$whereClause}"
   );
   $filteredStmt->bind_param($bindType, ...$bindParams);
   $filteredStmt->execute();
   $filteredResult = $filteredStmt->get_result()->fetch_assoc();
   $recordsFiltered = $filteredResult['total'];
   $filteredStmt->close();
   
   // GET DATA WITH PAGINATION
   $query = "SELECT * FROM ms_patient WHERE {$whereClause} ORDER BY {$orderByField} {$orderDir} LIMIT ?, ?";
   
   $dataStmt = $koneksi->prepare($query);
   $bindParams[] = $start;
   $bindParams[] = $length;
   $dataStmt->bind_param($bindType . "ii", ...$bindParams);
   $dataStmt->execute();
   
   $data = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $dataStmt->close();
   
   // RETURN DATATABLE FORMAT
   echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $data
   ]);
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_patient 
       WHERE id_patient=? AND id_customer=?"
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan']);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_patient'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }

   $id = $_PUT['id_patient'];

   $allowedFields = [
      'patient_name',
      'patient_phone',
      'patient_address',
      'patient_gender',
      'patient_datebirth'
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada update']);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_patient SET " . implode(',', $fields) . " 
             WHERE id_patient=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID kosong'
      ]);
      return;
   }

   // CEK RELASI KE pasien_visit
   $check = $koneksi->prepare(
      "SELECT COUNT(*) FROM pasien_visit 
       WHERE id_patient=?"
   );

   $count = 0;

   $check->bind_param("i", $id);
   $check->execute();
   $check->bind_result($count);
   $check->fetch();
   $check->close();

   if ($count > 0) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data Tidak Dapat Dihapus Karena Sudah Ada Riwayat Pasien Visit'
      ]);
      return;
   }

   // DELETE (kalau aman)
   $stmt = $koneksi->prepare(
      "DELETE FROM ms_patient 
       WHERE id_patient=? AND id_customer=?"
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
         'message' => 'Gagal menghapus data'
      ]);
   }

   $stmt->close();
}
