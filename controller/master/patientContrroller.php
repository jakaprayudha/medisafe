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

// 🔥 HANDLE FAKE PUT (INI YANG BELUM ADA)
if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
   updateData($id_customer);
   exit;
}
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

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      exit;
   }

   // AMBIL nomor RM per customer
   $stmt = $koneksi->prepare(
      "SELECT nomor_rm_end FROM setting_clinic 
       WHERE id_customer=? LIMIT 1"
   );
   $stmt->bind_param("i", $id_customer);
   $stmt->execute();
   $result = $stmt->get_result();

   if ($row = $result->fetch_assoc()) {
      $lastRM = intval($row['nomor_rm_end']);
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

   // generate nomor RM
   $newRM = $lastRM + 1;
   $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);
   $count = 0;

   // generate patient_number unik
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

   // update nomor_rm_end per customer
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
         'patient_address',
         'patient_nik',
         'patient_bpjs',

         // 🔥 TAMBAHAN
         'patient_provinsi',
         'patient_kabupaten',
         'patient_kecamatan',
         'patient_kelurahan'
      ];

   $fields = ['patient_number', 'nomor_rm', 'id_customer'];
   $values = [$patientNumber, $nomorRM, $id_customer];
   $types  = "ssi";

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
         $types .= "s";
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   $stmt = $koneksi->prepare("INSERT INTO ms_patient ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'patient_number' => $patientNumber,
         'nomor_rm' => $nomorRM
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
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
      $whereClause .= " AND (patient_nik LIKE ? OR patient_name LIKE ? OR patient_bpjs LIKE ?)";
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

   $_PUT = $_POST; // 🔥 FIX UTAMA

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
      'patient_datebirth',
      'patient_nik',
      'patient_bpjs',
      'patient_religion',
      'patient_place',
      'patient_provinsi',
      'patient_kabupaten',
      'patient_kecamatan',
      'patient_kelurahan'
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
      echo json_encode(['status' => 'success', 'message' => 'Update berhasil']);
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
   $force = $_GET['force'] ?? false;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID kosong'
      ]);
      return;
   }

   // 🔍 CEK & AMBIL SEMUA VISIT
   $visits = [];
   $getVisit = $koneksi->prepare(
      "SELECT visit_ID FROM pasien_visit WHERE id_patient=? AND id_customer=?"
   );

   $getVisit->bind_param("ii", $id, $id_customer);
   $getVisit->execute();
   $result = $getVisit->get_result();

   while ($row = $result->fetch_assoc()) {
      $visits[] = $row['visit_ID'];
   }

   $getVisit->close();

   // ❗ CEK RELASI
   if (count($visits) > 0 && !$force) {
      echo json_encode([
         'status' => 'has_relation',
         'message' => 'Ada data relasi'
      ]);
      return;
   }

   // 🚀 FORCE DELETE
   if (count($visits) > 0 && $force) {

      $koneksi->begin_transaction();

      try {

         // ubah array jadi string: 1,2,3
         $visitList = "'" . implode("','", $visits) . "'";

         // 🔥 hapus semua relasi berdasarkan visit
         $koneksi->query("DELETE FROM permintaan_ranap WHERE visit_ID_inpatient IN ($visitList)");
         $koneksi->query("DELETE FROM permintaan_pharmacy WHERE id_visit IN ($visitList)");
         $koneksi->query("DELETE FROM pasien_billing WHERE id_visit IN ($visitList)");

         // 🔥 hapus pasien_visit
         $koneksi->query("DELETE FROM pasien_visit WHERE id_patient='$id' AND id_customer='$id_customer'");

         // 🔥 hapus patient
         $stmt = $koneksi->prepare(
            "DELETE FROM ms_patient WHERE id_patient=? AND id_customer=?"
         );
         $stmt->bind_param("ii", $id, $id_customer);
         $stmt->execute();
         $stmt->close();

         $koneksi->commit();

         echo json_encode([
            'status' => 'success',
            'message' => 'Semua data berhasil dihapus'
         ]);
      } catch (Exception $e) {

         $koneksi->rollback();

         echo json_encode([
            'status' => 'error',
            'message' => 'Rollback: ' . $e->getMessage()
         ]);
      }

      return;
   }

   // 🧹 kalau tidak ada relasi → langsung hapus
   $stmt = $koneksi->prepare(
      "DELETE FROM ms_patient WHERE id_patient=? AND id_customer=?"
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
