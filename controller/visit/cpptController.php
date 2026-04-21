<?php
include '../../database/connect.php';

// 🔥 SESSION SAFE
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      if (isset($_GET['verify'])) {
         verifyData($_GET['verify'], $id_customer);
         exit;
      }
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

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      exit;
   }

   $allowedFields = [
      'id_patient',
      'visit_ID',
      'cppt_date',
      'cppt_time',
      'subjective',
      'objective',
      'analysis',
      'planning',
      'instruction',
      'users_entry',
      'cppt_profesi'
   ];

   // 🔥 tambah id_customer
   $fields = ['cppt_number', 'id_customer'];
   $values = [generateDoctorNumber($koneksi), $id_customer];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);
   $types = str_repeat('s', count($fields));

   $stmt = $koneksi->prepare("INSERT INTO visit_cppt ($columns) VALUES ($placeholders)");

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambahkan.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => $stmt->error]);
      }

      $stmt->close();
   }
}

// ================= GENERATE NUMBER =================
function generateDoctorNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999);
      $doctorNumber = "DCT-" . $random;

      $check = $koneksi->prepare("SELECT COUNT(*) FROM visit_cppt WHERE cppt_number=?");
      $check->bind_param("s", $doctorNumber);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $doctorNumber;
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   $no = $_GET['no'] ?? '';
   $id_patient = $_GET['id_patient'] ?? '';

   $stmt = $koneksi->prepare("SELECT * FROM visit_cppt 
      LEFT JOIN ms_users ON visit_cppt.users_entry = ms_users.id_user
      WHERE visit_cppt.visit_ID=? AND visit_cppt.id_patient=? AND visit_cppt.id_customer=?
      ORDER BY visit_cppt.cppt_date ASC
   ");

   $stmt->bind_param("ssi", $no, $id_patient, $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode(['status' => 'success', 'data' => $data]);

   $stmt->close();
}

// ================= GET BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare("
      SELECT * FROM visit_cppt 
      WHERE id_cppt=? AND id_customer=?
   ");

   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $res = $stmt->get_result();

   if ($res->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $res->fetch_assoc()
      ]);
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_cppt'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_cppt'];

   $allowedFields = [
      'id_patient',
      'visit_ID',
      'cppt_date',
      'cppt_time',
      'subjective',
      'objective',
      'analysis',
      'planning',
      'instruction',
      'users_entry',
      'cppt_profesi'
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada perubahan']);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $stmt = $koneksi->prepare("
      UPDATE visit_cppt SET " . implode(',', $fields) . " 
      WHERE id_cppt=? AND id_customer=?
   ");

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
      } else {
         echo json_encode(['status' => 'error', 'message' => $stmt->error]);
      }

      $stmt->close();
   }
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
      return;
   }

   $stmt = $koneksi->prepare("
      DELETE FROM visit_cppt 
      WHERE id_cppt=? AND id_customer=?
   ");

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error']);
   }

   $stmt->close();
}

// ================= VERIFY =================
function verifyData($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare("
      UPDATE visit_cppt 
      SET verifikasi=1 
      WHERE id_cppt=? AND id_customer=?
   ");

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Berhasil diverifikasi']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}
