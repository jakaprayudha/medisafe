<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'POST':
      createData();
      break;
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
function createData()
{
   global $koneksi;

   header('Content-Type: application/json');

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   // ===============================
   // 🔥 GENERATE ID CUSTOMER (COUNT + 1)
   // ===============================
   $q = mysqli_query($koneksi, "
      SELECT COUNT(*) as total 
      FROM setting_clinic
   ");

   if (!$q) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Query gagal: ' . mysqli_error($koneksi)
      ]);
      exit;
   }

   $d = mysqli_fetch_assoc($q);
   $total = (int)$d['total'];

   $id_customer = $total + 1;

   // ===============================
   // FIELD YANG DIIZINKAN
   // ===============================
   $allowedFields = [
      'clinic_name'
   ];

   $fields = ['id_customer'];
   $values = [$id_customer];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   if (count($fields) <= 1) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);
      exit;
   }

   // ===============================
   // PREPARE QUERY
   // ===============================
   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);

   $types = 'i' . str_repeat('s', count($fields) - 1);

   $query = "INSERT INTO setting_clinic ($columns) VALUES ($placeholders)";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare gagal: ' . $koneksi->error
      ]);
      exit;
   }

   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil ditambahkan.',
         'id_customer' => $id_customer
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal insert: ' . $stmt->error
      ]);
   }

   $stmt->close();
}

function getData()
{
   global $koneksi;

   $query = "SELECT setting_clinic.*, ms_faskes.contract_number, ms_faskes.faskes_code, ms_faskes.contract_start, ms_faskes.contract_end, ms_faskes.contract_amount, ms_users.fullname, ms_users.username, ms_users.password FROM setting_clinic LEFT JOIN ms_faskes ON setting_clinic.id = ms_faskes.id_clinic LEFT JOIN ms_users ON ms_users.id_customer = setting_clinic.id_customer WHERE setting_clinic.status != 99 GROUP BY setting_clinic.id ORDER BY setting_clinic.id DESC";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM setting_clinic WHERE id = ?";

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

   if (empty($_PUT['id_faskes'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_faskes'];
   $allowedFields = [
      'faskes_code',
      'faskes_name',
      'pic_name',
      'pic_phone',
      'pic_email',
      'pic_phone',
      'faskes_address',
      'faskes_prov',
      'faskes_city',
      'faskes_district',
      'faskes_village',
      'faskes_status',
      'contract_date',
      'faskes_payment',
      'contract_amount',
      'contract_start',
      'contract_end',
      'contract_number'
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

   $query = "UPDATE ms_faskes SET " . implode(',', $fields) . " WHERE id_clinic=?";
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
   $query = "UPDATE setting_clinic SET status = 99 WHERE id = ?";

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
