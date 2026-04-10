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
   session_start(); // 🔥 WAJIB

   $id_customer = $_SESSION['id_customer'] ?? null;

   if (!$id_customer) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Session id_customer tidak ditemukan'
      ]);
      exit;
   }

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   $allowedFields = [
      'id_visit',
      'billing_item',
      'billing_price',
      'billing_qty',
      'billing_discount',
      'billing_category',
      'billing_notes'
   ];

   // 🔥 TAMBAH id_customer
   $fields = ['billing_number', 'id_customer'];
   $values = [generateDoctorNumber($koneksi), $id_customer];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields));

   $query = "INSERT INTO pasien_billing ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan data: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);
   }
}
/**
 * Generate billing_number unik dengan format DCT-XXXXXX
 */
function generateDoctorNumber($koneksi)
{
   $count = 0; // inisialisasi supaya tidak merah
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $doctorNumber = "BIL-" . $random;

      // cek ke database apakah sudah ada
      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_billing WHERE billing_number = ?");
      $check->bind_param("s", $doctorNumber);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0); // ulang jika sudah ada

   return $doctorNumber;
}

function getData()
{
   global $koneksi;
   session_start(); // 🔥 WAJIB
   $id_customer = $_SESSION['id_customer'] ?? null;

   // pastikan ada parameter "no" (nomor_visit)
   $no = isset($_GET['no']) ? mysqli_real_escape_string($koneksi, $_GET['no']) : '';
   $query = "SELECT * FROM pasien_billing
          WHERE id_visit = '$no'
          AND id_customer = '$id_customer'
          ORDER BY id_billing ASC";
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
   $query = "SELECT * FROM pasien_billing    WHERE id_billing = ?";

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
   session_start(); // 🔥 WAJIB

   $id_customer = $_SESSION['id_customer'] ?? null;

   if (!$id_customer) {
      echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
      return;
   }

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_billing'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_billing'];

   $allowedFields = [
      'id_visit',
      'billing_item',
      'billing_price',
      'billing_qty',
      'billing_discount',
      'billing_category',
      'billing_notes'
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

   // 🔥 TAMBAH id_billing & id_customer
   $values[] = $id;
   $values[] = $id_customer;

   // 🔥 TYPES: semua string + id (int) + id_customer (string)
   $types = str_repeat('s', count($values) - 2) . "is";

   $query = "UPDATE pasien_billing 
             SET " . implode(',', $fields) . " 
             WHERE id_billing=? AND id_customer=?";

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


// Function untuk Delete User
function deleteData()
{
   global $koneksi;
   session_start(); // 🔥 WAJIB
   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';
   $id_customer = $_SESSION['id_customer'] ?? null;

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $query = "DELETE FROM pasien_billing WHERE id_billing = ? AND id_customer = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ss", $id, $id_customer);

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
