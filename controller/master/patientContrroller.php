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

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      exit;
   }

   // 🔥 AMBIL nomor RM per customer
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

   // 🔥 generate nomor RM
   $newRM = $lastRM + 1;
   $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);
   $count = 0;

   // 🔥 generate patient_number unik
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

   // 🔥 update nomor_rm_end per customer
   $update = $koneksi->prepare(
      "UPDATE setting_clinic 
       SET nomor_rm_end=? 
       WHERE id_customer=?"
   );
   $update->bind_param("ii", $newRM, $id_customer);
   $update->execute();
   $update->close();

   // 🔥 fields
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

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_patient 
       WHERE id_customer=? 
       ORDER BY patient_name DESC"
   );

   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode(['status' => 'success', 'data' => $data]);

   $stmt->close();
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

   // 🔥 CEK RELASI KE pasien_visit
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

   // 🔥 DELETE (kalau aman)
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
