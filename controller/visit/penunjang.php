<?php
include '../../database/connect.php';

session_start();
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
      if (!empty($_POST['id_inspection'])) {
         updateData();
      } else {
         createData();
      }
      break;

   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
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

# ================= CREATE =================
function createData()
{
   global $koneksi, $id_customer;

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_note'
   ];

   $fields = ['inspection_number', 'id_customer'];
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

   $query = "INSERT INTO visit_inspection ($columns) VALUES ($placeholders)";
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
            'message' => $stmt->error
         ]);
      }

      $stmt->close();
   }
}

# ================= READ ALL =================
function getData()
{
   global $koneksi, $id_customer;

   $no = $_GET['no'] ?? '';

   $stmt = $koneksi->prepare("
      SELECT * FROM visit_inspection 
      WHERE id_visit=? AND id_customer=? 
      ORDER BY id_inspection ASC
   ");

   $stmt->bind_param("ss", $no, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);

   $stmt->close();
}

# ================= READ BY ID =================
function getID($id)
{
   global $koneksi, $id_customer;

   $stmt = $koneksi->prepare("
      SELECT * FROM visit_inspection 
      WHERE id_inspection=? AND id_customer=?
   ");

   $stmt->bind_param("is", $id, $id_customer);
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
         'message' => 'Data tidak ditemukan'
      ]);
   }

   $stmt->close();
}

# ================= UPDATE =================
function updateData()
{
   global $koneksi, $id_customer;

   $id = $_POST['id_inspection'] ?? null;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_note'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = "$f=?";
         $values[] = $_POST[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data diupdate'
      ]);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($fields)) . "is";

   $query = "UPDATE visit_inspection SET " . implode(',', $fields) . " 
             WHERE id_inspection=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => $stmt->error
         ]);
      }

      $stmt->close();
   }
}

# ================= DELETE =================
function deleteData()
{
   global $koneksi, $id_customer;

   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   $stmt = $koneksi->prepare("
      DELETE FROM visit_inspection 
      WHERE id_inspection=? AND id_customer=?
   ");

   $stmt->bind_param("is", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil dihapus'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}

# ================= GENERATE NUMBER =================
function generateDoctorNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999);
      $number = "DCT-" . $random;

      $check = $koneksi->prepare("
         SELECT COUNT(*) FROM visit_inspection 
         WHERE inspection_number=?
      ");

      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}
