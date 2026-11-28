<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
if (empty($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}
function ctLog($id, ?array $lama, string $ket): void
{
   global $koneksi;
   $r = $lama ?? logAmbilBaris($koneksi, 'ms_counter', 'id_counter', $id) ?? [];
   logBaris($koneksi, 'master', 'ms_counter', 'id_counter', $id, $lama, ['tipe' => 'counter', 'label' => trim(($r['counter_code'] ?? '') . ' · ' . ($r['counter_name'] ?? ''), ' ·')], $ket);
}
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

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   // Ambil semua field yang valid untuk tabel ms_counter
   $allowedFields = [
      'counter_code',
      'counter_name',
      'counter_type'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);
      exit;
   }

   // Buat placeholder dan tipe untuk prepared statement
   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields)); // semua string (varchar), bisa ubah jika ada int

   $query = "INSERT INTO ms_counter ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         ctLog($stmt->insert_id, null, 'Counter antrean ditambahkan');
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

function getData()
{
   global $koneksi;

   $query = "SELECT * FROM ms_counter  ORDER BY counter_name DESC";
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
   $query = "SELECT * FROM ms_counter WHERE id_counter = ?";

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

   if (empty($_PUT['id_counter'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_counter'];
   $allowedFields = [
      'counter_code',
      'counter_name',
      'counter_type'
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

   $query = "UPDATE ms_counter SET " . implode(',', $fields) . " WHERE id_counter=?";
   $ctLama = logAmbilBaris($koneksi, 'ms_counter', 'id_counter', $id);
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         if ($ctLama) ctLog($id, $ctLama, 'Counter antrean diubah');
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
   $query = "DELETE FROM ms_counter WHERE id_counter = ?";
   $ctLama = logAmbilBaris($koneksi, 'ms_counter', 'id_counter', $id);

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
         if ($ctLama && $stmt->affected_rows > 0) ctLog($id, $ctLama, 'Counter antrean dihapus');
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
