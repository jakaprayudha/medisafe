<?php
include '../../database/connect.php';

header('Content-Type: application/json');

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
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
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   $allowedFields = [
      'doctor_name',
      'id_poli',
      'doctor_category',
      'doctor_phone',
      'doctor_address'
   ];

   $fields = ['doctor_number'];
   $values = [generateDoctorNumber($koneksi)];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   // 🔥 inject id_customer
   $fields[] = 'id_customer';
   $values[] = $id_customer;

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);

   $types = str_repeat('s', count($values) - 1) . 'i';

   $query = "INSERT INTO ms_doctor ($columns) VALUES ($placeholders)";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal: ' . $stmt->error
         ]);
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

      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_doctor WHERE doctor_number = ?");
      $check->bind_param("s", $doctorNumber);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $doctorNumber;
}

// ================= READ ALL =================
function getData($id_customer)
{
   global $koneksi;

   $query = "SELECT d.*, p.nmPoli
             FROM ms_doctor d
             INNER JOIN master_poli p 
             ON p.kdPoli = d.id_poli 
             WHERE d.id_customer = ?
             ORDER BY d.doctor_name DESC";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);

   $stmt->close();
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $query = "SELECT * FROM ms_doctor 
             WHERE id_doctor = ? 
             AND id_customer = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ii", $id, $id_customer);
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
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_doctor'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      return;
   }

   $id = $_PUT['id_doctor'];

   // 🔥 TOGGLE STATUS
   if (isset($_PUT['doctor_status'])) {
      $status = $_PUT['doctor_status'];

      $stmt = $koneksi->prepare(
         "UPDATE ms_doctor 
          SET doctor_status=? 
          WHERE id_doctor=? AND id_customer=?"
      );

      $stmt->bind_param("iii", $status, $id, $id_customer);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Status berhasil diupdate.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal update status.'
         ]);
      }

      $stmt->close();
      return;
   }

   // 🔥 UPDATE NORMAL
   $allowedFields = [
      'doctor_nik',
      'doctor_name',
      'id_poli',
      'doctor_subspesialis',
      'doctor_category',
      'doctor_phone',
      'doctor_mail',
      'doctor_address',
      'doctor_gender',
      'doctor_region'
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
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_doctor SET " . implode(',', $fields) . " 
             WHERE id_doctor=? AND id_customer=?";

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
   }
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   $query = "DELETE FROM ms_doctor 
             WHERE id_doctor = ? 
             AND id_customer = ?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ii", $id, $id_customer);

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
}
